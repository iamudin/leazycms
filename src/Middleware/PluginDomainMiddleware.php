<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Option;

class PluginDomainMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Cari apakah host saat ini didaftarkan sebagai custom domain oleh sebuah plugin
        $option = Option::where('value', $host)
            ->where('name', 'like', '%-domain')
            ->first();

        if ($option) {
            // Jika ini adalah custom domain plugin, dan rutenya adalah root ('/')
            // Cegat agar tidak di-handle oleh WebController CMS utama
            if ($request->path() === '/') {
                $routes = config('modules.custom_web_route', []);
                foreach ($routes as $r) {
                    // Cari rute plugin yang mendaftarkan '/'
                    if (isset($r['path']) && ltrim($r['path'], '/') === '') {
                        $controller = app($r['controller']);
                        return $controller->{$r['function']}(request());
                    }
                }
            }
        }

        return $next($request);
    }
}
