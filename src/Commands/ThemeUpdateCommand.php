<?php
namespace Leazycms\Web\Commands;
use ZipArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class ThemeUpdateCommand extends Command
{
    protected $signature = 'cms:update-template {slug}';
    protected $description = 'Update a CMS theme from GitHub Tags if newer version available';

    public function handle()
    {
        $slug = $this->argument('slug');
        $themePath = resource_path("views/template/$slug/theme.json");

        if (!File::exists($themePath)) {
            $this->error("Theme [$slug] not found.");
            return;
        }
        $extractPath = resource_path("views/template/$slug");

        if (File::isDirectory($extractPath . '/.git') || Cache::has('enablededitortemplate')) {
            $this->warn("Update template tidak di izinkan");
            return;
        }

        // Baca metadata theme lokal
        $theme = json_decode(File::get($themePath), true);
        $localVersion = $theme['version'] ?? '0.0.0';
        $repo = $theme['repo'] ?? null; // format: username/repo

        if (!$repo) {
            $this->error("Tema tidak bisa diupgrade");
            return;
        }

        // Ambil daftar tags dari GitHub pakai Http::get
        $apiUrl = "https://api.github.com/repos/{$repo}/tags";
        $response = Http::withHeaders([
            'User-Agent' => 'LaravelCMS-Updater'
        ])->get($apiUrl);

        if (!$response->ok()) {
            $this->error("Gagal terhubung ke server.");
            return;
        }

        $tags = $response->json();

        if (!$tags || !isset($tags[0]['name'])) {
            $this->error("Tema tidak Valid.");
            return;
        }

        $latestTag = $tags[0]['name'];
        $downloadUrl = $tags[0]['zipball_url'];

        // Bandingkan versi (hilangkan prefix v kalau ada)
        if (version_compare(ltrim($latestTag, 'v'), ltrim($localVersion, 'v'), '<=')) {
            $this->info("Theme sudah versi terbaru (v{$localVersion}).");
            return;
        }

        $this->info("Update ditemukan: {$latestTag}");

        // === Backup sebelum update ===
        if (File::isDirectory($extractPath)) {
            $backupDir = storage_path("app/backups/template/{$slug}/" . now()->format('Ymd_His'));
            File::ensureDirectoryExists($backupDir);
            File::copyDirectory($extractPath, $backupDir);

            $this->info("Backup template lama berhasil disimpan di: $backupDir");
        } else {
            File::makeDirectory($extractPath, 0755, true);
        }

        // Download zip dari tag pakai Http::get
        $zipPath = storage_path("app/{$slug}.zip");
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
            $tmpDir = storage_path("app/tmp_{$slug}");
            File::deleteDirectory($tmpDir);
            $zip->extractTo($tmpDir);
            $zip->close();

            // GitHub zip punya subfolder repo-hash
            $subDir = File::directories($tmpDir)[0] ?? null;
            if (!$subDir) {
                $this->error("Struktur zip tidak valid.");
                return;
            }

            // --- Pastikan folder views/template/$slug ---
            $extractPath = resource_path("views/template/$slug");
            if (!File::isDirectory($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }

            // === Copy semua isi repo ke resources/views/template/{slug} ===
            foreach (File::directories($subDir) as $dir) {
                $targetDir = $extractPath . '/' . basename($dir);

                // Jika folder assets → skip file php
                if (basename($dir) === 'assets') {
                    File::ensureDirectoryExists($targetDir);
                    foreach (File::allFiles($dir) as $file) {
                        if ($file->getExtension() === 'php') {
                            $this->warn("Skip file PHP di assets: " . $file->getRelativePathname());
                            continue;
                        }
                        $target = $targetDir . '/' . $file->getRelativePathname();
                        File::ensureDirectoryExists(dirname($target));
                        File::copy($file->getPathname(), $target);
                    }
                } else {
                    // Folder lain langsung copy
                    File::copyDirectory($dir, $targetDir);
                }
            }

            // Copy file di root repo (theme.json, readme, dll)
            foreach (File::files($subDir) as $file) {
                File::copy($file->getPathname(), $extractPath . '/' . $file->getFilename());
            }

            // Bersihkan tmp & zip
            File::deleteDirectory($tmpDir);
            unlink($zipPath);

            // Update version di theme.json
            $theme['version'] = $latestTag;
            File::put($themePath, json_encode($theme, JSON_PRETTY_PRINT));

            $this->info("Theme [$slug] berhasil diupdate ke {$latestTag}!");
              Artisan::call("cms:link-asset $slug --force");
        } else {
            $this->error("Gagal extract zip update.");
        }
    }
}
