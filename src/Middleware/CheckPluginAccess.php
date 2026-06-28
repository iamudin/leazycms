<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPluginAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $adminPrefix = admin_path();
        $pluginName = null;

        if (str_starts_with($path, $adminPrefix . '/')) {
            $segments = explode('/', $path);
            if (isset($segments[1])) {
                $pluginName = $segments[1];
            }
        } else {
            $segments = explode('/', $path);
            if (isset($segments[0])) {
                $pluginName = $segments[0];
            }
        }

        if ($pluginName) {
            $pluginPath = resource_path('plugins/' . $pluginName);

            if (is_dir($pluginPath)) {
                $disabledPlugins = get_disabled_plugins();

                if (is_array($disabledPlugins) && in_array($pluginName, $disabledPlugins)) {
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
