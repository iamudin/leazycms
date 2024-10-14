<?php

namespace Leazycms\Web\Http\Controllers;

use \Leazycms\Web\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SetupController extends Controller
{

    public function index(Request $request)
    {
        if (config('modules.installed')) {
            return to_route('home');
        }
        if ($request->isMethod('post')) {
            if (!cache('dbcredential')) {
                $dbcredential = $request->validate([
                    'db_host' => 'required',
                    'db_username' => 'required',
                    'db_database' => 'required',
                    'db_password' => 'nullable',
                ]);
                $result = $this->checkConnection($request->db_host, $request->db_username, $request->db_password, $request->db_database);
                    if ($result == 'no_table_exists') {

                        Cache::put('dbcredential', $dbcredential);

                        $request->session()->regenerateToken();
                        return back()->with('success', 'DB Connection Success!');
                    } elseif (is_array($result)) {
                        $request->session()->regenerateToken();
                        return back()->with('danger', 'Please select empty Database!');
                    } else {
                        $request->session()->regenerateToken();
                        return back()->with('danger', $result);
                    }

            } else {
                $usercredential = $request->validate([
                    'username' => 'required|string|regex:/^[a-zA-Z\p{P}]+$/u',
                    'email' => 'required|string',
                    'password' => 'required|confirmed',
                ]);
                $option = $request->validate([
                    'site_title' => 'required|string',
                    'site_description' => 'required|string',
                ]);
                Cache::put('usercredential',$usercredential);
                Cache::put('option',$option);
                $db['APP_URL'] = 'http://' . $request->getHttpHost();
                $db['DB_CONNECTION'] = 'mysql';
                $db['APP_ENV'] = 'production';
                $db['APP_TIMEZONE'] = '"Asia/Jakarta"';
                foreach (cache('dbcredential') as $k => $row) {
                    $key = Str::upper($k);
                    $db[$key] =  $row;
                }
                $db['DB_PORT'] = '3306';
                if (!isset($db['DB_PASSWORD'])) {
                    $db['DB_PASSWORD'] = '';
                }
                $this->createEnvConfig($db);
                return to_route('initializing');
            }
        }
        return view('cms::install.index');
    }
    public function initializing(){
        $usercredential = Cache::get('usercredential');
        $option = Cache::get('option');
        if(empty($usercredential) || empty($option)){
            return to_route('install');
        }
        Artisan::call('migrate');
        if ($this->generate_dummy_content($usercredential)) {
            foreach ($option as $k => $row) {
                \Leazycms\Web\Models\Option::updateOrCreate([
                    'name' => $k
                ], ['value' => $row, 'autoload' => 1]);
            }
            clear_route();
            if ($this->createEnvConfig(['APP_INSTALLED' => true])) {
                Artisan::call('vendor:publish --tag=cms');
                Artisan::call('route:cache');
                Artisan::call('config:cache');
                Cache::forget('dbcredential');
                Cache::forget('option');
                Cache::forget('usercredential');
                return to_route('login');
            }
        }
    }
    public function createEnvConfig(array $keyPairs)
    {
        if (rewrite_env($keyPairs)) {
            return true;
        }
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
    function generate_dummy_content($user)
    {
        $data = array('username' => $user['username'], 'password' => bcrypt($user['password']), 'host' => request()->getHost(), 'email' => $user['email'], 'status' => 'active', 'slug' => 'admin-web', 'name' => 'Admin Web', 'url' => 'author/admin-web', 'photo' => null, 'level' => 'admin');
        $id = User::UpdateOrcreate(['username' => $user['username']], $data);
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
            ['name' => 'site_keyword', 'value' => 'Web, Official, New', 'autoload' => 1],
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
}
