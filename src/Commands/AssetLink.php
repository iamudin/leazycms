<?php 
namespace Leazycms\Web\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AssetLink extends Command
{
    protected $signature = 'cms:link-asset {slug} {--force : Overwrite jika sudah ada link}';

    protected $description = 'Buat symlink assets template ke public/template/dinas';

    public function handle()
    {
        $slug = $this->argument("slug");
        $target = resource_path("views/template/$slug/assets");
        $link   = public_path("template/$slug");

        if (File::exists($link)) {
            if (!$this->option('force')) {
                return $this->warn("Link sudah ada di $link. Gunakan --force untuk overwrite.");
            }
            File::delete($link);
        }

        File::link($target, $link);

        $this->info("Symlink berhasil: $link → $target");
    }
}
