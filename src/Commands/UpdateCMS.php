<?php
namespace Leazycms\Web\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Http;

class UpdateCMS extends Command
{
    protected $signature = 'cms:update';
    protected $description = 'Update CMS jika versi terbaru tersedia';

    protected $packages = [
        'leazycms/web',
        'leazycms/flc',
    ];

    public function handle()
    {
        foreach ($this->packages as $package) {
            $this->info("🔍 Mengecek update untuk: $package");

            $localVersion = $this->getLocalVersion($package);
            $latestVersion = $this->getLatestPackagistVersion($package);

            if (!$localVersion || !$latestVersion) {
                $this->error("Gagal mendapatkan versi untuk $package");
                continue;
            }

            $this->line("📦 Versi lokal  : $localVersion");
            $this->line("🌐 Versi remote : $latestVersion");

            if (version_compare($localVersion, $latestVersion, '<')) {
                $this->info("⚙️  Menjalankan composer update $package...");
                $this->runComposerUpdate($package);
            } else {
                $this->info("✅ Sudah versi terbaru, update dilewati.");
            }

            $this->newLine();
        }

        $this->info("🎉 Proses selesai.");
        return Command::SUCCESS;
    }

    protected function getLocalVersion($package)
    {
        $lockPath = base_path('composer.lock');
        if (!file_exists($lockPath)) {
            return null;
        }

        $lockData = json_decode(file_get_contents($lockPath), true);
        $installed = collect($lockData['packages'] ?? [])
            ->merge($lockData['packages-dev'] ?? []);

        $packageData = $installed->firstWhere('name', $package);
        return $packageData['version'] ?? null;
    }

    protected function getLatestPackagistVersion($package)
    {
        try {
            $response = Http::get("https://repo.packagist.org/p2/{$package}.json");

            if ($response->ok()) {
                $data = $response->json();
                $versions = $data['packages'][$package] ?? [];

                $stableVersions = collect($versions)
                    ->filter(fn($v) => !str_contains($v['version'], 'dev') && !str_contains($v['version'], 'RC'))
                    ->pluck('version');

                return $stableVersions->first(); // Ambil versi paling baru yang stabil
            }
        } catch (\Exception $e) {
            $this->error("Gagal mengambil versi dari Packagist: " . $e->getMessage());
        }

        return null;
    }

    protected function runComposerUpdate($package)
    {
        $process = new Process(['composer', 'update', $package]);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            $this->error("❌ Update gagal untuk $package");
        } else {
            $this->info("✅ Berhasil update $package");
        }
    }
}
