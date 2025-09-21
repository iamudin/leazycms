<?php

namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

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

        // Baca metadata theme lokal
        $theme = json_decode(File::get($themePath), true);
        $localVersion = $theme['version'] ?? '0.0.0';
        $repo = $theme['github'] ?? null; // format: username/repo

        if (!$repo) {
            $this->error("Theme [$slug] tidak punya field 'github' di theme.json");
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
            $this->error("Failed to connect GitHub API.");
            return;
        }

        $tags = json_decode($response, true);

        if (!$tags || !isset($tags[0]['name'])) {
            $this->error("GitHub tags data tidak valid.");
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
            $extractPath = resource_path("views/template/$slug");

            // Hapus theme lama
            File::deleteDirectory($extractPath);

            // Extract ke tmp
            $tmpDir = storage_path("app/tmp_{$slug}");
            File::deleteDirectory($tmpDir);
            $zip->extractTo($tmpDir);
            $zip->close();

            // GitHub zip punya subfolder repo-hash
            $subDir = File::directories($tmpDir)[0] ?? null;
            if ($subDir) {
                File::moveDirectory($subDir, $extractPath);
            }

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
