<?php
namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class TailwindCommand extends Command
{
    protected $signature = 'cms:tailwind 
                            {slug? : Nama folder template di resources/views/template}
                            {--init : Hanya inisialisasi file konfigurasi dan npm script tanpa build}
                            {--watch : Jalankan Tailwind dalam mode live watch / dev}
                            {--dev : Alias untuk mode watch}
                            {--minify : Minify output CSS (default true saat build)}';

    protected $description = 'Generate konfigurasi Tailwind CSS, update header.blade.php, dan build/watch asset per template';

    public function handle()
    {
        $slug = $this->argument('slug');

        // 1. Tentukan slug template jika tidak diberikan
        if (!$slug) {
            $templates = $this->getAvailableTemplates();
            if (empty($templates)) {
                $this->error("❌ Tidak ada template ditemukan di resources/views/template");
                return 1;
            }

            $default = function_exists('template') ? template() : $templates[0];
            if (!in_array($default, $templates)) {
                $default = $templates[0];
            }

            $slug = $this->choice('Pilih template yang ingin dikonfigurasi/di-build:', $templates, $default);
        }

        $templatePath = resource_path("views/template/{$slug}");
        if (!File::isDirectory($templatePath)) {
            $this->error("❌ Folder template tidak ditemukan: resources/views/template/{$slug}");
            return 1;
        }

        $this->info("🎨 Template: <comment>{$slug}</comment>");

        // 2. Setup path direktori & file
        $sourceCssDir = resource_path("views/template/{$slug}/assets/css");
        $sourceInputCss = "{$sourceCssDir}/input.css";

        $publicCssDir = public_path("template/{$slug}/assets/css");
        $publicOutputCss = "{$publicCssDir}/style.css";

        $assetOutputCss = "{$sourceCssDir}/style.css";

        // Pastikan folder source ada
        if (!File::exists($sourceCssDir)) {
            File::makeDirectory($sourceCssDir, 0755, true);
        }

        // Pastikan folder public output ada
        if (!File::exists($publicCssDir)) {
            File::makeDirectory($publicCssDir, 0755, true);
        }

        // 3. Buat input.css starter jika belum ada
        if (!File::exists($sourceInputCss)) {
            $extractedThemeVars = $this->extractThemeFromHeader($slug);
            $starterCss = <<<CSS
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

@source "../..";

/* Custom theme variables & utilities untuk template {$slug} */
@theme {
{$extractedThemeVars}
}

CSS;
            File::put($sourceInputCss, $starterCss);
            $this->info("✅ Berhasil membuat file input: <info>resources/views/template/{$slug}/assets/css/input.css</info>");
        } else {
            $this->line("ℹ️  File input CSS sudah ada: resources/views/template/{$slug}/assets/css/input.css");
        }

        // 4. Update package.json dengan script dev & build khusus template ini
        $this->updatePackageJsonScripts($slug);

        // 5. Update header.blade.php: replace Tailwind CDN dengan link style.css
        $this->updateTemplateHeader($slug);

        // Jika hanya --init, selesai di sini
        if ($this->option('init')) {
            $this->newLine();
            $this->info("🎉 Konfigurasi Tailwind untuk template [{$slug}] berhasil disiapkan!");
            $this->line("👉 Jalankan build/watch dengan salah satu cara berikut:");
            $this->line("   1. <comment>php artisan cms:tailwind {$slug} --watch</comment> (atau <comment>npm run dev:{$slug}</comment>)");
            $this->line("   2. <comment>php artisan cms:tailwind {$slug}</comment>         (atau <comment>npm run build:{$slug}</comment>)");
            return 0;
        }

        // 6. Jalankan Tailwind CLI
        $isWatch = $this->option('watch') || $this->option('dev');
        $modeText = $isWatch ? 'WATCH / LIVE MODE' : 'BUILD / PRODUCTION (MINIFIED)';

        $this->newLine();
        $this->info("🚀 Menjalankan Tailwind CSS [{$modeText}]...");

        $inputRelative = "resources/views/template/{$slug}/assets/css/input.css";
        $outputRelative = "public/template/{$slug}/assets/css/style.css";

        $command = "npx @tailwindcss/cli -i {$inputRelative} -o {$outputRelative}";
        if ($isWatch) {
            $command .= " --watch";
        } else {
            $command .= " --minify";
        }

        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(null); // Tanpa batas timeout untuk mode watch

        try {
            $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            if (!$isWatch && $process->isSuccessful()) {
                // Copy ke assets jika berhasil build
                if (File::exists($publicOutputCss)) {
                    File::copy($publicOutputCss, $assetOutputCss);
                    $fileSizeKb = round(filesize($publicOutputCss) / 1024, 2);
                    $this->newLine();
                    $this->info("✅ Build selesai! Output file: <info>{$outputRelative}</info> ({$fileSizeKb} KB)");
                    $this->line("📦 Asset juga disinkronkan ke: <comment>resources/views/template/{$slug}/assets/css/style.css</comment>");
                }
            }
        } catch (\Throwable $e) {
            $this->error("❌ Error saat menjalankan build Tailwind: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Update header.blade.php: replace CDN tailwind atau sisipkan link style.css
     */
    protected function updateTemplateHeader(string $slug): void
    {
        $headerPath = resource_path("views/template/{$slug}/header.blade.php");

        // Fallback jika tidak ada header.blade.php
        if (!File::exists($headerPath)) {
            $fallbacks = ['layout.blade.php', 'index.blade.php', 'main.blade.php'];
            foreach ($fallbacks as $fb) {
                $checkPath = resource_path("views/template/{$slug}/{$fb}");
                if (File::exists($checkPath)) {
                    $headerPath = $checkPath;
                    break;
                }
            }
        }

        if (!File::exists($headerPath)) {
            return;
        }

        $content = File::get($headerPath);
        $cssTag = '<link rel="stylesheet" href="{{ template_asset(\'assets/css/style.css\') }}">';

        // 1. Cek apakah sudah ada link stylesheet
        if (str_contains($content, "template_asset('assets/css/style.css')") || str_contains($content, 'template_asset("assets/css/style.css")')) {
            // Hapus CDN tailwind jika masih tersisa
            if (preg_match('/<script[^>]*src=["\'][^"\']*cdn\.tailwindcss\.com[^"\']*["\'][^>]*>\s*<\/script>/i', $content)) {
                $content = preg_replace('/<script[^>]*src=["\'][^"\']*cdn\.tailwindcss\.com[^"\']*["\'][^>]*>\s*<\/script>\s*/i', '', $content);
                File::put($headerPath, $content);
                $relativePath = str_replace(base_path() . '/', '', $headerPath);
                $this->info("🧹 Tailwind CDN lama telah dibersihkan dari: <info>{$relativePath}</info>");
            }
            return;
        }

        $replaced = false;

        // 2. Cari dan replace Tailwind CDN script
        $cdnRegex = '/<script[^>]*src=["\'][^"\']*(?:cdn\.tailwindcss\.com|unpkg\.com\/tailwindcss|jsdelivr\.net\/npm\/tailwindcss)[^"\']*["\'][^>]*>\s*<\/script>/i';
        if (preg_match($cdnRegex, $content)) {
            $content = preg_replace($cdnRegex, $cssTag, $content, 1);
            $replaced = true;
        }
        // 3. Jika tidak ada CDN, sisipkan sebelum </head>
        elseif (stripos($content, '</head>') !== false) {
            $content = preg_replace('/(<\/head>)/i', "    " . $cssTag . "\n$1", $content, 1);
            $replaced = true;
        }

        if ($replaced) {
            File::put($headerPath, $content);
            $relativePath = str_replace(base_path() . '/', '', $headerPath);
            $this->info("✅ Berhasil menambahkan <comment><link rel=\"stylesheet\" href=\"{{ template_asset('assets/css/style.css') }}\"></comment> ke <info>{$relativePath}</info>");
        }
    }

    /**
     * Ekstrak konfigurasi warna/font dari inline script jika ada di header.blade.php
     */
    protected function extractThemeFromHeader(string $slug): string
    {
        $headerPath = resource_path("views/template/{$slug}/header.blade.php");
        if (!File::exists($headerPath)) {
            return "    /* --color-brand: #0284c7; */\n    /* --font-sans: 'Inter', sans-serif; */";
        }

        $content = File::get($headerPath);
        $themeVars = [];

        // Cek colors di script tailwind.config
        if (preg_match('/colors\s*:\s*\{([^}]+)\}/s', $content, $match)) {
            $colorBlock = $match[1];
            if (preg_match_all('/([a-zA-Z0-9_-]+)\s*:\s*[\'"]([#a-zA-Z0-9_-]+)[\'"]/', $colorBlock, $colMatches, PREG_SET_ORDER)) {
                foreach ($colMatches as $cm) {
                    $key = strtolower($cm[1]);
                    $val = $cm[2];
                    if ($key === 'default') {
                        $themeVars[] = "    --color-brand: {$val};";
                    } else {
                        $themeVars[] = "    --color-{$key}: {$val};";
                    }
                }
            }
        }

        if (empty($themeVars)) {
            return "    /* --color-brand: #0284c7; */\n    /* --font-sans: 'Inter', sans-serif; */";
        }

        return implode("\n", $themeVars);
    }

    /**
     * Ambil semua daftar slug template yang ada
     */
    protected function getAvailableTemplates(): array
    {
        $templateDir = resource_path('views/template');
        if (!File::isDirectory($templateDir)) {
            return [];
        }

        $directories = File::directories($templateDir);
        $slugs = [];
        foreach ($directories as $dir) {
            $base = basename($dir);
            if (!str_starts_with($base, '.') && !str_starts_with($base, '_')) {
                $slugs[] = $base;
            }
        }

        return $slugs;
    }

    /**
     * Tambahkan script npm otomatis ke package.json
     */
    protected function updatePackageJsonScripts(string $slug): void
    {
        $packageJsonPath = base_path('package.json');
        if (!File::exists($packageJsonPath)) {
            return;
        }

        $content = json_decode(File::get($packageJsonPath), true);
        if (!is_array($content)) {
            return;
        }

        $inputPath = "./resources/views/template/{$slug}/assets/css/input.css";
        $outputPath = "./public/template/{$slug}/assets/css/style.css";

        $devScriptKey = "dev:{$slug}";
        $buildScriptKey = "build:{$slug}";

        $devCommand = "npx @tailwindcss/cli -i {$inputPath} -o {$outputPath} --watch";
        $buildCommand = "npx @tailwindcss/cli -i {$inputPath} -o {$outputPath} --minify";

        $changed = false;

        if (!isset($content['scripts'][$devScriptKey]) || $content['scripts'][$devScriptKey] !== $devCommand) {
            $content['scripts'][$devScriptKey] = $devCommand;
            $changed = true;
        }

        if (!isset($content['scripts'][$buildScriptKey]) || $content['scripts'][$buildScriptKey] !== $buildCommand) {
            $content['scripts'][$buildScriptKey] = $buildCommand;
            $changed = true;
        }

        if ($changed) {
            File::put(
                $packageJsonPath,
                json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
            );
            $this->info("✅ Script <comment>\"{$devScriptKey}\"</comment> & <comment>\"{$buildScriptKey}\"</comment> telah didaftarkan di <info>package.json</info>");
        }
    }
}
