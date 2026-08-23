<?php

namespace Leazycms\Web\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:make-module {name? : Nama atau slug modul baru} {--template= : Slug nama template target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wizard interaktif untuk membuat modul baru dan otomatis menambahkannya ke modules.blade.php';

    /**
     * Aliases for the command.
     *
     * @var array
     */
    protected $aliases = ['cms:create-module', 'cms:add-module'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================================");
        $this->info("             🧩 LEAZYCMS - ADD MODULE WIZARD 🧩");
        $this->info("  Wizard interaktif pembuat modul baru dengan fungsi add_module()");
        $this->info("=================================================================\n");

        // 1. Pilih Template Target
        $targetTemplate = $this->resolveTargetTemplate();
        if (!$targetTemplate) {
            $this->error("Proses dibatalkan: Tidak ada template target yang dipilih.");
            return 1;
        }

        $templatePath = $targetTemplate['path'];
        $templateName = $targetTemplate['name'];
        $modulesFile = $targetTemplate['modules_file'];

        $this->line("🎯 Target Template: <comment>{$templateName}</comment>");
        $this->line("📁 File Tujuan    : <comment>{$modulesFile}</comment>\n");

        // 2. Identitas Modul
        $nameInput = $this->argument('name') ?: $this->ask('Masukkan nama / slug modul (contoh: lowongan-kerja, kegiatan, alumni)');
        if (empty($nameInput)) {
            $this->error('Nama modul wajib diisi!');
            return 1;
        }

        $slug = Str::slug($nameInput);
        $defaultTitle = Str::headline($slug);

        $title = $this->ask("Judul Menu Modul (Admin & Navigasi)", $defaultTitle);
        $description = $this->ask("Deskripsi Modul", "Menu untuk mengelola data {$title}");
        
        $suggestedIcon = $this->suggestIcon($slug);
        $icon = $this->ask("Font Awesome Icon (contoh: fa-briefcase, fa-newspaper, fa-cube)", $suggestedIcon);
        if (!str_starts_with($icon, 'fa-') && !str_starts_with($icon, 'fab fa-') && !str_starts_with($icon, 'fas fa-')) {
            $icon = 'fa-' . ltrim($icon, 'fa-');
        }

        $position = (int) $this->ask("Nomor Posisi / Urutan Menu di Sidebar", 15);

        // 3. Fitur Form Dasar
        $this->info("\n--- 🛠️  Fitur & Kemampuan Form ---");
        $hasThumbnail = $this->confirm("Aktifkan Cover / Gambar Utama (thumbnail)?", true);
        $hasEditor = $this->confirm("Aktifkan WYSIWYG Rich Text Editor untuk konten?", true);
        $hasCategory = $this->confirm("Aktifkan Kategori Modul?", true);
        $hasTag = $this->confirm("Aktifkan Tagar / Tag Modul?", true);
        $uniqueTitle = $this->confirm("Apakah Judul harus Unik?", false);

        // 4. Looping Data / Repeater (Galeri / Lampiran Berulang)
        $this->info("\n--- 🔁 Data Looping / Galeri Berulang (Repeater) ---");
        $hasLooping = $this->confirm("Apakah modul membutuhkan Data Looping / Galeri Berulang (Repeater)?", false);
        $loopingName = null;
        $loopingData = [];

        if ($hasLooping) {
            $loopingName = $this->ask("Nama Bagian Looping", "Galeri Foto {$title}");
            $loopType = $this->choice(
                "Pilih tipe struktur data looping:",
                [
                    "Galeri Foto Standar ([Caption, text], [Foto, file])",
                    "Lampiran Berkas / Dokumen ([Keterangan, text], [File Berkas, file])",
                    "Custom Sub-Fields"
                ],
                0
            );

            if (str_starts_with($loopType, "Galeri Foto")) {
                $loopingData = [
                    ['Caption', 'text'],
                    ['Foto', 'file'],
                ];
            } elseif (str_starts_with($loopType, "Lampiran Berkas")) {
                $loopingData = [
                    ['Keterangan', 'text'],
                    ['File', 'file'],
                ];
            } else {
                $loopingData = $this->wizardCustomLooping();
            }
        }

        // 5. Custom Fields Wizard
        $this->info("\n--- 📝 Custom Fields Wizard ---");
        $customFields = [];
        $hasCustomFields = $this->confirm("Apakah ingin menambahkan Custom Fields khusus untuk modul ini?", true);

        if ($hasCustomFields) {
            $customFields = $this->wizardCustomFields($title);
        }

        // 6. Datatable Columns
        $this->info("\n--- 📊 Pengaturan Tabel Data Admin (Datatable) ---");
        $dataTitle = $this->ask("Nama Kolom Utama di Datatable", "Judul / Nama {$title}");
        
        $customColumns = [];
        if (!empty($customFields)) {
            $availableColNames = [];
            foreach ($customFields as $cf) {
                if (is_array($cf) && isset($cf[0]) && is_string($cf[0])) {
                    $cType = $cf[1]['type'] ?? 'text';
                    if ($cType !== 'break' && $cType !== 'textarea' && !is_array($cType) || is_array($cType)) {
                        $availableColNames[] = $cf[0];
                    }
                }
            }

            if (!empty($availableColNames)) {
                $defaultCols = array_slice($availableColNames, 0, 3);
                $this->line("Custom fields yang tersedia untuk kolom datatable: " . implode(', ', $availableColNames));
                $colInput = $this->ask("Pilih kolom untuk datatable (pisahkan dengan koma)", implode(', ', $defaultCols));
                if (!empty($colInput)) {
                    $customColumns = array_values(array_filter(array_map('trim', explode(',', $colInput))));
                }
            }
        }

        // 7. Web Routing
        $this->info("\n--- 🌐 Pengaturan Halaman Web Publik ---");
        $webIndex = $this->confirm("Buat rute halaman arsip/daftar publik (/{$slug})?", true);
        $webDetail = $this->confirm("Buat rute halaman detail publik (/{$slug}/slug-item)?", true);
        $webApi = $this->confirm("Aktifkan REST API publik (/api/v1/{$slug})?", true);
        $webHistory = $this->confirm("Aktifkan navigasi Next / Previous postingan?", true);

        // 8. Rakit array add_module
        $moduleArray = [
            'position' => $position,
            'name' => $slug,
            'title' => $title,
            'description' => $description,
            'parent' => false,
            'icon' => $icon,
            'route' => ['index', 'create', 'show', 'update', 'delete'],
            'datatable' => [
                'custom_column' => $customColumns,
                'data_title' => $dataTitle,
            ],
            'form' => [
                'unique_title' => $uniqueTitle,
                'post_parent' => false,
                'thumbnail' => $hasThumbnail,
                'editor' => $hasEditor,
                'category' => $hasCategory,
                'tag' => $hasTag,
            ],
            'web' => [
                'api' => $webApi,
                'archive' => true,
                'index' => $webIndex,
                'detail' => $webDetail,
                'history' => $webHistory,
                'auto_query' => true,
                'sortable' => false,
            ],
            'public' => true,
            'cache' => false,
            'active' => true,
        ];

        if ($hasLooping && !empty($loopingName)) {
            $moduleArray['form']['looping_name'] = $loopingName;
            $moduleArray['form']['looping_data'] = $loopingData;
        }

        if (!empty($customFields)) {
            $moduleArray['form']['custom_field'] = $customFields;
        }

        // 9. Generate Kode PHP add_module
        $generatedCode = $this->formatAddModuleCode($moduleArray);

        $this->info("\n=================================================================");
        $this->info("                 📄 PRATINJAU KODE ADD_MODULE");
        $this->info("=================================================================");
        $this->line($generatedCode);
        $this->info("=================================================================\n");

        if ($this->confirm("Apakah Anda ingin langsung menambahkan modul ini ke [{$modulesFile}]?", true)) {
            $this->writeToModulesBlade($modulesFile, $slug, $generatedCode);
            $this->info("\n✅ Modul [{$title}] ({$slug}) berhasil ditambahkan ke: {$modulesFile}");
            
            $this->line("\n💡 <info>Tips Penggunaan di Template Blade:</info>");
            $this->line("   1. Cek ketersediaan modul : <comment>@if(current_module_exists('{$slug}'))</comment>");
            $this->line("   2. Ambil data di Beranda   : <comment>\${$slug} = query()->index_limit('{$slug}', 4);</comment>");
            $this->line("   3. Link Navigasi Header   : <comment><a href=\"{{ url('/{$slug}') }}\">{$title}</a></comment>");
            $this->line("   4. Akses Custom Field     : <comment>{{ \$detail->field->nama_field ?? '' }}</comment>");
            if ($hasLooping) {
                $this->line("   5. Akses Data Looping     : <comment>@foreach(\$detail->data as \$item) ... @endforeach</comment>");
            }
        } else {
            $this->warn("Modul tidak disimpan. Anda dapat menyalin kode pratinjau di atas secara manual.");
        }

        return 0;
    }

    /**
     * Resolve target template to write modules.blade.php
     */
    protected function resolveTargetTemplate(): ?array
    {
        $templateOption = $this->option('template');
        $baseTemplateDir = resource_path('views/template');

        if (!File::exists($baseTemplateDir)) {
            File::makeDirectory($baseTemplateDir, 0755, true);
        }

        $directories = File::directories($baseTemplateDir);
        $choices = [];

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            if ($dirName === '_backup' || str_starts_with($dirName, '.')) {
                continue;
            }
            $choices[$dirName] = [
                'name' => $dirName,
                'path' => $dir,
                'modules_file' => $dir . '/modules.blade.php',
            ];
        }

        // Add global template option
        $choices['[Global] resources/views/template'] = [
            'name' => '[Global]',
            'path' => $baseTemplateDir,
            'modules_file' => $baseTemplateDir . '/modules.blade.php',
        ];

        if ($templateOption && isset($choices[$templateOption])) {
            return $choices[$templateOption];
        }

        $activeTheme = function_exists('get_option') ? get_option('template') : null;
        $defaultChoice = ($activeTheme && isset($choices[$activeTheme])) ? $activeTheme : array_key_first($choices);

        $selectedKey = $this->choice(
            "Pilih Template Target:",
            array_keys($choices),
            $defaultChoice
        );

        return $choices[$selectedKey] ?? null;
    }

    /**
     * Suggest icon based on module slug
     */
    protected function suggestIcon(string $slug): string
    {
        $icons = [
            'berita' => 'fa-newspaper',
            'artikel' => 'fa-newspaper',
            'lowongan' => 'fa-briefcase',
            'karir' => 'fa-briefcase',
            'kerja' => 'fa-briefcase',
            'kegiatan' => 'fa-calendar-days',
            'agenda' => 'fa-calendar-days',
            'acara' => 'fa-calendar-check',
            'alumni' => 'fa-user-graduate',
            'lulusan' => 'fa-user-graduate',
            'prestasi' => 'fa-trophy',
            'penghargaan' => 'fa-award',
            'juara' => 'fa-medal',
            'fasilitas' => 'fa-building',
            'gedung' => 'fa-building-columns',
            'sarana' => 'fa-warehouse',
            'produk' => 'fa-box-open',
            'barang' => 'fa-boxes-stacked',
            'katalog' => 'fa-cart-shopping',
            'mobil' => 'fa-car',
            'motor' => 'fa-motorcycle',
            'kendaraan' => 'fa-truck',
            'rental' => 'fa-key',
            'dokter' => 'fa-user-md',
            'medis' => 'fa-stethoscope',
            'jadwal' => 'fa-calendar-check',
            'klinik' => 'fa-hospital',
            'tarif' => 'fa-money-bill-wave',
            'harga' => 'fa-tag',
            'biaya' => 'fa-file-invoice-dollar',
            'pustaka' => 'fa-book-open',
            'buku' => 'fa-book',
            'perpustakaan' => 'fa-book-bookmark',
            'portofolio' => 'fa-briefcase',
            'proyek' => 'fa-diagram-project',
            'karya' => 'fa-palette',
            'testimoni' => 'fa-comments',
            'ulasan' => 'fa-star',
            'review' => 'fa-comment-dots',
            'tim' => 'fa-users-gear',
            'karyawan' => 'fa-id-badge',
            'pengurus' => 'fa-users',
            'ekstrakurikuler' => 'fa-volleyball',
            'program' => 'fa-hand-holding-heart',
            'donasi' => 'fa-hand-holding-dollar',
            'transparansi' => 'fa-file-invoice-dollar',
            'anggaran' => 'fa-chart-pie',
            'potensi' => 'fa-map-location-dot',
            'wisata' => 'fa-mountain-sun',
            'inventaris' => 'fa-boxes-stacked',
            'surat' => 'fa-envelope-open-text',
            'sertifikat' => 'fa-certificate',
            'download' => 'fa-file-arrow-down',
            'unduhan' => 'fa-download',
            'galeri' => 'fa-camera',
            'foto' => 'fa-images',
            'video' => 'fa-video',
            'faq' => 'fa-circle-question',
            'tanya' => 'fa-comments',
        ];

        foreach ($icons as $key => $icon) {
            if (str_contains($slug, $key)) {
                return $icon;
            }
        }

        return 'fa-folder-open';
    }

    /**
     * Wizard for interactive custom fields
     */
    protected function wizardCustomFields(string $moduleTitle): array
    {
        $fields = [];
        $fieldTypes = [
            'text' => 'Input Teks Pendek (String / Text)',
            'textarea' => 'Input Teks Panjang (Textarea / Paragraf)',
            'number' => 'Angka / Nominal (Number)',
            'date' => 'Tanggal (Datepicker)',
            'time' => 'Waktu / Jam (Timepicker)',
            'file' => 'Upload Berkas / Gambar / Dokumen (File)',
            'select' => 'Pilihan Dropdown (Select Box)',
            'break' => 'Pemisah Bagian Form (Section Break Header)',
        ];

        $addMore = true;
        $counter = 1;

        while ($addMore) {
            $this->line("\n<info>Custom Field #{$counter}:</info>");

            $selectedTypeKey = $this->choice(
                "Pilih Tipe Input Field:",
                $fieldTypes,
                'text'
            );

            if ($selectedTypeKey === 'break') {
                $label = $this->ask("Judul Header Pemisah Bagian (Section Header)", "Informasi Detail {$moduleTitle}");
                $fields[] = [$label, ['type' => 'break']];
            } else {
                $label = $this->ask("Label / Nama Field (contoh: Lokasi, Biaya, Penulis, No. Telepon)");
                if (empty($label)) {
                    $this->warn("Label tidak boleh kosong, field dibatalkan.");
                    continue;
                }

                $fieldConfig = [];

                if ($selectedTypeKey === 'select') {
                    $optsInput = $this->ask("Masukkan opsi pilihan dropdown (pisahkan dengan koma, contoh: Aktif, Nonaktif, Selesai)");
                    $options = array_values(array_filter(array_map('trim', explode(',', $optsInput))));
                    $fieldConfig['type'] = $options;
                } elseif ($selectedTypeKey === 'file') {
                    $fieldConfig['type'] = 'file';
                    $customMime = $this->ask("Format file khusus (opsional, contoh: application/pdf atau kosongkan untuk default)");
                    if (!empty($customMime)) {
                        $fieldConfig['mime_type'] = $customMime;
                    }
                } else {
                    $fieldConfig['type'] = $selectedTypeKey;
                }

                $isRequired = $this->confirm("Apakah field ini Wajib Diisi (Required)?", false);
                if ($isRequired) {
                    $fieldConfig['required'] = true;
                }

                $fields[] = [$label, $fieldConfig];
            }

            $counter++;
            $addMore = $this->confirm("Tambah Custom Field lagi?", true);
        }

        return $fields;
    }

    /**
     * Wizard for custom looping data
     */
    protected function wizardCustomLooping(): array
    {
        $loopData = [];
        $addMore = true;
        $idx = 1;

        while ($addMore) {
            $colName = $this->ask("Nama Kolom Looping #{$idx} (contoh: Keterangan, File, Tautan, Jabatan)");
            if (!empty($colName)) {
                $colType = $this->choice("Tipe Kolom:", ['text' => 'Teks', 'file' => 'File / Gambar'], 'text');
                $loopData[] = [$colName, $colType];
            }
            $idx++;
            $addMore = $this->confirm("Tambah kolom data looping lagi?", count($loopData) < 2);
        }

        return $loopData;
    }

    /**
     * Format add_module array to clean PHP string
     */
    protected function formatAddModuleCode(array $module): string
    {
        $code = "add_module([\n";
        $code .= "    'position' => " . var_export($module['position'], true) . ",\n";
        $code .= "    'name' => " . var_export($module['name'], true) . ",\n";
        $code .= "    'title' => " . var_export($module['title'], true) . ",\n";
        $code .= "    'description' => " . var_export($module['description'], true) . ",\n";
        $code .= "    'parent' => false,\n";
        $code .= "    'icon' => " . var_export($module['icon'], true) . ",\n";
        $code .= "    'route' => ['index', 'create', 'show', 'update', 'delete'],\n";
        
        // Datatable
        $code .= "    'datatable' => [\n";
        $code .= "        'custom_column' => " . $this->exportInlineArray($module['datatable']['custom_column']) . ",\n";
        $code .= "        'data_title' => " . var_export($module['datatable']['data_title'], true) . ",\n";
        $code .= "    ],\n";

        // Form
        $code .= "    'form' => [\n";
        $code .= "        'unique_title' => " . ($module['form']['unique_title'] ? 'true' : 'false') . ",\n";
        $code .= "        'post_parent' => false,\n";
        $code .= "        'thumbnail' => " . ($module['form']['thumbnail'] ? 'true' : 'false') . ",\n";
        $code .= "        'editor' => " . ($module['form']['editor'] ? 'true' : 'false') . ",\n";
        $code .= "        'category' => " . ($module['form']['category'] ? 'true' : 'false') . ",\n";
        $code .= "        'tag' => " . ($module['form']['tag'] ? 'true' : 'false') . ",\n";

        if (!empty($module['form']['looping_name'])) {
            $code .= "        'looping_name' => " . var_export($module['form']['looping_name'], true) . ",\n";
            $code .= "        'looping_data' => [\n";
            foreach ($module['form']['looping_data'] as $ld) {
                $code .= "            [" . var_export($ld[0], true) . ", " . var_export($ld[1], true) . "],\n";
            }
            $code .= "        ],\n";
        }

        if (!empty($module['form']['custom_field'])) {
            $code .= "        'custom_field' => [\n";
            foreach ($module['form']['custom_field'] as $cf) {
                $fLabel = var_export($cf[0], true);
                $fConfig = $cf[1];
                $fType = $fConfig['type'];

                $configParts = [];
                if (is_array($fType)) {
                    $configParts[] = "'type' => " . $this->exportInlineArray($fType);
                } else {
                    $configParts[] = "'type' => " . var_export($fType, true);
                }

                if (!empty($fConfig['required'])) {
                    $configParts[] = "'required' => true";
                }

                if (!empty($fConfig['mime_type'])) {
                    $configParts[] = "'mime_type' => " . var_export($fConfig['mime_type'], true);
                }

                $configStr = "[" . implode(', ', $configParts) . "]";
                $code .= "            [{$fLabel}, {$configStr}],\n";
            }
            $code .= "        ],\n";
        }

        $code .= "    ],\n";

        // Web
        $code .= "    'web' => [\n";
        $code .= "        'api' => " . ($module['web']['api'] ? 'true' : 'false') . ",\n";
        $code .= "        'archive' => true,\n";
        $code .= "        'index' => " . ($module['web']['index'] ? 'true' : 'false') . ",\n";
        $code .= "        'detail' => " . ($module['web']['detail'] ? 'true' : 'false') . ",\n";
        $code .= "        'history' => " . ($module['web']['history'] ? 'true' : 'false') . ",\n";
        $code .= "        'auto_query' => true,\n";
        $code .= "        'sortable' => false,\n";
        $code .= "    ],\n";
        $code .= "    'public' => true,\n";
        $code .= "    'cache' => false,\n";
        $code .= "    'active' => true,\n";
        $code .= "]);";

        return $code;
    }

    /**
     * Export inline array representation
     */
    protected function exportInlineArray(array $arr): string
    {
        $items = array_map(fn($item) => var_export($item, true), $arr);
        return "[" . implode(', ', $items) . "]";
    }

    /**
     * Append add_module code to modules.blade.php safely
     */
    protected function writeToModulesBlade(string $filePath, string $slug, string $code): void
    {
        $dir = dirname($filePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (!File::exists($filePath)) {
            $initialContent = "<?php\n\n" . $code . "\n";
            File::put($filePath, $initialContent);
            return;
        }

        $existingContent = File::get($filePath);

        // Jika file belum memiliki tag <?php di awal
        if (!str_contains($existingContent, '<?php')) {
            $existingContent = "<?php\n\n" . ltrim($existingContent);
        }

        $updatedContent = rtrim($existingContent) . "\n\n" . $code . "\n";
        File::put($filePath, $updatedContent);
    }
}
