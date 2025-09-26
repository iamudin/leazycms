<?php

namespace Leazycms\Web\Commands;

use ZipArchive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

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

        // Ambil daftar tags dari GitHub
        $apiUrl = "https://api.github.com/repos/{$repo}/tags";
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LaravelCMS-Updater'); // wajib
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            $this->error("Gagal terhubung ke server.");
            return;
        }

        $tags = json_decode($response, true);

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
        // Download zip dari tag
        $zipPath = storage_path("app/{$slug}.zip");
        $options = [
            "http" => [
                "header" => "User-Agent: PHP\r\n"
            ]
        ];

        $context = stream_context_create($options);
        $content = file_get_contents($downloadUrl, false, $context);
        file_put_contents($zipPath, $content);

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

            // --- Pisahkan assets dari source remote ---
            $remoteAssets = $subDir . '/assets';

            // Copy semua isi kecuali folder assets ke resources/views/template/$slug
            foreach (File::directories($subDir) as $dir) {
                if (basename($dir) !== 'assets') {
                    File::copyDirectory($dir, $extractPath . '/' . basename($dir));
                }
            }
            foreach (File::files($subDir) as $file) {
                File::copy($file->getPathname(), $extractPath . '/' . $file->getFilename());
            }

            // === Sinkronisasi folder assets ===
            $localAssets = public_path("template/$slug");
            if (File::isDirectory($remoteAssets)) {
                if (!File::isDirectory($localAssets)) {
                    File::makeDirectory($localAssets, 0755, true);
                }

                $remoteFiles = collect(File::allFiles($remoteAssets))
                    ->reject(fn($f) => $f->getExtension() === 'php');
                $localFiles = collect(File::allFiles($localAssets))
                    ->reject(fn($f) => $f->getExtension() === 'php');

                $remoteCount = $remoteFiles->count();
                $localCount  = $localFiles->count();

                $remoteSize = $remoteFiles->sum(fn($f) => $f->getSize());
                $localSize  = $localFiles->sum(fn($f) => $f->getSize());

                if ($remoteCount !== $localCount || $remoteSize !== $localSize) {
                    $this->info("Sinkronisasi assets ke public/template/$slug ...");
                    if (File::isDirectory($localAssets)) {
                        File::deleteDirectory($localAssets);
                    }

                    foreach ($remoteFiles as $file) {
                        $target = $localAssets . '/' . $file->getRelativePathname();
                        File::ensureDirectoryExists(dirname($target));
                        File::copy($file->getPathname(), $target);
                    }

                    $this->info("Assets berhasil diupdate.");
                } else {
                    $this->info("Assets sudah up to date, tidak perlu diupdate.");
                }
            } else {
                $this->info("Tidak ada folder assets di theme remote, dilewati.");
            }

            // Bersihkan tmp & zip
            File::deleteDirectory($tmpDir);
            unlink($zipPath);

            // Update version di theme.json
            $theme['version'] = $latestTag;
            File::put($themePath, json_encode($theme, JSON_PRETTY_PRINT));

            $this->info("Theme [$slug] berhasil diupdate ke {$latestTag}!");
        } else {
            $this->error("Gagal extract zip update.");
        }
    }
}
