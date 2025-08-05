<?php
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

if (!function_exists('query')) {
    function query()
    {
        return new \Leazycms\Web\Models\Post;
    }
}
if (!function_exists('is_main_domain')) {
    function is_main_domain()
    {
        return request()->getHost() == parse_url(config('app.url'), PHP_URL_HOST) ? true : false;
    }
}
if (!function_exists('main_domain')) {
    function main_domain()
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }
}
if (!function_exists('polling_form')) {
    function polling_form($keyword)
    {
        if (empty(request()->cookie('polling_' . $keyword))) {
            $data = (new \Leazycms\Web\Models\PollingTopic)->with('options')->whereKeyword($keyword)->whereStatus('publish')->first();
            if ($data) {
                return View::make('cms::backend.polling.web.form', compact('data'));
            }
        }
    }
}
if (!function_exists('web_header')) {
function web_header(){
    return View::make(blade_path('header'));
}
}
if (!function_exists('web_footer')) {
function web_footer(){
    return View::make(blade_path('footer'));
}
}
if (!function_exists('getThumbUrl')) {
    function getThumbUrl($url)
    {
        $response = Http::withOptions([
            'verify' => false,  // Menonaktifkan verifikasi SSL
        ])->get($url);

        if ($response->successful()) {
            // Ambil body HTML dari respons
            $html = $response->body();
            // Membuat objek DOMDocument dan memuat HTML
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);

            // Membuat objek XPath untuk query di dokumen
            $xpath = new \DOMXPath($dom);

            // Menjalankan query XPath untuk mencari gambar dengan class 'lz-thumbnail'
            $images = $xpath->query('//img[contains(@class, "lz-thumbnail")]');

            if ($images->length > 0) {
                // Ambil URL dari atribut 'src'
                $src = $images->item(0)->getAttribute('data-src');
                return $src;
            }
        }

        return null; // Jika gambar dengan class 'lz-thumbnail' tidak ditemukan
    }
}
if (!function_exists('getLatestVersion')) {
    function getLatestVersion($packageName = 'leazycms/web', $maxRetries = 1, $retryDelay = 1)
    {
        $url = "https://repo.packagist.org/p2/{$packageName}.json";
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                // Make a request to the Packagist API
                $response = Http::get($url);

                // Check if the request was successful
                if ($response->successful()) {
                    $packageData = $response->json();

                    // Ensure package data and version are available
                    if (isset($packageData['packages'][$packageName][0]['version'])) {
                        $latestVersion = $packageData['packages'][$packageName][0]['version'];
                        return $latestVersion;
                    } else {
                        return null;
                    }
                } else {
                }
            } catch (\Exception $e) {
            }

            // Increment retry count and wait before retrying
            $retryCount++;
            // sleep($retryDelay);
        }

        // Return null if all retries fail
        return null;
    }
}
if (!function_exists('no_http_url')) {

    function no_http_url($domain)
    {
        return parse_url($domain, PHP_URL_HOST);
    }
}
if (!function_exists('get_leazycms_version')) {

    function get_leazycms_version()
    {
        return config('modules.version');
    }
}
if (!function_exists('leazycms_version')) {

    function leazycms_version()
    {
        $composerLockPath = base_path('composer.lock');

        $composerLockContents = file_get_contents($composerLockPath);

        $composerData = json_decode($composerLockContents, true);

        $packageVersion = null;
        foreach ($composerData['packages'] as $package) {
            if ($package['name'] === 'leazycms/web') {
                $packageVersion = $package['version'];
                break;
            }
        }
        return $packageVersion;
    }
}

if (!function_exists('isNotInSession')) {
    function isNotInSession($request)
    {
        $user = $request->user();
        if ($user && md5(md5($request->session()->id())) != $user?->active_session) {
            \Illuminate\Support\Facades\Auth::logout();
            $user->update(['active_session' => null]);
            return to_route('login')->with('error', 'Session is expired or another user was logged your account!')->send();
        }
    }
}


if (!function_exists('forbidden')) {
    function forbidden($request, $k = false)
    {
        $rawKeywords = get_option('forbidden_keyword') ?? '';
        $cleanedKeywords = str_replace(' ', '', $rawKeywords);
        $keywords = explode(',', $cleanedKeywords);
        if($request->segment(2)!='appearance'){
        if (get_option('forbidden_keyword') && \Illuminate\Support\Str::contains(strtolower($request->fullUrl()), $keywords)) {
            $redirect = get_option('forbidden_redirect');
            if (!$k) {
                if (!empty($redirect) && str($redirect)->isUrl()) {
                    return Redirect::to($redirect)->send();
                } else {
                    abort(403);
                }
            }
        }
        if (get_option('block_ip') && in_array(get_client_ip(), explode(",", get_option('block_ip')))) {
            abort(403);
        }
    }
}
}
if (!function_exists('processVisitorData')) {
    function processVisitorData()
    {
        if (!Cache::has('visit_to_db')) {
            $cacheKey = 'visitor_sorted';
            $visitorDataList = Cache::pull($cacheKey, []);

            if (!empty($visitorDataList)) {
                // Step 1: Group by ip + session + page
                $groupedVisitors = [];

                foreach ($visitorDataList as $data) {
                    if (!is_array($data)) continue;

                    $key = $data['ip'] . '|' . $data['session'] . '|' . $data['page'];

                    if (!isset($groupedVisitors[$key])) {
                        $groupedVisitors[$key] = $data;
                        $groupedVisitors[$key]['times'] = 1;
                    } else {
                        $groupedVisitors[$key]['times'] += 1;
                    }
                }

                // Step 2: Update or Insert into database
                foreach ($groupedVisitors as $visitorData) {
                    \Leazycms\Web\Models\Visitor::updateOrCreate(
                        [
                            'ip' => $visitorData['ip'],
                            'session' => $visitorData['session'],
                            'page' => substr($visitorData['page'],0,191),
                        ],
                        [
                            'user_id' => $visitorData['user_id'],
                            'post_id' => $visitorData['post_id'],
                            'ip_location' => $visitorData['ip_location'],
                            'browser' => $visitorData['browser'],
                            'device' => $visitorData['device'],
                            'os' => $visitorData['os'],
                            'reference' => substr($visitorData['reference'],0,191),
                            'created_at' => $visitorData['created_at'],
                            'updated_at' => now(),
                            'times' => \Illuminate\Support\Facades\DB::raw('times + ' . $visitorData['times']), // ⬅️ Increment times
                        ]
                    );
                }
            }

            // Step 3: Set Cache lock supaya nggak double eksekusi
            Cache::put('visit_to_db', true, now()->addMinutes(1));
        }
    }
}
if (!function_exists('ratelimiter')) {
    function ratelimiter($request, $limittime)
    {
            $ip =get_client_ip();
            $sessionId = $request->session()->getId();
            $userAgent = $request->header('User-Agent');
            $url = $request->fullUrl();
            $referer = $request->header('referer');
            $limittime = (int)$limittime;
            $limitduration = (int)get_option('limit_duration');
            $key = generateRateLimitKey($ip, $sessionId, $userAgent, $url, $referer);
            $maxAttempts = $limittime > 0 ? $limittime : 10;
            $decayMinutes = $limitduration > 0 ? $limitduration : 1;
            if (Cache::has($key)) {
                $attempts = cache::get($key);
                if ($attempts >= $maxAttempts) {
                    return abort(429);
                }
            }
            Cache::increment($key);
            Cache::put($key, Cache::get($key), now()->addMinutes($decayMinutes));

    }
}
if (!function_exists('generateRateLimitKey')) {
    function generateRateLimitKey($ip, $sessionId, $userAgent, $url, $referer)
    {
        return md5($ip . '|' . $sessionId . '|' . $userAgent . '|' . $url . '|' . $referer);
    }
}
if (!function_exists('tanggal_indo')) {
    function tanggal_indo($val, $with0 = false)
    {

        $waktu = date('Y-m-d', strtotime($val));
        $hari_array = array(
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu'
        );
        $hr = date('w', strtotime($waktu));
        $hari = $hari_array[$hr];
        if ($with0 == true) {
            $tanggal = date('d', strtotime($waktu));
        } else {
            $tanggal = date('j', strtotime($waktu));
        }
        $bulan_array = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        );

        $bl = date('n', strtotime($waktu));
        $bulan = $bulan_array[$bl];
        $tahun = date('Y', strtotime($waktu));
        $jam = date('H:i T', strtotime($val));

        //untuk menampilkan hari, tanggal bulan tahun jam
        //return "$hari, $tanggal $bulan $tahun $jam";

        //untuk menampilkan hari, tanggal bulan tahun
        return $hari . ", " . $tanggal . " " . $bulan . " " . $tahun;
    }
}
if (!function_exists('getDirectorySize')) {
    function getDirectorySize($directory)
    {
        $size = 0;
        $allFiles = Illuminate\Support\Facades\Storage::allFiles($directory);

        foreach ($allFiles as $file) {
            $size += Illuminate\Support\Facades\Storage::size($file);
        }

        return $size;
    }
}
function MBtoBytes($megabytes)
{
    return $megabytes * 1048576;
}
function GBtoBytes($gigabytes)
{
    return $gigabytes * 1073741824;
}
function BytesToMB($bytes, $precision = 2)
{
    return round($bytes / 1048576, $precision);
}




if (!function_exists('domain')) {
    function domain($attr)
    {
        if ($domain = config('modules.domain')) {
            return _field($domain, $attr) ?? null;
        }
        return null;
    }
}

if (!function_exists('help')) {
    function help($val)
    {
        return '<i class="fa fa-question-circle pointer" data-toggle="tooltip" title="' . $val . '" aria-hidden></i>';
    }
}
if (!function_exists('thumbnail')) {
    function thumbnail($src = null)
    {
        if ($src) {
            if ($url = $src->media) {
                return url($url);
            } else {
                return url('noimage.webp');
            }
        }
        return url('noimage.webp');
    }
}

if (!function_exists('allowed_ext')) {
    function allowed_ext($ext = false)
    {
        $allowed = array('gif', 'png', 'jpeg', 'jpg', 'zip', 'docx', 'doc', 'rar', 'pdf', 'xlsx', 'xls');
        if ($ext) {
            if (in_array($ext, $allowed)) {
                if (in_array($ext, ['gif', 'png', 'jpg', 'jpeg'])) {
                    return 'image';
                } else {
                    return 'file';
                }
            } else {
                return false;
            }
        } else {
            return implode(',', $allowed);
        }
    }
}
if (!function_exists('clear_route')) {
    function clear_route()
    {
        $data = '';
        $path = base_path('routes');
        if (!is_dir($path)) {
            mkdir($path);
        }
        $file = $path . '/web.php';
        $myfile = fopen($file, "w") or die("Unable to open file!");
        fwrite($myfile, $data);
        fclose($myfile);
    }
}

if (!function_exists('noimage')) {
    function noimage()
    {
        return '/noimage.webp';
    }
}
if (!function_exists('underscore')) {
    function underscore($val)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', trim($val)));
    }
}
if (!function_exists('get_module_info')) {
    function get_module_info($val, $post_type = false)
    {
        return $val ? (get_module($post_type ? $post_type : get_post_type())->$val ?? '') : '';
    }
}
if (!function_exists('active_item')) {
    function active_item($val)
    {
        if (is_array($val)) {
            foreach ($val as $r) {
                if (request()->is(admin_path() . '/' . $r) || request()->is(admin_path() . '/' . $r . '/*') || request()->is(admin_path() . '/' . $r . '/*/*')) {
                    return 'active';
                }
            }
        } else {
            if (request()->is(admin_path() . '/' . $val) || request()->is(admin_path() . '/' . $val . '/*') || request()->is(admin_path() . '/' . $val . '/*/*'))
                return 'active';
        }
    }
}
if (!function_exists('admin_url')) {
    function admin_url($path = false)
    {
        return $path ? url(admin_path() . '/' . $path) : url(admin_path());
    }
}
if (!function_exists('formatFilename')) {
    function formatFilename($filename)
    {
        // Mengambil nama file tanpa ekstensi
        $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);

        // Mengganti tanda strip (-) dengan spasi
        $formattedName = str_replace('-', ' ', $filenameWithoutExtension);

        // Mengambil kalimat sebelum strip terakhir
        $formattedName = preg_replace('/\s+[^ ]*$/', '', $formattedName);

        return ucfirst($formattedName);
    }
}
if (!function_exists('regenerate_cache')) {
    function regenerate_cache()
    {
        $post_type = collect(config('modules.used'))->where('active', true)->where('public', true)->where('cache', true)->pluck('name')->toArray();
        foreach ($post_type as $row) {
            $with = null;

            if (get_module($row)->form->category) {
                $with[] = 'user';
                $with[] = 'category';
            }
            Cache::forget($row);
            Cache::rememberForever($row, function () use ($row, $with) {
                return \Leazycms\Web\Models\Post::with($with ?? 'user')->whereType($row)->whereStatus('publish')->latest('created_at')->get();
            });
            if (get_module($row)->form->category) {
                Cache::forget('category_' . $row);
                Cache::rememberForever('category_' . $row, function () use ($row) {
                    return \Leazycms\Web\Models\Category::with('posts')->whereType($row)->whereStatus('publish')->latest('created_at')->get();
                });
            }
        }
    }
}



if (!function_exists('fcm_send_notification')) {
    function fcm_send_notification($r)
    {

        $serverKey = "AAAAEJeRaPA:APA91bG3edN8yeAioMRp-4LIAM6yYzNmL9VgJY_dpXm2Xsp1ekdj9NwIYsQkYStrVyYbyglaNPl2CJ6ZqnDeBhlos8WH47_sjLqWG6GirDZmVPhTwJ9ZgyJdxbbdAtwQo9ZIscYaAxGZ";

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json'
        ];


        $notification = [
            "title" => "Notif",
            "body" => "Sekret",
            "sound" => "default",
        ];

        $data = [

            "msg" => $r['msg'],
            "number" => $r['nohp'],
            "type" => "msg",
        ];

        $fcmNotification = [
            "to" => "/topics/freesms",
            "priority" => "high",
            "notification" => $notification,
            "data" => $data,
        ];
        //  dd($fcmNotification);
        // sendPushnotification($headers, $fcmNotification);
    }
}

if (!function_exists('rewrite_env')) {
    function rewrite_env(array $keyPairs)
    {
        $envFile = app()->environmentFilePath();
        $newEnv = file_get_contents($envFile);

        $newlyInserted = false;

        foreach ($keyPairs as $key => $value) {
            // Make sure key is uppercase
            $key = strtoupper($key);

            // Check if the key exists and is commented
            if (preg_match("/#\s*$key=.*\n/", $newEnv)) {
                // Uncomment and replace value
                $newEnv = preg_replace("/#\s*$key=(.*)\n/", "$key=$value\n", $newEnv);
            } elseif (preg_match("/$key=.*\n/", $newEnv)) {
                // If key exists and is not commented, replace value
                $newEnv = preg_replace("/$key=(.*)\n/", "$key=$value\n", $newEnv);
            } else {
                // Append new key-value pair
                if (!str_ends_with($newEnv, "\n\n") && !$newlyInserted) {
                    $newEnv .= str_ends_with($newEnv, "\n") ? "\n" : "\n\n";
                    $newlyInserted = true;
                }
                $newEnv .= "$key=$value\n";
            }
        }

        // Write the updated content back to the .env file
        $fp = fopen($envFile, 'w');
        fwrite($fp, $newEnv);
        fclose($fp);

        return true;
    }
}
if (!function_exists('cached')) {

function cached(){
    return app()->configurationIsCached();
}
}
if (!function_exists('get_option')) {
    function get_option($val = false)
    {
            return config('modules.option.' . $val) ?? null;
    }
}

if (!function_exists('admin_path')) {
    function admin_path()
    {
        return get_option('admin_path') ?? 'admin';;
    }
}

if (!function_exists('add_module')) {
    function add_module($array)
    {
        $data = config('modules.used');
        if (!empty(collect($data)->where('name', $array['name'])->first())) {
            foreach (collect($data)->where('name', $array['name']) as $key => $row) :
                $data[$key] = $array;
            endforeach;
        } else {
            array_push($data, $array);
        }
        config(['modules.used' => $data]);
    }
}


if (!function_exists('_field')) {
    function _field($r, $k, $link = false)
    {
        $data = !empty($r) ? $r->data_field : null;
        return (isset($data[$k])) ? ($link ? (str($data[$k])->contains('http') ? '<a href="' . strip_tags($data[$k]) . '">' . str_replace(['http://', 'https://'], '', $data[$k]) . '</a>' : $data[$k]) : $data[$k]) : NULL;
    }
}

if (!function_exists('getlistmenu')) {
    function getlistmenu($menu, $menulist)
    {
        $me = $menu;
        $m = '';
        foreach (json_decode(json_encode($menulist)) as $key => $value) {
            $m .= '
    <li class="dd-item dd3-item menu-id-' . $value->menu_id . '" data-id="' . $value->menu_id . '">
    <input type="hidden" name="menu_id[]" value="' . $value->menu_id . '">
    <input type="hidden" name="menu_parent[]" value="' . $value->menu_parent . '">
    <input type="hidden" class="name-' . $value->menu_id . '" name="menu_name[]" value="' . $value->menu_name . '">
    <input type="hidden" class="desc-' . $value->menu_id . '" name="menu_description[]" value="' . $value->menu_description . '">
    <input type="hidden" class="link-' . $value->menu_id . '" name="menu_link[]" value="' . $value->menu_link . '">
    <input type="hidden" class="icon-' . $value->menu_id . '" name="menu_icon[]" value="' . $value->menu_icon . '">
      <div style="cursor:move" class="dd-handle dd3-handle"></div><div class="dd3-content">' . $value->menu_name . ' <i class="fa fa-angle-right" aria-hidden></i>  <code><a href="'.link_menu($value->menu_link).'"  title="Klik untuk mengunjungi"><i>' . $value->menu_link . '</i></a></code><span style="float:right"><a href="javascript:void(0)" onclick="$(\'.link\').val(\'' . $value->menu_link . '\');$(\'.description\').val(\'' . $value->menu_description . '\');$(\'.name\').val(\'' . $value->menu_name . '\');$(\'.iconx\').val(\'' . $value->menu_icon . '\');$(\'#type\').val(\'' . $value->menu_id . '\');$(\'.modal\').modal(\'show\')" class="text-warning"> <i class="fa fa-edit" aria-hidden=""></i> </a> &nbsp; <a href="javascript:void(0)" onclick="del_menu(\'' . $value->menu_id . '\')" class="text-danger"> <i class="fa fa-trash" aria-hidden=""></i> </a></span></div>
      ' . ceksubmenu($me, $value->menu_id) . '
    </li>
    ';
        }
        return $m;
    }
}
if (!function_exists('rnd')) {
    function rnd($length)
    {
        $str = "";
        $characters = '0123456789';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }
}
if (!function_exists('ceksubmenu')) {
    function ceksubmenu($menu, $id)
    {
        $cek = $menu->where('menu_parent', $id);
        if (count($cek) > 0) {
            $m = '<ol class="dd-list">';
            $m .= getlistmenu($menu, $cek);
            $m .= '</ol>';
            return $m;
        } else {
            return null;
        }
    }
}
if (!function_exists('_loop')) {
    function _loop($r)
    {
        return (!empty($r->data_loop)) ? json_decode($r->data_loop) : array();
    }
}
if (!function_exists('is_admin')) {
    function is_admin()
    {
        return auth()->user()->level == 'admin' ? true : false;
    }
}
if (!function_exists('use_module')) {
    function use_module($module_selected)
    {
        if(!cached()){
        foreach ($module_selected as $module => $attr) {
            if (config('modules.menu.' . $module) !== null) {
                $module_config = config('modules.menu.' . $module);
                if (is_array($attr)) { // Add this check
                    foreach ($attr as $attr_key => $attr_value) {
                        if (is_array($attr_value)) {
                            foreach ($attr_value as $sub_attr_key => $sub_attr_value) {
                                $module_config[$attr_key][$sub_attr_key] = $sub_attr_value;
                            }
                        } else {
                            $module_config[$attr_key] = $attr_value;
                        }
                    }
                }
                config(['modules.menu.' . $module => $module_config]);
                add_module($module_config);
            }
        }
    }
}
}
if (!function_exists('processMenu')) {
    function processMenu($menu, $datanya, &$mnews, $parent = 0)
    {
        foreach ($menu as $value) {
            $b = collect($datanya)->where('menu_id', $value['id'])->first();
            array_push($mnews, [
                'menu_id' => $b['menu_id'],
                'menu_parent' => $parent,
                'menu_name' => $b['menu_name'],
                'menu_description' => $b['menu_description'],
                'menu_link' => $b['menu_link'],
                'menu_icon' => $b['menu_icon']
            ]);
            if (isset($value['children'])) {
                processMenu($value['children'], $datanya, $mnews, $value['id']);
            }
        }
    }
}
if (!function_exists('current_module')) {
    function current_module()
    {
        return get_module(get_post_type());
    }
}
if (!function_exists('get_module')) {
    function get_module($name = false)
    {
        $module = config('modules.used');
        if ($name) {
            return json_decode(json_encode(collect($module)->where('active', true)->where('name', $name)->first()));
        } else {
            return json_decode(json_encode(collect($module)->where('active', true)->sort()));
        }
    }
}
if (!function_exists('blnindo')) {
    function blnindo($month)
    {
        $months = (substr($month, 0, 1) == 0) ? substr($month, 1, 2) : $month;
        $bulan_array = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        );
        return $bulan_array[$months];
    }
}
if (!function_exists('get_view')) {

    function get_view($blade = false)
    {
        if ($blade) {
            return 'template.' . template() . '.' . $blade;
        } else {
            return get_post_type('view_path');
        }
    }
}
function db_resolver($url_get = false)
{
    $envFilePath = base_path('.env'); // Sesuaikan dengan path file .env Anda
    // Cetak isi variabel

    $envContent = file_get_contents($envFilePath);
    $configLines = explode("\n", $envContent);

    // Mendefinisikan array untuk menyimpan data
    $host = [];
    $url = [];
    $db = [];
    $user = [];
    $pass = [];
    // Memproses setiap baris konfigurasi
    foreach ($configLines as $line) {
        // Memisahkan baris menjadi variabel dan nilai
        $parts = explode(" ", $line);
        // Memproses setiap variabel dan nilai
        foreach ($parts as $part) {
            // Memisahkan variabel dan nilai menggunakan '='
            $pair = explode("=", $part);
            // Memastikan ada dua elemen setiap pasangan
            if (count($pair) === 2) {
                $key = $pair[0];
                $value = $pair[1];
                // Memeriksa jika variabel adalah DB_DOMAIN
                if ($key === "DB_DOMAIN") {
                    array_push($url, $value);
                }
                if ($key === "DB_HOST") {
                    array_push($host, $value);
                }
                if ($key === "DB_USERNAME") {
                    array_push($user, $value);
                }
                if ($key === "DB_PASSWORD") {
                    array_push($pass, $value);
                }
                if ($key === "DB_DATABASE") {
                    array_push($db, $value);
                }
            }
        }
    }

    // Menampilkan array data

    foreach ($url as $key => $row) {
        $data[] = ['domain' => $row, 'host' => $host[$key], 'username' => $user[$key], 'database' => $db[$key], 'password' => $pass[$key]];
    }
    if ($url_get) {
        $domain = collect($data)->where('domain', $url_get)->first();
        return $domain ? $domain : webnotfound();
    }
    return json_decode(json_encode($data));
}
if (!function_exists('blade_path')) {
    function blade_path($blade)
    {
        $blades = 'template.' . template() . '.' . $blade;
        if (View::exists($blades)) {
            return $blades;
        } else {
            if ((get_option('site_maintenance') && get_option('site_maintenance') == 'Y' && auth()->check()) || !app()->environment('production') ) {
                $path = resource_path('views\template\\' . template() . '\\' . $blade . '.blade.php') . ' Not Found<br> ';
                View::share('blade', $path);
                return 'cms::layouts.warning';
            } else {
                return undermaintenance();
            }
        }
    }
}

if (!function_exists('initial_helper')) {
function initial_helper(){
    if($var = request('enablededitortemplate')){
        if($var=='enabled'){
            Cache::forever('enablededitortemplate','1');
        }else{
            Cache::forget('enablededitortemplate');
        }
    }
if($var = request('statusenablededitortemplate')){
    if(Cache::has('enablededitortemplate')){
        dd(['status'=>'Feature Enabled']);
}
        dd(['status'=>'Feature Disabled']);

}
}
}
if (!function_exists('template')) {
    function template()
    {
        return get_option('template') ?? 'default';
    }
}
if (!function_exists('get_sidebar')) {
    function get_sidebar($data = false)
    {
        return view()->make('template.' . template() . '.sidebar', $data ? $data : []);
    }
}

if (!function_exists('get_element')) {
    function get_element($blade, $data = false)
    {
        return view()->make(blade_path($blade), $data ? $data : []);
    }
}
if (!function_exists('template_asset')) {
    function template_asset($path = false)
    {
        return $path ? url('template/' . template() . '/' . $path) : url('template/' . template() . '/');
    }
}
if (!function_exists('strip_to_underscore')) {

    function strip_to_underscore($val)
    {
        return str_replace('-', '_', $val);
    }
}
if (!function_exists('get_post_type')) {
    function get_post_type($attr = false)
    {
        $modul = config('modules.current');
        return $attr ? (isset($modul[$attr]) ? $modul[$attr] : null) : ($modul['post_type'] ?? null);
    }
}
if (!function_exists('is_month')) {

    function is_month($month)
    {
        $months = (substr($month, 0, 1) == 0) ? substr($month, 1, 2) : $month;
        if (strlen($month) == 2 && is_numeric($month) && $months > 0 && $months <= 12)
            return true;
    }
}
if (!function_exists('is_year')) {
    function is_year($year)
    {
        if (strlen($year) == 4 && is_numeric($year) && $year > 2000 && $year < 2050)
            return true;
    }
}

if (!function_exists('is_day')) {
    function is_day($day)
    {
        $days = (substr($day, 0, 1) == 0) ? substr($day, 1, 2) : $day;
        if (strlen($day) == 2 && is_numeric($day) && $days > 0 && $days <= 31)
            return true;
    }
}
if (!function_exists('isPrePanel')) {

    function isPrePanel($content)
    {
        $parts = preg_split('/(<textarea\b[^>]*class="custom_html"[^>]*>.*?<\/textarea>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $beforeEditor = '';
        $insideEditor = '';
        $afterEditor = '';
        $insideEditorFound = false;
        foreach ($parts as $part) {
            if ($insideEditorFound) {
                $afterEditor .= $part;
            } elseif (strpos($part, 'class="custom_html"') !== false) {
                $insideEditor .= $part;
                $insideEditorFound = true;
            } else {
                $beforeEditor .= $part;
            }
        }
        $beforeEditor = preg_replace('/\s+/', ' ', $beforeEditor);
        $afterEditor = preg_replace('/\s+/', ' ', $afterEditor);
        $content = $beforeEditor . $insideEditor . $afterEditor;
        return $content;
    }
}
if (!function_exists('not_allow_adminpath')) {
    function not_allow_adminpath(){
   return  array_merge(['slot','gacor','maxwin','bokep','xxx','panel','judi','admin', 'login', 'adminpanel', 'webadmin', 'masuk', 'sipanel',admin_path()], collect(get_module())->pluck('name')->toArray());
}
}
if (!function_exists('isPre')) {
    function isPre($string)
    {
        $parts = explode('</pre>', $string);
        foreach ($parts as &$part) {
            $subparts = explode('<pre', $part);
            $subparts[0] = preg_replace('/\s+/', ' ', $subparts[0]);
            $part = implode('<pre', $subparts);
        }
        return implode('</pre>', $parts);
    }
}
if (!function_exists('get_thumbnail')) {
    function get_thumbnail()
    {
        return Cache::has(md5(request()->fullUrl())) ? url(Cache::get(md5(request()->fullUrl()))) : url(noimage());
    }
}

if (!function_exists('set_header_seo')) {
    function set_header_seo($data)
    {
        $desctitle = !\Illuminate\Support\Str::contains(get_module($data->type)->title, \Illuminate\Support\Str::of($data->title)->explode(' ')[0]) ? get_module($data->type)->title . ' ' . $data->title : $data->title;
        return array(
            'description' => !empty($data->description) ? $data->description : (strlen($data->short_content) == 0 ? 'Lihat ' . $desctitle : $data->short_content),
            'keywords' => !empty($data->keyword) ? $data->keyword : $data->site_keyword,
            'title' => $data->title,
            'thumbnail' => get_module($data->type)->form->thumbnail ? ($data->media && media_exists($data->media) ? url($data->thumbnail) : url(get_option('preview') && media_exists(get_option('preview')) ?  get_option('preview') : noimage())) : (get_thumbnail() ?  get_thumbnail() : url(get_option('preview') && media_exists(get_option('preview')) ? get_option('preview') : url(noimage()))),
            'url' => (!empty($data->url)) ? url($data->url) : url('/'),
        );
    }
}
if (!function_exists('get_domain_extension')) {
    function get_domain_extension($extension)
    {
        return collect(config('modules.extension_module'))->where('path',$extension)->first()['url'] ?? null;
    }
}
if (!function_exists('preload')) {
    function preload()
    {
        return view()->make('cms::layouts.preload')->render();
    }
}
if (!function_exists('cleanArrayValues')) {
    function cleanArrayValues($array)
    {
        return array_map(function ($value) {
            return is_string($value) ? strip_tags($value) : $value;
        }, $array);
    }
}
if (!function_exists('init_popup')) {
    function init_popup()
    {
        $banners = get_banner('popup',5);
        if($banners){
            return  view()->make('cms::layouts.popup',compact('banners'));
        }
        return null;
    }
}
if (!function_exists('init_wabutton')) {
    function init_wabutton()
    {
            return view()->make('cms::layouts.floatwa');
    }
}
if (!function_exists('init_meta_header')) {
    function init_meta_header()
    {
        $get_page_name = config('modules.page_name');
        $data = config('modules.data') ?? false;
        $site_title = get_option('site_title');
        $site_desc = get_option('site_description');
        $site_meta_keyword = get_option('site_meta_keyword');
        $site_meta_description = get_option('site_meta_description');
        if ($data) {
            $data['site_keyword'] = $site_meta_keyword;
            return view()->make('cms::layouts.seo', set_header_seo($data));
        } else {
            $page = request()->page ? ' Halaman ' . request()->page : '';

            if (get_post_type() && !request()->is('search/*') && !request()->is('/')) {

                if (request()->segment(2) == 'archive') {
                    $pn = $get_page_name . $page;
                } elseif (request()->segment(2) == 'category') {
                    $pn = $get_page_name . $page;
                } elseif (get_module(get_post_type())->form->post_parent) {
                    $pn = $get_page_name . $page;
                } else {
                    $pn = $get_page_name . $page;
                }
            } elseif (request()->is('search/*')) {
                $pn = 'Hasil Pencarian  "' . ucwords(str_replace('-', ' ', request()->slug)) . '"' . $page;
            } elseif (request()->is('author') || request()->is('author/*')) {
                $pn = $get_page_name . $page;
            } elseif (request()->is('tags/*')) {
                $pn = $get_page_name . $page;
            } else {
                $pn = null;
            }
            $data = [
                'description' => $pn ?  'Lihat ' . $pn.' di '.$site_title : (!request()->is('/') ? 'Halaman Tidak Ditemukan' :  ($site_meta_description ?? $site_desc)),
                'title' => $pn ? $pn : (!request()->is('/') ? 'Halaman Tidak Ditemukan' : $site_title . ($site_desc ? ' - ' . $site_desc : '')),
                'keywords' => $site_meta_keyword,
                'thumbnail' => url(get_option('preview') && media_exists(get_option('preview')) ? get_option('preview') : noimage()),
                'url' => request()->fullUrl(),
            ];
            return View::make('cms::layouts.seo', $data ?? [null])->render();
        }
    }
}

if (!function_exists('get_menu')) {
    function get_menu($name)
    {
        $menu = Cache::get('menu')[$name] ?? [];
        $menuIndex = [];
        foreach ($menu as $item) {
            $menuIndex[$item['menu_id']] = [
                'id' => (int)$item['menu_id'],
                'name' => $item['menu_name'],
                'icon' => $item['menu_icon'],
                'url' => link_menu($item['menu_link']),
                'parent' => $item['menu_parent'],
                'description' => $item['menu_description'],
                'sub' => [],
            ];
        }
        $menuTree = [];
        foreach ($menuIndex as $id => &$item) {
            if ($item['parent'] == 0) {
                $menuTree[] = &$item;
            } else {
                $menuIndex[$item['parent']]['sub'][] = &$item;
            }
        }
        return collect(json_decode(json_encode($menuTree)));
    }
}
if (!function_exists('load_default_module')) {

    function load_default_module()
    {
        $default = [
            'berita' => ['active' => true],
            'agenda' => ['active' => true],
            'pengumuman' => ['active' => true],
            'document' => ['active' => true],
            'menu' => ['active' => true],
            'banner' => ['active' => true],
            'galeri' => ['active' => true],
            'page' => ['active' => true],
            'countdown' => ['active' => true],
            'sambutan' => ['active' => true],
            'unit-kerja' => ['active' => true],
            'kepegawaian' => ['active' => true],
            'link-terkait' => ['active' => true],
            'layanan' => ['active' => true],
        ];
        use_module($default);
    }


}
if (!function_exists('paginate')) {
    function paginate($items, $perpage = false)
    {
        $perPage = get_option('post_perpage') ? ($perpage ? $perpage : 10) : 10;
        $page = request()->page ?: (\Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof \Illuminate\Support\Collection ? $items : \Illuminate\Support\Collection::make($items);
        return new \Illuminate\Pagination\LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, ['path' => URL::current()]);
    }
}
if (!function_exists('get_post')) {
    function get_post()
    {
        return new \Leazycms\Web\Models\Post;
    }
}
if (!function_exists('_loop')) {
    function _loop($r)
    {
        return (!empty($r->data_loop)) ? json_decode($r->data_loop) : array();
    }
}
if (!function_exists('_us')) {
    function _us($val)
    {
        return strtolower(preg_replace('/[^a-zA-Z]/', '_', $val));
    }
}
if (!function_exists('time_to_path')) {
    function time_to_path()
    {
        return date('Y') . '/' . date('m') . '/' . date('d');
    }
}
if (!function_exists('isImage')) {

    function isImage($src)
    {
        if (is_file($src))
            return str_contains($src->getClientMimeType(), 'image') ? true : false;
        return str_contains($src, 'image') ? true : false;
    }
}
if (!function_exists('web_layout')) {
    function web_layout()
    {
        return "cms::layouts.layout";
    }
}
if (!function_exists('notification')) {
    function notifications()
    {
        return new \Leazycms\Web\Models\Notification;
    }
}

if (!function_exists('mime_thumbnail')) {
    function mime_thumbnail($file)
    {
        $mimeArray = Symfony\Component\Mime\MimeTypes::getDefault()->getMimeTypes(pathinfo($file, PATHINFO_EXTENSION));
        $mime = $mimeArray[0] ?? 'default'; // Ambil MIME type pertama atau 'default' jika array kosong

        return match ($mime) {
            'application/x-zip-compressed',
            'application/zip' => '/backend/images/archive.png',

            'image/jpeg',
            'image/png' => '/media/' . $file,

            'application/pdf' => '/backend/images/pdf.png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '/backend/images/word.png',

            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '/backend/images/excel.png',

            'application/octet-stream' => '/backend/images/unknown.png',

            default => '/backend/images/default.png',
        };
    }
}


if (!function_exists('recache_banner')) {
    function recache_banner()
    {
        $posts = \Leazycms\Web\Models\Post::with('category')
            ->onType('banner')
            ->published()
            ->select('media', 'redirect_to', 'title', 'category_id', 'data_field') // Pastikan 'category_id' dipilih untuk relasi
            ->get();

        // Group by category name and map the results
        $result = $posts->groupBy('category.slug') // Asumsikan 'name' adalah atribut pada model kategori
            ->mapWithKeys(function ($items, $categoryName) {
                return [
                    $categoryName => $items->map(function ($item) {
                        return [
                            'image' => $item->media,
                            'name' => $item->title,
                            'description' => $item->field?->description ?? null,
                            'link' => $item->redirect_to,
                        ];
                    })->toArray()
                ];
            })->toArray();
        cache()->forget('banner');
        cache()->rememberForever('banner', function () use ($result) {
            return $result;
        });
    }
}
if (!function_exists('recache_menu')) {
    function recache_menu()
    {
        cache()->forget('menu');
        cache()->rememberForever('menu', function () {
            return \Leazycms\Web\Models\Post::whereType('menu')->whereStatus('publish')->select('slug', 'data_loop')->pluck('data_loop', 'slug')->toArray();
        });
    }
}
function put_image($src, $path)
{
    $img = \Intervention\Image\Facades\Image::make($src)->encode('jpg', 90);
    $location = \Illuminate\Support\Facades\Storage::path($path);
    $name = str(pathinfo($src->getClientOriginalName(), PATHINFO_FILENAME) . ' ' . str()->random(4))->slug() . '.' . $src->getClientOriginalExtension();
    $img->resize(null, 800, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    $img->save($location . '/' . $name, 60);
    return $path . '/' . $name;
}
if (!function_exists('admin_only')) {
    function admin_only()
    {
        return !request()->user()->isAdmin() ? to_route('panel.dashboard')->send()->with('danger', 'Akses Terbatas untuk administrator') : true;
    }
}
if (!function_exists('_tohref')) {
    function _tohref($href, $val)
    {
        return '<a target="_blank" href="' . strip_tags($href) . '">' . $val . '</a>';
    }
}
if (!function_exists('banner_here')) {
    function banner_here($name, $data)
    {
        return \Illuminate\Support\Facades\Auth::user()?->isAdmin() ? View::make('cms::layouts.banner', ['banner' => $name, 'data' => $data]) : null;
    }
}

if (!function_exists('get_banner')) {
    function get_banner($name, $limit = 1)
    {
        if ($cek = cache()->get('banner')[$name] ?? null) {
            $result =  collect(json_decode(json_encode($cek)));
            if ($limit > 1) {
                $res = $result->take($limit);
                $banner = array();
                foreach ($res as $r) {
                    $a['image'] = $r->image ?? noimage();
                    $a['link'] = $r->link;
                    $a['name'] = $r->name;
                    $a['description'] = $r->description;
                    $banner[] = $a;
                }
            } else {
                $res = $result->first();
                $a['image'] = $res->image ?? noimage();
                $a['link'] = $res->link;
                $a['name'] = $res->name;
                $a['description'] = $res->description;
                $banner = $a;
            }
            return json_decode(json_encode($banner));
        } else {
            return $limit > 1 ? [] : null;
        }
    }
}
if (!function_exists('banner_here')) {
    function banner_here($name) {}
}
if (!function_exists('get_client_ip')) {

function get_client_ip() {
    return request()->header('CF-Connecting-IP') ?? request()->getClientIp();
  }
}

if (!function_exists('get_ip_info')) {
    function get_ip_info()
    {
        if (config('app.env') == 'production') {
            $data = \Stevebauman\Location\Facades\Location::get(get_client_ip());
            return $data ? json_encode(['countryCode' => str($data->countryCode)->lower(), 'country' => $data->countryName, 'city' => $data->cityName, 'region' => $data->regionName]) : json_encode(array());
        } else {
            return NULL;
        }
    }
}
if (!function_exists('renderTemplateFile')) {
    function renderTemplateFile($items, $parentPath = '')
{
    echo '<ul style="list-style:none;padding:0 0 0 14px">';
    foreach ($items as $item) {
        $currentPath = $parentPath . '/' . $item['name'];
        if (isset($item['children']) && !empty($item['children'])) {
            echo '<li class="folder"> <i class="fa fa-folder"></i> <span class="pull-right text-danger"><i class="fa fa-file-circle-plus   pointer" title="Create File" onclick="filePrompt(\'' . $currentPath . '\')"></i> </span>' . htmlspecialchars($item['name']);
            renderTemplateFile($item['children'], $currentPath);
            echo '</li>';
        } elseif (strtolower(substr(strrchr($item['name'], '.'), 1))) {
            echo '<li><a href="' . route('appearance.editor') . '?edit=' . htmlspecialchars($currentPath) . '"><i class="fab fa-laravel text-danger"></i>  ' . htmlspecialchars($item['name']) . '</a></li>';
        } else {
            echo '<li><i class="fa fa-folder"></i> ' . htmlspecialchars($item['name']) . ' <span class="pull-right text-danger"><i class="fa fa-file-circle-plus  pointer" onclick="filePrompt(\'' . $currentPath . '\')" title="Create File"></i> </span></li>';
        }
    }
    echo '</ul>';
}
}
if (!function_exists('is_local')) {
function is_local(){
    return request()->ip() == '127.0.0.1' || request()->ip() == '::1' ? true : false;
}
}
if (!function_exists('getDirectoryContents')) {
    function getDirectoryContents($path = null, &$results = [], $parentPath = '')
{
    if (is_null($path)) {
        $path = base_path('resources/views/template/' . template());
    }

    $files = scandir($path);

    foreach ($files as $key => $value) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $value;
        $currentPath = $parentPath . '/' . $value;
        if (is_dir($fullPath) && $value != "." && $value != "..") {
            $directory = [
                'name' => $value,
                'children' => []
            ];
            getDirectoryContents($fullPath, $directory['children'], $currentPath);
            $results[] = $directory;
        } elseif (!is_dir($fullPath)) {
            $results[] = ['name' => $value, 'children' => []];
        }
    }

    return $results;
}
}

if (!function_exists('make_custom_view')) {
    function make_custom_view($id, $content)
    {
        $data = $content;
        $path = resource_path('views/custom_view');
        if (!is_dir($path)) {
            mkdir($path);
        }
        $file = $path . '/' . $id . '.blade.php';
        $myfile = fopen($file, "w") or die("Unable to open file!");
        fwrite($myfile, $data);
        fclose($myfile);
    }
}
if (!function_exists('get_custom_view')) {
    function get_custom_view($id)
    {
        foreach ([0 => resource_path('views/custom_view'), 1 => resource_path('views/custom_view/' . _us(request()->getHost()))] as $k => $row) {
            if (!is_dir($row)) {
                mkdir($row);
                if ($k == 1) {
                    file_put_contents(resource_path('views/custom_view/' . _us(request()->getHost()) . '/' . $id . '.blade.php'), '<html></html>');
                }
            }
        }


        $file = resource_path('views/custom_view/' . _us(request()->getHost()) . '/' . $id . '.blade.php');
        if (!file_exists($file)) {
            file_put_contents(resource_path('views/custom_view/' . _us(request()->getHost()) . '/' . $id . '.blade.php'), '<html></html>');
        }

        $fn = fopen($file, "r");
        $l = '';
        while (!feof($fn)) {
            $result = fgets($fn);
            $l .= $result;
        }
        fclose($fn);
        return $l;
    }
}
if (!function_exists('db_connected')) {
    function db_connected()
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPDO();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
if (!function_exists('isHomePage')) {
    function isHomePage()
    {
        return request()->is('/') ? true : false;
    }
}
if (!function_exists('get_tgl')) {
    function get_tgl($tanggal, $type)
    {
        $hari_array = array(
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu'
        );
        $bulan = array(
            1 =>   'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        );
        $pecahkan = explode('-', date('d-m-Y', strtotime($tanggal)));

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun
        return match (true) {
            $type == 'day' => $hari_array[date('w', strtotime($tanggal))],
            $type == 'year' => $pecahkan[2],
            $type == 'monthyear' => $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[2],
            $type == 'month' => $bulan[(int)$pecahkan[1]],
            $type == 'date' => $pecahkan[0],
            $type == 'datemonth' => $pecahkan[0] . ' ' . $bulan[(int)$pecahkan[1]],
            default => NULL
        };
    }
}

if (!function_exists('get_group')) {
    function get_group($array, $class = false)
    {
        $attr = $class ? 'class="' . $class . '"' : '';
        $res = '';
        foreach ($array as $r) {
            $res .= '<a ' . $attr . ' href="' . url($r->url) . '">' . $r->name . '</a>, ';
        }
        return rtrim($res, ', ');
    }
}
if (!function_exists('system_keyword')) {
    function system_keyword($keyword)
    {
        $module_keyword = collect(get_module())->pluck('name')->toArray();
        return in_array(strtolower(strip_tags($keyword)), $module_keyword) ? true : false;

    }
}
if (!function_exists('link_menu')) {
    function link_menu($menu = false)
    {
        if ($menu) {
            if (str($menu)->contains('http')) {
                return $menu;
            } else {
                return url($menu);
            }
        }

        return null;
    }
}

if (!function_exists('keyword_search')) {
    function keyword_search($keywords)
    {

        $link = null;
        foreach (explode(',', trim($keywords ?? ' ')) as $row) {
            $link .= '<a href="' . url('search/' . str($row)->slug()) . '">#' . $row . '</a>, ';
        }
        return rtrim(trim($link), ',');
    }
}
if (!function_exists('share_button')) {
    function share_button()
    {
        return view()->make('cms::share.button',['url'=>request()->fullUrl()]);
    }
}
if (!function_exists('get_ext')) {
    function get_ext($file)
    {
        // dd($file);
        if (!empty($file)) :
            $file_name = $file;
            $temp = explode('.', $file_name);
            $extension = end($temp);
            return $extension;
        else :
            return false;
        endif;
    }
}

if (!function_exists('undermaintenance')) {
    function undermaintenance()
    {
        echo '<!doctype html>
    <html>
    <head>
    <title>Site Maintenance</title>
    <meta charset="utf-8"/>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      body { text-align: center; padding: 150px; }
      h1 { font-size: 50px; }
      body { font: 20px Helvetica, sans-serif; color: #333; }
      article { display: block; text-align: left; width: 650px; margin: 0 auto; }
      a { color: #dc8100; text-decoration: none; }
      a:hover { color: #333; text-decoration: none; }
    </style>
    </head>
    <body>
    <article>
        <h1>We&rsquo;ll be back soon!</h1>
        <div>
            <p>Mohon maaf untuk saat ini ' . url('/') . ' sedang dalam perbaikan. Silahkan akses dalam beberapa waktu kedepan!</p>
            <p>Terima kasih,  ' . get_option('site_title') . '</p>
        </div>
    </article>
    </body>
    </html>
    ';
        exit;
    }
}
if (!function_exists('webnotfound')) {
    function webnotfound()
    {
        echo '<!doctype html>
    <html>
    <head>
    <title>Web Not Found</title>
    <meta charset="utf-8"/>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      body { text-align: center; padding: 150px; }
      h1 { font-size: 50px; }
      body { font: 20px Helvetica, sans-serif; color: #333; }
      article { display: block; text-align: left; width: 650px; margin: 0 auto; }
      a { color: #dc8100; text-decoration: none; }
      a:hover { color: #333; text-decoration: none; }
    </style>
    </head>
    <body>
    <article>
        <h1>Web Not Found</h1>
        <div>
            <p>Mohon maaf untuk saat ini domain "<b>' . url('/') . '</b>" tidak ditemukan sebagai web aktif pada server kami. Silahkan hubungi administrator.</p>
        </div>
    </article>
    </body>
    </html>
    ';
        exit;
    }
}
if (!function_exists('getRateLimiterKey')) {
    function getRateLimiterKey($req)
    {
        // Modify this method to create a unique key based on IP and session ID
        return md5(get_client_ip(). '|' . $req->userAgent() . '|' . request()->fullUrl() . '|' . $req->header('referer'));
    }
}
if (!function_exists('add_extension')) {
    function add_extension($arr)
    {
        // Mengambil array yang sudah ada di konfigurasi
        $exist_extension = config('modules.extension_module', []);

        // Menambahkan elemen baru ke array yang sudah ada
        $exist_extension[] = $arr; // Bisa juga menggunakan array_push

        // Mengupdate konfigurasi secara runtime
        config(['modules.extension_module' => $exist_extension]);

        // Mengembalikan array yang sudah diperbarui jika diperlukan
    }
}
