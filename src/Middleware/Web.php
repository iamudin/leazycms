<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (strpos($request->getRequestUri(), 'index.php') !== false || $request->getHost()!=str_replace('http://','',config('app.url'))) {
            return redirect( config('app.url') . str_replace('/index.php', '', $request->getRequestUri()));
        }
        $response = $next($request);
        if (get_option('site_maintenance') == 'Y' && !Auth::check()) {
            return undermaintenance();
        }
        processVisitorData();
        if ($response->headers->get('Content-Type') == 'text/html; charset=UTF-8') {
            $content = $response->getContent();
            $content = preg_replace_callback('/<img\s+([^>]*?)src=["\']([^"\']*?)["\']([^>]*?)>/', function ($matches) {
                $attributes = $matches[1] . 'data-src="' . $matches[2] . '" ' . $matches[3];
                if (strpos($attributes, 'class="') !== false) {
                    $attributes = preg_replace('/class=["\']([^"\']*?)["\']/', 'class="$1 lazyload"', $attributes);
                } else {
                    $attributes .= ' class="lazyload"';
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
            if($request->getHost()!='leazycms.com'){
                $footer .= '<footer style="text-align:center;background:#000;padding:10px;color:#ccc" class="'.str()->random(5).'_credit"><small>Leazycms <sup>'.get_leazycms_version().'</sup></small></footer>';
            }
            $footer .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>';
            $content = preg_replace('/<\/body>/', $footer. '</body>',
             $content);

            $response->setContent($content);
        }
        $this->securityHeaders($response,$request);
        return $response;
    }

    function securityHeaders($response,$request){
        $response->headers->set('Cache-Control', 'public, max-age=2592000');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if(get_option('frame_embed') && get_option('frame_embed')=='Y' && !Auth::check()){
        // Set X-Frame-Options Header
        $response->headers->set('X-Frame-Options', 'DENY');
         }

        // Set X-XSS-Protection Header
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Set Content-Security-Policy Header
        $response->headers->set('Content-Security-Policy', " base-uri 'self'; form-action 'self';");


    }

}
