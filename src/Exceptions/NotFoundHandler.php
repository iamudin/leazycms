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
            if(!config('modules.installed')){
            return redirect()->route('install');
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
            if (strpos($content, '</head>') !== false && strpos($content, 'loading-spin') === false) {
                $content = str_replace(
                    '</head>',
                    '</head>' . preload(),
                    $content
                );
            }
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
                $minifiedContent = preg_replace('/\s+/', ' ', $content);
                return response($minifiedContent, 404)->header('Content-Type', 'text/html; charset=UTF-8');
            }
        }

        return parent::render($request, $exception);
    }
}
