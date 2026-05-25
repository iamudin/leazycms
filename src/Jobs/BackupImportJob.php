<?php

namespace Leazycms\Web\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Leazycms\Web\Services\BackupTransferService;

class BackupImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 3;

    public function __construct(
        public string $statusCacheKey,
        public string $zipPath,
        public string $host,
        public bool $multisite,
        public bool $isTenantScope,
        public ?int $tenantId,
        public bool $replace,
        public bool $replaceNonTenant,
        public bool $overwriteUsers,
    ) {
    }

    public function handle(BackupTransferService $service): void
    {
        Cache::put($this->statusCacheKey, array_merge(Cache::get($this->statusCacheKey, []), [
            'state' => 'running',
            'started_at' => now()->toIso8601String(),
            'message' => 'Import sedang diproses.',
        ]), now()->addHours(6));

        try {
            $report = $service->importFromZipPath($this->zipPath, [
                'host' => $this->host,
                'multisite' => $this->multisite,
                'is_tenant_scope' => $this->isTenantScope,
                'tenant_id' => $this->tenantId,
                'replace' => $this->replace,
                'replace_non_tenant' => $this->replaceNonTenant,
                'overwrite_users' => $this->overwriteUsers,
            ]);

            if (is_file($this->zipPath)) {
                File::delete($this->zipPath);
            }

            Cache::put($this->statusCacheKey, array_merge(Cache::get($this->statusCacheKey, []), [
                'state' => 'done',
                'finished_at' => now()->toIso8601String(),
                'message' => 'Import selesai. ' . ($report ? json_encode($report) : ''),
            ]), now()->addHours(6));
        } catch (Throwable $e) {
            Cache::put($this->statusCacheKey, array_merge(Cache::get($this->statusCacheKey, []), [
                'state' => 'failed',
                'finished_at' => now()->toIso8601String(),
                'message' => 'Import gagal: ' . $e->getMessage(),
            ]), now()->addHours(6));

            throw $e;
        }
    }
}
