<?php

namespace Leazycms\Web\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Leazycms\Web\Services\BackupTransferService;

class BackupExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 3;

    public function __construct(
        public string $statusCacheKey,
        public string $host,
        public bool $multisite,
        public bool $isTenantScope,
        public bool $isMainDomain,
        public ?int $tenantId,
        public bool $includeUsers,
    ) {
    }

    public function handle(BackupTransferService $service): void
    {
        Cache::put($this->statusCacheKey, array_merge(Cache::get($this->statusCacheKey, []), [
            'state' => 'running',
            'started_at' => now()->toIso8601String(),
            'message' => 'Export sedang diproses.',
        ]), now()->addHours(6));

        try {
            $zipPath = $service->exportToZipPath([
                'host' => $this->host,
                'multisite' => $this->multisite,
                'is_tenant_scope' => $this->isTenantScope,
                'is_main_domain' => $this->isMainDomain,
                'tenant_id' => $this->tenantId,
                'include_users' => $this->includeUsers,
            ]);

            $storageApp = rtrim(storage_path('app'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $downloadRelPath = str_starts_with($zipPath, $storageApp)
                ? substr($zipPath, strlen($storageApp))
                : $zipPath;

            Cache::put($this->statusCacheKey, array_merge(Cache::get($this->statusCacheKey, []), [
                'state' => 'done',
                'finished_at' => now()->toIso8601String(),
                'message' => 'Export selesai. File siap diunduh.',
                'download_rel_path' => $downloadRelPath,
                'download_name' => basename($zipPath),
            ]), now()->addHours(6));
        } catch (Throwable $e) {
            Cache::put($this->statusCacheKey, array_merge(Cache::get($this->statusCacheKey, []), [
                'state' => 'failed',
                'finished_at' => now()->toIso8601String(),
                'message' => 'Export gagal: ' . $e->getMessage(),
            ]), now()->addHours(6));

            throw $e;
        }
    }
}
