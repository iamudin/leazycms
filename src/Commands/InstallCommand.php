<?php

namespace Leazycms\Web\Commands;
use Dotenv\Dotenv;
use Leazycms\Web\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'cms:install';
    protected $description = 'Mengatur kredensial database di file .env';

    public function handle()
    {
        $this->line('');
        $this->line('=================================================================');
        $this->line('               🌟 Selamat datang di LEAZYCMS 🌟');
        $this->line('       🌟 Bangun CMS Laravel bisa dengan Lazy dan Easy 🌟');
        $this->line('=================================================================');
        $this->line('');

        if (config('modules.installed') == 0) {
            $this->info("Aplikasi ini hampir siap digunakan. Ikuti langkah berikut:");

            $domain = $this->ask('Masukkan URL domain web tanpa http://', 'localhost');
            $dbHost = $this->ask('Masukkan host database MySQL (default: 127.0.0.1)', '127.0.0.1');
            $dbPort = $this->ask('Masukkan port database MySQL (default: 3306)', '3306');
            $dbName = $this->ask('Masukkan nama database MySQL');
            $dbUser = $this->ask('Masukkan username database MySQL');
            $dbPass = $this->secret('Masukkan password database MySQL');

            if (!$dbName || !$dbUser) {
                $this->error('Nama database dan username tidak boleh kosong!');
                return;
            }

            // Periksa koneksi database
            $result = $this->checkConnection($dbHost, $dbUser, $dbPass, $dbName);
            if ($result == 'no_table_exists') {
                $this->info("Database kosong, siap digunakan.");
            } elseif (is_array($result)) {
                $this->error('Database sudah memiliki tabel dan data!');
                return;
            } else {
                $this->error('Koneksi database tidak ditemukan!');
                return;
            }

            // Update file .env
            $this->createEnvConfig([
                "DB_CONNECTION" => "mysql",
                "DB_HOST" => $dbHost,
                "DB_PORT" => $dbPort,
                "DB_DATABASE" => $dbName,
                "DB_USERNAME" => $dbUser,
                "DB_PASSWORD" => $dbPass,
                "APP_URL" => "http://".$domain,
                "CACHE_STORE" => "file",
                "SESSION_DRIVER" => "file",
                "APP_TIMEZONE" => "Asia/Jakarta",
               " DB_COLLATION" => "utf8mb4_unicode_ci"
            ]);

            // Bersihkan cache
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            // Paksa Laravel membaca ulang konfigurasi database
            config(['database.default' => 'mysql']);
            config(['database.connections.mysql.collation' => 'utf8mb4_unicode_ci']);
            config(['database.connections.mysql.host' => $dbHost]);
            config(['database.connections.mysql.port' => $dbPort]);
            config(['database.connections.mysql.database' => $dbName]);
            config(['database.connections.mysql.username' => $dbUser]);
            config(['database.connections.mysql.password' => $dbPass]);

            // Debugging
            $this->info('Database connection: ' . config('database.default'));
            $this->info('Database name: ' . config('database.connections.mysql.database'));

            // Jalankan migrasi
            $this->call('migrate:fresh', ['--force' => true]);

            // Tambahkan APP_INSTALLED
            $this->createEnvConfig([
                'APP_INSTALLED' => true,
                'APP_ENV' => 'production'
            ]);

            // Generate data dummy
            $this->generate_dummy_content($domain);

            // Cache konfigurasi baru
            Artisan::call('config:cache');
            Artisan::call('route:cache');

            Artisan::call('vendor:publish', ['--tag' => 'cms']);

            $this->info('Instalasi Berhasil! Silahkan akses: ');
            $this->line('Url login : ' . route('login'));
            $this->line('Username  : adminsuper');
            $this->line('Password  : password');
        } else {
            $this->info('Laravel sudah terpasang module LEAZYCMS!');
        }
    }
    public function createEnvConfig(array $keyPairs)
    {
        if (rewrite_env($keyPairs)) {
            return true;
        }
    }
    function generate_dummy_content($domain)
    {
        $data = array('username' => 'adminsuper', 'password' => bcrypt('password'), 'host' => $domain, 'email' => 'email@'.$domain,'status' => 'active', 'slug' => 'admin-super', 'name' => 'Admin Web', 'url' => 'author/admin-web', 'photo' => null, 'level' => 'admin');
        $id = User::UpdateOrcreate(['username' => 'adminsuper'], $data);
        $id->posts()->updateOrcreate(
            [
                'title' => $title = 'Header',
                'slug' => $slug = str()->slug($title),
                'status' => 'publish',
                'type' => 'menu',
                'data_loop' => array(
                    ['menu_id' => 'm1', 'menu_parent' => 0,  'menu_name' => 'Profil', 'menu_description' => null, 'menu_link' => '#', 'menu_icon' => null],
                    ['menu_id' => 'm2', 'menu_parent' => 'm1',  'menu_name' => 'Visi Misi', 'menu_description' => null, 'menu_link' => '#', 'menu_icon' => null],
                    ['menu_id' => 'm3', 'menu_parent' => 'm1',  'menu_name' => 'Sejarah', 'menu_description' => null, 'menu_link' => '#', 'menu_icon' => null],
                    ['menu_id' => 'm4', 'menu_parent' => 0, 'menu_name' => 'Publikasi', 'menu_description' => null, 'menu_link' => '#', 'menu_icon' => null],
                    ['menu_id' => 'm5', 'menu_parent' => 'm4',  'menu_name' => 'Berita', 'menu_description' => null, 'menu_link' => '#', 'menu_icon' => null],
                    ['menu_id' => 'm6', 'menu_parent' => 'm4',  'menu_name' => 'Agenda', 'menu_description' => null, 'menu_link' => '#', 'menu_icon' => null]
                ),
            ]
        );

        $option = array(
            ['name' => 'site_maintenance', 'value' => 'Y', 'autoload' => 1],
            ['name' => 'app_env', 'value' => 'production', 'autoload' => 1],
            ['name' => 'post_perpage', 'value' => 10, 'autoload' => 1],
            ['name' => 'site_title', 'value' => 'Your Website Official', 'autoload' => 1],
            ['name' => 'template', 'value' => 'default', 'autoload' => 1],
            ['name' => 'admin_path', 'value' => 'panel', 'autoload' => 1],
            ['name' => 'logo', 'value' => 'noimage.webp', 'autoload' => 1],
            ['name' => 'favicon', 'value' => 'noimage.webp', 'autoload' => 1],
            ['name' => 'site_url', 'value' => request()->getHttpHost(), 'autoload' => 1],
            ['name' => 'site_meta_keyword', 'value' => 'Web, Official, New', 'autoload' => 1],
            ['name' => 'site_description', 'value' => 'My Offical Web', 'autoload' => 1],
            ['name' => 'address', 'value' => 'Anggrek Streen, 2', 'autoload' => 1],
            ['name' => 'phone', 'value' => '123456789', 'autoload' => 1],
            ['name' => 'email', 'value' => 'your@email.com', 'autoload' => 1],
            ['name' => 'fax', 'value' => '123456789', 'autoload' => 1],
            ['name' => 'latitude', 'value' => null, 'autoload' => 1],
            ['name' => 'longitude', 'value' => null, 'autoload' => 1],
            ['name' => 'facebook', 'value' => 'https://fb.com/yourcompany', 'autoload' => 1],
            ['name' => 'youtube', 'value' => 'https://youtube.com/@yourchannel', 'autoload' => 1],
            ['name' => 'instagram', 'value' => null, 'autoload' => 1],
            ['name' => 'comment_status', 'value' => 0, 'autoload' => 1],
            ['name' => 'home_page', 'value' => 'default', 'autoload' => 1],
            ['name' => 'preview', 'value' => 'noimage.webp', 'autoload' => 1],
            ['name' => 'icon', 'value' => 'noimage.webp', 'autoload' => 1],
        );


        foreach ($option as $row) {
            \Leazycms\Web\Models\Option::updateOrCreate([
                'name' => $row['name']
            ], ['value' => $row['value'], 'autoload' => $row['autoload']]);
        }
        return true;
    }
    public function checkConnection($host, $username, $password, $db)
    {
        $host = $host;
        $database = $db;
        $username = $username;
        $password = $password ?? '';

        config([
            'database.connections.custom' => [
                'driver' => 'mysql',
                'host' => $host,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);

        try {
            DB::purge('custom');
            DB::reconnect('custom');

        // Memeriksa tabel dalam database
        $tables = DB::connection('custom')->select('SHOW TABLES');

        if (empty($tables)) {
            return "no_table_exists";
        } else {
            $tableNames = array_map('current', $tables);
            return $tableNames;
        }
        } catch (\Exception $e) {
            return 'Database Connection Not Found!';
        }
    }
}
