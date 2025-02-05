<?php
namespace Leazycms\Web\Exceptions;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */

    public function render($request, Throwable $exception)
    {

        if ($exception instanceof NotFoundHttpException) {
            if (config('modules.installed')=="0") {
                exit('Please running leazycms:install');
            }
            $current_host = $request->getHost();
        $origin_host = parse_url(config('app.url'), PHP_URL_HOST);
        $uri = $request->getRequestUri();
        $current_scheme = strpos($request,'https') !==false ? 'https' : 'http';

        // Initialize variables
        $redirectUrl = null;
        // Remove "index.php" from URI
        if (strpos($uri, 'index.php') !== false) {
            $uri = str_replace('index.php', '', $uri);
        }
        if(!(request()->ip() == '127.0.0.1' || request()->ip() == '::1')) {
            $scheme = 'https';
        }else{
            $scheme = 'http';
        }
        // Build the redirect URL if needed
        if ($current_scheme!=$scheme || $current_host != $origin_host || $uri != $request->getRequestUri()) {
            $redirectUrl = $scheme.'://'.$origin_host . '/' . ltrim($uri, '/');
        }
        // Redirect if necessary
        if ($redirectUrl && !(request()->ip() == '127.0.0.1' || request()->ip() == '::1')) {
            return redirect($redirectUrl);
        }
            forbidden($request);

            if (get_option('site_maintenance') == 'Y' && !Auth::check()) {
                return undermaintenance();
            }
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Not Found'], 404);
            } else {
                  $attr['view_type'] = '404';
                  $attr['view_path'] = '404';
                config(['modules.current' => $attr]);
                $view = View::exists(get_view(get_view())) ? 'cms::layouts.master' : 'cms::errors.404';
            $content = view($view)->render();
            if (strpos($content, '<head>') !== false) {
                $content = str_replace(
                    '<head>',
                    '<head>' . init_meta_header(),
                    $content
                );
            }
            if (strpos($content, '</head>') !== false && strpos($content, 'spinner-spin') === false) {
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
