<?php

namespace Leazycms\Web\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Leazycms\Web\Services\BackupTransferService;

class ImportBackupCommand extends Command
{
    protected $signature = 'cms:import-backup {path} {--host=} {--replace} {--replace-non-tenant}';
    protected $description = 'Import LeazyCMS Smart SQL backup file';
    public function handle(BackupTransferService $service)
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return Command::FAILURE;
        }

        $host = $this->option('host');
        if (!$host) {
            $host = 'localhost';
            $this->warn("Host tidak diberikan. Menggunakan fallback: {$host}");
        }

        $this->info("Memulai proses restore dari {$path}...");
        
        $multisite = (bool) config('modules.multisite_enabled');
        
        $context = [
            'host' => $host,
            'multisite' => $multisite,
            'is_tenant_scope' => false,
            'replace' => $this->option('replace'),
            'replace_non_tenant' => $this->option('replace-non-tenant'),
            'overwrite_users' => true,
        ];

        try {
            $sqlPathToImport = $path;
            $extractedSqlPath = '';

            if (str_ends_with(strtolower($path), '.zip')) {
                $this->info("Mengekstrak file ZIP...");
                $zip = new \ZipArchive();
                if ($zip->open($path) === TRUE) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);
                        if (str_ends_with($filename, '.sql')) {
                            $extractedSqlPath = storage_path('app/temp-import-' . time() . '.sql');
                            file_put_contents($extractedSqlPath, $zip->getFromIndex($i));
                            $sqlPathToImport = $extractedSqlPath;
                            break;
                        }
                    }
                    $zip->close();
                }

                if (empty($extractedSqlPath)) {
                    $this->error("File ZIP tidak berisi file .sql yang valid.");
                    return Command::FAILURE;
                }
            }

            $service->importFromSqlPath($sqlPathToImport, $context);
            
            if (!empty($extractedSqlPath) && file_exists($extractedSqlPath)) {
                unlink($extractedSqlPath);
            }

            $this->info("Backup berhasil direstore!");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal melakukan restore: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
