<?php

namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Leazycms\Web\Services\BackupTransferService;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;

class RunBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan backup otomatis dan upload ke Google Drive (jika terkonfigurasi)';

    /**
     * Execute the console command.
     */
    public function handle(BackupTransferService $service)
    {
        $this->info('Memulai backup...');

        $multisite = (bool) config('modules.multisite_enabled');
        $isMainDomain = true;
        $isTenantScope = false;
        $tenantId = null;

        try {
            $host = env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : 'localhost';
            
            $sqlPath = $service->exportToSqlPath([
                'host' => $host,
                'multisite' => $multisite,
                'is_tenant_scope' => $isTenantScope,
                'is_main_domain' => $isMainDomain,
                'tenant_id' => $tenantId,
                'include_users' => true,
            ]);

            $zipPath = $sqlPath . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($sqlPath, basename($sqlPath));
                $zip->close();
                @unlink($sqlPath);
                $sqlPath = $zipPath;
            }

            $this->info('Backup berhasil dibuat: ' . $sqlPath);

            // Periksa Google Drive terkonfigurasi
            if (get_option('google_drive_client_id') && get_option('google_drive_client_secret') && get_option('google_drive_refresh_token')) {
                $this->info('Mengupload ke Google Drive...');
                try {
                    $fileName = basename($sqlPath);
                    $gDriveService = new \Leazycms\Web\Services\GoogleDriveService();
                    
                    if ($gDriveService->upload($sqlPath, $fileName)) {
                        $this->info('Berhasil upload ke Google Drive!');
                        $this->cleanupGoogleDrive($gDriveService);
                    } else {
                        $this->error('Gagal upload ke Google Drive.');
                    }
                } catch (\Exception $e) {
                    $this->error('Gagal upload ke Google Drive: ' . $e->getMessage());
                }
            }

            // Cleanup old local files (> 7 days)
            $this->cleanupLocal();

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal melakukan backup: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function cleanupLocal()
    {
        $this->info('Membersihkan backup lokal lama...');
        $baseDir = storage_path('app/leazycms-transfer/exports');
        if (is_dir($baseDir)) {
            $files = \Illuminate\Support\Facades\File::files($baseDir);
            $now = Carbon::now();
            foreach ($files as $file) {
                if (str_ends_with($file->getFilename(), '.zip')) {
                    $fileMTime = Carbon::createFromTimestamp($file->getMTime());
                    if ($now->diffInDays($fileMTime) >= 7) {
                        @unlink($file->getRealPath());
                        $this->info('Menghapus ' . $file->getFilename());
                    }
                }
            }
        }
    }

    private function cleanupGoogleDrive($gDriveService)
    {
        $this->info('Membersihkan backup Google Drive lama...');
        try {
            $files = $gDriveService->list();
            $now = Carbon::now();
            foreach ($files as $file) {
                $fileMTime = Carbon::createFromTimestamp($file['time']);
                if ($now->diffInDays($fileMTime) >= 7) {
                    $gDriveService->delete($file['id']);
                    $this->info('Menghapus dari Google Drive: ' . $file['name']);
                }
            }
        } catch (\Exception $e) {
            $this->error('Gagal membersihkan Google Drive: ' . $e->getMessage());
        }
    }
}
