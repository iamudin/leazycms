<?php

namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateTemplateCommand extends Command
{
    protected $signature = 'cms:make-template 
                            {name? : Nama template yang ingin dibuat} 
                            {--slug= : Slug folder template} 
                            {--category= : Kategori template} 
                            {--modules=* : Override daftar module yang dipilih (opsional)} 
                            {--build : Otomatis build CSS Tailwind} 
                            {--force : Timpa jika folder template sudah ada}';

    protected $aliases = ['cms:create-template'];

    protected $description = 'Generate template baru dengan modul rekomendasi otomatis sesuai kategori website';

    /**
     * Definisi Kategori dan Modul Rekomendasi
     */
    public static function getCategoryPresets(): array
    {
        return [
            'Pemerintahan' => [
                'title' => 'Pemerintahan / Desa / Kelurahan / Instansi',
                'description' => 'Website Resmi Pemerintah Daerah, Desa, Kelurahan, Kecamatan, Dinas, dan BUMDes.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'layanan',
                    'pengumuman',
                    'agenda',
                    'galeri',
                    'download',
                    'kepegawaian',
                    'sambutan',
                    'transparansi-anggaran',
                    'potensi-desa',
                    'statistik',
                    'sakip',
                    'pustaka',
                    'faq',
                ],
            ],
            'Pendidikan' => [
                'title' => 'Pendidikan / Sekolah / Madrasah / Pesantren / Kampus',
                'description' => 'Website Sekolah (SD/SMP/SMA/SMK), Madrasah, Pondok Pesantren, Kampus, dan Kursus.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'pengumuman',
                    'agenda',
                    'galeri',
                    'prestasi',
                    'fasilitas',
                    'pustaka',
                    'ekstrakurikuler',
                    'kepegawaian',
                    'sambutan',
                    'download',
                    'alumni',
                    'faq',
                ],
            ],
            'Kesehatan' => [
                'title' => 'Kesehatan / Rumah Sakit / Klinik / Puskesmas',
                'description' => 'Website Rumah Sakit, Klinik Spesialis, Puskesmas, Laboratorium, dan Layanan Kesehatan.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'layanan',
                    'dokter',
                    'jadwal-dokter',
                    'poliklinik',
                    'tarif-layanan',
                    'fasilitas',
                    'pengumuman',
                    'galeri',
                    'download',
                    'faq',
                    'sambutan',
                ],
            ],
            'Bisnis' => [
                'title' => 'Bisnis / Perusahaan / UMKM / Rental / Jasa',
                'description' => 'Website Profil Bisnis, Corporate, Katalog Produk UMKM, Rental Mobil/Motor, dan Jasa Profesional.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'layanan',
                    'produk',
                    'portofolio',
                    'testimoni',
                    'tim',
                    'galeri',
                    'faq',
                    'download',
                ],
            ],
            'Organisasi' => [
                'title' => 'Organisasi / Yayasan / Komunitas / Masjid',
                'description' => 'Website Yayasan Sosial, Komunitas Hobi, Masjid/Keagamaan, LSM, dan Ormas.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'program-kerja',
                    'agenda',
                    'galeri',
                    'pengumuman',
                    'kepegawaian',
                    'sambutan',
                    'download',
                    'pustaka',
                    'testimoni',
                    'faq',
                ],
            ],
            'Portal Berita' => [
                'title' => 'Portal Berita & Media Informasi',
                'description' => 'Website Media Siber, Berita Online, Majalah Digital, dan Publikasi Informasi Publik.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'galeri',
                    'pengumuman',
                    'agenda',
                    'kepegawaian',
                    'faq',
                ],
            ],
            'Personal' => [
                'title' => 'Personal Branding / Portfolio / Blog',
                'description' => 'Website Pribadi, Portofolio Kreator, Resume Profesional, Konsultan, dan Blog Pribadi.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'layanan',
                    'portofolio',
                    'prestasi',
                    'testimoni',
                    'galeri',
                    'download',
                ],
            ],
            'Umum' => [
                'title' => 'Umum & Universal (Multi-Purpose Portal)',
                'description' => 'Template serbaguna yang cocok untuk berbagai kebutuhan website standar.',
                'modules' => [
                    'banner',
                    'menu',
                    'berita',
                    'page',
                    'layanan',
                    'pengumuman',
                    'galeri',
                    'download',
                    'sambutan',
                    'faq',
                ],
            ],
        ];
    }

    public function handle()
    {
        $this->newLine();
        $this->line('=================================================================');
        $this->line('        🎨 LEAZYCMS - GENERATOR TEMPLATE BARU 🎨');
        $this->line('  Generator template modular dengan modul otomatis per kategori');
        $this->line('=================================================================');
        $this->newLine();

        // 1. Nama Template
        $name = $this->argument('name');
        if (empty($name)) {
            if (function_exists('Laravel\Prompts\text')) {
                $name = text(
                    label: 'Nama Template',
                    placeholder: 'Contoh: Web Sekolah Modern / Portal Desa Sukamaju / RSUD Sehat',
                    required: true
                );
            } else {
                $name = $this->ask('Masukkan Nama Template (Contoh: Web Sekolah Modern):');
            }
        }

        if (empty($name)) {
            $this->error('❌ Nama template tidak boleh kosong!');
            return 1;
        }

        // 2. Slug Direktori
        $slug = $this->option('slug') ?: Str::slug($name);
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $slug));

        $templateDir = resource_path("views/template/{$slug}");
        $publicDir = public_path("template/{$slug}/assets/css");

        if (File::exists($templateDir) && !$this->option('force')) {
            $this->warn("⚠️  Folder template [{$slug}] sudah ada di: {$templateDir}");
            if (!$this->confirm('Apakah Anda ingin menimpa (overwrite) file template ini?', false)) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        // 3. Kategori Template (User hanya menentukan kategori web)
        $presets = self::getCategoryPresets();
        $categoryOptions = [];
        foreach ($presets as $cKey => $cData) {
            $categoryOptions[$cKey] = $cData['title'];
        }

        $category = $this->option('category');
        if (empty($category) || !isset($presets[$category])) {
            if (function_exists('Laravel\Prompts\select')) {
                $category = select(
                    label: 'Pilih Kategori Website:',
                    options: $categoryOptions,
                    default: 'Umum'
                );
            } else {
                $category = $this->choice('Pilih Kategori Website:', array_keys($categoryOptions), 0);
            }
        }

        $selectedPreset = $presets[$category] ?? $presets['Umum'];
        $author = 'LeazyCMS Developer';
        $description = "Template {$name} ({$category}) modern responsif untuk LeazyCMS. " . $selectedPreset['description'];

        // 4. Modul Rekomendasi Otomatis berdasarkan Kategori Web
        $customModules = $this->option('modules');
        $selectedModules = !empty($customModules) ? $customModules : $selectedPreset['modules'];

        $allConfigModules = config('modules.menu', []);

        $this->newLine();
        $this->info("🎯 Kategori Terpilih: <comment>{$category}</comment> ({$selectedPreset['title']})");
        $this->line("📦 <info>Sistem telah menetapkan modul rekomendasi untuk kategori ini:</info>");
        foreach ($selectedModules as $m) {
            $title = $allConfigModules[$m]['title'] ?? ucfirst(str_replace('-', ' ', $m));
            $this->line("   ✓ <comment>{$m}</comment> - {$title}");
        }

        $this->newLine();
        $this->info("🚀 Membuat struktur template [{$slug}]...");

        // Buat folder-folder yang dibutuhkan
        File::ensureDirectoryExists($templateDir);
        File::ensureDirectoryExists($templateDir . '/assets/css');
        File::ensureDirectoryExists($publicDir);

        // 5. Generate Semua File Template
        $this->generateThemeJson($templateDir, $name, $slug, $author, $category, $description);
        $this->generateModulesBlade($templateDir, $selectedModules, $allConfigModules);
        $this->generateHeaderBlade($templateDir, $name, $slug, $category);
        $this->generateFooterBlade($templateDir, $name, $category);
        $this->generateHomeBlade($templateDir, $name, $category, $selectedModules);
        $this->generateIndexBlade($templateDir, $name);
        $this->generateDetailBlade($templateDir, $name);
        $this->generateSidebarBlade($templateDir, $selectedModules);
        $this->generateSearchBlade($templateDir, $name);
        $this->generate404Blade($templateDir, $name);
        $this->generateInputCss($templateDir, $slug);
        $this->generateStyleCssPlaceholder($templateDir, $publicDir);

        // 6. Update package.json dengan script Tailwind
        $this->updatePackageJsonScripts($slug);

        $this->newLine();
        $this->info("🎉 Template [{$name}] ({$slug}) berhasil dibuat dengan kategori [{$category}]!");
        $this->info("📁 Lokasi file template: <info>resources/views/template/{$slug}</info>");
        $this->info("📁 Lokasi public assets : <info>public/template/{$slug}/assets/css</info>");

        // 7. Opsi Compile Tailwind CSS
        $shouldBuild = $this->option('build');
        if (!$shouldBuild && $this->input->isInteractive()) {
            $shouldBuild = $this->confirm('Apakah Anda ingin langsung men-compile Tailwind CSS sekarang?', true);
        }

        if ($shouldBuild) {
            $this->buildTailwindCss($slug);
        } else {
            $this->newLine();
            $this->line("👉 Untuk mulai mengembangkan & build Tailwind CSS, jalankan:");
            $this->line("   <comment>npm run dev:{$slug}</comment>   (Watch mode)");
            $this->line("   <comment>npm run build:{$slug}</comment> (Production minified)");
            $this->line("   atau <comment>php artisan cms:tailwind {$slug}</comment>");
        }

        return 0;
    }

    protected function generateThemeJson($dir, $name, $slug, $author, $category, $description): void
    {
        $data = [
            'name' => $name,
            'slug' => $slug,
            'version' => '1.0.0',
            'author' => $author,
            'url' => url('/'),
            'category' => $category,
            'description' => $description,
        ];

        File::put($dir . '/theme.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line("  ✓ <info>theme.json</info> (Metadata template & kategori)");
    }

    protected function generateModulesBlade($dir, $selectedModules, $allModules): void
    {
        $moduleConfigs = [];
        $noIndexModules = ['banner', 'menu', 'countdown', 'jadwal-dokter', 'tarif-layanan', 'testimoni'];

        foreach ($selectedModules as $mod) {
            if ($mod === 'banner' || $mod === 'menu') {
                $moduleConfigs[$mod] = [
                    'active' => true,
                ];
            } else {
                $hasIndex = !in_array($mod, $noIndexModules);
                $hasDetail = !in_array($mod, ['jadwal-dokter', 'tarif-layanan', 'testimoni']);
                $moduleConfigs[$mod] = [
                    'active' => true,
                    'web' => [
                        'detail' => $hasDetail,
                        'index' => $hasIndex,
                        'auto_query' => true,
                    ],
                ];
            }
        }

        $exportedArray = $this->formatPhpArray($moduleConfigs, 1);

        $content = <<<PHP
<?php

use_module({$exportedArray});

PHP;

        File::put($dir . '/modules.blade.php', $content);
        $this->line("  ✓ <info>modules.blade.php</info> (Konfigurasi use_module rekomendasi)");
    }

    protected function generateHeaderBlade($dir, $name, $slug, $category): void
    {
        $content = <<<'BLADE'
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ meta_title() }}</title>

    <!-- Font & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Template Compiled Stylesheet -->
    <link rel="stylesheet" href="{{ template_asset('assets/css/style.css') }}">

    <style>
        html, body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            overflow-x: hidden;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .brand-gradient {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        }

        .bg-pattern {
            background-image: radial-gradient(rgba(2, 132, 199, 0.12) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        .bg-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">

    <!-- Top Bar Informasi -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Website Resmi</span>
                </span>
                <span class="hidden md:inline text-slate-600">&bull;</span>
                <span class="hidden md:inline">{{ get_option('site_tagline') ?? 'Portal Informasi dan Pelayanan Terpadu' }}</span>
            </div>
            <div class="flex items-center gap-4">
                @if(get_option('phone') || get_option('nomor_whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', get_option('nomor_whatsapp') ?? get_option('phone') ?? '') }}" target="_blank" class="hover:text-white flex items-center gap-1">
                        <i class="fab fa-whatsapp text-emerald-400"></i> {{ get_option('nomor_whatsapp') ?? get_option('phone') }}
                    </a>
                @endif
                @if(get_option('email'))
                    <a href="mailto:{{ get_option('email') }}" class="hover:text-white flex items-center gap-1">
                        <i class="fa-regular fa-envelope"></i> {{ get_option('email') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 glass-nav border-b border-slate-200/80 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                <!-- Logo & Brand Title -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    @if(get_option('logo_image'))
                        <img src="{{ get_option('logo_image') }}" alt="{{ get_option('site_title') ?? 'Logo' }}" class="h-10 w-auto object-contain">
                    @else
                        <div class="w-10 h-10 rounded-xl brand-gradient flex items-center justify-center text-white font-black text-lg shadow-sm">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                    @endif
                    <div>
                        <div class="text-lg font-extrabold text-slate-900 leading-tight group-hover:text-sky-600 transition-colors">
                            {{ get_option('site_title') ?? config('app.name') }}
                        </div>
                        <div class="text-[11px] font-semibold text-slate-500 line-clamp-1">
                            {{ get_option('site_description') ?? 'Portal Resmi' }}
                        </div>
                    </div>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-slate-700">
                    <a href="{{ url('/') }}" class="hover:text-sky-600 transition-colors {{ request()->is('/') ? 'text-sky-600 font-bold' : '' }}">
                        Beranda
                    </a>
                    @if(current_module_exists('berita'))
                        <a href="{{ url('/berita') }}" class="hover:text-sky-600 transition-colors {{ request()->is('berita*') ? 'text-sky-600 font-bold' : '' }}">
                            Berita
                        </a>
                    @endif
                    @if(current_module_exists('layanan'))
                        <a href="{{ url('/layanan') }}" class="hover:text-sky-600 transition-colors {{ request()->is('layanan*') ? 'text-sky-600 font-bold' : '' }}">
                            Layanan
                        </a>
                    @endif
                    @if(current_module_exists('produk'))
                        <a href="{{ url('/produk') }}" class="hover:text-sky-600 transition-colors {{ request()->is('produk*') ? 'text-sky-600 font-bold' : '' }}">
                            Produk
                        </a>
                    @endif
                    @if(current_module_exists('fasilitas'))
                        <a href="{{ url('/fasilitas') }}" class="hover:text-sky-600 transition-colors {{ request()->is('fasilitas*') ? 'text-sky-600 font-bold' : '' }}">
                            Fasilitas
                        </a>
                    @endif
                    @if(current_module_exists('prestasi'))
                        <a href="{{ url('/prestasi') }}" class="hover:text-sky-600 transition-colors {{ request()->is('prestasi*') ? 'text-sky-600 font-bold' : '' }}">
                            Prestasi
                        </a>
                    @endif
                    @if(current_module_exists('dokter'))
                        <a href="{{ url('/dokter') }}" class="hover:text-sky-600 transition-colors {{ request()->is('dokter*') ? 'text-sky-600 font-bold' : '' }}">
                            Dokter
                        </a>
                    @endif
                    @if(current_module_exists('pengumuman'))
                        <a href="{{ url('/pengumuman') }}" class="hover:text-sky-600 transition-colors {{ request()->is('pengumuman*') ? 'text-sky-600 font-bold' : '' }}">
                            Pengumuman
                        </a>
                    @endif
                    @if(current_module_exists('galeri'))
                        <a href="{{ url('/galeri') }}" class="hover:text-sky-600 transition-colors {{ request()->is('galeri*') ? 'text-sky-600 font-bold' : '' }}">
                            Galeri
                        </a>
                    @endif
                    @if(current_module_exists('pustaka'))
                        <a href="{{ url('/pustaka') }}" class="hover:text-sky-600 transition-colors {{ request()->is('pustaka*') ? 'text-sky-600 font-bold' : '' }}">
                            Pustaka
                        </a>
                    @endif
                </nav>

                <!-- Action Button Desktop -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ url('/search') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </a>
                    @if(get_option('nomor_whatsapp'))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', get_option('nomor_whatsapp')) }}" target="_blank" class="px-5 py-2.5 rounded-xl font-bold text-xs text-white brand-gradient hover:opacity-95 shadow-md shadow-sky-500/20 transition flex items-center gap-2">
                            <i class="fab fa-whatsapp text-sm"></i> Kontak Kami
                        </a>
                    @endif
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex lg:hidden items-center gap-2">
                    <button id="mobile-menu-toggle" type="button" class="p-2.5 rounded-xl bg-slate-100 text-slate-700 hover:text-sky-600 focus:outline-none">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 pt-3 pb-6 space-y-2">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('/') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                <i class="fa-solid fa-house w-6"></i> Beranda
            </a>
            @if(current_module_exists('berita'))
                <a href="{{ url('/berita') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('berita*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-newspaper w-6"></i> Berita
                </a>
            @endif
            @if(current_module_exists('layanan'))
                <a href="{{ url('/layanan') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('layanan*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-cubes w-6"></i> Layanan
                </a>
            @endif
            @if(current_module_exists('produk'))
                <a href="{{ url('/produk') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('produk*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-box-open w-6"></i> Produk
                </a>
            @endif
            @if(current_module_exists('fasilitas'))
                <a href="{{ url('/fasilitas') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('fasilitas*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-building w-6"></i> Fasilitas
                </a>
            @endif
            @if(current_module_exists('prestasi'))
                <a href="{{ url('/prestasi') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('prestasi*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-trophy w-6"></i> Prestasi
                </a>
            @endif
            @if(current_module_exists('pustaka'))
                <a href="{{ url('/pustaka') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('pustaka*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-book-open w-6"></i> Pustaka
                </a>
            @endif
            @if(current_module_exists('dokter'))
                <a href="{{ url('/dokter') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('dokter*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-user-md w-6"></i> Tim Dokter
                </a>
            @endif
            @if(current_module_exists('pengumuman'))
                <a href="{{ url('/pengumuman') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('pengumuman*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-bullhorn w-6"></i> Pengumuman
                </a>
            @endif
            @if(current_module_exists('galeri'))
                <a href="{{ url('/galeri') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('galeri*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-camera w-6"></i> Galeri
                </a>
            @endif
            @if(current_module_exists('download'))
                <a href="{{ url('/download') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->is('download*') ? 'bg-sky-50 text-sky-600' : 'text-slate-700' }}">
                    <i class="fa-solid fa-file-arrow-down w-6"></i> Unduhan
                </a>
            @endif
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            const menu = document.getElementById('mobile-menu');
            if (toggleBtn && menu) {
                toggleBtn.addEventListener('click', function () {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>
BLADE;

        File::put($dir . '/header.blade.php', $content);
        $this->line("  ✓ <info>header.blade.php</info> (Navigasi header responsif)");
    }

    protected function generateFooterBlade($dir, $name, $category): void
    {
        $content = <<<'BLADE'
    <!-- Main Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-auto border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">

                <!-- Col 1: Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        @if(get_option('logo_image'))
                            <img src="{{ get_option('logo_image') }}" alt="{{ get_option('site_title') ?? 'Logo' }}" class="h-10 w-auto object-contain brightness-0 invert">
                        @else
                            <div class="w-10 h-10 rounded-xl brand-gradient flex items-center justify-center text-white font-black text-lg">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        @endif
                        <span class="text-lg font-extrabold text-white">{{ get_option('site_title') ?? config('app.name') }}</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        {{ get_option('site_description') ?? 'Memberikan pelayanan informasi terbaik, transparan, dan terintegrasi untuk masyarakat.' }}
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        @if(get_option('facebook'))
                            <a href="{{ get_option('facebook') }}" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-sky-600 text-slate-300 hover:text-white flex items-center justify-center text-sm transition">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        @endif
                        @if(get_option('instagram'))
                            <a href="{{ get_option('instagram') }}" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-pink-600 text-slate-300 hover:text-white flex items-center justify-center text-sm transition">
                                <i class="fab fa-instagram"></i>
                            </a>
                        @endif
                        @if(get_option('youtube'))
                            <a href="{{ get_option('youtube') }}" target="_blank" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-red-600 text-slate-300 hover:text-white flex items-center justify-center text-sm transition">
                                <i class="fab fa-youtube"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Tautan Pintas</h4>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-slate-400">
                        <li><a href="{{ url('/') }}" class="hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-sky-500"></i> Beranda</a></li>
                        @if(current_module_exists('berita'))
                            <li><a href="{{ url('/berita') }}" class="hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-sky-500"></i> Berita & Artikel</a></li>
                        @endif
                        @if(current_module_exists('layanan'))
                            <li><a href="{{ url('/layanan') }}" class="hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-sky-500"></i> Layanan Kami</a></li>
                        @endif
                        @if(current_module_exists('pengumuman'))
                            <li><a href="{{ url('/pengumuman') }}" class="hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-sky-500"></i> Pengumuman</a></li>
                        @endif
                        @if(current_module_exists('download'))
                            <li><a href="{{ url('/download') }}" class="hover:text-white transition-colors flex items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px] text-sky-500"></i> Pusat Unduhan</a></li>
                        @endif
                    </ul>
                </div>

                <!-- Col 3: Kontak Layanan -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Kontak & Alamat</h4>
                    <ul class="space-y-3 text-xs sm:text-sm text-slate-400">
                        @if(get_option('address'))
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-location-dot text-sky-500 mt-1"></i>
                                <span>{{ get_option('address') }}</span>
                            </li>
                        @endif
                        @if(get_option('phone') || get_option('nomor_whatsapp'))
                            <li class="flex items-center gap-2.5">
                                <i class="fab fa-whatsapp text-emerald-400"></i>
                                <span>{{ get_option('nomor_whatsapp') ?? get_option('phone') }}</span>
                            </li>
                        @endif
                        @if(get_option('email'))
                            <li class="flex items-center gap-2.5">
                                <i class="fa-regular fa-envelope text-sky-500"></i>
                                <span>{{ get_option('email') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Col 4: Jam Operasional -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Jam Layanan</h4>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700/80 space-y-2 text-xs">
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Senin - Jumat</span>
                            <span class="font-bold text-emerald-400">{{ get_option('jam_kerja') ?? '08.00 - 16.00 WIB' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-400">
                            <span>Sabtu - Minggu</span>
                            <span class="font-bold text-rose-400">Tutup</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="mt-12 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ get_option('site_title') ?? config('app.name') }}. All rights reserved.</p>
                <p class="flex items-center gap-1">
                    <span>Powered by</span>
                    <a href="https://leazycms.com" target="_blank" class="font-bold text-slate-300 hover:text-white">LeazyCMS</a>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
BLADE;

        File::put($dir . '/footer.blade.php', $content);
        $this->line("  ✓ <info>footer.blade.php</info> (Footer multi-kolom)");
    }

    protected function generateHomeBlade($dir, $name, $category, $selectedModules): void
    {
        $content = <<<'BLADE'
@php
    $heroSlides = function_exists('get_banner') ? get_banner('home-slider', 5) : [];
    $sambutan = function_exists('query') && current_module_exists('sambutan') ? query()->detail('sambutan') : null;
    $berita = function_exists('query') && current_module_exists('berita') ? query()->index_limit('berita', 3) : collect();
    $layanan = function_exists('query') && current_module_exists('layanan') ? query()->index_limit('layanan', 4) : collect();
    $produk = function_exists('query') && current_module_exists('produk') ? query()->index_limit('produk', 4) : collect();
    $fasilitas = function_exists('query') && current_module_exists('fasilitas') ? query()->index_limit('fasilitas', 4) : collect();
    $prestasi = function_exists('query') && current_module_exists('prestasi') ? query()->index_limit('prestasi', 3) : collect();
    $dokter = function_exists('query') && current_module_exists('dokter') ? query()->index_limit('dokter', 4) : collect();
    $pengumuman = function_exists('query') && current_module_exists('pengumuman') ? query()->index_limit('pengumuman', 3) : collect();
    $galeri = function_exists('query') && current_module_exists('galeri') ? query()->index_limit('galeri', 4) : collect();
    $pustaka = function_exists('query') && current_module_exists('pustaka') ? query()->index_limit('pustaka', 4) : collect();
    $testimoni = function_exists('query') && current_module_exists('testimoni') ? query()->index_limit('testimoni', 3) : collect();
@endphp

<!-- Hero Section -->
<section class="relative bg-slate-900 text-white overflow-hidden py-16 lg:py-24 bg-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-6 max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-sky-300 backdrop-blur-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>{{ get_option('hero_badge') ?? 'Selamat Datang di Portal Resmi' }}</span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
            {{ get_option('site_title') ?? config('app.name') }}
        </h1>

        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
            {{ get_option('site_description') ?? 'Menghadirkan pelayanan informasi terpadu, cepat, transparan, dan akurat untuk seluruh masyarakat.' }}
        </p>

        <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            @if(current_module_exists('layanan'))
                <a href="{{ url('/layanan') }}" class="px-7 py-3.5 rounded-xl font-bold text-sm text-slate-900 bg-white hover:bg-slate-100 shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fa-solid fa-cubes text-sky-600"></i> Layanan Kami
                </a>
            @elseif(current_module_exists('produk'))
                <a href="{{ url('/produk') }}" class="px-7 py-3.5 rounded-xl font-bold text-sm text-slate-900 bg-white hover:bg-slate-100 shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-sky-600"></i> Katalog Produk
                </a>
            @elseif(current_module_exists('fasilitas'))
                <a href="{{ url('/fasilitas') }}" class="px-7 py-3.5 rounded-xl font-bold text-sm text-slate-900 bg-white hover:bg-slate-100 shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fa-solid fa-building text-sky-600"></i> Fasilitas
                </a>
            @endif
            @if(current_module_exists('berita'))
                <a href="{{ url('/berita') }}" class="px-7 py-3.5 rounded-xl font-bold text-sm text-white bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-sky-300"></i> Berita Terkini
                </a>
            @endif
        </div>
    </div>
</section>

<!-- Sambutan Pimpinan Section -->
@if($sambutan)
<section class="py-16 bg-white border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-8 lg:p-12 rounded-3xl bg-slate-50 border border-slate-200/80 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-4 text-center flex flex-col items-center">
                @if(!empty($sambutan->thumbnail))
                    <img src="{{ $sambutan->thumbnail }}" alt="{{ $sambutan->title }}" class="w-44 h-44 rounded-3xl object-cover shadow-md border-4 border-white mb-3">
                @else
                    <div class="w-44 h-44 rounded-3xl brand-gradient flex items-center justify-center text-white text-5xl shadow-md border-4 border-white mb-3">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                @endif
                <h3 class="text-base font-extrabold text-slate-900">{{ $sambutan->field?->name ?? $sambutan->field?->nama ?? $sambutan->title }}</h3>
                <span class="text-xs font-semibold text-sky-600">{{ $sambutan->field?->jabatan ?? 'Pimpinan' }}</span>
            </div>
            <div class="lg:col-span-8 space-y-4">
                <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Kata Sambutan</span>
                <h2 class="text-2xl font-extrabold text-slate-900">{{ $sambutan->title }}</h2>
                <div class="text-sm text-slate-600 leading-relaxed line-clamp-4">
                    {!! $sambutan->content !!}
                </div>
                <a href="{{ url($sambutan->url) }}" class="inline-flex items-center gap-2 text-xs font-bold text-sky-600 hover:underline pt-2">
                    <span>Baca Sambutan Lengkap</span> <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Layanan Section -->
@if(current_module_exists('layanan') && $layanan->count() > 0)
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
            <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Layanan Terpadu</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Fasilitas & Layanan Kami</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($layanan as $item)
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-sky-300 hover:shadow-lg transition-all flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-sky-600 transition">
                            <a href="{{ url($item->url) }}">{{ $item->title }}</a>
                        </h3>
                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed">
                            {{ Str::limit(strip_tags($item->content ?? ''), 100) }}
                        </p>
                    </div>
                    <a href="{{ url($item->url) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:underline pt-4 mt-4 border-t border-slate-200/60">
                        <span>Lihat Detail</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Produk Section (Bisnis) -->
@if(current_module_exists('produk') && $produk->count() > 0)
<section class="py-16 lg:py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
            <div>
                <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Katalog Unggulan</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Produk & Layanan</h2>
            </div>
            <a href="{{ url('/produk') }}" class="inline-flex items-center gap-2 text-xs font-bold text-sky-600 hover:underline">
                <span>Lihat Semua Produk</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($produk as $p)
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="aspect-square bg-slate-100 relative overflow-hidden">
                        @if(!empty($p->thumbnail))
                            <img src="{{ $p->thumbnail }}" alt="{{ $p->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <i class="fa-solid fa-box-open text-3xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-5 flex flex-col flex-grow justify-between space-y-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-sky-600 transition line-clamp-2">
                                <a href="{{ url($p->url) }}">{{ $p->title }}</a>
                            </h3>
                            @if(!empty($p->field?->harga_normal_rp))
                                <div class="text-xs font-bold text-emerald-600 mt-1">Rp {{ number_format((float) preg_replace('/[^0-9]/', '', $p->field->harga_normal_rp), 0, ',', '.') }}</div>
                            @endif
                        </div>
                        <a href="{{ url($p->url) }}" class="text-xs font-bold text-sky-600 hover:underline pt-2 border-t border-slate-100 flex items-center gap-1">
                            <span>Detail Produk</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Dokter Section (Kesehatan) -->
@if(current_module_exists('dokter') && $dokter->count() > 0)
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
            <div>
                <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Tenaga Medis</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Tim Dokter Spesialis</h2>
            </div>
            <a href="{{ url('/dokter') }}" class="inline-flex items-center gap-2 text-xs font-bold text-sky-600 hover:underline">
                <span>Lihat Semua Dokter</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($dokter as $d)
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 text-center space-y-3 group hover:shadow-md transition">
                    <div class="w-28 h-28 mx-auto rounded-full overflow-hidden bg-slate-200 border-2 border-white shadow">
                        @if(!empty($d->thumbnail))
                            <img src="{{ $d->thumbnail }}" alt="{{ $d->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-3xl">
                                <i class="fa-solid fa-user-md"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-sky-600 transition">
                            <a href="{{ url($d->url) }}">{{ $d->title }}</a>
                        </h3>
                        <span class="text-xs text-sky-600 font-semibold block mt-0.5">{{ $d->field?->spesialisasi ?? 'Dokter Medis' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Berita Terkini Section -->
@if(current_module_exists('berita') && $berita->count() > 0)
<section class="py-16 lg:py-20 bg-slate-50 border-t border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
            <div>
                <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">Publikasi Terbaru</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Berita & Artikel</h2>
            </div>
            <a href="{{ url('/berita') }}" class="inline-flex items-center gap-2 text-xs font-bold text-sky-600 hover:underline">
                <span>Lihat Semua Berita</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($berita as $b)
                <article class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col group">
                    <div class="relative overflow-hidden aspect-[16/10] bg-slate-100">
                        @if(!empty($b->thumbnail))
                            <img src="{{ $b->thumbnail }}" alt="{{ $b->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <i class="fa-regular fa-image text-3xl"></i>
                            </div>
                        @endif
                        @if(!empty($b->category))
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-lg text-[10px] font-bold bg-white/90 backdrop-blur-sm text-slate-800">
                                {{ $b->category->name }}
                            </span>
                        @endif
                    </div>

                    <div class="p-6 flex flex-col flex-grow justify-between space-y-4">
                        <div>
                            <div class="text-[11px] text-slate-400 font-medium mb-2">
                                <i class="fa-regular fa-calendar mr-1"></i> {{ $b->created }}
                            </div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-sky-600 transition line-clamp-2 leading-snug">
                                <a href="{{ url($b->url) }}">{{ $b->title }}</a>
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mt-2">
                                {{ Str::limit(strip_tags($b->short_content ?? $b->content ?? ''), 110) }}
                            </p>
                        </div>
                        <a href="{{ url($b->url) }}" class="text-xs font-bold text-sky-600 hover:underline pt-3 border-t border-slate-100 flex items-center gap-1.5">
                            <span>Baca Selengkapnya</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
BLADE;

        File::put($dir . '/home.blade.php', $content);
        $this->line("  ✓ <info>home.blade.php</info> (Beranda modular terintegrasi)");
    }

    protected function generateIndexBlade($dir, $name): void
    {
        $content = <<<'BLADE'
<!-- Archive / Index Page -->
<section class="py-12 bg-slate-50 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-xs text-slate-500 mb-3 flex items-center gap-2">
            <a href="{{ url('/') }}" class="hover:text-sky-600">Beranda</a>
            <i class="fa-solid fa-chevron-right text-[9px]"></i>
            <span class="text-slate-800 font-bold">{{ config('modules.page_name') ?? 'Arsip' }}</span>
        </nav>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            {{ config('modules.page_name') ?? 'Daftar Konten' }}
        </h1>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Main Content Grid -->
            <div class="lg:col-span-8 space-y-6">
                @if(isset($index) && count($index) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($index as $row)
                            <article class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col group">
                                <div class="relative overflow-hidden aspect-[16/10] bg-slate-100">
                                    @if(!empty($row->thumbnail))
                                        <img src="{{ $row->thumbnail }}" alt="{{ $row->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <i class="fa-regular fa-image text-3xl"></i>
                                        </div>
                                    @endif
                                    @if(!empty($row->category))
                                        <span class="absolute top-3 left-3 px-3 py-1 rounded-lg text-[10px] font-bold bg-white/90 backdrop-blur-sm text-slate-800">
                                            {{ $row->category->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="p-6 flex flex-col flex-grow justify-between space-y-4">
                                    <div>
                                        <div class="text-[11px] text-slate-400 font-medium mb-2">
                                            <i class="fa-regular fa-calendar mr-1"></i> {{ $row->created }}
                                        </div>
                                        <h3 class="text-base font-bold text-slate-900 group-hover:text-sky-600 transition line-clamp-2 leading-snug">
                                            <a href="{{ url($row->url) }}">{{ $row->title }}</a>
                                        </h3>
                                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mt-2">
                                            {{ Str::limit(strip_tags($row->short_content ?? $row->content ?? ''), 110) }}
                                        </p>
                                    </div>
                                    <a href="{{ url($row->url) }}" class="text-xs font-bold text-sky-600 hover:underline pt-3 border-t border-slate-100 flex items-center gap-1.5">
                                        <span>Selengkapnya</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($index, 'links'))
                        <div class="pt-8">
                            {{ $index->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-16 bg-slate-50 rounded-3xl border border-slate-200/80 p-8 space-y-3">
                        <i class="fa-regular fa-folder-open text-4xl text-slate-400"></i>
                        <h3 class="text-base font-bold text-slate-700">Belum Ada Konten</h3>
                        <p class="text-xs text-slate-500">Konten pada bagian ini belum tersedia atau sedang dipersiapkan.</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar Column -->
            <aside class="lg:col-span-4 space-y-6">
                @include(blade_path('sidebar'))
            </aside>

        </div>
    </div>
</section>
BLADE;

        File::put($dir . '/index.blade.php', $content);
        $this->line("  ✓ <info>index.blade.php</info> (Halaman daftar postingan)");
    }

    protected function generateDetailBlade($dir, $name): void
    {
        $content = <<<'BLADE'
<!-- Detail Single Post / Page -->
<section class="py-10 bg-slate-50 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-xs text-slate-500 mb-3 flex items-center gap-2">
            <a href="{{ url('/') }}" class="hover:text-sky-600">Beranda</a>
            <i class="fa-solid fa-chevron-right text-[9px]"></i>
            @if(isset($detail->category))
                <a href="{{ url($detail->category->url) }}" class="hover:text-sky-600">{{ $detail->category->name }}</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
            @endif
            <span class="text-slate-800 font-bold truncate max-w-xs">{{ $detail->title ?? 'Detail' }}</span>
        </nav>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
            {{ $detail->title }}
        </h1>
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 mt-4">
            <span><i class="fa-regular fa-calendar mr-1.5"></i> {{ $detail->created }}</span>
            <span>&bull;</span>
            <span><i class="fa-regular fa-user mr-1.5"></i> {{ $detail->user->name ?? 'Admin' }}</span>
            <span>&bull;</span>
            <span><i class="fa-regular fa-eye mr-1.5"></i> {{ $detail->visited ?? 0 }}x dilihat</span>
        </div>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Main Content -->
            <article class="lg:col-span-8 space-y-8">
                @if(!empty($detail->thumbnail))
                    <div class="rounded-3xl overflow-hidden shadow-sm border border-slate-200/80">
                        <img src="{{ $detail->thumbnail }}" alt="{{ $detail->title }}" class="w-full h-auto object-cover">
                    </div>
                @endif

                <!-- Rich Text Content -->
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
                    {!! $detail->content !!}
                </div>

                <!-- Custom Fields if any -->
                @if(!empty($detail->field))
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Informasi Tambahan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            @foreach($detail->field as $fKey => $fVal)
                                @if(is_string($fVal) && !empty($fVal))
                                    <div class="p-3 bg-white rounded-xl border border-slate-200/60">
                                        <span class="text-slate-400 block font-medium">{{ str($fKey)->headline() }}</span>
                                        <span class="font-bold text-slate-800">{{ $fVal }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Share Buttons -->
                <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                    <span class="font-bold text-slate-700">Bagikan konten ini:</span>
                    <div class="flex items-center gap-2">
                        <a href="https://wa.me/?text={{ urlencode($detail->title . ' ' . url()->current()) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 font-bold hover:bg-emerald-600 hover:text-white transition flex items-center gap-1.5">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 font-bold hover:bg-blue-600 hover:text-white transition flex items-center gap-1.5">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                    </div>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-6">
                @include(blade_path('sidebar'))
            </aside>

        </div>
    </div>
</section>
BLADE;

        File::put($dir . '/detail.blade.php', $content);
        $this->line("  ✓ <info>detail.blade.php</info> (Halaman detail postingan / page)");
    }

    protected function generateSidebarBlade($dir, $selectedModules): void
    {
        $content = <<<'BLADE'
@php
    $recentPosts = function_exists('query') && current_module_exists('berita') ? query()->index_recent('berita') : collect();
@endphp

<!-- Search Widget -->
<div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-4">
    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Cari Informasi</h3>
    <form action="{{ url('/search') }}" method="GET" class="relative">
        <input type="text" name="q" placeholder="Ketik kata kunci..." required class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:border-sky-600">
        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-sky-600">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </button>
    </form>
</div>

<!-- Recent Posts Widget -->
@if($recentPosts->count() > 0)
<div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-4">
    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Berita Terbaru</h3>
    <div class="space-y-4">
        @foreach($recentPosts as $rp)
            <div class="flex items-center gap-3 group">
                <div class="w-16 h-14 rounded-xl overflow-hidden bg-slate-200 flex-shrink-0">
                    @if(!empty($rp->thumbnail))
                        <img src="{{ $rp->thumbnail }}" alt="{{ $rp->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                            <i class="fa-regular fa-image text-xs"></i>
                        </div>
                    @endif
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-sky-600 line-clamp-2 leading-snug transition">
                        <a href="{{ url($rp->url) }}">{{ $rp->title }}</a>
                    </h4>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $rp->created }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Contact Card Widget -->
<div class="p-6 rounded-3xl brand-gradient text-white space-y-4 text-center">
    <div class="w-12 h-12 rounded-2xl bg-white/20 mx-auto flex items-center justify-center text-xl">
        <i class="fa-solid fa-headset"></i>
    </div>
    <div>
        <h3 class="text-base font-bold">Butuh Bantuan?</h3>
        <p class="text-xs text-sky-100 mt-1">Hubungi kami melalui WhatsApp resmi untuk konsultasi dan informasi.</p>
    </div>
    @if(get_option('nomor_whatsapp'))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', get_option('nomor_whatsapp')) }}" target="_blank" class="block w-full py-2.5 rounded-xl bg-white text-sky-900 font-bold text-xs hover:bg-slate-100 transition shadow">
            <i class="fab fa-whatsapp text-emerald-600 mr-1"></i> Hubungi CS
        </a>
    @endif
</div>
BLADE;

        File::put($dir . '/sidebar.blade.php', $content);
        $this->line("  ✓ <info>sidebar.blade.php</info> (Widget sidebar modular)");
    }

    protected function generateSearchBlade($dir, $name): void
    {
        $content = <<<'BLADE'
<!-- Search Results Page -->
<section class="py-12 bg-slate-50 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
            Hasil Pencarian
        </h1>
        <p class="text-xs text-slate-500 mt-2">
            Menampilkan hasil untuk kata kunci: <strong class="text-slate-800">"{{ request('q') }}"</strong>
        </p>
    </div>
</section>

<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($index) && count($index) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($index as $row)
                    <article class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col group p-6 space-y-3">
                        <div class="text-[11px] text-slate-400 font-medium">
                            <i class="fa-regular fa-calendar mr-1"></i> {{ $row->created }}
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-sky-600 transition line-clamp-2">
                            <a href="{{ url($row->url) }}">{{ $row->title }}</a>
                        </h3>
                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed">
                            {{ Str::limit(strip_tags($row->short_content ?? $row->content ?? ''), 120) }}
                        </p>
                        <a href="{{ url($row->url) }}" class="text-xs font-bold text-sky-600 hover:underline pt-3 border-t border-slate-100 flex items-center gap-1.5">
                            <span>Baca Selengkapnya</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </article>
                @endforeach
            </div>
            @if(method_exists($index, 'links'))
                <div class="pt-8">
                    {{ $index->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16 bg-slate-50 rounded-3xl border border-slate-200/80 p-8 space-y-3 max-w-xl mx-auto">
                <i class="fa-solid fa-magnifying-glass text-4xl text-slate-400"></i>
                <h3 class="text-base font-bold text-slate-700">Tidak Ada Hasil Ditemukan</h3>
                <p class="text-xs text-slate-500">Maaf, kami tidak menemukan konten yang sesuai dengan kata kunci pencarian Anda. Silakan coba kata kunci lain.</p>
            </div>
        @endif
    </div>
</section>
BLADE;

        File::put($dir . '/search.blade.php', $content);
        $this->line("  ✓ <info>search.blade.php</info> (Halaman hasil pencarian)");
    }

    protected function generate404Blade($dir, $name): void
    {
        $content = <<<'BLADE'
<!-- 404 Error Page -->
<section class="py-20 flex-1 flex items-center justify-center min-h-[calc(100vh-250px)] bg-pattern">
    <div class="max-w-md mx-auto px-4 text-center space-y-6">
        <div class="relative">
            <h1 class="text-9xl font-black text-transparent bg-clip-text brand-gradient tracking-widest">404</h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-xs font-extrabold uppercase px-3 py-1 bg-white border border-slate-200 rounded-full shadow-sm text-slate-800">
                    Halaman Tidak Ditemukan
                </span>
            </div>
        </div>
        <p class="text-slate-600 text-sm leading-relaxed">
            Maaf, halaman atau informasi yang Anda cari tidak tersedia, telah dipindahkan, atau alamat URL salah.
        </p>
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl font-bold text-xs text-white brand-gradient shadow-lg hover:opacity-95 transition">
                <i class="fa-solid fa-house mr-2"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</section>
BLADE;

        File::put($dir . '/404.blade.php', $content);
        $this->line("  ✓ <info>404.blade.php</info> (Halaman 404 Not Found)");
    }

    protected function generateInputCss($dir, $slug): void
    {
        $content = <<<CSS
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

@source "../..";

/* Custom theme variables & utilities untuk template {$slug} */
@theme {
    --color-brand: #0284c7;
    --color-brand-dark: #0369a1;
    --color-brand-light: #e0f2fe;
    --font-sans: 'Plus Jakarta Sans', sans-serif;
}

@utility bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}

CSS;

        File::put($dir . '/assets/css/input.css', $content);
        $this->line("  ✓ <info>assets/css/input.css</info> (Tailwind CSS v4 starter config)");
    }

    protected function generateStyleCssPlaceholder($templateDir, $publicDir): void
    {
        $placeholder = "/* Compiled Tailwind CSS will be written here */\n";
        File::put($templateDir . '/assets/css/style.css', $placeholder);
        File::put($publicDir . '/style.css', $placeholder);
        $this->line("  ✓ <info>assets/css/style.css</info> (CSS output placeholder)");
    }

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
            File::put($packageJsonPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->line("  ✓ <info>package.json</info> (Ditambahkan script npm <comment>dev:{$slug}</comment> & <comment>build:{$slug}</comment>)");
        }
    }

    protected function buildTailwindCss(string $slug): void
    {
        $this->newLine();
        $this->info("⚙️  Menjalankan Tailwind CLI compile...");

        $inputRelative = "resources/views/template/{$slug}/assets/css/input.css";
        $outputRelative = "public/template/{$slug}/assets/css/style.css";
        $assetOutputCss = resource_path("views/template/{$slug}/assets/css/style.css");
        $publicOutputCss = public_path("template/{$slug}/assets/css/style.css");

        $command = "npx @tailwindcss/cli -i {$inputRelative} -o {$outputRelative} --minify";
        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(120);

        try {
            $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            if ($process->isSuccessful() && File::exists($publicOutputCss)) {
                File::copy($publicOutputCss, $assetOutputCss);
                $fileSizeKb = round(filesize($publicOutputCss) / 1024, 2);
                $this->newLine();
                $this->info("✅ Build Tailwind selesai! Output: <info>{$outputRelative}</info> ({$fileSizeKb} KB)");
            }
        } catch (\Throwable $e) {
            $this->warn("⚠️  Gagal menjalankan compile otomatis: " . $e->getMessage());
            $this->line("Silakan jalankan manual: <comment>npm run build:{$slug}</comment>");
        }
    }

    protected function formatPhpArray(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $lines = ["[\n"];

        foreach ($array as $key => $value) {
            $keyStr = is_string($key) ? "'{$key}' => " : '';
            if (is_array($value)) {
                $lines[] = $spaces . "    " . $keyStr . $this->formatPhpArray($value, $indent + 1) . ",\n";
            } elseif (is_bool($value)) {
                $lines[] = $spaces . "    " . $keyStr . ($value ? 'true' : 'false') . ",\n";
            } elseif (is_numeric($value)) {
                $lines[] = $spaces . "    " . $keyStr . $value . ",\n";
            } else {
                $lines[] = $spaces . "    " . $keyStr . "'{$value}',\n";
            }
        }

        $lines[] = $spaces . "]";
        return implode('', $lines);
    }
}
