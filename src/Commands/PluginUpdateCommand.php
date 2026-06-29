<?php
namespace Leazycms\Web\Commands;
use ZipArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class PluginUpdateCommand extends Command
{
    protected $signature = 'cms:update-plugin {slug}';
    protected $description = 'Update a CMS plugin from GitHub Tags if newer version available';

    public function handle()
    {
        $slug = $this->argument('slug');
        $pluginPath = resource_path("plugins/$slug/plugin.json");

        if (!File::exists($pluginPath)) {
            $this->error("Plugin [$slug] not found.");
            return;
        }
        $extractPath = resource_path("plugins/$slug");

        if (File::isDirectory($extractPath . '/.git') ) {
            $this->warn("Update plugin tidak di izinkan (karena terdapat folder .git)");
            return;
        }

        // Baca metadata plugin lokal
        $plugin = json_decode(File::get($pluginPath), true);
        $localVersion = $plugin['version'] ?? '0.0.0';
        $repo = $plugin['repository'] ?? null; // format: username/repo

        if (!$repo) {
            $this->error("Plugin tidak bisa diupgrade karena 'repository' tidak di-set di plugin.json");
            return;
        }

        // Ambil daftar tags dari GitHub pakai Http::get
        $apiUrl = "https://api.github.com/repos/{$repo}/tags";
        $response = Http::withHeaders([
            'User-Agent' => 'LaravelCMS-Updater'
        ])->get($apiUrl);

        if (!$response->ok()) {
            $this->error("Gagal terhubung ke server GitHub.");
            return;
        }

        $tags = $response->json();

        if (!$tags || !isset($tags[0]['name'])) {
            $this->error("Plugin tidak Valid atau tidak memiliki tag release.");
            return;
        }

        $latestTag = $tags[0]['name'];
        $downloadUrl = $tags[0]['zipball_url'];

        // Bandingkan versi (hilangkan prefix v kalau ada)
        if (version_compare(ltrim($latestTag, 'v'), ltrim($localVersion, 'v'), '<=')) {
            $this->info("Plugin sudah versi terbaru (v{$localVersion}).");
            return;
        }

        $this->info("Update ditemukan: {$latestTag}");

        // === Backup sebelum update ===
        if (File::isDirectory($extractPath)) {
            $backupDir = storage_path("app/backups/plugin/{$slug}/" . now()->format('Ymd_His'));
            File::ensureDirectoryExists($backupDir);
            File::copyDirectory($extractPath, $backupDir);

            $this->info("Backup plugin lama berhasil disimpan di: $backupDir");
        } else {
            File::makeDirectory($extractPath, 0755, true);
        }

        // Download zip dari tag pakai Http::get
        $zipPath = storage_path("app/plugin_{$slug}.zip");
        $zipResponse = Http::withHeaders([
            'User-Agent' => 'LaravelCMS-Updater'
        ])->get($downloadUrl);

        if (!$zipResponse->ok()) {
            $this->error("Gagal download file update.");
            return;
        }

        File::put($zipPath, $zipResponse->body());

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            // Extract ke tmp
            $tmpDir = storage_path("app/tmp_plugin_{$slug}");
            File::deleteDirectory($tmpDir);
            $zip->extractTo($tmpDir);
            $zip->close();

            // GitHub zip punya subfolder repo-hash
            $subDir = File::directories($tmpDir)[0] ?? null;
            if (!$subDir) {
                $this->error("Struktur zip tidak valid.");
                return;
            }

            // --- Pastikan folder resources/plugins/$slug ---
            if (!File::isDirectory($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }

            // === Copy semua isi repo ke resources/plugins/{slug} ===
            foreach (File::directories($subDir) as $dir) {
                $targetDir = $extractPath . '/' . basename($dir);

                // Jika folder assets → kita copy biasa dulu. Nanti kalau ada perintah symlink plugin, bisa di-handle di sana
                File::copyDirectory($dir, $targetDir);
            }

            // Copy file di root repo (plugin.json, readme, dll)
            foreach (File::files($subDir) as $file) {
                File::copy($file->getPathname(), $extractPath . '/' . $file->getFilename());
            }

            // Bersihkan tmp & zip
            File::deleteDirectory($tmpDir);
            unlink($zipPath);

            // Update version di plugin.json
            $plugin['version'] = $latestTag;
            File::put($pluginPath, json_encode($plugin, JSON_PRETTY_PRINT));

            $this->info("Plugin [$slug] berhasil diupdate ke {$latestTag}!");
            
            // Opsional: Jika Anda ingin me-run migrate saat plugin di update
            // Artisan::call("migrate");
        } else {
            $this->error("Gagal extract zip update.");
        }
    }
}
