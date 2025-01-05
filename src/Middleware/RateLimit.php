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
        if (!config('modules.installed') && strpos($request->fullUrl(), 'install') === false) {
            if (config('session.driver')!= 'file' || config('queue.default') != 'sync' || config('cache.default') != 'file') {
                $cfg['SESSION_DRIVER'] = 'file';
                $cfg['QUEUE_CONNECTION'] = 'sync';
                $cfg['CACHE_STORE'] = 'file';
                $cfg['APP_URL'] = 'http://' . $request->getHttpHost();
                rewrite_env($cfg);
            }
            return redirect()->away($request->getHttpHost() . '/install', 301);
        }
        $host = $request->getHost();
        $scheme = $request->getScheme();
        $uri = $request->getRequestUri();

        // Initialize variables
        $redirectUrl = null;

        // Remove "www." from domain
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        // Remove "index.php" from URI
        if (strpos($uri, 'index.php') !== false) {
            $uri = str_replace('index.php', '', $uri);
        }

        // Force HTTPS if not secure
        if ($scheme !== 'https' && app()->environment('production')) {
            $scheme = 'https';
        }

        // Build the redirect URL if needed
        if ($host !== $request->getHost() || $scheme !== $request->getScheme() || $uri !== $request->getRequestUri()) {
            $redirectUrl = $scheme . '://' . $host . '/' . ltrim($uri, '/');
        }

        // Redirect if necessary
        if ($redirectUrl) {
            return redirect($redirectUrl);
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
            $attr['post_type'] = null;
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'search';
            $attr['view_path'] = 'search';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('author') || $request->is('author/*')) {
            if ($request->is('author')) {
                $attr['post_type'] = null;
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'author.index';
                $attr['view_path'] = 'author.index';
                config([
                    'modules.current' => $attr
                ]);
            } else {
                $attr['post_type'] = null;
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'author.detail';
                $attr['view_path'] = 'author.detail';
                config([
                    'modules.current' => $attr
                ]);
            }
        }
        if ($request->is('tags/*')) {
            $attr['post_type'] = null;
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
            $attr['post_type'] = null;
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'home';
            $attr['view_path'] = 'home';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($o = config('modules.current.detail_visited')) {
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
                $content = preg_replace('/\s+/', ' ', $content);
            }
            $response->setContent($content);
        }
        return $response;
    }
}
