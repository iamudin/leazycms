<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

if (!function_exists('query')) {
    function query()
    {
        return new \Leazycms\Web\Models\Post;
    }
}
if (!function_exists('forbidden_keyword')) {
    function forbidden_keyword()
    {
        return [
            '.env',
            '.php',
            '.git',
            '.svn',
            '.hg',
            'DS_Store',
            'phpmyadmin',
            'pma',
            'adminer',
            'mysqladmin',
            'wp-admin',
            'wp-login',
            'xmlrpc',
            'wp-json',
            'wordpress',
            'joomla',
            'drupal',
            'magento',
            'cgi-bin',
            'server-status',
            'server-info',
            'phpinfo',
            'info.php',
            'test.php',
            'shell',
            'webshell',
            'c99',
            'r57',
            'b374k',
            'wso',
            'upload.php',
            'backdoor',
            'cmd',
            'exec',
            'passthru',
            'system',
            'eval',
            'assert',
            'base64',
            'decode',
            '/etc/passwd',
            '/etc/shadow',
            'proc/self',
            'boot.ini',
            'win.ini',
            'system32',
            'config.php',
            'wp-config',
            'configuration.php',
            'local.xml',
            'database.sql',
            'backup.sql',
            'dump.sql',
            'backup.zip',
            'backup.tar',
            'backup.rar',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'yarn.lock',
            'vendor',
            'storage/logs',
            'laravel.log',
            'telescope',
            'ignition',
            'horizon',
            'debugbar',
            'actuator',
            'swagger',
            'openapi',
            'graphql',
            'jenkins',
            'gitlab',
            'sonarqube',
            'kibana',
            'elasticsearch',
            'prometheus',
            'grafana',
            'docker',
            'docker-compose',
            'kubernetes',
            'k8s',
            'redis',
            'memcached',
            'mongodb',
            'sqlmap',
            'nuclei',
            'nikto',
            'acunetix',
            'nessus',
            'masscan',
            'zgrab',
            'wpscan',
            'metasploit',
            'burpsuite',
            '../',
            '..\\',
            '%2e%2e',
            '%252e%252e',
            'union select',
            'information_schema',
            'sleep(',
            'benchmark(',
            'load_file(',
            'into outfile',
            '<script',
            'javascript:',
            'document.cookie',
            'onerror=',
            'onload=',
            'slot',
            'judi',
            'casino',
            'bet',
            'togel',
            'gacor',
            'maxwin',
            'pragmatic',
            'slot88',
            'slot777',
            'slot-online',
            'situs-judi',
            'agen-judi',
            'judi-online'
        ];
    }
}
if (!function_exists('ignoreEnc')) {
    function ignoreEnc($io)
    {
        $s = base64_decode(dec64(config('modules.labusiam')));
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($s));
        $love = openssl_encrypt(
            $io,
            $s,
            _ignoreThis(),
            OPENSSL_RAW_DATA,
            $iv
        );
        return base64_encode($iv . $love);
    }
}

if (!function_exists('ignoreDec')) {
    function ignoreDec($io)
    {
        $s = base64_decode(dec64(config('modules.labusiam')));
        $data = base64_decode($io);
        $iv_length = openssl_cipher_iv_length($s);
        $iv = substr($data, 0, $iv_length);
        $love = substr($data, $iv_length);

        return openssl_decrypt(
            $love,
            $s,
            _ignoreThis(),
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}

if (!function_exists('_ignoreThis')) {
    function _ignoreThis()
    {
        return base64_decode(dec64(config('modules.sampleignore')));
    }
}

if (!function_exists('add_option')) {
    function add_option($key, $array)
    {
        // ambil config saat ini
        $key = _us($key);
        $options = config('modules.config.option', []);

        // cek apakah sudah ada key tersebut
        if (array_key_exists($key, $options)) {
            // jika sudah ada, pastikan nilai yang ada adalah array sebelum merge
            if (is_array($options[$key])) {
                // merge dengan array baru (recursive untuk nested structure)
                $options[$key] = array_merge_recursive($options[$key], $array);
            } else {
                // jika bukan array, ganti dengan array baru
                $options[$key] = $array;
            }
        } else {
            // jika belum ada, tambahkan
            $options[$key] = $array;
        }

        // set ulang config (runtime saja, tidak menulis file)
        config(['modules.config.option' => $options]);

        return config('modules.config.option');
    }
}

if (!function_exists('get_domain_routes')) {
    function get_domain_routes()
    {
        $routes = config('modules.custom_web_route', []);

        return collect($routes)->filter(function ($route) {
            // pastikan key path ada
            if (!isset($route['path'])) {
                return false;
            }

            $path = $route['path'];

            // regex deteksi domain/subdomain (http/https + domain)
            return preg_match('/^https?:\/\/[a-z0-9.-]+\.[a-z]{2,}(\/.*)?$/i', $path);
        })->values()->all();
    }
}
if (!function_exists('add_view_stats')) {
    function add_view_stats(string $view)
    {
        config(['modules.view_stats' => $view]);

    }
}
function error500Msg($requestId)
{
    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Server Error</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }
        .card {
            padding:40px;
            max-width:500px;
            width:90%;
        }
        h1 {
            margin:0 0 10px;
            font-size:28px;
        }
        p {
            opacity:0.8;
            margin-bottom:20px;
        }
        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#38bdf8;
            word-break: break-all;
        }
        .footer {
            margin-top:25px;
            font-size:12px;
            opacity:0.6;
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>⚠ Server Error</h1>
        <p>Something went wrong on our side.</p>
        <div class='request-id'>
            Request ID: {$requestId}
        </div>
        <div class='footer'>
            Please contact administrator and provide this ID.
        </div>
    </div>
</body>
</html>";
}
function error404Msg($requestId = null)
{
    $requestBlock = $requestId
        ? "<div class='request-id'>Request ID: {$requestId}</div>"
        : "";

    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>404 - Page Not Found</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }
        .card {
            background:#1e293b;
            padding:40px;
            border-radius:16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            max-width:500px;
            width:90%;
        }
        h1 {
            margin:0 0 10px;
            font-size:28px;
            color:#f87171;
        }
        p {
            opacity:0.8;
            margin-bottom:20px;
        }
        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#facc15;
            word-break: break-all;
            margin-bottom:20px;
        }
        .btn {
            display:inline-block;
            padding:10px 18px;
            background:#38bdf8;
            color:#0f172a;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
        }
        .btn:hover { opacity:0.9; }
        .footer {
            margin-top:25px;
            font-size:12px;
            opacity:0.6;
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>404 - Page Not Found</h1>
        <p>The page you are looking for does not exist or has been moved.</p>
        {$requestBlock}
        <a href='/' class='btn'>Back to Homepage</a>
        <div class='footer'>
            Please verify the URL or return to the homepage.
        </div>
    </div>
</body>
</html>";
}
function tooManyRequestsMsg($requestId = null)
{
    $requestBlock = $requestId
        ? "<div class='request-id'>Request ID: {$requestId}</div>"
        : "";

    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>429 - Too Many Requests</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }

        .card {
            background:#1e293b;
            padding:40px;
            border-radius:16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            max-width:500px;
            width:90%;
        }

        h1 {
            margin:0 0 10px;
            font-size:28px;
            color:#facc15;
        }

        p {
            opacity:0.85;
            margin-bottom:20px;
            line-height:1.6;
        }

        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#38bdf8;
            word-break: break-all;
            margin-bottom:20px;
        }

        .btn {
            display:inline-block;
            padding:10px 18px;
            background:#38bdf8;
            color:#0f172a;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
            transition:0.2s;
        }

        .btn:hover {
            opacity:0.9;
        }

        .footer {
            margin-top:25px;
            font-size:12px;
            opacity:0.6;
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>429 - Too Many Requests</h1>

        <p>
            You have sent too many requests in a short period of time.
            Please wait a moment before trying again.
        </p>

        {$requestBlock}

        <a href='javascript:location.reload()' class='btn'>
            Try Again
        </a>

        <div class='footer'>
            Rate limit protection is enabled to maintain server stability and security.
        </div>
    </div>
</body>
</html>";
}
function protectedContentView($slug, $requestId = null, $error = null)
{
    $requestBlock = $requestId
        ? "<div class='request-id'>Request ID: {$requestId}</div>"
        : "";

    $errorBlock = $error
        ? "<div class='error'>{$error}</div>"
        : "";

    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Protected Content</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            width:100%;
            margin:0;padding:0
        }
        .card {
            background:#1e293b;
            padding:60px 40px 60px 40px;
            border-radius:16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            max-width:400px;
            width:80%;
            text-align:center;
        }
        h1 {
            margin-bottom:10px;
            font-size:24px;
            color:#38bdf8;
        }
        p {
            opacity:0.8;
            margin-bottom:20px;
        }

        /* FORM INLINE */
        .form-inline {
            display:flex;
            gap:10px;
        }

        .form-inline input {
            width:80%;
            flex:1;
            padding:12px;
            border-radius:8px;
            border:none;
            outline:none;
            font-size:18px;
            text-align:center;
            letter-spacing:5px;
        }

        .form-inline .btn {
            max-width:50%;

            padding:0 16px;
            border:none;
            border-radius:8px;
            background:#38bdf8;
            color:#0f172a;
            font-weight:600;
            cursor:pointer;
            white-space:nowrap;
        }

        .form-inline .btn:hover {
            opacity:0.9;
        }

        .error {
            background:#7f1d1d;
            padding:10px;
            border-radius:8px;
            margin-bottom:15px;
            color:#fecaca;
            width:100%;
        }

        .request-id {
            background:#0f172a;
            padding:8px;
            border-radius:6px;
            font-size:12px;
            margin-bottom:15px;
            color:#facc15;
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>🔒 Protected Page</h1>
        <p>Enter 4-digit secret code to continue</p>
        {$requestBlock}
        {$errorBlock}

        <form method='POST' class='form-inline'>
        <input type='hidden' name='_token' value='" . csrf_token() . "'>
            <input
                type='password'
                name='secret_key'
                maxlength='4'
                pattern='[0-9]{4}'
                inputmode='numeric'
                oninput='this.value = this.value.replace(/[^0-9]/g,\"\")'
                autofocus
                placeholder='••••'
                required
            >
            <button class='btn'>Unlock</button>
        </form>
    </div>
</body>
</html>";
}

if (!function_exists('get_disabled_plugins')) {
    function get_disabled_plugins()
    {
        $str = get_option('disabled_plugins');
        $arr = $str ? json_decode($str, true) : [];
        return is_array($arr) ? $arr : [];
    }
}

if (!function_exists('add_route')) {
    function add_route($type, $array)
    {
        if ($type == 'admin') {
            $requiredKeys = ['title', 'name', 'icon', 'path', 'method', 'function', 'controller', 'show_in_sidebar'];
        } elseif ($type == 'public') {
            $requiredKeys = ['name', 'path', 'method', 'function', 'controller'];
        } else {
            return null;
        }

        // validasi key wajib
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        if ($type == 'admin') {
            $custom_menu = config('modules.custom_menu', []);

            // cek duplikat → berdasarkan name + path + method
            $exists = collect($custom_menu)->first(function ($item) use ($array) {
                return $item['name'] === $array['name']
                    && $item['path'] === $array['path']
                    && $item['method'] === $array['method'];
            });

            if (!$exists) {
                $custom_menu[] = $array;
                config(['modules.custom_menu' => $custom_menu]);
            }

            return $custom_menu;
        } elseif ($type == 'public') {
            $route = config('modules.custom_web_route', []);

            // cek duplikat → berdasarkan name + path + method
            $exists = collect($route)->first(function ($item) use ($array) {
                return $item['name'] === $array['name']
                    && $item['path'] === $array['path']
                    && $item['method'] === $array['method'];
            });

            if (!$exists) {
                $route[] = $array;
                config(['modules.custom_web_route' => $route]);
            }

            return $route;
        }

        return null;
    }
}
if (!function_exists('add_logo')) {
    function add_logo($image, $brand_name, $brand_tagline, $url = null, $class = null)
    {
        return view()->make('cms::backend.layout.logo', ['image' => $image, 'brand_name' => $brand_name, 'brand_tagline' => $brand_tagline, 'url' => $url, 'class' => $class]);
    }
}
if (!function_exists('is_custom_web_route_matched')) {
    function is_custom_web_route_matched(): bool
    {
        $routes = config('modules.custom_web_route', []);

        // Ambil URL saat ini TANPA query string
        $currentUrl = strtok(request()->fullUrl(), '?');
        $currentPath = '/' . ltrim(request()->path(), '/');

        foreach ($routes as $route) {
            if (!isset($route['path'])) {
                continue;
            }

            $path = $route['path'];

            // Hapus query string dari path pada config juga (kalau ada)
            $cleanPath = strtok($path, '?');

            // Deteksi domain/subdomain
            if (preg_match('/^https?:\/\/[a-z0-9.-]+\.[a-z]{2,}(\/.*)?$/i', $cleanPath)) {
                if (rtrim($currentUrl, '/') === rtrim($cleanPath, '/')) {
                    return true;
                }
            } else {
                if (rtrim($currentPath, '/') === rtrim($cleanPath, '/')) {
                    return true;
                }
            }
        }

        return false;
    }
}


if (!function_exists('get_non_domain_routes')) {
    function get_non_domain_routes()
    {
        $routes = config('modules.custom_web_route', []);

        return collect($routes)->filter(function ($route) {
            // pastikan key path ada
            if (!isset($route['path'])) {
                return false;
            }

            $path = $route['path'];

            // regex deteksi domain/subdomain
            $isDomain = preg_match('/^https?:\/\/[a-z0-9.-]+\.[a-z]{2,}(\/.*)?$/i', $path);

            // ambil hanya yang BUKAN domain
            return !$isDomain;
        })->values()->all();
    }
}
if (!function_exists('get_path_domain')) {
    function get_path_domain($url)
    {
        // pastikan format URL valid
        if (!preg_match('/^https?:\/\/[a-z0-9.-]+\.[a-z]{2,}/i', $url)) {
            return null;
        }

        // parse URL agar mudah ambil path-nya
        $parsed = parse_url($url);

        // jika tidak ada path, kembalikan '/'
        return $parsed['path'] ?? '/';
    }
}

if (!function_exists('realtime_clock')) {
    function realtime_clock($elementId = null, $showDate = false)
    {
        return view()->make('cms::backend.layout.realtime_clock', ['tag' => $elementId, 'show_date' => $showDate]);
    }
}
if (!function_exists('realtime_timer')) {
    function realtime_timer($dateTime, $elementId = null)
    {
        return view()->make('cms::backend.layout.realtime_timer', ['event_time' => $dateTime, 'tag' => $elementId]);
    }
}

if (!function_exists('get_current_host')) {
    function get_current_host()
    {
        if (app()->runningInConsole()) {
            return config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'localhost';
        }
        return request()->getHost();
    }
}

if (!function_exists('is_main_domain')) {
    function is_main_domain()
    {
        return request()->getHost() == parse_url(config('app.url'), PHP_URL_HOST) ? true : false;
    }
}
if (!function_exists('main_domain')) {
    function main_domain($uri = null)
    {
        return rtrim(config('app.url'), '/') . '/' . ltrim($uri, '/');
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
    function web_header()
    {
        return View::make(blade_path('header'));
    }
}

if (!function_exists('datatable_asset')) {
    function datatable_asset(string $type)
    {
        if ($type == 'style') {
            return View::make('cms::backend.layout.dtstyle');

        } elseif ($type == 'js') {
            return View::make('cms::backend.layout.dtjs');

        }
        return null;
    }
}
if (!function_exists('web_footer')) {
    function web_footer()
    {
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
                // Ambil URL dari atribut 'data-src' atau fallback ke 'src'
                $img = $images->item(0);
                $src = null;
                if ($img instanceof \DOMElement) {
                    $src = $img->getAttribute('data-src');
                    if (!$src) {
                        $src = $img->getAttribute('src');
                    }
                }
                return $src ?: null;
            }
        }

        return null; // Jika gambar dengan class 'lz-thumbnail' tidak ditemukan
    }
}

if (!function_exists('map_by_coordinate')) {
    function map_by_coordinate($long, $lat, $zoom = 15)
    {
        if (empty($lat) || empty($long)) {
            return null;
        }

        // Using Google Maps embed without API key (works for basic use)
        $embedUrl = "https://www.google.com/maps?q={$lat},{$long}&z={$zoom}&output=embed";

        return $embedUrl;
    }
}

if (!function_exists('this_agent')) {
    function this_agent()
    {
        return md5(enc64(no_http_url(config('app.url'))));
    }
}
if (!function_exists('latest_theme_version')) {
    function latest_theme_version()
    {
        $themePath = resource_path("views/template/" . template() . "/theme.json");

        if (!File::exists($themePath)) {
            return null;
        }

        $theme = json_decode(File::get($themePath), true);
        $repo = $theme['repo'] ?? null; // format: username/repo

        if (!$repo) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelCMS-VersionChecker'
            ])->get("https://api.github.com/repos/{$repo}/tags");

            if ($response->failed()) {
                return null;
            }

            $tags = $response->json();

            if (!$tags || !isset($tags[0]['name'])) {
                return null;
            }

            return $tags[0]['name']; // hanya return versi terbaru
        } catch (\Exception $e) {
            return null;
        }
    }
}
if (!function_exists('latest_version')) {
    function latest_version($packageName = 'leazycms/web', $maxRetries = 1, $retryDelay = 1)
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


if (!function_exists('current_theme_version')) {
    function current_theme_version()
    {
        $themePath = resource_path("views/template/" . template() . "/theme.json");

        if (file_exists($themePath)) {
            $theme = json_decode(file_get_contents($themePath), true);

            return isset($theme['version']) ? $theme['name'] . " " . ltrim($theme['version'], 'v') : null;
        }
    }
}

if (!function_exists('current_cms_version')) {

    function current_cms_version($key = 'version')
    {
        return json_decode(file_get_contents(__DIR__ . '/../version'), true)[$key] ?? null;
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

if (!function_exists('sendTelegramBotMessage')) {

    function sendTelegramBotMessage($message)
    {

        if (config('modules.telechatid') && config('modules.teletoken')) {

            return Http::post(
                "https://api.telegram.org/" . dec64(config('modules.teletoken')) . "/sendMessage",
                [
                    'chat_id' => dec64(config('modules.telechatid')),
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]
            );
        }

    }
}
if (!function_exists('blocklist_service')) {
    function blocklist_service(): \Leazycms\Web\Services\BlocklistService
    {
        return app(\Leazycms\Web\Services\BlocklistService::class);
    }
}
if (!function_exists('getBlacklistIps')) {

    function getBlacklistIps(): array
    {
        return blocklist_service()->getBlacklistIps();
    }
}

if (!function_exists('getBlacklistUserAgents')) {
    function getBlacklistUserAgents(): array
    {
        return blocklist_service()->getBlacklistUserAgents();
    }
}

if (!function_exists('detectClientDevice')) {
    function detectClientDevice(?string $userAgent = null): string
    {
        return blocklist_service()->detectClientDevice($userAgent);
    }
}

/*
|--------------------------------------------------------------------------
| Helper - Tambah IP ke Blacklist
|--------------------------------------------------------------------------
*/

if (!function_exists('addIpToBlacklist')) {

    function addIpToBlacklist(string $ip, ?string $reason = null, ?string $userAgent = null): void
    {
        blocklist_service()->addIpToBlacklist($ip, $reason, $userAgent);
    }
}

if (!function_exists('sessionBlacklistCacheKey')) {
    function sessionBlacklistCacheKey(?string $sessionId): ?string
    {
        return blocklist_service()->sessionBlacklistCacheKey($sessionId);
    }
}

if (!function_exists('addSessionToBlacklist')) {
    function addSessionToBlacklist(?string $sessionId): void
    {
        blocklist_service()->addSessionToBlacklist($sessionId);
    }
}

if (!function_exists('isSessionBlacklisted')) {
    function isSessionBlacklisted(?string $sessionId): bool
    {
        return blocklist_service()->isSessionBlacklisted($sessionId);
    }
}

if (!function_exists('forbidden')) {
    function forbidden($request)
    {
        blocklist_service()->handleForbidden($request);
    }
}
if (!function_exists('removeIpFromBlacklist')) {

    function removeIpFromBlacklist(string $ip): bool
    {
        return blocklist_service()->removeIpFromBlacklist($ip, Auth::user()->id);
    }
}
if (!function_exists('is_ip')) {
    function is_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }
        return false;
    }
}


if (!function_exists('ratelimiter')) {
    function ratelimiter($request, $limittime)
    {
        static $checked = [];
        $ip = get_client_ip();
        $sessionId = $request->session()->getId();
        $userAgent = $request->header('User-Agent');
        $url = $request->fullUrl();
        $referer = $request->header('referer');
        $limittime = (int) $limittime;
        $limitduration = (int) get_option('limit_duration');
        $key = generateRateLimitKey($ip, $sessionId, $userAgent, $url, $referer);

        if (isset($checked[$key])) {
            return;
        }

        $maxAttempts = $limittime > 0 ? $limittime : 10;
        $decayMinutes = $limitduration > 0 ? $limitduration : 1;

        $attempts = Cache::get($key, 0);
        if ($attempts >= $maxAttempts) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response(minify_all_one_line(tooManyRequestsMsg()), 429)->header('Content-Type', 'text/html')
            );
        }

        Cache::increment($key);
        Cache::put($key, Cache::get($key), now()->addMinutes($decayMinutes));
        $checked[$key] = true;
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
    function underscore(string $val)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '_', trim($val)));
    }
}
if (!function_exists('get_module_info')) {
    function get_module_info(string $val, $post_type = false)
    {
        return $val ? (get_module($post_type ? $post_type : get_post_type())->$val ?? '') : '';
    }
}
if (!function_exists('active_item')) {
    function active_item(string|array $val)
    {
        if (is_array($val)) {
            foreach ($val as $r) {
                if (request()->is(admin_path() . '/' . $r) || request()->is(admin_path() . '/' . $r . '/*') || request()->is(admin_path() . '/' . $r . '/*/*')) {
                    return 'active';
                }
            }
        } else {
            $trimmed = trim($val, '/');
            if (str_contains($trimmed, '/')) {
                if (request()->is(admin_path() . '/' . $trimmed) || request()->is(admin_path() . '/' . $trimmed . '/*')) {
                    return 'active';
                }
            } else {
                $firstSegment = explode('/', $trimmed)[0];
                if (request()->is(admin_path() . '/' . $firstSegment) || request()->is(admin_path() . '/' . $firstSegment . '/*') || request()->is(admin_path() . '/' . $firstSegment . '/*/*')) {
                    return 'active';
                }
            }
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
    function formatFilename(string $filename)
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
if (!function_exists('configIsCached')) {

    function configIsCached()
    {
        return app()->configurationIsCached();
    }
}


if (!function_exists('admin_path')) {
    function admin_path()
    {
        return dec64(config('modules.admin_path'));
        ;
    }
}

if (!function_exists('add_module')) {
    function add_module(array $array)
    {
        $data = config('modules.used');
        if (!empty(collect($data)->where('name', $array['name'])->first())) {
            foreach (collect($data)->where('name', $array['name']) as $key => $row):
                $data[$key] = $array;
            endforeach;
        } else {
            array_push($data, $array);
        }
        config(['modules.used' => $data]);
    }
}
if (!function_exists('no_cache_for_route')) {
    function no_cache_for_route(array $array)
    {
        if (is_array($array)) {
            config(['modules.no_cache_for_route' => $array]);
        }
    }
}
if (!function_exists('add_controller')) {
    function add_controller(array $array)
    {
        if (is_array($array)) {
            config(['modules.custom_controllers' => $array]);
        }
    }
}
if (!function_exists('add_static_menu_profile')) {
    function add_static_menu_profile(array $array)
    {
        if (is_array($array)) {
            config(['modules.static_menu_profile' => $array]);
        }
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
      <div style="cursor:move" class="dd-handle dd3-handle"></div><div class="dd3-content">' . $value->menu_name . ' <i class="fa fa-angle-right" aria-hidden></i>  <code><a href="' . link_menu($value->menu_link) . '"  title="Klik untuk mengunjungi"><i>' . Str::limit(link_menu($value->menu_link), 60, '...') . '</i></a></code><span style="float:right"><a href="javascript:void(0)" onclick="$(\'.link\').val(\'' . $value->menu_link . '\');$(\'.description\').val(\'' . $value->menu_description . '\');$(\'.name\').val(\'' . $value->menu_name . '\');$(\'.iconx\').val(\'' . $value->menu_icon . '\');$(\'#type\').val(\'' . $value->menu_id . '\');$(\'.modal\').modal(\'show\')" class="text-warning"> <i class="fa fa-edit" aria-hidden=""></i> </a> &nbsp; <a href="javascript:void(0)" onclick="del_menu(\'' . $value->menu_id . '\')" class="text-danger"> <i class="fa fa-trash" aria-hidden=""></i> </a></span></div>
      ' . ceksubmenu($me, $value->menu_id) . '
    </li>
    ';
        }
        return $m;
    }
}
if (!function_exists('rnd')) {
    function rnd(int $length)
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

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return Auth::user()->level == 'admin' ? true : false;
    }
}

if (!function_exists('use_module')) {
    if (!function_exists('merge_module_config')) {
        function merge_module_config($base, $override)
        {
            if (!is_array($override)) {
                return $override;
            }

            if (!is_array($base)) {
                return $override;
            }

            $isList = function (array $arr): bool {
                return $arr === [] || array_keys($arr) === range(0, count($arr) - 1);
            };

            $baseIsList = $isList($base);
            $overrideIsList = $isList($override);

            if ($baseIsList && $overrideIsList) {
                $shouldUseRowDirective = false;
                foreach ($override as $row) {
                    if (is_array($row) && isset($row[0]) && is_string($row[0])) {
                        $shouldUseRowDirective = true;
                        break;
                    }
                }

                if (!$shouldUseRowDirective) {
                    return array_merge($base, $override);
                }

                $result = array_values($base);
                $indexByLabel = [];
                foreach ($result as $i => $row) {
                    if (is_array($row) && isset($row[0]) && is_string($row[0])) {
                        $indexByLabel[strtolower(trim($row[0]))] = $i;
                    }
                }

                foreach ($override as $row) {
                    if (is_array($row) && isset($row[0]) && is_string($row[0])) {
                        $labelKey = strtolower(trim($row[0]));

                        if (array_key_exists(1, $row) && $row[1] === false) {
                            if (isset($indexByLabel[$labelKey])) {
                                $removeIndex = $indexByLabel[$labelKey];
                                unset($result[$removeIndex]);
                                $result = array_values($result);
                                $indexByLabel = [];
                                foreach ($result as $i => $r) {
                                    if (is_array($r) && isset($r[0]) && is_string($r[0])) {
                                        $indexByLabel[strtolower(trim($r[0]))] = $i;
                                    }
                                }
                            }
                            continue;
                        }

                        if (isset($indexByLabel[$labelKey])) {
                            $result[$indexByLabel[$labelKey]] = $row;
                            continue;
                        }

                        $result[] = $row;
                        $indexByLabel[$labelKey] = count($result) - 1;
                        continue;
                    }

                    $result[] = $row;
                }

                return array_values($result);
            }

            if (!$baseIsList && !$overrideIsList) {
                if (isset($override['__unset']) && is_array($override['__unset'])) {
                    foreach ($override['__unset'] as $unsetKey) {
                        if (is_string($unsetKey) && array_key_exists($unsetKey, $base)) {
                            unset($base[$unsetKey]);
                        }
                    }
                    unset($override['__unset']);
                }
                foreach ($override as $key => $value) {
                    if (array_key_exists($key, $base)) {
                        $base[$key] = merge_module_config($base[$key], $value);
                    } else {
                        $base[$key] = $value;
                    }
                }
                return $base;
            }

            if ($baseIsList && !$overrideIsList) {
                $result = $base;

                if (isset($override['__remove']) && is_array($override['__remove'])) {
                    $remove = array_values(array_filter($override['__remove'], fn($v) => is_string($v)));
                    if (count($remove)) {
                        $removeIndex = [];
                        foreach ($remove as $label) {
                            $removeIndex[strtolower(trim($label))] = true;
                        }

                        $result = array_values(array_filter($result, function ($row) use ($removeIndex) {
                            if (!is_array($row) || !isset($row[0]) || !is_string($row[0])) {
                                return true;
                            }
                            return !isset($removeIndex[strtolower(trim($row[0]))]);
                        }));
                    }
                }

                if (isset($override['__prepend']) && is_array($override['__prepend'])) {
                    $prepend = $override['__prepend'];
                    if ($isList($prepend)) {
                        $result = array_merge($prepend, $result);
                    }
                }

                if (isset($override['__append']) && is_array($override['__append'])) {
                    $append = $override['__append'];
                    if ($isList($append)) {
                        $result = array_merge($result, $append);
                    }
                }

                return $result;
            }

            return $override;
        }
    }
    function use_module(array $module_selected)
    {
        if (!configIsCached()) {
            foreach ($module_selected as $module => $attr) {
                if (config('modules.menu.' . $module) !== null) {
                    $module_config = config('modules.menu.' . $module);
                    if (is_array($attr)) {
                        $module_config = merge_module_config($module_config, $attr);
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
    function get_module($name = null)
    {
        static $modules = null;
        if ($modules === null) {
            $modules = collect(config('modules.used', []))->where('active', true);
        }

        if ($name) {
            static $singleModules = [];
            if (!isset($singleModules[$name])) {
                $module = $modules->where('name', $name)->first();
                $singleModules[$name] = $module ? json_decode(json_encode($module)) : null;
            }
            return $singleModules[$name];
        }

        return json_decode(json_encode($modules->sort()));
    }
}
if (!function_exists('blnindo')) {
    function blnindo(string $month)
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

if (!function_exists('blade_path')) {
    function blade_path($blade)
    {
        $blades = 'template.' . template() . '.' . $blade;
        if (View::exists($blades)) {
            return $blades;
        } else {
            if (config('app.debug') && Auth::check()) {
                $path = resource_path('views\template\\' . template() . '\\' . $blade . '.blade.php') . ' Not Found<br> ';
                View::share('blade', $path);
                return 'cms::layouts.warning';
            } else {
                abort(503, minify_all_one_line(error503Msg()));
            }
        }
    }
}
function undermaintenance($requestId = null)
{
    $requestBlock = $requestId
        ? "<div class='request-id'>Request ID: {$requestId}</div>"
        : "";

    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Website Under Maintenance</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }
        .card {
            padding:50px 40px;
            border-radius:18px;
            max-width:520px;
            width:90%;
        }
        h1 {
            margin:0 0 15px;
            font-size:30px;
            color:#38bdf8;
        }
        p {
            opacity:0.85;
            margin-bottom:25px;
            line-height:1.6;
        }
        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#facc15;
            word-break: break-all;
            margin-bottom:25px;
        }
        .btn {
            display:inline-block;
            padding:10px 20px;
            background:#38bdf8;
            color:#0f172a;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
        }
        .btn:hover { opacity:0.9; }
        .footer {
            margin-top:30px;
            font-size:12px;
            opacity:0.6;
        }
        .dot-typing {
            display:inline-block;
            width:6px;
            height:6px;
            border-radius:50%;
            background:#38bdf8;
            animation: blink 1.4s infinite both;
        }
        .dot-typing:nth-child(2) { animation-delay: .2s; }
        .dot-typing:nth-child(3) { animation-delay: .4s; }

        @keyframes blink {
            0% { opacity: .2; }
            20% { opacity: 1; }
            100% { opacity: .2; }
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>🚧 Website Under Maintenance</h1>
        <p>
            Our website is currently undergoing improvements.<br>
            Please come back shortly.
        </p>

        <div>
            <span class='dot-typing'></span>
            <span class='dot-typing'></span>
            <span class='dot-typing'></span>
        </div>

        <br><br>

        {$requestBlock}

        <a href='/' class='btn'>Refresh Page</a>

        <div class='footer'>
            Thank you for your patience.
        </div>
    </div>
</body>
</html>";
}
function error503Msg($requestId = null)
{
    $requestBlock = $requestId
        ? "<div class='request-id'>Request ID: {$requestId}</div>"
        : "";

    exit(preg_replace("/\s+/", " ", "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>503 - Service Unavailable</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }
        .card {
            background:#1e293b;
            padding:40px;
            border-radius:16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            max-width:500px;
            width:90%;
        }
        h1 {
            margin:0 0 10px;
            font-size:28px;
            color:#facc15;
        }
        p {
            opacity:0.8;
            margin-bottom:20px;
        }
        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#38bdf8;
            word-break: break-all;
            margin-bottom:20px;
        }
        .btn {
            display:inline-block;
            padding:10px 18px;
            background:#38bdf8;
            color:#0f172a;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
        }
        .btn:hover { opacity:0.9; }
        .footer {
            margin-top:25px;
            font-size:12px;
            opacity:0.6;
        }
        .spinner {
            margin:20px auto;
            width:40px;
            height:40px;
            border:4px solid rgba(255,255,255,0.2);
            border-top:4px solid #facc15;
            border-radius:50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>🛠 Under Maintenance</h1>
        <p>This page is currently being improved. Please check back later.</p>
        {$requestBlock}
        <a href='/' class='btn'>Back to Homepage</a>
        <div class='footer'>
            We apologize for the inconvenience.
        </div>
    </div>
</body>
</html>"));
}
if (!function_exists('custom_field_filter')) {

    /**
     * Filter collection berdasarkan key pada index [1]
     */
    function custom_field_filter($data, $key, $value, $exclude = false)
    {
        return collect($data)->filter(function ($item) use ($key, $value, $exclude) {

            $match = isset($item[1]->$key) &&
                $item[1]->$key == $value;

            return $exclude ? !$match : $match;
        });
    }
}
if (!function_exists('custom_field_without_break')) {
    function custom_field_without_break($data)
    {
        return collect($data)
            ->reject(fn($item) => ($item[1]->type ?? null) === 'break');
    }
}
function error403Msg($requestId = null)
{
    $requestBlock = $requestId
        ? "<div class='request-id'>Request ID: {$requestId}</div>"
        : "";

    return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>403 - Forbidden</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }
        .card {
            padding:40px;
            border-radius:16px;
            max-width:500px;
            width:90%;
        }
        h1 {
            margin:0 0 10px;
            font-size:28px;
            color:#f97316;
        }
        p {
            opacity:0.85;
            margin-bottom:20px;
        }
        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#38bdf8;
            word-break: break-all;
            margin-bottom:20px;
        }
        .btn {
            display:inline-block;
            padding:10px 18px;
            background:#38bdf8;
            color:#0f172a;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
        }
        .btn:hover { opacity:0.9; }
        .footer {
            margin-top:25px;
            font-size:12px;
            opacity:0.6;
        }
        .icon {
            font-size:48px;
            margin-bottom:15px;
        }
        .warning {
            background:#7c2d12;
            padding:10px;
            border-radius:8px;
            font-size:13px;
            color:#fed7aa;
            margin-bottom:15px;
        }
    </style>
</head>
<body>
    <div class='card'>
        <div class='icon'>⛔</div>
        <h1>403 - Access Forbidden</h1>
        <p>You do not have permission to access this resource.</p>

        <div class='warning'>
            Unauthorized or illegal access attempt may be logged and monitored.
        </div>

        {$requestBlock}

        <a href='/' class='btn'>Back to Homepage</a>

        <div class='footer'>
            If you believe this is an error, please contact the administrator.
        </div>
    </div>
</body>
</html>";
}
if (!function_exists('template')) {
    function template()
    {
        return config('modules.template') ?? get_option('template');
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

if (!function_exists('minify_all_one_line')) {
    function minify_all_one_line($html)
    {
        $html = preg_replace_callback(
            '/<script\b[^>]*>.*?<\/script>/is',
            function ($matches) {
                $script = $matches[0];
                $script = preg_replace('/(^|\s)\/\/(?!\/)(.*)/m', '/*$2*/', $script);
                return $script;
            },
            $html
        );
        $html = preg_replace('/\s+/', ' ', $html);
        return trim($html);
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
    function not_allow_adminpath()
    {
        return array_merge(['slot', 'gacor', 'maxwin', 'bokep', 'xxx', 'panel', 'judi', 'admin', 'login', 'adminpanel', 'webadmin', 'masuk', 'sipanel', admin_path()], collect(get_module())->pluck('name')->toArray());
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
        static $thumb = null;
        if ($thumb !== null) {
            return $thumb;
        }
        $key = md5(request()->fullUrl());
        return $thumb = Cache::has($key) ? url(Cache::get($key)) : url(noimage());
    }
}

if (!function_exists('set_header_seo')) {
    function set_header_seo($data)
    {
        $current_module = get_module($data->type);
        $desctitle = !Str::contains($current_module->title, Str::of($data->title)->explode(' ')[0]) ? $current_module->title . ' ' . $data->title : $data->title;
        return array(
            'description' => !empty($data->description) ? $data->description : (strlen($data->short_content) == 0 ? 'Lihat ' . $desctitle : $data->short_content),
            'keywords' => !empty($data->keyword) ? $data->keyword : $data->site_keyword,
            'title' => $data->title,
            'thumbnail' => (function () use ($data, $current_module) {
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $data->content);
                $images = $dom->getElementsByTagName('img');

                $validImageSrc = null;

                // Cari gambar pertama yang BUKAN base64
                foreach ($images as $img) {
                    $src = $img->getAttribute('src');
                    if (strpos($src, 'data:image') !== 0) {
                        $validImageSrc = $src;
                        break;
                    }
                }

                $hasThumbnailField = $current_module->form->thumbnail;

                if ($hasThumbnailField) {
                    if ($data->media && media_exists($data->media)) {
                        return url($data->thumbnail);
                    }

                    if ($validImageSrc) {
                        return $validImageSrc;
                    }

                    $preview = get_option('preview');
                    return url($preview && media_exists($preview) ? $preview : noimage());
                } else {
                    if ($validImageSrc) {
                        return $validImageSrc;
                    }

                    $preview = get_option('preview');
                    return url($preview && media_exists($preview) ? $preview : noimage());
                }
            })(),

            'url' => (!empty($data->url)) ? url($data->url) : url('/'),
        );
    }
}
if (!function_exists('enc64')) {
    function enc64($val)
    {
        return base64_encode(base64_encode($val));
    }
}
if (!function_exists('dec64')) {
    function dec64($val)
    {
        return base64_decode(base64_decode($val));
    }
}
if (!function_exists('cms_update_checker')) {
    function cms_update_checker()
    {
        $current = current_cms_version();

        $latest = latest_version(); // misalnya versi terbaru dari repo / config

        if ($current && version_compare($current, $latest, '<')) {
            $status = "Perlu update (current: $current, latest: $latest)";
        } elseif ($current && version_compare($current, $latest, '=')) {
            $status = "Sudah versi terbaru ($current)";
        } elseif ($current && version_compare($current, $latest, '>')) {
            $status = "Versi lokal lebih tinggi ($current > $latest)";
        } else {
            $status = "File version tidak ditemukan";
        }

        return $status;
    }
}
if (!function_exists('template_info')) {
    function template_info()
    {
        $path = resource_path('views/template/' . template() . '/theme.json');
        $unknown = 'tidak diketahui';
        $info = [
            'name' => $unknown,
            'author' => $unknown,
            'url' => '#',
            'version' => $unknown,
        ];

        if (file_exists($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $info = array_merge($info, $decoded);
            }
        }

        foreach (['name', 'author', 'version'] as $k) {
            $v = $info[$k] ?? null;
            if (!is_string($v) || trim($v) === '') {
                $info[$k] = $unknown;
            }
        }

        $url = $info['url'] ?? '#';
        if (!is_string($url) || trim($url) === '' || $url === $unknown) {
            $info['url'] = '#';
        }

        return view()->make('cms::layouts.themeinfo', compact('info'));
    }
}

if (!function_exists('get_domain_extension')) {
    function get_domain_extension($extension)
    {
        return collect(config('modules.extension_module'))->where('path', $extension)->first()['url'] ?? null;
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
if (!function_exists('init_goup')) {
    function init_goup()
    {

        return view()->make('cms::layouts.goup');

    }
}
if (!function_exists('init_popup')) {
    function init_popup()
    {
        $banners = get_banner('popup', 5);
        if ($banners) {
            return view()->make('cms::layouts.popup', compact('banners'));
        }
        return null;
    }
}
if (!function_exists('short_content')) {
    function short_content($content, $limit = 20)
    {
        // 1. Hapus semua tag HTML
        $text = strip_tags($content);

        // 2. Decode HTML entities (&nbsp;, &amp;, dll.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 3. Bersihkan spasi ganda
        $text = preg_replace('/\s+/', ' ', trim($text));

        // 4. Potong jadi array kata
        $words = explode(' ', $text);

        // 5. Ambil sesuai limit
        if (count($words) > $limit) {
            $words = array_slice($words, 0, $limit);
            return implode(' ', $words) . '...';
        }

        return implode(' ', $words);
    }
}
if (!function_exists('init_wabutton')) {
    function init_wabutton()
    {
        return view()->make('cms::layouts.floatwa')->render();
    }
}
if (!function_exists('page_name')) {
    function page_name($name)
    {
        config(['modules.page_name' => $name]);
    }
}
if (!function_exists('init_meta_header')) {
    function init_meta_header()
    {
        $get_page_name = config('modules.page_name');
        $data = config('modules.data') ?? false;
        $site_title = get_option('site_title') && strlen(get_option('site_title')) > 0 ? get_option('site_title') : 'You Website Title';
        $site_desc = get_option('site_description');
        $site_meta_keyword = get_option('site_meta_keyword') ?? 'Stus Resmi, Cara Buat, Buat Website';
        $site_meta_description = get_option('site_meta_description');
        if ($data) {
            $data['site_keyword'] = $site_meta_keyword;
            return view()->make('cms::layouts.seo', set_header_seo($data));
        } else {
            $page = request()->page ? ' Halaman ' . request()->page : '';

            if (get_post_type() && request()->segment(1) != 'search' && !request()->is('/')) {

                if (request()->segment(2) == 'archive' || request()->segment(2) == 'category' || request()->segment(1) == 'tags' || (get_module(get_post_type())?->form?->post_parent)) {
                    $pn = $get_page_name . $page;
                } else {
                    $pn = $get_page_name . $page;
                }
            } elseif (request()->is('search/*')) {
                $pn = 'Hasil Pencarian  "' . ucwords(str_replace('-', ' ', request()->slug)) . '"' . $page;
            } elseif (request()->is('author') || request()->is('author/*')) {
                $pn = $get_page_name . $page;
            } else {
                $pn = null;
            }
            $data = [
                'description' => $pn ? 'Lihat ' . $pn . ' di ' . $site_title : (!request()->is('/') ? 'Halaman tidak ditemukan' : ($site_meta_description ?? $site_desc)),
                'title' => $pn ? $pn : (!request()->is('/') ? 'Halaman tidak ditemukan' : $site_title . ($site_desc ? ' - ' . $site_desc : '')),
                'keywords' => $site_meta_keyword,
                'thumbnail' => url(get_option('preview') && media_exists(get_option('preview')) ? get_option('preview') : noimage()),
                'url' => request()->fullUrl(),
            ];
            return View::make('cms::layouts.seo', $data ?? [null])->render();
        }
    }
}
if (!function_exists('clean_url')) {
    function clean_url(string $url, string $action = 'http'): string
    {
        // Normalisasi spasi & trim
        $url = trim($url);

        // Regex cek prefix http atau https
        $hasHttp = preg_match('#^https?://#i', $url);

        if ($action === 'http') {
            // Tambah https:// kalau belum ada http:// atau https://
            if (!$hasHttp) {
                $url = 'https://' . $url;
            }
        } elseif ($action === 'nohttp') {
            // Hapus http:// atau https:// kalau ada
            if ($hasHttp) {
                $url = preg_replace('#^https?://#i', '', $url);
            }
        }

        return $url;
    }
}
if (!function_exists('get_menu')) {
    function get_menu($name)
    {
        static $menus = [];
        static $recached = [];
        $cacheKey = get_current_host() . ':menu';
        // Hanya ambil dari cache jika belum ada di static
        if (!isset($menus[$cacheKey])) {
            // Cek apakah cache ada dan ambil sekaligus
            $cachedData = Cache::get($cacheKey);
            if ($cachedData !== null) {
                $menus[$cacheKey] = $cachedData;
                $needsRecache = false;
            } else {
                $needsRecache = true;
            }
        } else {
            $needsRecache = false;
        }

        // Cek apakah menu dengan nama $name ada
        if (
            !$needsRecache &&
            !array_key_exists($name, $menus[$cacheKey] ?? [])
        ) {
            $menuQuery = \Leazycms\Web\Models\Post::query()
                ->whereType('menu')
                ->whereStatus('publish')
                ->where('slug', $name);
            $needsRecache = $menuQuery->exists();
        }

        if ($needsRecache && empty($recached[$cacheKey])) {
            recache_menu();
            unset($menus[$cacheKey]);
            $recached[$cacheKey] = true;
            // Ambil lagi dari cache setelah recache
            $menus[$cacheKey] = Cache::get($cacheKey, []);
        }

        // Pastikan static $menus sudah diisi
        if (!isset($menus[$cacheKey])) {
            $menus[$cacheKey] = Cache::get($cacheKey, []);
        }

        $menu = $menus[$cacheKey][$name] ?? [];
        $menuIndex = [];
        foreach ($menu as $item) {
            $menuIndex[$item['menu_id']] = [
                'id' => (int) $item['menu_id'],
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
            } elseif (isset($menuIndex[$item['parent']])) {
                $menuIndex[$item['parent']]['sub'][] = &$item;
            }
        }
        return collect(json_decode(json_encode($menuTree)));
    }
}
if (!function_exists('api_key')) {
    function api_key()
    {
        return config('modules.env_key') ? md5(enc64(config('modules.env_key'))) : null;
    }
}
if (!function_exists('load_default_module')) {

    function load_default_module()
    {
        $default = [
            'berita' => ['active' => true],
            'menu' => ['active' => true],
            'banner' => ['active' => true],
            'page' => ['active' => true],
        ];
        if (config('modules.app_master')) {
            $default['sites'] = ['active' => true];
        }
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
        return str_replace('-', '_', str($val)->slug());
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
            ->select('media', 'redirect_to', 'title', 'category_id', 'data_field')->get();
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
        $cacheKey = get_current_host() . ':banner';
        cache()->forget($cacheKey);
        cache()->rememberForever($cacheKey, function () use ($result) {
            return $result;
        });
    }
}
if (!function_exists('recache_menu')) {
    function recache_menu()
    {
        $cacheKey = get_current_host() . ':menu';
        cache()->forget($cacheKey);
        cache()->rememberForever($cacheKey, function () {
            $menu = \Leazycms\Web\Models\Post::whereType('menu')->whereStatus('publish')->select('slug', 'data_loop')->pluck('data_loop', 'slug')->toArray();
            return $menu;
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
        return Auth::user()?->level == 'admin' ? View::make('cms::layouts.banner', ['banner' => $name, 'data' => $data]) : null;
    }
}

if (!function_exists('get_banner')) {
    function get_banner($name, $limit = 1)
    {
        static $requestCache = [];
        static $recached = [];
        $cacheKey = get_current_host() . ':banner';

        // Hanya ambil dari cache jika belum ada di static
        if (!isset($requestCache[$cacheKey])) {
            // Cek dan ambil dari cache sekaligus
            $cachedData = cache()->get($cacheKey);
            if ($cachedData !== null) {
                $requestCache[$cacheKey] = $cachedData;
                $needsRecache = false;
            } else {
                $needsRecache = true;
            }
        } else {
            $needsRecache = false;
        }

        // Cek apakah banner dengan nama $name ada
        if (
            !$needsRecache &&
            !array_key_exists($name, $requestCache[$cacheKey] ?? [])
        ) {
            $bannerQuery = \Leazycms\Web\Models\Post::query()
                ->whereType('banner')
                ->whereStatus('publish')
                ->whereHas('category', function ($query) use ($name) {
                    $query->where('slug', $name);
                });

            $needsRecache = $bannerQuery->exists();
        }

        if ($needsRecache && empty($recached[$cacheKey])) {
            recache_banner();
            unset($requestCache[$cacheKey]);
            $recached[$cacheKey] = true;
            // Ambil lagi dari cache setelah recache
            $requestCache[$cacheKey] = cache()->get($cacheKey) ?? [];
        }

        // Pastikan static $requestCache sudah diisi
        if (!isset($requestCache[$cacheKey])) {
            $requestCache[$cacheKey] = cache()->get($cacheKey) ?? [];
        }

        $banners = $requestCache[$cacheKey][$name] ?? null;

        if (!$banners) {
            return $limit > 1 ? [] : null;
        }

        $result = collect($banners)->map(fn($r) => (object) [
            'image' => $r['image'] ?? $r->image ?? noimage(),
            'link' => $r['link'] ?? $r->link ?? null,
            'name' => $r['name'] ?? $r->name ?? null,
            'description' => $r['description'] ?? $r->description ?? null,
        ]);

        return $limit > 1 ? $result->take($limit)->all() : $result->first();
    }
}
if (!function_exists('banner_here')) {
    function banner_here($name)
    {
    }
}
if (!function_exists('get_client_ip')) {

    function get_client_ip()
    {
        return request()->header('CF-Connecting-IP') ?? request()->getClientIp();
    }
}

if (!function_exists('get_ip_info')) {
    function get_ip_info($ip)
    {
        if (!is_local()) {
            $data = \Stevebauman\Location\Facades\Location::get($ip);
            return $data ? ['countryCode' => str($data->countryCode)->lower(), 'country' => $data->countryName, 'city' => $data->cityName, 'region' => $data->regionName] : [];
        } else {
            return [];
        }
    }
}
if (!function_exists('renderTemplateFile')) {
    function renderTemplateFile($items, $parentPath = '')
    {
        echo '<ul style="list-style:none;padding:0 0 0 14px">';
        foreach ($items as $item) {

            $currentPath = $parentPath . '/' . $item['name'];
            if (str($currentPath)->contains(['.git', 'assets', 'dummy'])) {
                continue;
            }
            if (isset($item['children']) && !empty($item['children'])) {
                echo '<li class="folder"> <i class="fa fa-folder"></i> <span class="pull-right text-danger"><i class="fa fa-file-circle-plus   pointer" title="Create File" onclick="filePrompt(\'' . $currentPath . '\')"></i> </span>' . htmlspecialchars($item['name']);
                renderTemplateFile($item['children'], $currentPath);
                echo '</li>';
            } elseif (strtolower(substr(strrchr($item['name'], '.'), 1))) {
                if (str($item['name'])->contains('json')) {
                    continue;
                }
                if (!is_main_domain() && str($currentPath)->contains('modules')) {
                    continue;
                }
                echo '<li><a href="' . route('appearance.editor') . '?edit=' . enc64(htmlspecialchars($currentPath)) . '"><i class="fab fa-laravel text-danger"></i>  ' . htmlspecialchars($item['name']) . '</a></li>';
            } else {

                echo '<li><i class="fa fa-folder"></i> ' . htmlspecialchars($item['name']) . ' <span class="pull-right text-danger"><i class="fa fa-file-circle-plus  pointer" onclick="filePrompt(\'' . $currentPath . '\')" title="Create File"></i> </span></li>';
            }
        }
        echo '</ul>';
    }
}

if (!function_exists('browser')) {

    function browser()
    {
        $userAgent = request()->header('User-Agent');

        if (strpos($userAgent, 'MSIE') !== false) {
            $browser = 'Internet Explorer';
        } elseif (strpos($userAgent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Mozilla Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Google Chrome';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Apple Safari';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        } else {
            $browser = 'Unknown';
        }

        return $browser;
    }
}
if (!function_exists('os')) {
    function os()
    {
        $userAgent = request()->header('User-Agent');

        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            $os = 'Mac OS';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $os = 'iOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } else {
            $os = 'Unknown OS';
        }
        return $os;
    }
}
if (!function_exists('device')) {
    function device()
    {
        $userAgent = request()->header('User-Agent');

        if (strpos($userAgent, 'Mobi') !== false) {
            $deviceType = 'Mobile';
        } else {
            $deviceType = 'Desktop';
        }

        return $deviceType;
    }
}
if (!function_exists('is_local')) {
    function is_local()
    {
        return request()->ip() == '127.0.0.1' || request()->ip() == '::1' || config('app.env') == 'local' ? true : false;
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
            1 => 'Jan',
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
            $type == 'monthyear' => $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[2],
            $type == 'month' => $bulan[(int) $pecahkan[1]],
            $type == 'date' => $pecahkan[0],
            $type == 'datemonth' => $pecahkan[0] . ' ' . $bulan[(int) $pecahkan[1]],
            default => null
        };
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
        return view()->make('cms::share.button', ['url' => request()->fullUrl()]);
    }
}



if (!function_exists('webnotfound')) {
    function webnotfound()
    {
        $html = '<!doctype html>
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
    </html>';
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response($html, 404)->header('Content-Type', 'text/html')
        );
    }
}
if (!function_exists('getRateLimiterKey')) {
    function getRateLimiterKey($req)
    {
        // Modify this method to create a unique key based on IP and session ID
        return md5(get_client_ip() . '|' . $req->userAgent() . '|' . request()->fullUrl() . '|' . $req->header('referer'));
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
