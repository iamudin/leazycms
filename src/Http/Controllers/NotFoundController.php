<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NotFoundController extends Controller
{
    public function error404()
    {


        $request = request();
        $renderUnderMaintenance = function () {
            return response(
                preg_replace('/\s+/', ' ', undermaintenance()),
                503
            )->header('Content-Type', 'text/html');
        };


        if (config('modules.multisite_enabled')) {
            $isMainDomain = is_main_domain();

            if (config('app.debug')) {
                if (!$isMainDomain || !Auth::check()) {
                    return $renderUnderMaintenance();
                }
            }

            if (!config('app.debug') && app()->has('tenant') && !$isMainDomain) {
                $currentTenant = tenant();
                if (isset($currentTenant->status) && $currentTenant->status === 'maintenance' && !Auth::check()) {
                    return $renderUnderMaintenance();
                }
            }
        } elseif (config('app.debug') && !Auth::check()) {
            return $renderUnderMaintenance();
        }

        forbidden($request);
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Not Found'], 404);
        } else {
            $attr['view_type'] = '404';
            $attr['view_path'] = '404';
            config(['modules.current' => $attr]);
            $isPluginCustomDomain = app()->has('tenant') && tenant() && tenant()->getAttribute('is_plugin_custom_domain');

            if (!$isPluginCustomDomain && View::exists(get_view(get_view())) && (is_main_domain() || config('modules.multisite_enabled'))) {
                $showspin = true;
                $view = 'cms::layouts.master';
            } else {
                return response(preg_replace('/\s+/', ' ', error404Msg()), 404)
                    ->header('Content-Type', 'text/html;charset=UTF-8');

            }
            $content = view($view)->render();
            if (strpos($content, '<head>') !== false) {
                $content = str_replace(
                    '<head>',
                    '<head>' . init_meta_header(),
                    $content
                );
            }



            if (
                is_main_domain() && $showspin &&
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

            $minifiedContent = minify_all_one_line($content);
            return response($minifiedContent, 404)->header('Content-Type', 'text/html; charset=UTF-8');
        }
    }
}
