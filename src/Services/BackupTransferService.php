<?php

namespace Leazycms\Web\Services;

use ZipArchive;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupTransferService
{
    public function export(Request $request)
    {
        $multisite = (bool) config('modules.multisite_enabled');
        $isTenantScope = $multisite && app()->has('tenant') && !is_main_domain();
        $isMainDomain = $multisite && is_main_domain();

        $scopeTenantId = $isTenantScope ? tenant()->id : null;
        $scope = $isTenantScope ? 'tenant' : 'induk';

        $includeTenantId = $isMainDomain;
        $includeTenantTables = $isMainDomain;
        $filterByTenant = $isTenantScope;
        $filterByHost = $isTenantScope;

        $host = $request->getHost();
        $dbName = DB::connection()->getDatabaseName();

        $exportId = Str::uuid()->toString();
        $baseDir = storage_path('app/leazycms-transfer');
        $tmpDir = $baseDir . '/tmp/' . $exportId;
        $outDir = $baseDir . '/exports';

        File::ensureDirectoryExists($tmpDir);
        File::ensureDirectoryExists($outDir);

        $manifest = [
            'id' => $exportId,
            'created_at' => now()->toIso8601String(),
            'host' => $host,
            'scope' => $scope,
            'source_multisite_enabled' => $multisite,
            'include_tenant_id' => $includeTenantId,
            'app_url' => config('app.url'),
        ];

        $tables = $this->listTables($dbName, [
            'include_tenant_tables' => $includeTenantTables,
            'multisite' => $multisite,
        ]);
        $tableMeta = $this->getTablesMeta($dbName, $tables);

        $dbPayload = [
            'meta' => [
                'database' => $dbName,
                'created_at' => $manifest['created_at'],
                'host' => $host,
                'scope' => $scope,
                'source_multisite_enabled' => $multisite,
                'include_tenant_id' => $includeTenantId,
            ],
            'data' => [],
        ];

        $relationCache = [];
        foreach ($tables as $table) {
            $meta = $tableMeta[$table] ?? null;
            if (!$meta) {
                continue;
            }

            $query = DB::table($table);

            if ($filterByTenant && in_array('tenant_id', $meta['columns'] ?? [], true)) {
                $query->where('tenant_id', $scopeTenantId);
            }

            if ($filterByTenant && $table === 'tenants') {
                $query->where('id', $scopeTenantId);
            }

            if ($filterByHost && in_array('host', $meta['columns'] ?? [], true)) {
                $query->where('host', $host);
            }

            $rows = $query->get();
            $records = [];

            foreach ($rows as $row) {
                $attributes = (array) $row;
                if (!$includeTenantId) {
                    unset($attributes['tenant_id']);
                }

                $pk = $meta['primary_key'];
                $key = $this->makeRecordKey($table, $attributes, $pk);

                $relations = [];
                foreach ($meta['foreign_keys'] as $fk) {
                    $col = $fk['column'];
                    $refTable = $fk['referenced_table'];
                    $refColumn = $fk['referenced_column'] ?? 'id';
                    $refVal = $attributes[$col] ?? null;
                    if ($refVal === null || $refVal === '') {
                        continue;
                    }
                    $relations[$col] = $this->makeResolvedRelationKey(
                        $refTable,
                        $refColumn,
                        $refVal,
                        $tableMeta,
                        $dbName,
                        $relationCache
                    );
                }

                if ($includeTenantId && array_key_exists('tenant_id', $attributes) && $attributes['tenant_id'] !== null) {
                    $relations['tenant_id'] ??= $this->makeResolvedRelationKey(
                        'tenants',
                        'id',
                        $attributes['tenant_id'],
                        $tableMeta,
                        $dbName,
                        $relationCache
                    );
                }

                $records[] = [
                    'key' => $key,
                    'attributes' => $attributes,
                    'relations' => $relations,
                ];
            }

            $dbPayload['data'][$table] = $records;
        }

        $manifestPath = $tmpDir . '/manifest.json';
        $dbPath = $tmpDir . '/database.json';
        File::put($manifestPath, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        File::put($dbPath, json_encode($dbPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $zipPath = $outDir . '/backup-' . ($isTenantScope ? ('tenant-' . $scopeTenantId) : 'induk') . '-' . now()->format('Ymd-His') . '.zip';
        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            File::deleteDirectory($tmpDir);
            return back()->with('danger', 'Gagal membuat file ZIP.');
        }

        $zip->addFile($manifestPath, 'manifest.json');
        $zip->addFile($dbPath, 'database.json');

        $this->zipStorageFiles($zip, $host, $isTenantScope);

        $zip->close();
        File::deleteDirectory($tmpDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:zip'],
        ]);

        $multisite = (bool) config('modules.multisite_enabled');
        $isTenantScope = $multisite && app()->has('tenant') && !is_main_domain();
        $forceTenantId = $isTenantScope ? tenant()->id : null;
        $host = $request->getHost();

        $importId = Str::uuid()->toString();
        $baseDir = storage_path('app/leazycms-transfer');
        $tmpDir = $baseDir . '/tmp/' . $importId;
        File::ensureDirectoryExists($tmpDir);

        $zipPath = $request->file('backup_file')->getRealPath();
        $zip = new ZipArchive();
        $opened = $zip->open($zipPath);
        if ($opened !== true) {
            File::deleteDirectory($tmpDir);
            return back()->with('danger', 'File ZIP tidak bisa dibuka.');
        }

        $zip->extractTo($tmpDir);
        $zip->close();

        $dbFile = $tmpDir . '/database.json';
        if (!File::exists($dbFile)) {
            File::deleteDirectory($tmpDir);
            return back()->with('danger', 'Format backup tidak valid (database.json tidak ditemukan).');
        }

        $dbPayload = json_decode(File::get($dbFile), true);
        if (!is_array($dbPayload) || !isset($dbPayload['data']) || !is_array($dbPayload['data'])) {
            File::deleteDirectory($tmpDir);
            return back()->with('danger', 'Format database.json tidak valid.');
        }

        $replace = $request->boolean('replace');
        $replaceNonTenant = $request->boolean('replace_non_tenant');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            if ($replace) {
                $this->truncateScope(array_keys($dbPayload['data']), $forceTenantId, $host, $replaceNonTenant);
            }

            $this->restoreStorageFiles($tmpDir);
            $report = $this->importDatabase($dbPayload, $forceTenantId, $host);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (Throwable $ignored) {
            }
            File::deleteDirectory($tmpDir);
            return back()->with('danger', 'Import gagal: ' . $e->getMessage());
        }

        File::deleteDirectory($tmpDir);
        return back()->with('success', 'Import berhasil. ' . ($report ? json_encode($report) : ''));
    }

    private function importDatabase(array $dbPayload, ?int $forceTenantId, string $host): array
    {
        $data = $dbPayload['data'] ?? [];
        $dbName = DB::connection()->getDatabaseName();

        $idMap = [];
        $inserted = [];

        $tables = array_keys($data);
        $priority = [];
        foreach (['themes', 'tenants'] as $t) {
            if (in_array($t, $tables, true)) {
                $priority[] = $t;
            }
        }
        $ordered = array_values(array_unique(array_merge($priority, $tables)));

        $tablesWithPk = [];
        $tablesNoPk = [];
        foreach ($ordered as $table) {
            $pk = $this->getPrimaryKey($dbName, $table);
            if ($pk) {
                $tablesWithPk[] = $table;
            } else {
                $tablesNoPk[] = $table;
            }
        }

        foreach (array_merge($tablesWithPk, $tablesNoPk) as $table) {
            if (!isset($data[$table]) || !is_array($data[$table])) {
                continue;
            }

            $columns = $this->getTableColumns($dbName, $table);
            if (!$columns) {
                continue;
            }

            $pk = $this->getPrimaryKey($dbName, $table);
            $pkDef = $pk ? $this->getColumnDefinition($dbName, $table, $pk) : null;
            $pkAuto = (bool) ($pkDef['auto'] ?? false);
            $pkType = $pkDef['type'] ?? null;

            foreach ($data[$table] as $record) {
                if (!isset($record['attributes']) || !is_array($record['attributes'])) {
                    continue;
                }

                $attrs = array_intersect_key($record['attributes'], array_flip($columns));
                if ($pk) {
                    unset($attrs[$pk]);
                }

                $relations = (isset($record['relations']) && is_array($record['relations'])) ? $record['relations'] : [];

                if ($pk && $relations) {
                    foreach ($relations as $fkCol => $refKey) {
                        $refTable = $this->parseRelationTable($refKey);
                        if ($refTable === 'users') {
                            continue;
                        }
                        if (array_key_exists($fkCol, $attrs)) {
                            $attrs[$fkCol] = null;
                        }
                    }
                }

                if ($forceTenantId !== null && in_array('tenant_id', $columns, true)) {
                    $attrs['tenant_id'] = $forceTenantId;
                }

                if ($forceTenantId !== null && in_array('host', $columns, true)) {
                    $attrs['host'] = $host;
                }

                if ($pk && isset($record['key']) && is_string($record['key'])) {
                    if ($table === 'categories' && in_array('slug', $columns, true) && in_array('type', $columns, true)) {
                        $existing = DB::table('categories')
                            ->where('slug', $attrs['slug'] ?? null)
                            ->where('type', $attrs['type'] ?? null);

                        if (in_array('tenant_id', $columns, true)) {
                            $matchTenantId = $forceTenantId ?? ($attrs['tenant_id'] ?? null);
                            if ($matchTenantId !== null) {
                                $existing->where('tenant_id', $matchTenantId);
                            } else {
                                $existing->whereNull('tenant_id');
                            }
                        }
                        if (in_array('deleted_at', $columns, true)) {
                            $existing->whereNull('deleted_at');
                        }

                        $existingId = $existing->value($pk);
                        if ($existingId) {
                            $idMap[$record['key']] = $existingId;
                            continue;
                        }
                    }

                    if ($table === 'files' && in_array('host', $columns, true) && in_array('file_path', $columns, true)) {
                        $matchHost = $forceTenantId !== null ? $host : ($attrs['host'] ?? null);
                        if ($matchHost !== null) {
                            $existing = DB::table('files')
                                ->where('host', $matchHost)
                                ->where('file_path', $attrs['file_path'] ?? null);
                            if (in_array('disk', $columns, true)) {
                                $existing->where('disk', $attrs['disk'] ?? null);
                            }
                            $existingId = $existing->value($pk);
                            if ($existingId) {
                                $idMap[$record['key']] = $existingId;
                                continue;
                            }
                        }
                    }
                }

                if ($pk) {
                    if ($pkAuto) {
                        $newId = DB::table($table)->insertGetId($attrs);
                        if (isset($record['key']) && is_string($record['key'])) {
                            $idMap[$record['key']] = $newId;
                        }
                    } else {
                        $newPk = $this->generatePrimaryKey($pkType);
                        if (in_array($pk, $columns, true)) {
                            $attrs[$pk] = $newPk;
                        }
                        DB::table($table)->insert($attrs);
                        if (isset($record['key']) && is_string($record['key'])) {
                            $idMap[$record['key']] = $newPk;
                        }
                    }
                } else {
                    if ($relations) {
                        foreach ($relations as $fkCol => $refKey) {
                            if (!is_string($refKey) || !array_key_exists($fkCol, $attrs)) {
                                continue;
                            }

                            if (isset($idMap[$refKey])) {
                                $attrs[$fkCol] = $idMap[$refKey];
                                continue;
                            }

                            $externalId = $this->resolveExternalRelationId($refKey);
                            if ($externalId !== null) {
                                $attrs[$fkCol] = $externalId;
                            }
                        }
                    }
                    DB::table($table)->insert($attrs);
                }

                $inserted[$table] = ($inserted[$table] ?? 0) + 1;
            }
        }

        foreach ($tablesWithPk as $table) {
            if (!isset($data[$table]) || !is_array($data[$table])) {
                continue;
            }

            $pk = $this->getPrimaryKey($dbName, $table);
            if (!$pk) {
                continue;
            }

            foreach ($data[$table] as $record) {
                if (!isset($record['key'], $record['relations']) || !is_array($record['relations'])) {
                    continue;
                }
                if (!isset($idMap[$record['key']])) {
                    continue;
                }

                $updates = [];
                foreach ($record['relations'] as $fkCol => $refKey) {
                    if (!is_string($refKey)) {
                        continue;
                    }

                    if ($forceTenantId !== null && $fkCol === 'tenant_id') {
                        continue;
                    }

                    if (isset($idMap[$refKey])) {
                        $updates[$fkCol] = $idMap[$refKey];
                        continue;
                    }

                    $externalId = $this->resolveExternalRelationId($refKey);
                    if ($externalId !== null) {
                        $updates[$fkCol] = $externalId;
                    }
                }
                if ($updates) {
                    DB::table($table)->where($pk, $idMap[$record['key']])->update($updates);
                }
            }
        }

        return [
            'inserted' => $inserted,
            'source_host' => $dbPayload['meta']['host'] ?? null,
        ];
    }

    private function truncateScope(array $tables, ?int $forceTenantId, string $host, bool $replaceNonTenant): void
    {
        $dbName = DB::connection()->getDatabaseName();

        foreach ($tables as $table) {
            $columns = $this->getTableColumns($dbName, $table);
            if (!$columns) {
                continue;
            }

            if ($forceTenantId !== null) {
                if (in_array('tenant_id', $columns, true)) {
                    DB::table($table)->where('tenant_id', $forceTenantId)->delete();
                    continue;
                }
                if (in_array('host', $columns, true)) {
                    DB::table($table)->where('host', $host)->delete();
                }
                continue;
            }

            if ($replaceNonTenant) {
                DB::table($table)->truncate();
            }
        }
    }

    private function restoreStorageFiles(string $extractedDir): void
    {
        $storageDir = $extractedDir . '/storage';
        if (!File::isDirectory($storageDir)) {
            return;
        }

        $disks = File::directories($storageDir);
        foreach ($disks as $diskDir) {
            $disk = basename($diskDir);
            $files = File::allFiles($diskDir);
            foreach ($files as $file) {
                $relative = str_replace('\\', '/', $file->getRelativePathname());
                $targetPath = Storage::disk($disk)->path($relative);
                File::ensureDirectoryExists(dirname($targetPath));
                File::copy($file->getPathname(), $targetPath);
            }
        }
    }

    private function zipStorageFiles(ZipArchive $zip, string $host, bool $filterByHost): void
    {
        $query = DB::table('files')->select(['file_path', 'disk', 'file_name', 'host']);
        if ($filterByHost && $this->tableHasColumn('files', 'host')) {
            $query->where('host', $host);
        }
        $files = $query->get();

        foreach ($files as $file) {
            $disk = $file->disk ?: config('filesystems.default');
            $path = $file->file_path;
            if (!$disk || !$path) {
                continue;
            }

            if (!Storage::disk($disk)->exists($path)) {
                continue;
            }

            $source = Storage::disk($disk)->path($path);
            if (!is_file($source)) {
                continue;
            }

            $entry = 'storage/' . $disk . '/' . str_replace('\\', '/', ltrim($path, '/'));
            $zip->addFile($source, $entry);
        }
    }

    private function listTables(string $dbName, array $opts): array
    {
        $rows = DB::select('SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE" ORDER BY TABLE_NAME', [$dbName]);
        $tables = array_map(fn($r) => $r->name, $rows);

        $ignore = [
            'users',
            'migrations',
            'failed_jobs',
            'jobs',
            'job_batches',
            'password_reset_tokens',
            'password_resets',
            'sessions',
            'cache',
            'cache_locks',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
            'analytics_visitors',
            'analytics_daily',
        ];

        $multisite = (bool) ($opts['multisite'] ?? false);
        $includeTenantTables = (bool) ($opts['include_tenant_tables'] ?? false);
        if (!$multisite || !$includeTenantTables) {
            $ignore[] = 'tenants';
            $ignore[] = 'themes';
        }

        return array_values(array_filter($tables, fn($t) => !in_array($t, $ignore, true)));
    }

    private function getTableColumns(string $dbName, string $table): array
    {
        $rows = DB::select(
            'SELECT COLUMN_NAME as name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$dbName, $table]
        );
        return array_map(fn($r) => $r->name, $rows);
    }

    private function getTablesMeta(string $dbName, array $tables): array
    {
        $meta = [];
        foreach ($tables as $table) {
            $columns = $this->getTableColumns($dbName, $table);
            $primaryKey = $this->getPrimaryKey($dbName, $table);
            $pkDef = $primaryKey ? $this->getColumnDefinition($dbName, $table, $primaryKey) : null;
            $foreignKeys = $this->getForeignKeys($dbName, $table);

            $fkByColumn = [];
            foreach ($foreignKeys as $fk) {
                $fkByColumn[$fk['column']] = true;
            }

            foreach ($columns as $col) {
                if (!str_ends_with($col, '_id') || $col === 'tenant_id') {
                    continue;
                }
                if (isset($fkByColumn[$col])) {
                    continue;
                }
                $guess = Str::plural(substr($col, 0, -3));
                if (!in_array($guess, $tables, true)) {
                    continue;
                }
                $foreignKeys[] = [
                    'column' => $col,
                    'referenced_table' => $guess,
                    'referenced_column' => 'id',
                ];
                $fkByColumn[$col] = true;
            }

            $meta[$table] = [
                'columns' => $columns,
                'primary_key' => $primaryKey,
                'primary_key_type' => $pkDef['type'] ?? null,
                'primary_key_auto' => (bool) ($pkDef['auto'] ?? false),
                'foreign_keys' => $foreignKeys,
            ];
        }
        return $meta;
    }

    private function getColumnDefinition(string $dbName, string $table, string $column): ?array
    {
        $rows = DB::select(
            'SELECT DATA_TYPE as type, EXTRA as extra
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1',
            [$dbName, $table, $column]
        );

        if (!$rows) {
            return null;
        }

        $type = $rows[0]->type ?? null;
        $extra = $rows[0]->extra ?? '';

        return [
            'type' => $type,
            'auto' => str_contains((string) $extra, 'auto_increment'),
        ];
    }

    private function generatePrimaryKey(?string $pkType): string|int
    {
        $type = strtolower((string) $pkType);
        $stringTypes = ['char', 'varchar', 'binary', 'varbinary', 'uuid'];
        if (in_array($type, $stringTypes, true) || $type === '') {
            return Str::uuid()->toString();
        }

        return random_int(1, PHP_INT_MAX);
    }

    private function getPrimaryKey(string $dbName, string $table): ?string
    {
        $rows = DB::select(
            'SELECT k.COLUMN_NAME as name
             FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
             JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
               ON t.CONSTRAINT_NAME = k.CONSTRAINT_NAME
              AND t.TABLE_SCHEMA = k.TABLE_SCHEMA
              AND t.TABLE_NAME = k.TABLE_NAME
             WHERE t.CONSTRAINT_TYPE = "PRIMARY KEY"
               AND t.TABLE_SCHEMA = ?
               AND t.TABLE_NAME = ?
             ORDER BY k.ORDINAL_POSITION
             LIMIT 1',
            [$dbName, $table]
        );
        return $rows ? ($rows[0]->name ?? null) : null;
    }

    private function getForeignKeys(string $dbName, string $table): array
    {
        $rows = DB::select(
            'SELECT COLUMN_NAME as col, REFERENCED_TABLE_NAME as ref_table, REFERENCED_COLUMN_NAME as ref_col
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$dbName, $table]
        );

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'column' => $r->col,
                'referenced_table' => $r->ref_table,
                'referenced_column' => $r->ref_col,
            ];
        }
        return $out;
    }

    private function makeRecordKey(string $table, array $attributes, ?string $primaryKey): string
    {
        if ($primaryKey && isset($attributes[$primaryKey])) {
            return $table . '::pk:' . (string) $attributes[$primaryKey];
        }

        foreach (['uuid', 'slug', 'code', 'email', 'username', 'name'] as $field) {
            if (isset($attributes[$field]) && $attributes[$field] !== null && $attributes[$field] !== '') {
                return $table . '::' . $field . ':' . (string) $attributes[$field];
            }
        }

        return $table . '::hash:' . sha1(json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function makeRelationKey(string $refTable, mixed $refValue): string
    {
        if ($refTable !== 'users') {
            return $refTable . '::pk:' . (string) $refValue;
        }

        $user = DB::table('users')->select(['id', 'email', 'username'])->where('id', $refValue)->first();
        if ($user && !empty($user->email)) {
            return 'users::email:' . (string) $user->email;
        }
        if ($user && !empty($user->username)) {
            return 'users::username:' . (string) $user->username;
        }

        return 'users::pk:' . (string) $refValue;
    }

    private function makeResolvedRelationKey(
        string $refTable,
        string $refColumn,
        mixed $refValue,
        array $tableMeta,
        string $dbName,
        array &$cache
    ): string {
        if ($refTable === 'users') {
            return $this->makeRelationKey($refTable, $refValue);
        }

        $cacheKey = $refTable . '|' . $refColumn . '|' . (string) $refValue;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $refMeta = $tableMeta[$refTable] ?? null;
        $refPk = $refMeta['primary_key'] ?? null;

        $columns = $this->getTableColumns($dbName, $refTable);
        if (!$columns) {
            return $cache[$cacheKey] = $refTable . '::pk:' . (string) $refValue;
        }

        $selectCols = array_values(array_intersect($columns, ['id', 'uuid', 'slug', 'code', 'email', 'username', 'name']));
        if (!$selectCols) {
            $selectCols = [$refColumn];
        }

        $refRow = DB::table($refTable)->select($selectCols)->where($refColumn, $refValue)->first();
        if (!$refRow) {
            return $cache[$cacheKey] = $refTable . '::pk:' . (string) $refValue;
        }

        $refAttrs = (array) $refRow;
        $resolved = $this->makeRecordKey($refTable, $refAttrs, $refPk);
        return $cache[$cacheKey] = $resolved;
    }

    private function parseRelationTable(?string $relationKey): ?string
    {
        if (!$relationKey || !str_contains($relationKey, '::')) {
            return null;
        }
        return explode('::', $relationKey, 2)[0] ?? null;
    }

    private function resolveExternalRelationId(string $relationKey): string|int|null
    {
        if (str_starts_with($relationKey, 'users::email:')) {
            $email = substr($relationKey, strlen('users::email:'));
            if ($email === '') {
                return null;
            }
            $id = DB::table('users')->where('email', $email)->value('id');
            return $id ?: null;
        }

        if (str_starts_with($relationKey, 'users::username:')) {
            $username = substr($relationKey, strlen('users::username:'));
            if ($username === '') {
                return null;
            }
            $id = DB::table('users')->where('username', $username)->value('id');
            return $id ?: null;
        }

        return null;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        try {
            $dbName = DB::connection()->getDatabaseName();
            $rows = DB::select(
                'SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$dbName, $table, $column]
            );
            return (int) ($rows[0]->c ?? 0) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}
