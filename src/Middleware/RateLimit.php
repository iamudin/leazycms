<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RateLimit
{


    public function handle(Request $request, Closure $next)
    {
        if (config('modules.installed')=="0") {
            exit('Please running cms:install');
        }
        if(config('app.sub_app_enabled') && collect(config('modules.extension_module'))->count()){
            foreach(collect(config('modules.extension_module'))->pluck('path')->toArray() as $module){
                if($request->getHost()==parse_url(config('app.url'), PHP_URL_HOST)){
                    config([$module.'.route'=>'panel.'.$module.'.']);
                    config([$module.'.path_url'=>$module]);
                }
            }
        }
       

        $uri = $request->getRequestUri();
        $host = $request->getHost();
        $appUrl = config('app.url');
        $appUrlHost = parse_url($appUrl, PHP_URL_HOST);
        $isLocal = in_array($request->ip(), ['127.0.0.1', '::1']);
        $redirectUrl = null;
        // Deteksi apakah pakai Cloudflare
        $cfVisitor = $request->server('HTTP_CF_VISITOR');
        $isHttpsViaCf = $cfVisitor ? (json_decode($cfVisitor, true)['scheme'] ?? 'http') === 'https' : false;

        // Deteksi HTTPS native
        $isHttpsNative = $request->server('HTTPS') === 'on' || $request->server('SERVER_PORT') == 443;

        $isHttps = $isHttpsViaCf || $isHttpsNative;
        $scheme = $isHttps ? 'https' : 'http';

        // 1. Redirect jika ada "index.php/" di URI
        if (strpos($uri, 'index.php/') !== false) {
            $cleanUri = str_replace('index.php/', '', $uri);
            $redirectUrl = $scheme . '://' . $host . '/' . ltrim($cleanUri, '/');
        }

        // 2. Redirect ke HTTPS jika bukan lokal dan belum HTTPS
        elseif (!$isLocal && !$isHttps && app()->environment('production')) {
            $redirectUrl = 'https://' . $host . $uri;
        }
        // 3. Validasi domain jika sub_app_enabled diaktifkan
        elseif (config('app.sub_app_enabled')) {
            $allowedHosts = collect(config('modules.extension_module'))->pluck('url')->map(function ($url) {
                return parse_url($url, PHP_URL_HOST);
            })->toArray();

            if (!in_array($host, $allowedHosts, true)) {
                $redirectUrl = $scheme . '://' . $appUrlHost . $uri;
            }
        }
        elseif ($host !== $appUrlHost) {
            $redirectUrl = $scheme . '://' . $appUrlHost . $uri;
        }

        if ($redirectUrl && urldecode($scheme . '://' . $appUrlHost . preg_replace('#/+#', '/', $request->getRequestUri())) !== urldecode($scheme . '://' . $appUrlHost . $uri)) {
          return redirect(preg_replace('#/+#', '/', $redirectUrl), 301);
        }

    if(!is_main_domain()){
             if (!$isLocal && !$isHttps && app()->environment('production')) {
            return redirect('https://' . $host . $uri);
        }
    }
    if($request->segment(1)=='log-viewer'){
        abort_if($request->header('referer') != route('panel.logs'),404);
    }
        $modules = collect(get_module())->where('name', '!=', 'page')->where('public', true);
        foreach ($modules as $modul) {
            $attr['post_type'] = $modul->name;

            if ($modul->web->index && $request->is($modul->name)) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'index';
                $attr['view_path'] = $modul->name . '.index';
                config([
                    'modules.current' => $attr
                ]);
            }
            if ($modul->web->detail && $request->is($modul->name . '/*') && empty($request->segment(3))) {
                $attr['detail_visited'] = true;
                $attr['view_type'] = 'detail';
                $attr['view_path'] = $modul->name . '.detail';
                config([
                    'modules.current' => $attr
                ]);
            }
            if ($modul->form->category && $request->is($modul->name . '/category/*')) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'category';
                $attr['view_path'] = $modul->name . '.category';
                config([
                    'modules.current' => $attr
                ]);
            }

            if ($modul->web->archive && ($request->is($modul->name . '/archive') || $request->is($modul->name . '/archive/*') || $request->is($modul->name . '/archive/*/*') || $request->is($modul->name . '/archive/*/*/*'))) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'archive';
                $attr['view_path'] = $modul->name . '.archive';
                config([
                    'modules.current' => $attr
                ]);
            }
            if ($modul->form->post_parent && ($request->is($modul->name . '/' . $modul->form->post_parent[1]) || $request->is($modul->name . '/' . $modul->form->post_parent[1] . '/*'))) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'post-parent';
                $attr['view_path'] = $modul->name . '.post-parent';
                config([
                    'modules.current' => $attr
                ]);
            }
        }


        if ($request->is('*') && !in_array($request->segment(1), array_merge([admin_path()], $modules->pluck('name')->toArray()))) {
            $attr['post_type'] = 'page';
            $attr['detail_visited'] = true;
            $attr['view_type'] = 'detail';
            $attr['view_path'] = 'page.detail';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('search') || $request->is('search/*')) {
            $attr['post_type'] = 'search';
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'search';
            $attr['view_path'] = 'search';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('author') || $request->is('author/*')) {
            if ($request->is('author')) {
                $attr['post_type'] = 'author';
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'author.index';
                $attr['view_path'] = 'author.index';
                config([
                    'modules.current' => $attr
                ]);
            } else {
                $attr['post_type'] = 'author';
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'author.detail';
                $attr['view_path'] = 'author.detail';
                config([
                    'modules.current' => $attr
                ]);
            }
        }
        if ($request->is('tags/*')) {
            $attr['post_type'] = 'tags';
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'tags';
            $attr['view_path'] = 'tags.index';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is(['sitemap.xml', 'swk.js', 'site.manifest'])) {
            $attr['detail_visited'] = false;
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('/')) {
            $attr['post_type'] = 'home';
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'home';
            $attr['view_path'] = 'home';
            config([
                'modules.current' => $attr
            ]);
        }
        $this->logging_request($request);
        $this->dangerous_request($request);
        if ($o = config('modules.current.detail_visited') && !in_array($request->segment(1),['secure','media'])) {
            ratelimiter($request, get_option('time_limit_reload'));
        }
        forbidden($request, config('modules.current.detail_visited'));
        $response =  $next($request);
        if (str($response->headers->get('Content-Type'))->lower() == 'text/html; charset=utf-8') {

            $content = $response->getContent();
            
            $response->setContent($content);
        }
        return $response;
    }
    function dangerous_request($request){
        foreach ($request->allFiles() as $file) {
            // Handle multiple file input (array of files)
            if (is_array($file)) {
                foreach ($file as $subfile) {
                    if (!$this->isFileSafe($subfile)) {
                        abort('403', 'Malicious file detected.');
                    }
                }
            } else {
                if (!$this->isFileSafe($file)) {
             
                   abort('403', 'Malicious file detected.');
                }
            }
        }
        $dangerousFunctions = [
            'eval',
            ' system',
            ' exec',
            'passthru',
            'shell_exec',
            'proc_open',
            'popen',
            'assert',
            'base64_decode',
            'file_put_contents',
            'fopen',
            'curl_exec',
            'create_function',
            'file_get_contents',
            'unlink',
            'mkdir',
            'curl_exec',
            'create_function'
        ];
       
        // Dapatkan semua konten dari request
        $content = implode(",", $request->all());
   
        foreach ($dangerousFunctions as $function) {
            if (stripos($content, $function) !== false) {
                Log::channel('daily')->critical('Potentially dangerous code detected in request.', [
                    'info' => 'Dangerous function detected: ' . $function,
                    'ip' => get_client_ip(),
                    'url' => $request->fullUrl(),
                    'payload' => $request->except(['_token', 'password', 'password_confirmation']),
                ]);
                abort('403', 'Request contains potentially dangerous code');
            }
        }
    }
    function logging_request(Request $request): void
    {
        // 🚫 Skip Datatable POST
        if (
            $request->isMethod('POST') &&
            $request->has(['draw', 'columns'])
        ) {
            return;
        }

        $method = $request->method();

        // Mapping action + log level
        $map = [
            'POST' => ['CREATE ACTION', 'info'],
            'PUT' => ['UPDATE ACTION', 'warning'],
            'PATCH' => ['UPDATE ACTION', 'warning'],
            'DELETE' => ['DELETE ACTION', 'warning'], // danger
        ];

        if (!isset($map[$method])) {
            return;
        }

        [$message, $level] = $map[$method];

        $user = Auth::user();

        // Payload dibatasi & disaring
        $payload = $request->except([
            '_token',
            'password',
            'password_confirmation',
        ]);

        Log::channel('daily')->{$level}($message, [
            'method' => $method,
            'url' => $request->path(), // lebih ringan dari fullUrl
            'ip' => get_client_ip(),

            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ] : null,

            'payload' => $payload,
        ]);
    }
    protected function isFileSafe($file): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        $path = $file->getRealPath();

        // Skip scanning untuk file video/audio (mp4/mp3)
        $mime = $file->getMimeType();
        $skipScan = [
            'video/mp4',
            'audio/mpeg',
            'audio/mp3',
        ];

        if (in_array($mime, $skipScan)) {
            return true; // Aman, tidak perlu scan konten
        }

        // Skip file besar (lebih dari 5MB)
        if (filesize($path) > 5 * 1024 * 1024) {
            return true;
        }

        $content = file_get_contents($path);

        // Deteksi tag PHP
        if (preg_match('/<\?(php|=)/i', $content)) {
            Log::channel('daily')->critical('Malicious file detected in upload ' . $file->getClientOriginalName(), [
                'info' => 'PHP tag detected',
                'ip' => get_client_ip(),
                'url' => request()->fullUrl(),
            ]);
            return false;
        }

        // Cek fungsi berbahaya hanya untuk file teks
        $danger = [
            'eval',
            ' exec',
            ' system',
            'passthru',
            'shell_exec',
            'proc_open',
            'popen',
            'assert',
            'base64_decode',
            'file_put_contents',
            'fopen',
            'unlink',
            'mkdir',
            'curl_exec',
            'create_function',
            'file_get_contents',
        ];

        foreach ($danger as $func) {
            if (stripos($content, $func) !== false) {
                Log::channel('daily')->critical('Malicious file detected in upload ' . $file->getClientOriginalName(), [
                    'info' => 'Dangerous function detected: ' . $func,
                    'ip' => get_client_ip(),
                    'url' => request()->fullUrl(),
                ]);
                return false;
            }
        }

        return true;
    }
}
