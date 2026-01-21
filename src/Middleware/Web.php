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

        $response = $next($request);
        if (get_option('site_maintenance') == 'Y' && !Auth::check() && !Route::is('formaster')) {
            return undermaintenance();
        }
        if (str($response->headers->get('Content-Type'))->lower()== 'text/html; charset=utf-8') {
            $content = $response->getContent();
            
            if (strpos($content, '<head>') !== false && !is_custom_web_route_matched()) {
                $content = str_replace(
                    '<head>',
                    '<head>' . init_meta_header(),
                    $content
                );
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
            if(!is_custom_web_route_matched()){
            $footer = '';
            $footer .= $request->is('/') ? init_popup() : null;
            $footer .= init_wabutton();
            $footer .= get_option('top_button') && get_option('top_button') == 'Y' ? init_goup() : null;
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
            }else{
                 $footer = '<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js"></script>';
                 $content = preg_replace(
                '/<\/body>/',
                $footer . '</body>',
                $content
            );
            }

            if (strpos($content, '</body>') !== false  && strpos($content, 'spinner-spin') === false) {
                $content = str_replace(
                    '</body>',
                    preload() . '</body>',
                    $content
                );
            }

            if ($request->segment(1) == 'docs') {
                $content = isPre($content);
            } else {
              $content =  minify_all_one_line($content);
            }

            $response->setContent($content);
        }
        $this->securityHeaders($response, $request);
        tracking_visitor();
        forbidden($request);
        return $response;
    }

    function securityHeaders($response, $request)
    {
        $response->headers->set('Cache-Control', 'public, max-age=2592000');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if (get_option('frame_embed') == 'N' && !Auth::check()) {
            $response->headers->set('X-Frame-Options', 'DENY');
        }
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Content-Security-Policy', " base-uri 'self'; form-action 'self';");
    }
}
