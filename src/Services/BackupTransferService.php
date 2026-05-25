<?php

namespace Leazycms\Web\Services;

use ZipArchive;
use Throwable;
use RuntimeException;
use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupTransferService
{
    public function exportToZipPath(array $context): string
    {
        $host = (string) ($context['host'] ?? '');
        if ($host === '') {
            throw new InvalidArgumentException('Host tidak valid.');
        }

        $multisite = (bool) ($context['multisite'] ?? config('modules.multisite_enabled'));
        $isTenantScope = (bool) ($context['is_tenant_scope'] ?? false);
        $isMainDomain = (bool) ($context['is_main_domain'] ?? false);

        $scopeTenantId = $context['tenant_id'] ?? null;
        if ($isTenantScope && !$scopeTenantId) {
            throw new InvalidArgumentException('Tenant ID tidak ditemukan.');
        }

        $scope = $isTenantScope ? 'tenant' : 'induk';

        $includeTenantId = $isMainDomain;
        $includeTenantTables = $isMainDomain;
        $filterByTenant = $isTenantScope;
        $filterByHost = $isTenantScope;

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

        $includeUsers = (bool) ($context['include_users'] ?? false);
        $tables = $this->listTables($dbName, [
            'include_tenant_tables' => $includeTenantTables,
            'multisite' => $multisite,
            'include_users' => $includeUsers,
        ]);
        $tableMeta = $this->getTablesMeta($dbName, $tables);

        DB::disableQueryLog();

        $manifestPath = $tmpDir . '/manifest.json';
        File::put($manifestPath, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $dbMeta = [
            'format' => 'jsonl-v1',
            'database' => $dbName,
            'created_at' => $manifest['created_at'],
            'host' => $host,
            'scope' => $scope,
            'source_multisite_enabled' => $multisite,
            'include_tenant_id' => $includeTenantId,
        ];

        $dbDir = $tmpDir . '/database';
        $tablesDir = $dbDir . '/tables';
        File::ensureDirectoryExists($tablesDir);
        $dbMetaPath = $dbDir . '/meta.json';
        File::put($dbMetaPath, json_encode($dbMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $zipPath = $outDir . '/backup-' . ($isTenantScope ? ('tenant-' . $scopeTenantId) : 'induk') . '-' . now()->format('Ymd-His') . '.zip';
        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            File::deleteDirectory($tmpDir);
            throw new RuntimeException('Gagal membuat file ZIP.');
        }

        $zip->addFile($manifestPath, 'manifest.json');
        $zip->addFile($dbMetaPath, 'database/meta.json');

        $relationCache = [];
        foreach ($tables as $table) {
            $meta = $tableMeta[$table] ?? null;
            if (!$meta) {
                continue;
            }

            if (
                $isTenantScope
                && !in_array('tenant_id', $meta['columns'] ?? [], true)
                && !in_array('host', $meta['columns'] ?? [], true)
            ) {
                continue;
            }

            $outFile = $tablesDir . '/' . $table . '.jsonl';
            $fh = fopen($outFile, 'wb');
            if (!$fh) {
                $zip->close();
                File::deleteDirectory($tmpDir);
                throw new RuntimeException('Gagal menulis file export.');
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

            if (!empty($meta['primary_key'])) {
                $query->orderBy($meta['primary_key']);
            }

            foreach ($query->cursor() as $row) {
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

                $line = json_encode([
                    'key' => $key,
                    'attributes' => $attributes,
                    'relations' => $relations,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if ($line === false) {
                    continue;
                }

                fwrite($fh, $line . "\n");
            }

            fclose($fh);
            $zip->addFile($outFile, 'database/tables/' . $table . '.jsonl');
        }

        $this->zipStorageFiles($zip, $host, $isTenantScope);

        $zip->close();
        File::deleteDirectory($tmpDir);

        return $zipPath;
    }

    public function export(Request $request)
    {
        $host = $request->getHost();
        $multisite = (bool) config('modules.multisite_enabled');
        $isTenantScope = $multisite && app()->has('tenant') && !is_main_domain();
        $isMainDomain = $multisite && is_main_domain();
        $tenantId = $isTenantScope ? tenant()->id : null;

        try {
            $zipPath = $this->exportToZipPath([
                'host' => $host,
                'multisite' => $multisite,
                'is_tenant_scope' => $isTenantScope,
                'is_main_domain' => $isMainDomain,
                'tenant_id' => $tenantId,
            ]);
        } catch (Throwable $e) {
            return back()->with('danger', $e->getMessage());
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function importFromZipPath(string $zipPath, array $context): array
    {
        if (!is_file($zipPath)) {
            throw new InvalidArgumentException('File ZIP tidak ditemukan.');
        }

        $host = (string) ($context['host'] ?? '');
        if ($host === '') {
            throw new InvalidArgumentException('Host tidak valid.');
        }

        $multisite = (bool) ($context['multisite'] ?? config('modules.multisite_enabled'));
        $isTenantScope = (bool) ($context['is_tenant_scope'] ?? false);
        $forceTenantId = $isTenantScope ? ($context['tenant_id'] ?? null) : null;
        if ($isTenantScope && !$forceTenantId) {
            throw new InvalidArgumentException('Tenant ID tidak ditemukan.');
        }

        $replace = (bool) ($context['replace'] ?? false);
        $replaceNonTenant = (bool) ($context['replace_non_tenant'] ?? false);
        $overwriteUsers = (bool) ($context['overwrite_users'] ?? false);

        $importId = Str::uuid()->toString();
        $baseDir = storage_path('app/leazycms-transfer');
        $tmpDir = $baseDir . '/tmp/' . $importId;
        File::ensureDirectoryExists($tmpDir);

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath);
        if ($opened !== true) {
            File::deleteDirectory($tmpDir);
            throw new RuntimeException('File ZIP tidak bisa dibuka.');
        }

        DB::disableQueryLog();

        $zip->extractTo($tmpDir);
        $zip->close();

        $newTablesDir = $tmpDir . '/database/tables';
        $newMetaPath = $tmpDir . '/database/meta.json';

        $useNewFormat = File::isDirectory($newTablesDir);

        $dbPayload = null;
        $newMeta = [];
        $newTables = [];
        if ($useNewFormat) {
            if (File::exists($newMetaPath)) {
                $newMeta = json_decode(File::get($newMetaPath), true) ?: [];
            }
            $newTables = $this->listJsonlTables($newTablesDir);
            if (!$newTables) {
                File::deleteDirectory($tmpDir);
                throw new RuntimeException('Format backup tidak valid (database/tables kosong).');
            }
        } else {
            $dbFile = $tmpDir . '/database.json';
            if (!File::exists($dbFile)) {
                File::deleteDirectory($tmpDir);
                throw new RuntimeException('Format backup tidak valid (database.json tidak ditemukan).');
            }

            $dbPayload = json_decode(File::get($dbFile), true);
            if (!is_array($dbPayload) || !isset($dbPayload['data']) || !is_array($dbPayload['data'])) {
                File::deleteDirectory($tmpDir);
                throw new RuntimeException('Format database.json tidak valid.');
            }
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            if ($replace) {
                $tablesToTruncate = $useNewFormat ? array_keys($newTables) : array_keys($dbPayload['data']);
                $this->truncateScope($tablesToTruncate, $forceTenantId, $host, $replaceNonTenant);
            }

            $this->restoreStorageFiles($tmpDir);
            $report = $useNewFormat
                ? $this->importDatabaseFromJsonlTables($newTables, $newMeta, $forceTenantId, $host, $overwriteUsers)
                : $this->importDatabase($dbPayload, $forceTenantId, $host, $overwriteUsers);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (Throwable $ignored) {
            }
            File::deleteDirectory($tmpDir);
            throw new RuntimeException('Import gagal: ' . $e->getMessage(), 0, $e);
        }

        File::deleteDirectory($tmpDir);

        return $report;
    }

    private function listJsonlTables(string $tablesDir): array
    {
        if (!File::isDirectory($tablesDir)) {
            return [];
        }

        $out = [];
        foreach (File::files($tablesDir) as $file) {
            $name = $file->getFilename();
            if (!str_ends_with($name, '.jsonl')) {
                continue;
            }
            $table = substr($name, 0, -strlen('.jsonl'));
            if ($table === '') {
                continue;
            }
            $out[$table] = $file->getPathname();
        }

        return $out;
    }

    private function importDatabaseFromJsonlTables(array $tableFiles, array $meta, ?int $forceTenantId, string $host, bool $overwriteUsers): array
    {
        $dbName = DB::connection()->getDatabaseName();

        $idMap = [];
        $inserted = [];

        $tables = array_keys($tableFiles);
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
            $filePath = $tableFiles[$table] ?? null;
            if (!$filePath || !is_file($filePath)) {
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

            $fh = fopen($filePath, 'rb');
            if (!$fh) {
                continue;
            }

            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $record = json_decode($line, true);
                if (!is_array($record) || !isset($record['attributes']) || !is_array($record['attributes'])) {
                    continue;
                }

                $attrs = array_intersect_key($record['attributes'], array_flip($columns));
                if ($pk) {
                    unset($attrs[$pk]);
                }

                $relations = (isset($record['relations']) && is_array($record['relations'])) ? $record['relations'] : [];

                if ($table === 'tenants') {
                    if (in_array('domain', $columns, true) && !empty($attrs['domain'])) {
                        $existingId = DB::table('tenants')->where('domain', $attrs['domain'])->value($pk ?: 'id');
                        if ($existingId) {
                            if (isset($record['key']) && is_string($record['key'])) {
                                $idMap[$record['key']] = $existingId;
                            }
                            continue;
                        }
                    }
                }

                if ($table === 'users') {
                    $matchCol = null;
                    if (in_array('email', $columns, true) && !empty($attrs['email'])) {
                        $matchCol = 'email';
                    } elseif (in_array('username', $columns, true) && !empty($attrs['username'])) {
                        $matchCol = 'username';
                    }

                    if ($matchCol) {
                        $existingId = DB::table('users')->where($matchCol, $attrs[$matchCol])->value($pk ?: 'id');
                        if ($existingId) {
                            if (isset($record['key']) && is_string($record['key'])) {
                                $idMap[$record['key']] = $existingId;
                            }

                            if ($overwriteUsers) {
                                $updates = $attrs;
                                if ($pk) {
                                    unset($updates[$pk]);
                                }
                                if ($updates) {
                                    DB::table('users')->where($pk ?: 'id', $existingId)->update($updates);
                                }
                            }
                            continue;
                        }
                    }
                }

                if ($pk && $relations) {
                    foreach ($relations as $fkCol => $refKey) {
                        $refTable = $this->parseRelationTable(is_string($refKey) ? $refKey : null);
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

            fclose($fh);
        }

        foreach ($tablesWithPk as $table) {
            $filePath = $tableFiles[$table] ?? null;
            if (!$filePath || !is_file($filePath)) {
                continue;
            }

            $pk = $this->getPrimaryKey($dbName, $table);
            if (!$pk) {
                continue;
            }

            $fh = fopen($filePath, 'rb');
            if (!$fh) {
                continue;
            }

            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $record = json_decode($line, true);
                if (!is_array($record) || !isset($record['key'], $record['relations']) || !is_array($record['relations'])) {
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

            fclose($fh);
        }

        return [
            'inserted' => $inserted,
            'source_host' => $meta['host'] ?? null,
        ];
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
        $zipPath = $request->file('backup_file')->getRealPath();

        try {
            $report = $this->importFromZipPath($zipPath, [
                'host' => $host,
                'multisite' => $multisite,
                'is_tenant_scope' => $isTenantScope,
                'tenant_id' => $forceTenantId,
                'replace' => $request->boolean('replace'),
                'replace_non_tenant' => $request->boolean('replace_non_tenant'),
                'overwrite_users' => $request->boolean('overwrite_users'),
            ]);
        } catch (Throwable $e) {
            return back()->with('danger', $e->getMessage());
        }

        return back()->with('success', 'Import berhasil. ' . ($report ? json_encode($report) : ''));
    }

    private function importDatabase(array $dbPayload, ?int $forceTenantId, string $host, bool $overwriteUsers): array
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

                if ($table === 'tenants') {
                    if (in_array('domain', $columns, true) && !empty($attrs['domain'])) {
                        $existingId = DB::table('tenants')->where('domain', $attrs['domain'])->value($pk ?: 'id');
                        if ($existingId) {
                            if (isset($record['key']) && is_string($record['key'])) {
                                $idMap[$record['key']] = $existingId;
                            }
                            continue;
                        }
                    }
                }

                if ($table === 'users') {
                    $matchCol = null;
                    if (in_array('email', $columns, true) && !empty($attrs['email'])) {
                        $matchCol = 'email';
                    } elseif (in_array('username', $columns, true) && !empty($attrs['username'])) {
                        $matchCol = 'username';
                    }

                    if ($matchCol) {
                        $existingId = DB::table('users')->where($matchCol, $attrs[$matchCol])->value($pk ?: 'id');
                        if ($existingId) {
                            if (isset($record['key']) && is_string($record['key'])) {
                                $idMap[$record['key']] = $existingId;
                            }

                            if ($overwriteUsers) {
                                $updates = $attrs;
                                if ($pk) {
                                    unset($updates[$pk]);
                                }
                                if ($updates) {
                                    DB::table('users')->where($pk ?: 'id', $existingId)->update($updates);
                                }
                            }
                            continue;
                        }
                    }
                }

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
            if ($table === 'users') {
                continue;
            }

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
        foreach ($query->cursor() as $file) {
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

        $includeUsers = (bool) ($opts['include_users'] ?? false);

        $ignore = [
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
        if (!$includeUsers) {
            $ignore[] = 'users';
        }

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
             ORDER BY k.ORDINAL_POSITION',
            [$dbName, $table]
        );
        if (!$rows) {
            return null;
        }
        if (count($rows) !== 1) {
            return null;
        }
        return $rows[0]->name ?? null;
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
