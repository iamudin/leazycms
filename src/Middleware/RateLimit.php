<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;

class RateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('modules.installed')=="0") {
            exit('Please running cms:install');
        }
        if(collect(config('modules.extension_module'))->count()){
            foreach(collect(config('modules.extension_module'))->pluck('path')->toArray() as $module){
                if($request->getHost()==parse_url(config('app.url'), PHP_URL_HOST)){
                    config([$module.'.route'=>'panel.'.$module.'.']);
                    config([$module.'.path_url'=>$module]);
                }
            }
        }
        $current_host = $request->getHost();
        $origin_host = config('app.sub_app_enabled') ? $current_host : parse_url(config('app.url'), PHP_URL_HOST);
        $uri = $request->getRequestUri();
        $current_scheme = $request->getScheme();
        $is_local = in_array($request->ip(), ['127.0.0.1', '::1']);
        $expected_scheme = $is_local ? 'http' : 'https';

        $redirectUrl = null;

        // 1. Redirect jika ada "index.php" di URI
        if (strpos($uri, 'index.php/') !== false) {
            $cleanUri = str_replace('index.php/', '', $uri);
            $redirectUrl = $request->getSchemeAndHttpHost() . '/' . ltrim($cleanUri, '/');
        }

        // 2. Redirect jika scheme atau host tidak sesuai
        elseif (!$is_local && ($current_scheme !== $expected_scheme || $current_host !== $origin_host)) {
            $redirectUrl = $expected_scheme . '://' . $origin_host . $uri;
        }

        // 3. Redirect jika perlu dan tidak menyebabkan loop
        if ($redirectUrl && $redirectUrl !== $request->fullUrl()) {
            return redirect($redirectUrl, 301); // gunakan 301 (permanent redirect)
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
                $attr['view_type'] = 'post_parent';
                $attr['view_path'] = $modul->name . '.post_parent';
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
        if ($o = config('modules.current.detail_visited') && !in_array($request->segment(1),['secure','media'])) {
            ratelimiter($request, get_option('time_limit_reload'));
        }
        forbidden($request, config('modules.current.detail_visited'));
        $response =  $next($request);
        if ($response->headers->get('Content-Type') == 'text/html; charset=UTF-8') {
            $content = $response->getContent();
            if ($request->segment(1) != admin_path() && strpos($content, '</body>') !== false  && strpos($content, 'spinner-spin') === false) {
                $content = str_replace(
                    '</body>',
                    preload() . '</body>',
                    $content
                );
            }
            if ($request->segment(1) != admin_path()){
                $footer = '
                <div style="
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    padding: 10px;
                    background-color: #f1f1f1;
                    font-size: 14px;
                    color: #333;
                    padding:3px;background:#111;text-align:center;font-size:8px;border-radius:0 5px 0 0;color:#bbb">Build with <a href="https://leazycms.com" style="color:#fff;text-decoration:none">LeazyCMS</a></div>

             ';
                $content = preg_replace('/<\/body>/', $footer. '</body>',$content);

            }
            if ($request->segment(1) == 'docs') {
                $content = isPre($content);
            } elseif($request->segment(1)!= admin_path()) {
                $content = preg_replace('/\s+/', ' ', $content);
            }
            $response->setContent($content);
        }
        return $response;
    }
}
