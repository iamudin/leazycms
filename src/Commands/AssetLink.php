<?php 
namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AssetLink extends Command
{
    protected $signature = 'cms:link-asset {slug} {--force : Overwrite jika sudah ada link}';

    protected $description = 'Buat symlink assets template ke public/template/{slug}';

    public function handle()
    {
        $slug = $this->argument("slug");
        $target = resource_path("views/template/$slug/assets");
        $link   = public_path("template/$slug");

        // Pastikan target ada
        if (!File::exists($target)) {
            return $this->error("Folder assets tidak ditemukan: $target");
        }

        // Hapus semua file .php di dalam target
        foreach (File::allFiles($target) as $file) {
            if ($file->getExtension() === 'php') {
                File::delete($file->getPathname());
                $this->warn("File PHP dihapus: " . $file->getRelativePathname());
            }
        }

        // Jika link sudah ada
        if (File::exists($link)) {
            if (!$this->option('force')) {
                return $this->warn("Link sudah ada di $link. Gunakan --force untuk overwrite.");
            }
            File::delete($link);
        }

        // Buat symlink
        File::link($target, $link);

        $this->info("Symlink berhasil: $link → $target");
    }
}
