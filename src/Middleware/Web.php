<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

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
        if (get_option('site_maintenance') == 'Y' && !Auth::check()) {
            return undermaintenance();
        }
        if ($response->headers->get('Content-Type') == 'text/html; charset=UTF-8') {
            $content = $response->getContent();
            $content = preg_replace_callback('/<img\s+([^>]*?)src=["\']([^"\']*?)["\']([^>]*?)>/', function ($matches) use($request) {
                $attributes = $matches[1] . 'data-src="' . $matches[2] . '" ' . $matches[3];
                if (strpos($attributes, 'class="') !== false) {
                    $attributes = preg_replace('/class=["\']([^"\']*?)["\']/', 'class="$1 lazyload"', $attributes);
                } else {
                    $attributes .= ' class="lazyload"';
                }
                if (strpos($attributes, 'class="') !== false && strpos($attributes, 'lz-thumbnail') !== false) {
                    if(strpos($matches[2],'noimage.webp') === false && !empty($matches[2])){
                        $keycache = md5($request->fullUrl());
                        if(!Cache::has($keycache)){
                            Cache::put($keycache, $matches[2], now()->addSeconds(1800));
                        }
                    }
                }
                return '<img ' . $attributes . ' src="/shimmer.gif">';
            }, $content);
            if (strpos($content, '<head>') !== false) {
                $content = str_replace(
                    '<head>',
                    '<head>' . init_meta_header(),
                    $content
                );
            }

                if ($request->segment(1) == 'docs') {
                    $content = isPre($content);
                } else {
                    $content = preg_replace('/\s+/', ' ', $content);
                }

            $footer = '';
            $footer .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js"></script>';
            if(file_exists(public_path('template/'.template().'/scripts.js'))){
            $footer .= '<script src="'.url('template/'.template().'/scripts.js').'"></script>';
            }
            if(get_option('default_jquery') && get_option('default_jquery') == 'N'){
            $footer .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
        }

            $content = preg_replace('/<\/body>/', $footer. '</body>',
             $content);

                $content = preg_replace_callback(
                    '/<body([^>]*)>/',
                    function ($matches) {
                        $existingClasses = '';
                        if (preg_match('/class="([^"]*)"/', $matches[1], $classMatches)) {
                            $existingClasses = trim($classMatches[1]);
                            $existingClasses .= ' fade-in';
                        } else {
                            $existingClasses = 'fade-in';
                        }
                        return '<body' . ($existingClasses ? ' class="' . $existingClasses . '"' : '') . '>';
                    },
                    $content
                );
            $response->setContent($content);
        }
        $this->securityHeaders($response,$request);
        processVisitorData();
        return $response;
    }

    function securityHeaders($response,$request){
        $response->headers->set('Cache-Control', 'public, max-age=2592000');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if(get_option('frame_embed')=='Y' && !Auth::check()){
        $response->headers->set('X-Frame-Options', 'DENY');
         }
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Content-Security-Policy', " base-uri 'self'; form-action 'self';");


    }

}
