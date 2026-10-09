<?php

namespace Leazycms\Web\Services;

use Leazycms\Web\Models\Category;
use Leazycms\Web\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class DummyGenerator
{
    public static function generateFallback($currentType, $module, $userId = 1)
    {
        if (Post::onType($currentType)->exists()) {
            return;
        }

        $categoryIds = [];

        if (isset($module->form->category) && $module->form->category) {
            for ($c = 1; $c <= 3; $c++) {
                $catName = fake('id_ID')->words(2, true);
                $catSlug = Str::slug($catName);
                $cat = Category::onType($currentType)->where('slug', $catSlug)->first();
                if (!$cat) {
                    $cat = Category::create([
                        'type' => $currentType,
                        'name' => ucwords($catName),
                        'slug' => $catSlug,
                        'url' => $currentType . '/category/' . $catSlug,
                        'status' => 'publish'
                    ]);
                }
                $categoryIds[] = $cat->id;
            }
        } else {
            $catSlug = 'umum';
            $cat = Category::onType($currentType)->where('slug', $catSlug)->first();
            if (!$cat) {
                $cat = Category::create([
                    'type' => $currentType,
                    'name' => 'Umum',
                    'slug' => $catSlug,
                    'url' => $currentType . '/category/' . $catSlug,
                    'status' => 'publish'
                ]);
            }
            $categoryIds[] = $cat->id;
        }

        if ($currentType == 'menu') {
            $postHeader = new Post();
            $postHeader->type = $currentType;
            $postHeader->status = 'publish';
            $postHeader->user_id = $userId;
            $postHeader->shortcut = Str::random(6);
            $postHeader->category_id = $categoryIds[0];
            $postHeader->title = 'Header';
            $postHeader->slug = 'header';
            $postHeader->url = $currentType . '/header';
            $postHeader->data_loop = [
                ['menu_id' => '1', 'menu_parent' => '0', 'menu_name' => 'Beranda', 'menu_link' => '/', 'menu_description' => 'Halaman Utama', 'menu_icon' => 'fa fa-home'],
                ['menu_id' => '2', 'menu_parent' => '0', 'menu_name' => 'Profil', 'menu_link' => '#', 'menu_description' => 'Profil Instansi', 'menu_icon' => 'fa fa-building'],
                ['menu_id' => '21', 'menu_parent' => '2', 'menu_name' => 'Tentang Kami', 'menu_link' => '#', 'menu_description' => 'Informasi Umum', 'menu_icon' => 'fa fa-info-circle'],
                ['menu_id' => '211', 'menu_parent' => '21', 'menu_name' => 'Sejarah', 'menu_link' => '#', 'menu_description' => 'Sejarah Instansi', 'menu_icon' => 'fa fa-history'],
                ['menu_id' => '212', 'menu_parent' => '21', 'menu_name' => 'Visi & Misi', 'menu_link' => '#', 'menu_description' => 'Tujuan Utama', 'menu_icon' => 'fa fa-bullseye'],
                ['menu_id' => '213', 'menu_parent' => '21', 'menu_name' => 'Tugas & Fungsi', 'menu_link' => '#', 'menu_description' => 'Tugas Pokok', 'menu_icon' => 'fa fa-tasks'],
                ['menu_id' => '22', 'menu_parent' => '2', 'menu_name' => 'Struktur Organisasi', 'menu_link' => '#', 'menu_description' => 'Struktur', 'menu_icon' => 'fa fa-sitemap'],
                ['menu_id' => '221', 'menu_parent' => '22', 'menu_name' => 'Pimpinan', 'menu_link' => '#', 'menu_description' => 'Profil Pimpinan', 'menu_icon' => 'fa fa-user-tie'],
                ['menu_id' => '222', 'menu_parent' => '22', 'menu_name' => 'Pejabat Struktural', 'menu_link' => '#', 'menu_description' => 'Daftar Pejabat', 'menu_icon' => 'fa fa-users'],
                ['menu_id' => '3', 'menu_parent' => '0', 'menu_name' => 'Layanan', 'menu_link' => '#', 'menu_description' => 'Layanan Kami', 'menu_icon' => 'fa fa-briefcase'],
                ['menu_id' => '31', 'menu_parent' => '3', 'menu_name' => 'Layanan Publik', 'menu_link' => '#', 'menu_description' => 'Layanan Masyarakat', 'menu_icon' => 'fa fa-users'],
                ['menu_id' => '311', 'menu_parent' => '31', 'menu_name' => 'Perizinan', 'menu_link' => '#', 'menu_description' => 'Layanan Izin', 'menu_icon' => 'fa fa-file-signature'],
                ['menu_id' => '312', 'menu_parent' => '31', 'menu_name' => 'Non-Perizinan', 'menu_link' => '#', 'menu_description' => 'Layanan Lain', 'menu_icon' => 'fa fa-file-alt'],
                ['menu_id' => '32', 'menu_parent' => '3', 'menu_name' => 'Pengaduan', 'menu_link' => '#', 'menu_description' => 'Lapor Pengaduan', 'menu_icon' => 'fa fa-exclamation-triangle'],
                ['menu_id' => '4', 'menu_parent' => '0', 'menu_name' => 'Informasi Publik', 'menu_link' => '#', 'menu_description' => 'Layanan PPID', 'menu_icon' => 'fa fa-info'],
                ['menu_id' => '41', 'menu_parent' => '4', 'menu_name' => 'Informasi Berkala', 'menu_link' => '#', 'menu_description' => 'Info Berkala', 'menu_icon' => 'fa fa-file'],
                ['menu_id' => '42', 'menu_parent' => '4', 'menu_name' => 'Informasi Serta Merta', 'menu_link' => '#', 'menu_description' => 'Info Serta Merta', 'menu_icon' => 'fa fa-bolt'],
                ['menu_id' => '43', 'menu_parent' => '4', 'menu_name' => 'Informasi Setiap Saat', 'menu_link' => '#', 'menu_description' => 'Info Setiap Saat', 'menu_icon' => 'fa fa-clock'],
                ['menu_id' => '5', 'menu_parent' => '0', 'menu_name' => 'Publikasi', 'menu_link' => '#', 'menu_description' => 'Daftar Publikasi', 'menu_icon' => 'fa fa-newspaper'],
                ['menu_id' => '51', 'menu_parent' => '5', 'menu_name' => 'Berita', 'menu_link' => '/berita', 'menu_description' => 'Berita Terbaru', 'menu_icon' => 'fa fa-newspaper'],
                ['menu_id' => '52', 'menu_parent' => '5', 'menu_name' => 'Galeri', 'menu_link' => '#', 'menu_description' => 'Galeri Media', 'menu_icon' => 'fa fa-images'],
                ['menu_id' => '521', 'menu_parent' => '52', 'menu_name' => 'Galeri Foto', 'menu_link' => '/galeri-foto', 'menu_description' => 'Kumpulan Foto', 'menu_icon' => 'fa fa-image'],
                ['menu_id' => '522', 'menu_parent' => '52', 'menu_name' => 'Galeri Video', 'menu_link' => '/galeri-video', 'menu_description' => 'Kumpulan Video', 'menu_icon' => 'fa fa-video'],
                ['menu_id' => '6', 'menu_parent' => '0', 'menu_name' => 'Kontak', 'menu_link' => '/kontak', 'menu_description' => 'Hubungi Kami', 'menu_icon' => 'fa fa-phone'],
            ];
            $postHeader->save();

            $postFooter = new Post();
            $postFooter->type = $currentType;
            $postFooter->status = 'publish';
            $postFooter->user_id = $userId;
            $postFooter->shortcut = Str::random(6);
            $postFooter->category_id = $categoryIds[0];
            $postFooter->title = 'Footer';
            $postFooter->slug = 'footer';
            $postFooter->url = $currentType . '/footer';
            $postFooter->data_loop = [
                ['menu_id' => '1', 'menu_parent' => '0', 'menu_name' => 'Profil', 'menu_link' => '#', 'menu_description' => 'Profil Instansi', 'menu_icon' => 'fa fa-building'],
                ['menu_id' => '11', 'menu_parent' => '1', 'menu_name' => 'Sejarah', 'menu_link' => '#', 'menu_description' => 'Sejarah Instansi', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '12', 'menu_parent' => '1', 'menu_name' => 'Visi & Misi', 'menu_link' => '#', 'menu_description' => 'Tujuan Utama', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '13', 'menu_parent' => '1', 'menu_name' => 'Struktur Organisasi', 'menu_link' => '#', 'menu_description' => 'Struktur', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '2', 'menu_parent' => '0', 'menu_name' => 'Publikasi', 'menu_link' => '#', 'menu_description' => 'Daftar Publikasi', 'menu_icon' => 'fa fa-newspaper'],
                ['menu_id' => '21', 'menu_parent' => '2', 'menu_name' => 'Berita', 'menu_link' => '/berita', 'menu_description' => 'Berita Terbaru', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '22', 'menu_parent' => '2', 'menu_name' => 'Pengumuman', 'menu_link' => '/pengumuman', 'menu_description' => 'Pengumuman Resmi', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '23', 'menu_parent' => '2', 'menu_name' => 'Artikel', 'menu_link' => '/artikel', 'menu_description' => 'Artikel', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '3', 'menu_parent' => '0', 'menu_name' => 'Link Terkait', 'menu_link' => '#', 'menu_description' => 'Tautan', 'menu_icon' => 'fa fa-link'],
                ['menu_id' => '31', 'menu_parent' => '3', 'menu_name' => 'Kementerian', 'menu_link' => '#', 'menu_description' => 'Situs Kementerian', 'menu_icon' => 'fa fa-caret-right'],
                ['menu_id' => '32', 'menu_parent' => '3', 'menu_name' => 'Pemerintah Daerah', 'menu_link' => '#', 'menu_description' => 'Situs Pemda', 'menu_icon' => 'fa fa-caret-right'],
            ];
            $postFooter->save();
        } elseif ($currentType == 'page') {
            $pageData = [
                [
                    'title' => 'Sejarah',
                    'content' => '<h2>Sejarah Instansi</h2><p>Instansi ini didirikan dengan tujuan untuk melayani masyarakat dan memajukan daerah. Sejak awal berdirinya, kami terus berkomitmen untuk memberikan pelayanan terbaik melalui berbagai inovasi dan peningkatan kualitas sumber daya manusia.</p><p>Perjalanan panjang telah dilalui dengan berbagai tantangan yang berhasil diatasi berkat dukungan dari seluruh elemen masyarakat. Ke depan, kami akan terus beradaptasi dengan perkembangan zaman demi mewujudkan visi dan misi yang telah ditetapkan.</p>'
                ],
                [
                    'title' => 'Visi dan Misi',
                    'content' => '<h2>Visi</h2><p>Mewujudkan pelayanan publik yang prima, transparan, dan akuntabel demi tercapainya kesejahteraan masyarakat yang merata dan berkelanjutan.</p><h2>Misi</h2><ul><li>Meningkatkan kualitas sumber daya aparatur yang profesional dan berintegritas.</li><li>Mendorong partisipasi aktif masyarakat dalam proses pembangunan.</li><li>Mengoptimalkan pemanfaatan teknologi informasi untuk pelayanan yang efisien.</li><li>Memperkuat sinergi antara pemerintah, swasta, dan masyarakat.</li></ul>'
                ],
                [
                    'title' => 'Tugas Pokok dan Fungsi',
                    'content' => '<h2>Tugas Pokok</h2><p>Melaksanakan urusan pemerintahan daerah berdasarkan asas otonomi dan tugas pembantuan di bidang yang menjadi kewenangan daerah, serta merumuskan kebijakan teknis sesuai dengan peraturan perundang-undangan.</p><h2>Fungsi</h2><ul><li>Perumusan kebijakan teknis di bidang terkait.</li><li>Penyelenggaraan urusan pemerintahan dan pelayanan umum.</li><li>Pembinaan dan pelaksanaan tugas di bidang yang relevan.</li><li>Pelaksanaan tugas lain yang diberikan oleh pimpinan sesuai dengan tugas dan fungsinya.</li></ul>'
                ],
                [
                    'title' => 'Struktur Organisasi',
                    'content' => '<h2>Struktur Organisasi</h2><p>Struktur organisasi dirancang untuk memastikan berjalannya tugas pokok dan fungsi secara efektif dan efisien. Terdiri dari pimpinan tertinggi, unit-unit pelaksana teknis, serta berbagai bidang atau divisi yang menangani urusan spesifik.</p><p>Setiap bagian memiliki uraian tugas yang jelas dan saling berkoordinasi dalam mencapai tujuan bersama instansi. Pembagian kewenangan diatur berdasarkan prinsip profesionalisme dan tata kelola pemerintahan yang baik (Good Corporate Governance).</p>'
                ],
                [
                    'title' => 'Syarat Pelayanan',
                    'content' => '<h2>Persyaratan Pelayanan</h2><p>Untuk memastikan proses pelayanan berjalan lancar, masyarakat dihimbau untuk melengkapi persyaratan dokumen yang dibutuhkan sesuai dengan jenis layanan yang diajukan.</p><ul><li>Fotokopi Kartu Tanda Penduduk (KTP) yang masih berlaku.</li><li>Fotokopi Kartu Keluarga (KK).</li><li>Dokumen pendukung lainnya sesuai dengan spesifikasi layanan (misal: surat pengantar RT/RW, fotokopi ijazah, dsb).</li></ul><p>Berkas yang lengkap akan mempercepat proses verifikasi dan tindak lanjut dari petugas kami.</p>'
                ],
                [
                    'title' => 'Makna Logo',
                    'content' => '<h2>Makna Logo Instansi</h2><p>Logo kami merupakan visualisasi dari identitas, visi, dan nilai-nilai luhur yang dijunjung tinggi. Setiap elemen, bentuk, dan warna memiliki filosofi tersendiri.</p><ul><li><strong>Warna Biru:</strong> Melambangkan profesionalisme, kepercayaan, dan kedamaian.</li><li><strong>Warna Emas:</strong> Menggambarkan kejayaan, kesejahteraan, dan kualitas pelayanan prima.</li><li><strong>Simbol Bintang:</strong> Menunjukkan cita-cita yang tinggi dan ketakwaan kepada Tuhan Yang Maha Esa.</li><li><strong>Bentuk Perisai:</strong> Bermakna perlindungan, keamanan, dan ketahanan dalam menghadapi berbagai tantangan.</li></ul>'
                ]
            ];

            foreach ($pageData as $index => $data) {
                $post = new Post();
                $post->type = $currentType;
                $post->status = 'publish';
                $post->user_id = $userId;
                $post->shortcut = Str::random(6);
                $post->category_id = $categoryIds[array_rand($categoryIds)];
                $post->title = $data['title'];
                $post->slug = Str::slug($post->title);
                $post->url = $post->slug;

                if (isset($module->form->thumbnail) && $module->form->thumbnail) {
                    $colors = ['31343C', '1F2937', '1E40AF', '065F46', '991B1B', '6B21A8'];
                    $bgColor = $colors[$index % count($colors)];
                    $post->media = 'https://placehold.co/800x600/EEE/' . $bgColor . '?text=' . urlencode($data['title']);
                }

                if (isset($module->form->editor) && $module->form->editor) {
                    $post->content = $data['content'];
                }

                if (isset($module->form->custom_field) && $module->form->custom_field) {
                    $generatedFields = [];
                    $safeCustomFields = json_decode(json_encode($module->form->custom_field), true);
                    foreach (custom_field_without_break($safeCustomFields) as $row) {
                        $fieldLabel = $row[0] ?? (is_string($row) ? $row : 'field');
                        $fieldKey = _us($fieldLabel);
                        $fieldType = $row[1]['type'] ?? 'text';

                        if ($fieldType == 'number') {
                            $generatedFields[$fieldKey] = fake()->randomNumber(5, true);
                        } elseif ($fieldType == 'email') {
                            $generatedFields[$fieldKey] = fake()->safeEmail();
                        } elseif ($fieldType == 'date') {
                            $generatedFields[$fieldKey] = fake()->date();
                        } elseif ($fieldType == 'phone') {
                            $generatedFields[$fieldKey] = fake('id_ID')->phoneNumber();
                        } elseif ($fieldType == 'url') {
                            $generatedFields[$fieldKey] = fake()->url();
                        } elseif ($fieldType == 'textarea') {
                            $generatedFields[$fieldKey] = "Data dummy untuk " . $fieldLabel;
                        } elseif ($fieldType == 'image' || $fieldType == 'file') {
                            $generatedFields[$fieldKey] = 'https://placehold.co/400x400/EEE/31343C?text=Gambar';
                        } else {
                            $generatedFields[$fieldKey] = 'Contoh ' . $fieldLabel . ' ' . ($index + 1);
                        }
                    }
                    $post->data_field = $generatedFields;
                }
                $post->save();
            }
        } else {
            for ($i = 1; $i <= 6; $i++) {
                $post = new Post();
                $post->type = $currentType;
                $post->status = 'publish';
                $post->user_id = $userId;
                $post->shortcut = Str::random(6);
                $post->category_id = $categoryIds[array_rand($categoryIds)];

                $indoWords = ['Pembangunan', 'Ekonomi', 'Daerah', 'Masyarakat', 'Kesejahteraan', 'Program', 'Kegiatan', 'Sosial', 'Budaya', 'Pendidikan', 'Kesehatan', 'Infrastruktur', 'Teknologi', 'Informasi', 'Layanan', 'Publik', 'Pemerintah', 'Kebijakan', 'Strategi', 'Inovasi', 'Nasional', 'Berkelanjutan', 'Digital', 'Masa', 'Depan'];
                $indoParagraphs = [
                    "Pemerintah terus berupaya meningkatkan kualitas layanan publik di berbagai sektor. Hal ini sejalan dengan visi dan misi untuk mewujudkan kesejahteraan masyarakat yang merata. Berbagai program unggulan telah diluncurkan dan terus dievaluasi efektivitasnya di lapangan. Partisipasi aktif dari seluruh elemen masyarakat sangat diharapkan untuk mendukung keberhasilan program-program tersebut.",
                    "Dalam rangka mempercepat pertumbuhan ekonomi daerah, pengembangan infrastruktur menjadi salah satu prioritas utama. Pembangunan jalan, jembatan, dan fasilitas umum lainnya terus digenjot. Selain itu, pemberdayaan Usaha Mikro, Kecil, dan Menengah (UMKM) juga terus didorong melalui berbagai pelatihan dan bantuan modal. Diharapkan langkah ini dapat membuka lapangan kerja baru dan mengurangi tingkat pengangguran.",
                    "Sektor pendidikan dan kesehatan juga tidak luput dari perhatian. Peningkatan kualitas sumber daya manusia merupakan kunci untuk menghadapi tantangan global di masa depan. Oleh karena itu, penyediaan fasilitas pendidikan dan layanan kesehatan yang memadai terus diupayakan. Program beasiswa dan jaminan kesehatan nasional menjadi bentuk nyata komitmen pemerintah dalam bidang ini.",
                    "Perkembangan teknologi informasi yang pesat membawa dampak signifikan di berbagai bidang kehidupan. Transformasi digital menjadi sebuah keharusan agar tidak tertinggal. Pemerintah daerah terus mendorong penerapan sistem pemerintahan berbasis elektronik (SPBE) untuk meningkatkan efisiensi dan transparansi birokrasi. Masyarakat pun dihimbau untuk melek teknologi dan memanfaatkannya secara positif.",
                    "Menjaga kelestarian lingkungan hidup adalah tanggung jawab bersama. Pembangunan yang dilakukan harus selalu memperhatikan asas keberlanjutan. Program penghijauan, pengelolaan sampah yang baik, dan penggunaan energi terbarukan terus disosialisasikan. Dengan sinergi antara pemerintah, swasta, dan masyarakat, diharapkan lingkungan yang bersih, sehat, dan lestari dapat diwariskan ke generasi mendatang."
                ];

                if ($currentType == 'banner') {
                    $fakerTitle = implode(' ', fake()->randomElements($indoWords, rand(4, 7)));
                    $post->title = 'Banner ' . $i . ' ' . $fakerTitle;
                    $post->slug = Str::slug($post->title);
                    $post->url = $currentType . '/' . $post->slug;
                    $colors = ['31343C', '1F2937', '1E40AF', '065F46', '991B1B', '6B21A8'];
                    $bgColor = $colors[($i - 1) % count($colors)];
                    $post->media = 'https://placehold.co/800x400/EEE/' . $bgColor . '?text=Banner+Contoh+' . $i;
                    $post->redirect_to = '#';
                    $post->description = 'Ini adalah deskripsi untuk banner contoh ke-' . $i;
                } else {
                    $fakerTitle = implode(' ', fake()->randomElements($indoWords, rand(4, 7)));
                    $post->title = ucfirst($currentType) . ' ' . $i . ' ' . $fakerTitle;
                    $post->slug = Str::slug($post->title);

                    $post->url = $currentType . '/' . $post->slug;

                    if (isset($module->form->thumbnail) && $module->form->thumbnail) {
                        $colors = ['31343C', '1F2937', '1E40AF', '065F46', '991B1B', '6B21A8'];
                        $bgColor = $colors[($i - 1) % count($colors)];
                        $post->media = 'https://placehold.co/800x600/EEE/' . $bgColor . '?text=Thumbnail+' . $i;
                    }

                    if (isset($module->form->editor) && $module->form->editor) {
                        $paragraphs = [];
                        for ($p = 0; $p < 5; $p++) {
                            $paragraphs[] = '<p>' . fake()->randomElement($indoParagraphs) . '</p>';
                        }
                        $post->content = implode('', $paragraphs);
                    }

                    if (isset($module->form->custom_field) && $module->form->custom_field) {
                        $generatedFields = [];
                        $safeCustomFields = json_decode(json_encode($module->form->custom_field), true);
                        foreach (custom_field_without_break($safeCustomFields) as $row) {
                            $fieldLabel = $row[0] ?? (is_string($row) ? $row : 'field');
                            $fieldKey = _us($fieldLabel);

                            $fieldType = $row[1]['type'] ?? 'text';

                            if ($fieldType == 'number') {
                                $generatedFields[$fieldKey] = fake()->randomNumber(5, true);
                            } elseif ($fieldType == 'email') {
                                $generatedFields[$fieldKey] = fake()->safeEmail();
                            } elseif ($fieldType == 'date') {
                                $generatedFields[$fieldKey] = fake()->date();
                            } elseif ($fieldType == 'phone') {
                                $generatedFields[$fieldKey] = fake('id_ID')->phoneNumber();
                            } elseif ($fieldType == 'url') {
                                $generatedFields[$fieldKey] = fake()->url();
                            } elseif ($fieldType == 'textarea') {
                                $generatedFields[$fieldKey] = fake()->randomElement($indoParagraphs);
                            } elseif ($fieldType == 'image' || $fieldType == 'file') {
                                $generatedFields[$fieldKey] = 'https://placehold.co/400x400/EEE/31343C?text=Gambar';
                            } else {
                                $generatedFields[$fieldKey] = 'Contoh ' . $fieldLabel . ' ' . $i;
                            }
                        }
                        $post->data_field = $generatedFields;
                    }
                }
                $post->save();
            }
        }
    }

    public static function getDummyFilePath($currentType = null)
    {
        $tpl = template();
        if (!$tpl) {
            return null;
        }

        // 1. Cek folder modul spesifik di template aktif, misal: template/theme-koperasi-main/banner/dummy.json
        if ($currentType) {
            $modulePath = resource_path("views/template/{$tpl}/{$currentType}/dummy.json");
            if (file_exists($modulePath)) {
                return $modulePath;
            }
        }

        // 2. Cek root template aktif, misal: template/gov-id/dummy.json
        $rootPath = resource_path("views/template/{$tpl}/dummy.json");
        if (file_exists($rootPath)) {
            return $rootPath;
        }

        return null;
    }

    public static function getSyncBatch($syncId, $batchIndex)
    {
        if (!$syncId) {
            return [];
        }
        $cleanSyncId = preg_replace('/[^a-zA-Z0-9_-]/', '', $syncId);
        $batchFile = storage_path('framework/cache/' . $cleanSyncId . '/batch_' . (int) $batchIndex . '.json');
        if (file_exists($batchFile)) {
            $content = file_get_contents($batchFile);
            $items = json_decode($content, true);
            return is_array($items) ? $items : [];
        }
        return [];
    }

    public static function cleanupSyncBatches($syncId)
    {
        if (!$syncId) {
            return;
        }
        $cleanSyncId = preg_replace('/[^a-zA-Z0-9_-]/', '', $syncId);
        $syncDir = storage_path('framework/cache/' . $cleanSyncId);
        if (File::isDirectory($syncDir)) {
            File::deleteDirectory($syncDir);
        }
    }

    public static function prepareTasks($currentType, $module = null, $dummyFile = null, $userId = 1)
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');
        DB::disableQueryLog();

        $tasks = [];

        if (!$dummyFile) {
            $dummyFile = self::getDummyFilePath($currentType);
        }

        if ($dummyFile && file_exists($dummyFile)) {
            $dummyData = json_decode(file_get_contents($dummyFile), true);
            if ($dummyData) {
                $categories = $dummyData['categories'] ?? [];
                $posts = $dummyData['posts'] ?? [];

                // Preload existing slugs dalam hash map O(1) untuk lookup instan ribuan data
                $existingCatSlugs = Category::onType($currentType)->pluck('slug')->flip()->all();
                $existingPostSlugs = Post::onType($currentType)->pluck('slug')->flip()->all();

                // Filter categories for current type
                if (is_array($categories)) {
                    foreach ($categories as $cat) {
                        $type = $cat['type'] ?? $currentType ?? 'post';
                        if ($currentType && $type !== $currentType) {
                            continue;
                        }
                        $slug = $cat['slug'] ?? Str::slug($cat['name'] ?? '');
                        if (!isset($existingCatSlugs[$slug])) {
                            $tasks[] = [
                                'kind' => 'category',
                                'title' => 'Kategori: ' . ($cat['name'] ?? 'Kategori'),
                                'data' => array_merge($cat, ['type' => $type, 'slug' => $slug])
                            ];
                            $existingCatSlugs[$slug] = true;
                        }
                    }
                }

                // Filter posts for current type - TIDAK DIBATASI (semua ribuan baris disinkronkan)
                if (is_array($posts)) {
                    foreach ($posts as $postData) {
                        $type = $postData['type'] ?? $currentType ?? 'post';
                        if ($currentType && $type !== $currentType) {
                            continue;
                        }
                        $slug = $postData['slug'] ?? Str::slug($postData['title'] ?? '');
                        if (!isset($existingPostSlugs[$slug])) {
                            $tasks[] = [
                                'kind' => 'post',
                                'title' => 'Post: ' . Str::limit($postData['title'] ?? 'Postingan', 35),
                                'data' => array_merge($postData, ['type' => $type, 'slug' => $slug])
                            ];
                            $existingPostSlugs[$slug] = true;
                        }
                    }
                }
            }
        }

        // Smart Dummy Generator (Fallback) if no tasks from JSON and no posts exist
        if (empty($tasks) && $currentType && !Post::onType($currentType)->exists()) {
            $fallbackTasks = self::buildFallbackTasks($currentType, $module, $userId);
            $tasks = array_merge($tasks, $fallbackTasks);
        }

        $totalItems = count($tasks);
        if ($totalItems === 0) {
            return [
                'total_items' => 0,
                'total_batches' => 0,
                'batches' => [],
                'mode' => 'direct',
                'module_title' => $module->title ?? ucfirst($currentType ?? 'Modul')
            ];
        }

        // Ukuran batch terukur agar transaksi DB dan response tetap cepat
        if ($totalItems > 5000) {
            $chunkSize = 100;
        } elseif ($totalItems > 1000) {
            $chunkSize = 50;
        } elseif ($totalItems > 200) {
            $chunkSize = 25;
        } elseif ($totalItems > 50) {
            $chunkSize = 10;
        } elseif ($totalItems > 10) {
            $chunkSize = 5;
        } else {
            $chunkSize = 2;
        }

        $chunks = array_chunk($tasks, $chunkSize);
        $totalBatches = count($chunks);

        // Jika data sedikit (<= 100), gunakan mode direct (kirim langsung ke browser)
        if ($totalItems <= 100) {
            return [
                'total_items' => $totalItems,
                'total_batches' => $totalBatches,
                'batches' => $chunks,
                'mode' => 'direct',
                'module_title' => $module->title ?? ucfirst($currentType ?? 'Modul')
            ];
        }

        // Server mode untuk dataset ribuan data:
        // Simpan chunks di direktori cache temporary agar browser dan jaringan tidak overload
        $cleanType = preg_replace('/[^a-zA-Z0-9_-]/', '', $currentType ?? 'all');
        $syncId = 'dummy_' . $cleanType . '_' . $userId . '_' . time();
        $syncDir = storage_path('framework/cache/' . $syncId);
        if (!File::isDirectory($syncDir)) {
            File::makeDirectory($syncDir, 0755, true);
        }

        $batchDescriptors = [];
        foreach ($chunks as $bIdx => $chunkItems) {
            File::put($syncDir . '/batch_' . $bIdx . '.json', json_encode($chunkItems));
            $batchDescriptors[] = [
                'index' => $bIdx,
                'count' => count($chunkItems),
                'title' => $chunkItems[0]['title'] ?? 'Data dummy'
            ];
        }

        return [
            'total_items' => $totalItems,
            'total_batches' => $totalBatches,
            'batches' => $batchDescriptors,
            'mode' => 'server',
            'sync_id' => $syncId,
            'module_title' => $module->title ?? ucfirst($currentType ?? 'Modul')
        ];
    }

    public static function buildFallbackTasks($currentType, $module = null, $userId = 1)
    {
        $tasks = [];

        if (isset($module->form->category) && $module->form->category) {
            for ($c = 1; $c <= 3; $c++) {
                $catName = fake('id_ID')->words(2, true);
                $catSlug = Str::slug($catName);
                if (!Category::onType($currentType)->where('slug', $catSlug)->exists()) {
                    $tasks[] = [
                        'kind' => 'category',
                        'title' => 'Kategori: ' . ucwords($catName),
                        'data' => [
                            'type' => $currentType,
                            'name' => ucwords($catName),
                            'slug' => $catSlug,
                            'url' => $currentType . '/category/' . $catSlug,
                            'status' => 'publish'
                        ]
                    ];
                }
            }
        } else {
            $catSlug = 'umum';
            if (!Category::onType($currentType)->where('slug', $catSlug)->exists()) {
                $tasks[] = [
                    'kind' => 'category',
                    'title' => 'Kategori: Umum',
                    'data' => [
                        'type' => $currentType,
                        'name' => 'Umum',
                        'slug' => $catSlug,
                        'url' => $currentType . '/category/' . $catSlug,
                        'status' => 'publish'
                    ]
                ];
            }
        }

        if ($currentType == 'menu') {
            $tasks[] = [
                'kind' => 'post',
                'title' => 'Menu: Header',
                'data' => [
                    'type' => $currentType,
                    'title' => 'Header',
                    'slug' => 'header',
                    'url' => $currentType . '/header',
                    'data_loop' => [
                        ['menu_id' => '1', 'menu_parent' => '0', 'menu_name' => 'Beranda', 'menu_link' => '/', 'menu_description' => 'Halaman Utama', 'menu_icon' => 'fa fa-home'],
                        ['menu_id' => '2', 'menu_parent' => '0', 'menu_name' => 'Profil', 'menu_link' => '#', 'menu_description' => 'Profil Instansi', 'menu_icon' => 'fa fa-building'],
                        ['menu_id' => '21', 'menu_parent' => '2', 'menu_name' => 'Tentang Kami', 'menu_link' => '#', 'menu_description' => 'Informasi Umum', 'menu_icon' => 'fa fa-info-circle'],
                        ['menu_id' => '211', 'menu_parent' => '21', 'menu_name' => 'Sejarah', 'menu_link' => '#', 'menu_description' => 'Sejarah Instansi', 'menu_icon' => 'fa fa-history'],
                        ['menu_id' => '212', 'menu_parent' => '21', 'menu_name' => 'Visi & Misi', 'menu_link' => '#', 'menu_description' => 'Tujuan Utama', 'menu_icon' => 'fa fa-bullseye'],
                        ['menu_id' => '213', 'menu_parent' => '21', 'menu_name' => 'Tugas & Fungsi', 'menu_link' => '#', 'menu_description' => 'Tugas Pokok', 'menu_icon' => 'fa fa-tasks'],
                        ['menu_id' => '22', 'menu_parent' => '2', 'menu_name' => 'Struktur Organisasi', 'menu_link' => '#', 'menu_description' => 'Struktur', 'menu_icon' => 'fa fa-sitemap'],
                        ['menu_id' => '221', 'menu_parent' => '22', 'menu_name' => 'Pimpinan', 'menu_link' => '#', 'menu_description' => 'Profil Pimpinan', 'menu_icon' => 'fa fa-user-tie'],
                        ['menu_id' => '222', 'menu_parent' => '22', 'menu_name' => 'Pejabat Struktural', 'menu_link' => '#', 'menu_description' => 'Daftar Pejabat', 'menu_icon' => 'fa fa-users'],
                        ['menu_id' => '3', 'menu_parent' => '0', 'menu_name' => 'Layanan', 'menu_link' => '#', 'menu_description' => 'Layanan Kami', 'menu_icon' => 'fa fa-briefcase'],
                        ['menu_id' => '31', 'menu_parent' => '3', 'menu_name' => 'Layanan Publik', 'menu_link' => '#', 'menu_description' => 'Layanan Masyarakat', 'menu_icon' => 'fa fa-users'],
                        ['menu_id' => '311', 'menu_parent' => '31', 'menu_name' => 'Perizinan', 'menu_link' => '#', 'menu_description' => 'Layanan Izin', 'menu_icon' => 'fa fa-file-signature'],
                        ['menu_id' => '312', 'menu_parent' => '31', 'menu_name' => 'Non-Perizinan', 'menu_link' => '#', 'menu_description' => 'Layanan Lain', 'menu_icon' => 'fa fa-file-alt'],
                        ['menu_id' => '32', 'menu_parent' => '3', 'menu_name' => 'Pengaduan', 'menu_link' => '#', 'menu_description' => 'Lapor Pengaduan', 'menu_icon' => 'fa fa-exclamation-triangle'],
                        ['menu_id' => '4', 'menu_parent' => '0', 'menu_name' => 'Informasi Publik', 'menu_link' => '#', 'menu_description' => 'Layanan PPID', 'menu_icon' => 'fa fa-info'],
                        ['menu_id' => '41', 'menu_parent' => '4', 'menu_name' => 'Informasi Berkala', 'menu_link' => '#', 'menu_description' => 'Info Berkala', 'menu_icon' => 'fa fa-file'],
                        ['menu_id' => '42', 'menu_parent' => '4', 'menu_name' => 'Informasi Serta Merta', 'menu_link' => '#', 'menu_description' => 'Info Serta Merta', 'menu_icon' => 'fa fa-bolt'],
                        ['menu_id' => '43', 'menu_parent' => '4', 'menu_name' => 'Informasi Setiap Saat', 'menu_link' => '#', 'menu_description' => 'Info Setiap Saat', 'menu_icon' => 'fa fa-clock'],
                        ['menu_id' => '5', 'menu_parent' => '0', 'menu_name' => 'Publikasi', 'menu_link' => '#', 'menu_description' => 'Daftar Publikasi', 'menu_icon' => 'fa fa-newspaper'],
                        ['menu_id' => '51', 'menu_parent' => '5', 'menu_name' => 'Berita', 'menu_link' => '/berita', 'menu_description' => 'Berita Terbaru', 'menu_icon' => 'fa fa-newspaper'],
                        ['menu_id' => '52', 'menu_parent' => '5', 'menu_name' => 'Galeri', 'menu_link' => '#', 'menu_description' => 'Galeri Media', 'menu_icon' => 'fa fa-images'],
                        ['menu_id' => '521', 'menu_parent' => '52', 'menu_name' => 'Galeri Foto', 'menu_link' => '/galeri-foto', 'menu_description' => 'Kumpulan Foto', 'menu_icon' => 'fa fa-image'],
                        ['menu_id' => '522', 'menu_parent' => '52', 'menu_name' => 'Galeri Video', 'menu_link' => '/galeri-video', 'menu_description' => 'Kumpulan Video', 'menu_icon' => 'fa fa-video'],
                        ['menu_id' => '6', 'menu_parent' => '0', 'menu_name' => 'Kontak', 'menu_link' => '/kontak', 'menu_description' => 'Hubungi Kami', 'menu_icon' => 'fa fa-phone'],
                    ]
                ]
            ];

            $tasks[] = [
                'kind' => 'post',
                'title' => 'Menu: Footer',
                'data' => [
                    'type' => $currentType,
                    'title' => 'Footer',
                    'slug' => 'footer',
                    'url' => $currentType . '/footer',
                    'data_loop' => [
                        ['menu_id' => '1', 'menu_parent' => '0', 'menu_name' => 'Profil', 'menu_link' => '#', 'menu_description' => 'Profil Instansi', 'menu_icon' => 'fa fa-building'],
                        ['menu_id' => '11', 'menu_parent' => '1', 'menu_name' => 'Sejarah', 'menu_link' => '#', 'menu_description' => 'Sejarah Instansi', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '12', 'menu_parent' => '1', 'menu_name' => 'Visi & Misi', 'menu_link' => '#', 'menu_description' => 'Tujuan Utama', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '13', 'menu_parent' => '1', 'menu_name' => 'Struktur Organisasi', 'menu_link' => '#', 'menu_description' => 'Struktur', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '2', 'menu_parent' => '0', 'menu_name' => 'Publikasi', 'menu_link' => '#', 'menu_description' => 'Daftar Publikasi', 'menu_icon' => 'fa fa-newspaper'],
                        ['menu_id' => '21', 'menu_parent' => '2', 'menu_name' => 'Berita', 'menu_link' => '/berita', 'menu_description' => 'Berita Terbaru', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '22', 'menu_parent' => '2', 'menu_name' => 'Pengumuman', 'menu_link' => '/pengumuman', 'menu_description' => 'Pengumuman Resmi', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '23', 'menu_parent' => '2', 'menu_name' => 'Artikel', 'menu_link' => '/artikel', 'menu_description' => 'Artikel', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '3', 'menu_parent' => '0', 'menu_name' => 'Link Terkait', 'menu_link' => '#', 'menu_description' => 'Tautan', 'menu_icon' => 'fa fa-link'],
                        ['menu_id' => '31', 'menu_parent' => '3', 'menu_name' => 'Kementerian', 'menu_link' => '#', 'menu_description' => 'Situs Kementerian', 'menu_icon' => 'fa fa-caret-right'],
                        ['menu_id' => '32', 'menu_parent' => '3', 'menu_name' => 'Pemerintah Daerah', 'menu_link' => '#', 'menu_description' => 'Situs Pemda', 'menu_icon' => 'fa fa-caret-right'],
                    ]
                ]
            ];
        } elseif ($currentType == 'page') {
            $pageData = [
                [
                    'title' => 'Sejarah',
                    'content' => '<h2>Sejarah Instansi</h2><p>Instansi ini didirikan dengan tujuan untuk melayani masyarakat dan memajukan daerah. Sejak awal berdirinya, kami terus berkomitmen untuk memberikan pelayanan terbaik melalui berbagai inovasi dan peningkatan kualitas sumber daya manusia.</p><p>Perjalanan panjang telah dilalui dengan berbagai tantangan yang berhasil diatasi berkat dukungan dari seluruh elemen masyarakat. Ke depan, kami akan terus beradaptasi dengan perkembangan zaman demi mewujudkan visi dan misi yang telah ditetapkan.</p>'
                ],
                [
                    'title' => 'Visi dan Misi',
                    'content' => '<h2>Visi</h2><p>Mewujudkan pelayanan publik yang prima, transparan, dan akuntabel demi tercapainya kesejahteraan masyarakat yang merata dan berkelanjutan.</p><h2>Misi</h2><ul><li>Meningkatkan kualitas sumber daya aparatur yang profesional dan berintegritas.</li><li>Mendorong partisipasi aktif masyarakat dalam proses pembangunan.</li><li>Mengoptimalkan pemanfaatan teknologi informasi untuk pelayanan yang efisien.</li><li>Memperkuat sinergi antara pemerintah, swasta, dan masyarakat.</li></ul>'
                ],
                [
                    'title' => 'Tugas Pokok dan Fungsi',
                    'content' => '<h2>Tugas Pokok</h2><p>Melaksanakan urusan pemerintahan daerah berdasarkan asas otonomi dan tugas pembantuan di bidang yang menjadi kewenangan daerah, serta merumuskan kebijakan teknis sesuai dengan peraturan perundang-undangan.</p><h2>Fungsi</h2><ul><li>Perumusan kebijakan teknis di bidang terkait.</li><li>Penyelenggaraan urusan pemerintahan dan pelayanan umum.</li><li>Pembinaan dan pelaksanaan tugas di bidang yang relevan.</li><li>Pelaksanaan tugas lain yang diberikan oleh pimpinan sesuai dengan tugas dan fungsinya.</li></ul>'
                ],
                [
                    'title' => 'Struktur Organisasi',
                    'content' => '<h2>Struktur Organisasi</h2><p>Struktur organisasi dirancang untuk memastikan berjalannya tugas pokok dan fungsi secara efektif dan efisien. Terdiri dari pimpinan tertinggi, unit-unit pelaksana teknis, serta berbagai bidang atau divisi yang menangani urusan spesifik.</p><p>Setiap bagian memiliki uraian tugas yang jelas dan saling berkoordinasi dalam mencapai tujuan bersama instansi. Pembagian kewenangan diatur berdasarkan prinsip profesionalisme dan tata kelola pemerintahan yang baik (Good Corporate Governance).</p>'
                ],
                [
                    'title' => 'Syarat Pelayanan',
                    'content' => '<h2>Persyaratan Pelayanan</h2><p>Untuk memastikan proses pelayanan berjalan lancar, masyarakat dihimbau untuk melengkapi persyaratan dokumen yang dibutuhkan sesuai dengan jenis layanan yang diajukan.</p><ul><li>Fotokopi Kartu Tanda Penduduk (KTP) yang masih berlaku.</li><li>Fotokopi Kartu Keluarga (KK).</li><li>Dokumen pendukung lainnya sesuai dengan spesifikasi layanan (misal: surat pengantar RT/RW, fotokopi ijazah, dsb).</li></ul><p>Berkas yang lengkap akan mempercepat proses verifikasi dan tindak lanjut dari petugas kami.</p>'
                ],
                [
                    'title' => 'Makna Logo',
                    'content' => '<h2>Makna Logo Instansi</h2><p>Logo kami merupakan visualisasi dari identitas, visi, dan nilai-nilai luhur yang dijunjung tinggi. Setiap elemen, bentuk, dan warna memiliki filosofi tersendiri.</p><ul><li><strong>Warna Biru:</strong> Melambangkan profesionalisme, kepercayaan, dan kedamaian.</li><li><strong>Warna Emas:</strong> Menggambarkan kejayaan, kesejahteraan, dan kualitas pelayanan prima.</li><li><strong>Simbol Bintang:</strong> Menunjukkan cita-cita yang tinggi dan ketakwaan kepada Tuhan Yang Maha Esa.</li><li><strong>Bentuk Perisai:</strong> Bermakna perlindungan, keamanan, dan ketahanan dalam menghadapi berbagai tantangan.</li></ul>'
                ]
            ];

            foreach ($pageData as $index => $data) {
                $postItem = [
                    'type' => $currentType,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'url' => Str::slug($data['title']),
                ];

                if (isset($module->form->thumbnail) && $module->form->thumbnail) {
                    $colors = ['31343C', '1F2937', '1E40AF', '065F46', '991B1B', '6B21A8'];
                    $bgColor = $colors[$index % count($colors)];
                    $postItem['media'] = 'https://placehold.co/800x600/EEE/' . $bgColor . '?text=' . urlencode($data['title']);
                }

                if (isset($module->form->editor) && $module->form->editor) {
                    $postItem['content'] = $data['content'];
                }

                if (isset($module->form->custom_field) && $module->form->custom_field) {
                    $generatedFields = [];
                    $safeCustomFields = json_decode(json_encode($module->form->custom_field), true);
                    foreach (custom_field_without_break($safeCustomFields) as $row) {
                        $fieldLabel = $row[0] ?? (is_string($row) ? $row : 'field');
                        $fieldKey = _us($fieldLabel);
                        $fieldType = $row[1]['type'] ?? 'text';

                        if ($fieldType == 'number') {
                            $generatedFields[$fieldKey] = fake()->randomNumber(5, true);
                        } elseif ($fieldType == 'email') {
                            $generatedFields[$fieldKey] = fake()->safeEmail();
                        } elseif ($fieldType == 'date') {
                            $generatedFields[$fieldKey] = fake()->date();
                        } elseif ($fieldType == 'phone') {
                            $generatedFields[$fieldKey] = fake('id_ID')->phoneNumber();
                        } elseif ($fieldType == 'url') {
                            $generatedFields[$fieldKey] = fake()->url();
                        } elseif ($fieldType == 'textarea') {
                            $generatedFields[$fieldKey] = "Data dummy untuk " . $fieldLabel;
                        } elseif ($fieldType == 'image' || $fieldType == 'file') {
                            $generatedFields[$fieldKey] = 'https://placehold.co/400x400/EEE/31343C?text=Gambar';
                        } else {
                            $generatedFields[$fieldKey] = 'Contoh ' . $fieldLabel . ' ' . ($index + 1);
                        }
                    }
                    $postItem['data_field'] = $generatedFields;
                }

                $tasks[] = [
                    'kind' => 'post',
                    'title' => 'Halaman: ' . $data['title'],
                    'data' => $postItem
                ];
            }
        } else {
            $indoWords = ['Pembangunan', 'Ekonomi', 'Daerah', 'Masyarakat', 'Kesejahteraan', 'Program', 'Kegiatan', 'Sosial', 'Budaya', 'Pendidikan', 'Kesehatan', 'Infrastruktur', 'Teknologi', 'Informasi', 'Layanan', 'Publik', 'Pemerintah', 'Kebijakan', 'Strategi', 'Inovasi', 'Nasional', 'Berkelanjutan', 'Digital', 'Masa', 'Depan'];
            $indoParagraphs = [
                "Pemerintah terus berupaya meningkatkan kualitas layanan publik di berbagai sektor. Hal ini sejalan dengan visi dan misi untuk mewujudkan kesejahteraan masyarakat yang merata. Berbagai program unggulan telah diluncurkan dan terus dievaluasi efektivitasnya di lapangan. Partisipasi aktif dari seluruh elemen masyarakat sangat diharapkan untuk mendukung keberhasilan program-program tersebut.",
                "Dalam rangka mempercepat pertumbuhan ekonomi daerah, pengembangan infrastruktur menjadi salah satu prioritas utama. Pembangunan jalan, jembatan, dan fasilitas umum lainnya terus digenjot. Selain itu, pemberdayaan Usaha Mikro, Kecil, dan Menengah (UMKM) juga terus didorong melalui berbagai pelatihan dan bantuan modal. Diharapkan langkah ini dapat membuka lapangan kerja baru dan mengurangi tingkat pengangguran.",
                "Sektor pendidikan dan kesehatan juga tidak luput dari perhatian. Peningkatan kualitas sumber daya manusia merupakan kunci untuk menghadapi tantangan global di masa depan. Oleh karena itu, penyediaan fasilitas pendidikan dan layanan kesehatan yang memadai terus diupayakan. Program beasiswa dan jaminan kesehatan nasional menjadi bentuk nyata komitmen pemerintah dalam bidang ini.",
                "Perkembangan teknologi informasi yang pesat membawa dampak signifikan di berbagai bidang kehidupan. Transformasi digital menjadi sebuah keharusan agar tidak tertinggal. Pemerintah daerah terus mendorong penerapan sistem pemerintahan berbasis elektronik (SPBE) untuk meningkatkan efisiensi dan transparansi birokrasi. Masyarakat pun dihimbau untuk melek teknologi dan memanfaatkannya secara positif.",
                "Menjaga kelestarian lingkungan hidup adalah tanggung jawab bersama. Pembangunan yang dilakukan harus selalu memperhatikan asas keberlanjutan. Program penghijauan, pengelolaan sampah yang baik, dan penggunaan energi terbarukan terus disosialisasikan. Dengan sinergi antara pemerintah, swasta, dan masyarakat, diharapkan lingkungan yang bersih, sehat, dan lestari dapat diwariskan ke generasi mendatang."
            ];

            for ($i = 1; $i <= 6; $i++) {
                $postItem = ['type' => $currentType];
                if ($currentType == 'banner') {
                    $fakerTitle = implode(' ', fake()->randomElements($indoWords, rand(4, 7)));
                    $postItem['title'] = 'Banner ' . $i . ' ' . $fakerTitle;
                    $postItem['slug'] = Str::slug($postItem['title']);
                    $postItem['url'] = $currentType . '/' . $postItem['slug'];
                    $colors = ['31343C', '1F2937', '1E40AF', '065F46', '991B1B', '6B21A8'];
                    $bgColor = $colors[($i - 1) % count($colors)];
                    $postItem['media'] = 'https://placehold.co/800x400/EEE/' . $bgColor . '?text=Banner+Contoh+' . $i;
                    $postItem['redirect_to'] = '#';
                    $postItem['description'] = 'Ini adalah deskripsi untuk banner contoh ke-' . $i;
                } else {
                    $fakerTitle = implode(' ', fake()->randomElements($indoWords, rand(4, 7)));
                    $postItem['title'] = ucfirst($currentType) . ' ' . $i . ' ' . $fakerTitle;
                    $postItem['slug'] = Str::slug($postItem['title']);
                    $postItem['url'] = $currentType . '/' . $postItem['slug'];

                    if (isset($module->form->thumbnail) && $module->form->thumbnail) {
                        $colors = ['31343C', '1F2937', '1E40AF', '065F46', '991B1B', '6B21A8'];
                        $bgColor = $colors[($i - 1) % count($colors)];
                        $postItem['media'] = 'https://placehold.co/800x600/EEE/' . $bgColor . '?text=Thumbnail+' . $i;
                    }

                    if (isset($module->form->editor) && $module->form->editor) {
                        $paragraphs = [];
                        for ($p = 0; $p < 5; $p++) {
                            $paragraphs[] = '<p>' . fake()->randomElement($indoParagraphs) . '</p>';
                        }
                        $postItem['content'] = implode('', $paragraphs);
                    }

                    if (isset($module->form->custom_field) && $module->form->custom_field) {
                        $generatedFields = [];
                        $safeCustomFields = json_decode(json_encode($module->form->custom_field), true);
                        foreach (custom_field_without_break($safeCustomFields) as $row) {
                            $fieldLabel = $row[0] ?? (is_string($row) ? $row : 'field');
                            $fieldKey = _us($fieldLabel);
                            $fieldType = $row[1]['type'] ?? 'text';

                            if ($fieldType == 'number') {
                                $generatedFields[$fieldKey] = fake()->randomNumber(5, true);
                            } elseif ($fieldType == 'email') {
                                $generatedFields[$fieldKey] = fake()->safeEmail();
                            } elseif ($fieldType == 'date') {
                                $generatedFields[$fieldKey] = fake()->date();
                            } elseif ($fieldType == 'phone') {
                                $generatedFields[$fieldKey] = fake('id_ID')->phoneNumber();
                            } elseif ($fieldType == 'url') {
                                $generatedFields[$fieldKey] = fake()->url();
                            } elseif ($fieldType == 'textarea') {
                                $generatedFields[$fieldKey] = fake()->randomElement($indoParagraphs);
                            } elseif ($fieldType == 'image' || $fieldType == 'file') {
                                $generatedFields[$fieldKey] = 'https://placehold.co/400x400/EEE/31343C?text=Gambar';
                            } else {
                                $generatedFields[$fieldKey] = 'Contoh ' . $fieldLabel . ' ' . $i;
                            }
                        }
                        $postItem['data_field'] = $generatedFields;
                    }
                }

                $tasks[] = [
                    'kind' => 'post',
                    'title' => ($module->title ?? ucfirst($currentType)) . ' ' . $i . ': ' . Str::limit($postItem['title'], 25),
                    'data' => $postItem
                ];
            }
        }

        return $tasks;
    }

    public static function processBatch($items, $currentType, $userId = 1)
    {
        $processed = 0;
        if (!is_array($items) || empty($items)) {
            return 0;
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                self::executeSingleItem($item, $currentType, $userId);
                $processed++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Dummy sync processBatch error: " . $e->getMessage());
            throw $e;
        }

        return $processed;
    }

    public static function executeSingleItem($item, $currentType, $userId = 1)
    {
        static $catCache = [];
        static $parentCache = [];

        $kind = $item['kind'] ?? 'post';
        $data = $item['data'] ?? [];

        if ($kind === 'category') {
            $type = $data['type'] ?? $currentType;
            $slug = $data['slug'] ?? Str::slug($data['name']);

            if (isset($catCache[$type][$slug])) {
                return $catCache[$type][$slug];
            }

            $exists = Category::onType($type)->where('slug', $slug)->first();
            if (!$exists) {
                $newCat = Category::create([
                    'type' => $type,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'sort' => $data['sort'] ?? 0,
                    'url' => $type . '/category/' . $slug,
                    'status' => 'publish',
                    'description' => $data['description'] ?? null,
                ]);
                $catCache[$type][$slug] = $newCat->id;
                return $newCat;
            }
            $catCache[$type][$slug] = $exists->id;
            return $exists;
        }

        if ($kind === 'post') {
            $type = $data['type'] ?? $currentType;
            $slug = $data['slug'] ?? Str::slug($data['title'] ?? Str::random(8));
            $exists = Post::onType($type)->where('slug', $slug)->first();
            if (!$exists) {
                $post = new Post();
                $post->type = $type;
                $post->title = $data['title'] ?? null;
                $post->slug = $slug;
                $post->redirect_to = $data['redirect_to'] ?? null;
                $post->description = $data['description'] ?? null;
                if (isset($data['short_content'])) {
                    $post->short_content = $data['short_content'];
                }
                $post->url = $type != 'page' ? $type . '/' . $slug : $slug;
                $post->status = 'publish';
                $post->slug_edited = $data['slug_edited'] ?? false;
                $post->shortcut = Str::random(6);
                $post->user_id = $userId;

                if (isset($data['category'])) {
                    $catSlug = Str::slug($data['category']);
                    if (!isset($catCache[$type][$catSlug])) {
                        $cat = Category::onType($type)->where('slug', $catSlug)->first();
                        $catCache[$type][$catSlug] = $cat ? $cat->id : null;
                    }
                    $post->category_id = $catCache[$type][$catSlug];
                }
                if (!$post->category_id) {
                    if (!isset($catCache[$type]['__first__'])) {
                        $firstCat = Category::onType($type)->first();
                        $catCache[$type]['__first__'] = $firstCat ? $firstCat->id : null;
                    }
                    $post->category_id = $catCache[$type]['__first__'];
                }

                if (isset($data['parent'])) {
                    $pSlug = $data['parent'];
                    if (!isset($parentCache[$pSlug])) {
                        $parentPost = Post::where('slug', $pSlug)->first();
                        $parentCache[$pSlug] = $parentPost ? $parentPost->id : null;
                    }
                    $post->parent_id = $parentCache[$pSlug];
                }

                if (isset($data['media'])) {
                    $post->media = $data['media'];
                }

                if (isset($data['data_loop'])) {
                    if ($type == 'menu') {
                        $flatDataLoop = [];
                        $menuCounter = 1;

                        $flattenMenu = function ($items, $parentId) use (&$flattenMenu, &$flatDataLoop, &$menuCounter) {
                            foreach ($items as $mItem) {
                                $currentId = (string) $menuCounter++;

                                $flatItem = $mItem;
                                unset($flatItem['child']);

                                $flatItem['menu_id'] = $flatItem['menu_id'] ?? $currentId;
                                $flatItem['menu_parent'] = $flatItem['menu_parent'] ?? $parentId;

                                $flatDataLoop[] = $flatItem;

                                if (isset($mItem['child']) && is_array($mItem['child'])) {
                                    $flattenMenu($mItem['child'], $flatItem['menu_id']);
                                }
                            }
                        };

                        $isNested = false;
                        foreach ($data['data_loop'] as $mItem) {
                            if (isset($mItem['child'])) {
                                $isNested = true;
                                break;
                            }
                        }

                        if ($isNested) {
                            $flattenMenu($data['data_loop'], '0');
                            $post->data_loop = $flatDataLoop;
                        } else {
                            $post->data_loop = $data['data_loop'];
                        }
                    } else {
                        $post->data_loop = $data['data_loop'];
                    }
                }

                if (isset($data['data_field'])) {
                    $post->data_field = $data['data_field'];
                }

                if (isset($data['content'])) {
                    $post->content = $data['content'];
                }

                $post->save();
                return $post;
            }
            return $exists;
        }

        return null;
    }

    public static function syncFromJson($dummyFile, $userId = 1, $targetType = null)
    {
        $typesProcessed = [];

        if (!$dummyFile || !file_exists($dummyFile)) {
            $dummyFile = self::getDummyFilePath($targetType);
        }

        if ($dummyFile && file_exists($dummyFile)) {
            $dummyData = json_decode(file_get_contents($dummyFile), true);
            if ($dummyData) {
                $categories = $dummyData['categories'] ?? null;
                $posts = $dummyData['posts'] ?? null;

                $defaultCategoryIds = [];

                if (is_array($categories)) {
                    foreach ($categories as $cat) {
                        $type = $cat['type'] ?? $targetType ?? 'post';
                        if ($targetType && $type !== $targetType) {
                            continue;
                        }
                        $slug = $cat['slug'] ?? Str::slug($cat['name']);
                        $exists = Category::onType($type)->where('slug', $slug)->first();
                        if (!$exists) {
                            $newCat = Category::create([
                                'type' => $type,
                                'name' => $cat['name'],
                                'slug' => $slug,
                                'sort'=>isset($cat['sort'])?$cat['sort']:0,
                                'url' => $type . '/category/' . $slug,
                                'status' => 'publish',
                                'description' => $cat['description'] ?? null,
                            ]);

                            if (!isset($defaultCategoryIds[$type])) {
                                $defaultCategoryIds[$type] = $newCat->id;
                            }
                        } else {
                            if (!isset($defaultCategoryIds[$type])) {
                                $defaultCategoryIds[$type] = $exists->id;
                            }
                        }
                    }
                }

                if (is_array($posts)) {
                    foreach ($posts as $postData) {
                        $type = $postData['type'] ?? $targetType ?? 'post';
                        if ($targetType && $type !== $targetType) {
                            continue;
                        }
                        $slug = $postData['slug'] ?? Str::slug($postData['title']);
                        $exists = Post::onType($type)->where('slug', $slug)->first();
                        if (!$exists) {
                            $post = new Post();
                            $post->type = $type;
                            $post->title = $postData['title'] ?? null;
                            $post->slug = $slug;
                            $post->redirect_to = $postData['redirect_to'] ?? null;
                            $post->description = $postData['description'] ?? null;
                            if (isset($postData['short_content'])) {
                                $post->short_content = $postData['short_content'];
                            }
                            $post->url =  $type != 'page' ? $type . '/' . $slug : $slug;
                            $post->status = 'publish';
                            $post->slug_edited =$postData['slug_edited'] ?? false;
                            $post->shortcut = Str::random(6);
                            $post->user_id = $userId;

                            if (isset($postData['category'])) {
                                $catSlug = Str::slug($postData['category']);
                                $cat = Category::onType($type)->where('slug', $catSlug)->first();
                                $post->category_id = $cat ? $cat->id : ($defaultCategoryIds[$type] ?? null);
                            } elseif (isset($defaultCategoryIds[$type])) {
                                $post->category_id = $defaultCategoryIds[$type];
                            }

                            if (isset($postData['parent'])) {
                                $parentPost = Post::where('slug', $postData['parent'])->first();
                                if ($parentPost) {
                                    $post->parent_id = $parentPost->id;
                                }
                            }

                            if (isset($postData['media'])) {
                                $post->media = $postData['media'];
                            }
                            if (isset($postData['data_loop'])) {
                                if ($type == 'menu') {
                                    $flatDataLoop = [];
                                    $menuCounter = 1;
                                    
                                    $flattenMenu = function ($items, $parentId) use (&$flattenMenu, &$flatDataLoop, &$menuCounter) {
                                        foreach ($items as $item) {
                                            $currentId = (string) $menuCounter++;
                                            
                                            $flatItem = $item;
                                            unset($flatItem['child']);
                                            
                                            $flatItem['menu_id'] = $flatItem['menu_id'] ?? $currentId;
                                            $flatItem['menu_parent'] = $flatItem['menu_parent'] ?? $parentId;
                                            
                                            $flatDataLoop[] = $flatItem;
                                            
                                            if (isset($item['child']) && is_array($item['child'])) {
                                                $flattenMenu($item['child'], $flatItem['menu_id']);
                                            }
                                        }
                                    };
                                    
                                    $isNested = false;
                                    foreach ($postData['data_loop'] as $item) {
                                        if (isset($item['child'])) {
                                            $isNested = true;
                                            break;
                                        }
                                    }
                                    
                                    if ($isNested) {
                                        $flattenMenu($postData['data_loop'], '0');
                                        $post->data_loop = $flatDataLoop;
                                    } else {
                                        $post->data_loop = $postData['data_loop'];
                                    }
                                } else {
                                    $post->data_loop = $postData['data_loop'];
                                }
                            }
                            if (isset($postData['data_field'])) {
                                $post->data_field = $postData['data_field'];
                            }
                            if (isset($postData['content'])) {
                                $post->content = $postData['content'];
                            }

                            $post->save();
                        }
                    }
                }

                // Recache for all types processed
                if (is_array($categories)) {
                    foreach ($categories as $cat) {
                        $type = $cat['type'] ?? 'post';
                        if ($targetType && $type !== $targetType) {
                            continue;
                        }
                        $typesProcessed[$type] = true;
                    }
                }
                if (is_array($posts)) {
                    foreach ($posts as $post) {
                        $type = $post['type'] ?? 'post';
                        if ($targetType && $type !== $targetType) {
                            continue;
                        }
                        $typesProcessed[$type] = true;
                    }
                }
            }
        }

        return $typesProcessed;
    }
}
