<?php
namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AssetLink extends Command
{
    protected $signature = 'cms:link-asset {slug} {--force : Overwrite jika sudah ada link}';
    protected $description = 'Buat symlink assets template ke public/template/{slug} dan hapus file .php, .zip, .rar sebelum link';

    public function handle()
    {
        $slug   = $this->argument('slug');
        $target = resource_path("views/template/$slug/assets");
        $link   = public_path("template/$slug/assets");
        if (!File::exists($target)) {
            $this->info("ℹ️ Folder assets tidak ditemukan. Link asset diabaikan.");
            return 0;
        }

        if (count(File::allFiles($target)) === 0) {
            $this->info("ℹ️ Folder assets kosong. Link asset diabaikan.");
            return 0;
        }

         if (!File::exists($link)) {
            File::makeDirectory( public_path("template/$slug"));
        }

        $this->info("🔍 Membersihkan file berbahaya di: $target");

        $deletedCount = 0;
        $forbiddenExt = ['php', 'zip', 'rar'];

        // fungsi rekursif pakai scandir agar hidden file ikut keambil
        $iterator = function ($dir) use (&$iterator, $forbiddenExt, &$deletedCount) {
            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..') continue;

                $path = $dir . DIRECTORY_SEPARATOR . $item;

                if (is_dir($path)) {
                    $iterator($path);
                } else {
                    // Ambil ekstensi terakhir secara manual
                    if (preg_match('/\.([a-zA-Z0-9]+)$/', $item, $m)) {
                        $ext = strtolower($m[1]);
                        if (in_array($ext, $forbiddenExt)) {
                            @unlink($path);
                            $deletedCount++;
                            $this->warn("Dihapus: " . str_replace(base_path(), '', $path));
                        }
                    }
                }
            }
        };

        $iterator($target);

        if ($deletedCount > 0) {
            $this->info("🧹 Total {$deletedCount} file berbahaya telah dihapus.");
        } else {
            $this->info("✅ Tidak ada file berbahaya ditemukan.");
        }

        // cek dan buat symlink
        if (File::exists($link)) {
            if (!$this->option('force')) {
                return $this->warn("Link sudah ada di $link. Gunakan --force untuk overwrite.");
            }

            File::delete($link);
            $this->warn("Link lama dihapus: $link");
        }

        File::link($target, $link);

        $this->info("✅ Symlink berhasil dibuat: $link → $target");
    }
}
