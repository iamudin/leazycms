<?php

namespace Leazycms\Web\Services;

use Throwable;
use RuntimeException;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class BackupTransferService
{
    public function exportToSqlPath(array $context): string
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

        $dbName = DB::connection()->getDatabaseName();

        $exportId = Str::uuid()->toString();
        $baseDir = storage_path('app/leazycms-transfer');
        $outDir = $baseDir . '/exports';
        File::ensureDirectoryExists($outDir);

        $sqlPath = $outDir . '/backup-' . ($isTenantScope ? ('tenant-' . $scopeTenantId) : 'induk') . '-' . now()->format('Ymd-His') . '.sql';
        
        $fh = fopen($sqlPath, 'wb');
        if (!$fh) {
            throw new RuntimeException('Gagal membuat file SQL.');
        }

        fwrite($fh, "-- LeazyCMS Smart SQL Backup\n");
        fwrite($fh, "-- Generated at: " . now()->toIso8601String() . "\n");
        fwrite($fh, "-- Host: {$host}\n");
        fwrite($fh, "-- Scope: " . ($isTenantScope ? 'tenant' : 'induk') . "\n\n");
        
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $includeUsers = (bool) ($context['include_users'] ?? false);
        $tables = $this->listTables($dbName, [
            'include_users' => $includeUsers,
        ]);

        foreach ($tables as $table) {
            if ($table === '_leazycms_media_backup') continue;

            $createTable = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            if ($createTable) {
                $createSql = $createTable->{'Create Table'} ?? $createTable->{'Create View'} ?? '';
                if ($createSql) {
                    // Inject IF NOT EXISTS
                    $createSql = preg_replace('/^CREATE TABLE /i', 'CREATE TABLE IF NOT EXISTS ', $createSql);
                    fwrite($fh, "\n-- Table structure for table `{$table}`\n");
                    if (!$isTenantScope) {
                        fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n");
                    }
                    fwrite($fh, $createSql . ";\n\n");
                }
            }

            $query = DB::table($table);

            if ($isTenantScope && $this->tableHasColumn($table, 'tenant_id')) {
                $query->where('tenant_id', $scopeTenantId);
            }

            if ($isTenantScope && $table === 'tenants') {
                $query->where('id', $scopeTenantId);
            }

            if ($isTenantScope && $this->tableHasColumn($table, 'host')) {
                $query->where('host', $host);
            }

            $rows = $query->cursor();
            foreach ($rows as $row) {
                $attributes = (array) $row;
                $columns = array_keys($attributes);
                $values = array_values($attributes);
                
                $escapedColumns = array_map(function($col) {
                    return "`{$col}`";
                }, $columns);
                
                $escapedValues = array_map(function($val) {
                    if ($val === null) return 'NULL';
                    return DB::connection()->getPdo()->quote($val);
                }, $values);

                $sql = "REPLACE INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                fwrite($fh, $sql);
            }
        }

        // Handle Media Storage Backup
        fwrite($fh, "\n-- Media Storage Backup\n");
        fwrite($fh, "CREATE TABLE IF NOT EXISTS `_leazycms_media_backup` (\n");
        fwrite($fh, "  `id` INT AUTO_INCREMENT PRIMARY KEY,\n");
        fwrite($fh, "  `disk` VARCHAR(100),\n");
        fwrite($fh, "  `file_path` VARCHAR(500),\n");
        fwrite($fh, "  `content` LONGBLOB\n");
        fwrite($fh, ");\n\n");

        $fileQuery = DB::table('files')->select(['file_path', 'disk', 'file_name', 'host']);
        if ($isTenantScope && $this->tableHasColumn('files', 'host')) {
            $fileQuery->where('host', $host);
        }

        foreach ($fileQuery->cursor() as $fileRecord) {
            $disk = $fileRecord->disk ?: config('filesystems.default');
            $path = $fileRecord->file_path;
            
            if (!$disk || !$path) continue;
            if (!Storage::disk($disk)->exists($path)) continue;

            $source = Storage::disk($disk)->path($path);
            if (!is_file($source)) continue;

            $content = file_get_contents($source);
            if ($content !== false) {
                if ($content === '') {
                    $hex = "''";
                } else {
                    $hex = '0x' . bin2hex($content);
                }
                $escapedDisk = DB::connection()->getPdo()->quote($disk);
                $escapedPath = DB::connection()->getPdo()->quote($path);
                
                $sql = "INSERT INTO `_leazycms_media_backup` (`disk`, `file_path`, `content`) VALUES ({$escapedDisk}, {$escapedPath}, {$hex});\n";
                fwrite($fh, $sql);
            }
        }

        fwrite($fh, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);

        return $sqlPath;
    }

    public function export(Request $request)
    {
        $host = $request->getHost();
        $multisite = (bool) config('modules.multisite_enabled');
        $isTenantScope = $multisite && app()->has('tenant') && !is_main_domain();
        $isMainDomain = $multisite && is_main_domain();
        $tenantId = $isTenantScope ? tenant()->id : null;

        try {
            $sqlPath = $this->exportToSqlPath([
                'host' => $host,
                'multisite' => $multisite,
                'is_tenant_scope' => $isTenantScope,
                'is_main_domain' => $isMainDomain,
                'tenant_id' => $tenantId,
            ]);
        } catch (Throwable $e) {
            return back()->with('danger', $e->getMessage());
        }

        return response()->download($sqlPath)->deleteFileAfterSend(true);
    }

    public function importFromSqlPath(string $sqlPath, array $context): array
    {
        if (!is_file($sqlPath)) {
            throw new InvalidArgumentException('File SQL tidak ditemukan.');
        }

        $host = (string) ($context['host'] ?? '');
        if ($host === '') {
            throw new InvalidArgumentException('Host tidak valid.');
        }

        $isTenantScope = (bool) ($context['is_tenant_scope'] ?? false);
        $forceTenantId = $isTenantScope ? ($context['tenant_id'] ?? null) : null;
        if ($isTenantScope && !$forceTenantId) {
            throw new InvalidArgumentException('Tenant ID tidak ditemukan.');
        }

        $replace = (bool) ($context['replace'] ?? false);
        $replaceNonTenant = (bool) ($context['replace_non_tenant'] ?? false);
        $overwriteUsers = (bool) ($context['overwrite_users'] ?? false);
        $multisite = (bool) config('modules.multisite_enabled');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            if ($replace) {
                // Determine which tables to truncate
                $dbName = DB::connection()->getDatabaseName();
                $tables = $this->listTables($dbName, ['include_users' => $overwriteUsers]);
                $this->truncateScope($tables, $forceTenantId, $host, $replaceNonTenant);
            }

            // Execute SQL Line by Line
            $fh = fopen($sqlPath, 'rb');
            if (!$fh) {
                throw new RuntimeException('Gagal membaca file SQL.');
            }

            $originalUser1 = \Illuminate\Support\Facades\DB::table('users')->where('id', 1)->first();
            $originalTenant1 = $multisite ? \Illuminate\Support\Facades\DB::table('tenants')->where('id', 1)->first() : null;

            $buffer = '';
            while (($line = fgets($fh)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }
                
                $buffer .= $line;
                
                if (str_ends_with($trimmed, ';')) {
                    
                    if ($originalUser1 && preg_match('/^(?:REPLACE|INSERT)\s+(?:IGNORE\s+)?INTO\s+`users`\s+\(`id`.*?\)\s+VALUES\s+\(\s*(?:\'1\'|1)\s*,/i', $buffer)) {
                        $buffer = '';
                        continue;
                    }
                    
                    if ($originalTenant1 && preg_match('/^(?:REPLACE|INSERT)\s+(?:IGNORE\s+)?INTO\s+`tenants`\s+\(`id`.*?\)\s+VALUES\s+\(\s*(?:\'1\'|1)\s*,/i', $buffer)) {
                        $buffer = '';
                        continue;
                    }

                    DB::unprepared($buffer);
                    $buffer = '';
                }
            }
            if (trim($buffer) !== '') {
                DB::unprepared($buffer);
            }
            fclose($fh);

            // Restore User 1 and Tenant 1 completely
            if ($originalUser1) {
                \Illuminate\Support\Facades\DB::table('users')->where('id', 1)->delete();
                \Illuminate\Support\Facades\DB::table('users')->insert((array) $originalUser1);
            }
            if ($originalTenant1) {
                \Illuminate\Support\Facades\DB::table('tenants')->where('id', 1)->delete();
                \Illuminate\Support\Facades\DB::table('tenants')->insert((array) $originalTenant1);
            }

            // Restore files from temporary table
            if (Schema::hasTable('_leazycms_media_backup')) {
                foreach (DB::table('_leazycms_media_backup')->cursor() as $media) {
                    $disk = $media->disk;
                    $path = $media->file_path;
                    $content = $media->content;
                    
                    if ($disk && $path && $content !== null) {
                        Storage::disk($disk)->put($path, $content);
                    }
                }
                
                Schema::dropIfExists('_leazycms_media_backup');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                if (Schema::hasTable('_leazycms_media_backup')) {
                    Schema::dropIfExists('_leazycms_media_backup');
                }
            } catch (Throwable $ignored) {
            }
            throw new RuntimeException('Import gagal: ' . $e->getMessage(), 0, $e);
        }

        return [
            'inserted' => [],
            'source_host' => $host,
        ];
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
            '_leazycms_media_backup',
        ];

        if (!$includeUsers) {
            $ignore[] = 'users';
            $ignore[] = 'model_has_permissions';
            $ignore[] = 'model_has_roles';
        }

        $out = [];
        foreach ($tables as $t) {
            if (in_array($t, $ignore, true)) {
                continue;
            }
            $out[] = $t;
        }

        return $out;
    }

    private function truncateScope(array $tables, ?int $forceTenantId, string $host, bool $replaceNonTenant): void
    {
        $multisite = (bool) config('modules.multisite_enabled');
        foreach ($tables as $table) {
            if ($table === 'users') {
                continue;
            }

            if ($forceTenantId !== null) {
                if ($this->tableHasColumn($table, 'tenant_id')) {
                    DB::table($table)->where('tenant_id', $forceTenantId)->delete();
                    continue;
                }
                if ($this->tableHasColumn($table, 'host')) {
                    DB::table($table)->where('host', $host)->delete();
                }
                continue;
            }

            if ($replaceNonTenant) {
                if ($table === 'tenants' && $multisite) {
                    DB::table($table)->where('id', '!=', 1)->delete();
                } else {
                    DB::table($table)->truncate();
                }
            }
        }
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
