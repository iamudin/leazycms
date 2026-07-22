<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class Web
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
        $host = $request->getHost();
        $isPluginDomain = is_plugin_domain($host);

        // Intercept rute '/' untuk custom domain plugin sebelum masuk ke WebController CMS utama
        if ($isPluginDomain && $request->path() === '/') {
            $routes = config('modules.custom_web_route', []);
            foreach ($routes as $r) {
                if (isset($r['path']) && ltrim($r['path'], '/') === '') {
                    if (isset($r['domain']) && $r['domain'] !== $host) {
                        continue;
                    }

                    $controller = app($r['controller']);
                    $response = $controller->{$r['function']}(request());

                    if (!$response instanceof \Symfony\Component\HttpFoundation\Response) {

                        $response = response($response);
                        $content = $response->getContent();

                        $disableSeo = config('modules.disable_dynamic_seo');
                        if (isset($r['seo']) && $r['seo'] === false) {
                            $disableSeo = true;
                        }
                        if (strpos($content, '<head>') !== false && !$disableSeo) {
                            $content = str_replace(
                                '<head>',
                                '<head>' . init_plugin_meta_header(),
                                $content
                            );
                        }
                        $response->setContent(minify_all_one_line($content));
                    }

                    // Injeksi SEO khusus halaman plugin
                    if (str($response->headers->get('Content-Type'))->lower() == 'text/html; charset=utf-8') {
                        $content = $response->getContent();
                        $disableSeo = config('modules.disable_dynamic_seo');
                        if (isset($r['seo']) && $r['seo'] === false) {
                            $disableSeo = true;
                        }
                        if (strpos($content, '<head>') !== false && !$disableSeo) {
                            $content = str_replace(
                                '<head>',
                                '<head>' . init_plugin_meta_header(),
                                $content
                            );
                        }
                        $response->setContent($content);
                    }
                    return $response;
                }
            }
        }

        $response = $next($request);
        $path = $request->path();
        if ($path !== strtolower($path) && !Route::is('tag.posts')) {
            return redirect(strtolower($request->fullUrl()), 301);
        }


        if (str($response->headers->get('Content-Type'))->lower() == 'text/html; charset=utf-8') {
            $content = $response->getContent();

            $disableSeo = config('modules.disable_dynamic_seo');
            if (!$disableSeo) {
                $currentRouteName = \Illuminate\Support\Facades\Route::currentRouteName();
                if ($currentRouteName) {
                    $customRoutes = config('modules.custom_web_route', []);
                    foreach ($customRoutes as $cr) {
                        if (isset($cr['name']) && $cr['name'] === $currentRouteName) {
                            if (isset($cr['seo']) && $cr['seo'] === false) {
                                $disableSeo = true;
                            }
                            break;
                        }
                    }
                }
            }

            if (strpos($content, '<head>') !== false && !$disableSeo) {
                if (!is_custom_web_route_matched()) {
                    $content = str_replace(
                        '<head>',
                        '<head>' . init_meta_header(),
                        $content
                    );
                } else {
                    $content = str_replace(
                        '<head>',
                        '<head>' . init_plugin_meta_header(),
                        $content
                    );
                }
            }
            $content = preg_replace_callback('/<img\s+([^>]*?)src=["\']([^"\']*?)["\']([^>]*?)>/', function ($matches) use ($request) {
                $attributes = $matches[1] . 'data-src="' . $matches[2] . '" ' . $matches[3];

                // Tambahkan class lazyload
                if (strpos($attributes, 'class="') !== false) {
                    $attributes = preg_replace('/class=["\']([^"\']*?)["\']/', 'class="$1 lazyload"', $attributes);
                } else {
                    $attributes .= ' class="lazyload"';
                }

                // Tambahkan atribut alt jika tidak ada
                if (!preg_match('/\balt=["\']([^"\']*?)["\']/', $attributes)) {
                    $altValue = pathinfo($matches[2], PATHINFO_FILENAME); // Ambil nama file dari src
                    $attributes .= ' alt="' . formatFilename(htmlspecialchars($altValue, ENT_QUOTES)) . '"';
                }

                // Cache jika class mengandung lz-thumbnail


                return '<img ' . $attributes . ' src="/shimmer.gif">';
            }, $content);
            if (!is_custom_web_route_matched()) {
                $footer = '';

                $footer .= $request->is('/') ? init_popup() : null;
                $footer .= init_wabutton();
                $footer .= get_option('top_button') && get_option('top_button') == 'Y' ? init_goup() : null;
                $footer .= get_option('accessibility_widget') && get_option('accessibility_widget') == 'Y' ? '<script src="https://cdn.jsdelivr.net/npm/sienna-accessibility@latest/dist/sienna-accessibility.umd.js" defer></script>' : null;
                $footer .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js"></script>';
                if (file_exists(public_path('template/' . template() . '/scripts.js'))) {
                    $footer .= '<script src="' . url('template/' . template() . '/scripts.js') . '"></script>';
                }
                if (get_option('default_jquery') && get_option('default_jquery') == 'Y') {
                    $footer .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
                }


                $content = preg_replace(
                    '/<\/body>/',
                    $footer . '</body>',
                    $content
                );
            } else {
                $footer = '<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js"></script>';
                $content = preg_replace(
                    '/<\/body>/',
                    $footer . '</body>',
                    $content
                );
            }

            if (
                strpos($content, '<body') !== false &&
                strpos($content, 'circular-spinner') === false
            ) {
                $content = preg_replace(
                    '/<body\b[^>]*>/i',
                    '$0' . preload(),
                    $content,
                    1
                );
            }

            if ($request->segment(1) == 'docs') {
                $content = isPre($content);
            } else {
                $content = minify_all_one_line($content);
            }

            $response->setContent($content);
        }
        $this->securityHeaders($response, $request);

        return $response;
    }

    // function securityHeaders($response, $request)
    // {
    //     $cacheEnabled = app()->routesAreCached();
    //     $isCacheableRequest = $request->isMethod('GET') && !$request->expectsJson();

    //     $noCacheRoutes = config('modules.no_cache_for_route', []);

    //     $isExcludedRoute = collect($noCacheRoutes)
    //         ->contains(fn($pattern) => $request->is($pattern));

    //     if ($cacheEnabled && get_option('cache_web') == 'Y' && $isCacheableRequest && !$isExcludedRoute) {
    //         $response->setPublic();
    //         $response->setMaxAge(86400);
    //         $response->setSharedMaxAge(86400);
    //     } else {
    //         $response->headers->set(
    //             'Cache-Control',
    //             'no-cache, no-store, must-revalidate'
    //         );
    //     }
    //     $response->headers->set('X-Content-Type-Options', 'nosniff');
    //     if (get_option('frame_embed') == 'N' && !Auth::check()) {
    //         $response->headers->set('X-Frame-Options', 'DENY');
    //     }
    //     $response->headers->set('X-XSS-Protection', '1; mode=block');
    //     $response->headers->set('Content-Security-Policy', " base-uri 'self'; form-action 'self';");
    // }

    protected function securityHeaders($response, Request $request): void
    {
        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        */

        $cacheEnabled = app()->routesAreCached();

        $isCacheableRequest =
            $request->isMethod('GET')
            && !$request->expectsJson();

        $noCacheRoutes = config('modules.no_cache_for_route', []);

        $isExcludedRoute = collect($noCacheRoutes)
            ->contains(fn($pattern) => $request->is($pattern));

        if (
            $cacheEnabled &&
            get_option('cache_web') === 'Y' &&
            $isCacheableRequest &&
            !$isExcludedRoute
        ) {
            $response->setPublic();
            $response->setMaxAge(86400);
            $response->setSharedMaxAge(86400);
        } else {
            $response->headers->set(
                'Cache-Control',
                'private, no-cache, no-store, must-revalidate'
            );

            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        /*
        |--------------------------------------------------------------------------
        | Security Headers
        |--------------------------------------------------------------------------
        */

        // Prevent MIME sniffing
        $response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

        // Prevent Clickjacking
        if (get_option('frame_embed') === 'N' && !Auth::check()) {
            $response->headers->set(
                'X-Frame-Options',
                'DENY'
            );
        }

        // Hide Referer
        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );

        // Browser Permissions
        $response->headers->set(
            'Permissions-Policy',
            'accelerometer=(), autoplay=(), camera=(), display-capture=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), publickey-credentials-get=(self), usb=(), xr-spatial-tracking=()'
        );

        // Cross Origin
        $response->headers->set(
            'Cross-Origin-Opener-Policy',
            'same-origin'
        );

        $response->headers->set(
            'Cross-Origin-Resource-Policy',
            'same-origin'
        );

        // HTTPS Only
        if ($request->isSecure()) {

            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Content Security Policy
        |--------------------------------------------------------------------------
        */

        $cspRules = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:",
            "style-src 'self' 'unsafe-inline' https:",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https:",
            "connect-src 'self' https: wss:",
            "media-src 'self' blob:",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
            "frame-src 'self' https:",
            "upgrade-insecure-requests",
            "block-all-mixed-content"
        ];

        if (get_option('frame_embed') === 'N' && !Auth::check()) {
            $cspRules[] = "frame-ancestors 'none'";
        } else {
            $cspRules[] = "frame-ancestors *";
        }

        $csp = implode('; ', $cspRules);

        $response->headers->set(
            'Content-Security-Policy',
            $csp
        );
    }
}
