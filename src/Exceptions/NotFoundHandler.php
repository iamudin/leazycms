<?php
namespace Leazycms\Web\Exceptions;
use Leazycms\Web\Http\Controllers\AppMasterController;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function render($request, Throwable $exception): \Symfony\Component\HttpFoundation\Response
    {

        if ($exception instanceof NotFoundHttpException) {
            if (config('modules.installed')=="0") {
                exit('Please running cms:install');
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
        if ($redirectUrl && rtrim(urldecode($redirectUrl),'/') !== urldecode($request->fullUrl())) {
            return redirect($redirectUrl, 301);
        }
            if (!Route::is('stream')) {
                tracking_visitor('404');
            }
            forbidden($request);

            if (get_option('site_maintenance')  && get_option('site_maintenance') == 'Y') {
                return undermaintenance();
            }
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Not Found'], 404);
            } else {
                  $attr['view_type'] = '404';
                  $attr['view_path'] = '404';
                config(['modules.current' => $attr]);
                if(View::exists(get_view(get_view())) && is_main_domain()){
                    $showspin = true;
                    $view = 'cms::layouts.master';
                }else{
                    $showspin = false;
                    $view =  'cms::errors.404';

                }
            $content = view($view)->render();
            if (strpos($content, '<head>') !== false) {
                $content = str_replace(
                    '<head>',
                    '<head>' . init_meta_header(),
                    $content
                );
            }

            if (is_main_domain() && $showspin && strpos($content, '</head>') !== false && strpos($content, 'spinner-spin') === false) {
                $content = str_replace(
                    '</head>',
                    '</head>' . preload(),
                    $content
                );
            }

                $minifiedContent = preg_replace('/\s+/', ' ', $content);
                return response($minifiedContent, 404)->header('Content-Type', 'text/html; charset=UTF-8');
            }
        }

        return parent::render($request, $exception);
    }
}
