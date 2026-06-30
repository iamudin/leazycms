<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Leazycms\Web\Models\Option;
use Leazycms\Web\Models\Tenant;
class IdentifyTenant
{
    protected static $currentTenant = null;

    public function handle(Request $request, Closure $next)
    {

        if (!config('modules.multitenant_installed')) {
            $view = view('cms::backend.multisite-active')->render();
            return response(minify_all_one_line($view), 503)->header('Content-Type', 'text/html');
        }

        $host = $request->getHost();

        if (self::$currentTenant === null) {
            $tenantData = Cache::rememberForever(
                "tenant:$host",
                function() use ($host) {
                    $t = Tenant::whereDomain($host)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                    if ($t) {
                        return $t->getRawOriginal();
                    }

                    // Fallback: Cek custom domain plugin
                    if (class_exists(\Leazycms\Web\Models\Option::class)) {
                        $option = \Leazycms\Web\Models\Option::withoutGlobalScope('tenant')
                            ->where('value', $host)
                            ->where('name', 'like', '%-domain')
                            ->whereNotNull('tenant_id')
                            ->first();

                        if ($option) {
                            $t = Tenant::where('id', $option->tenant_id)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                            if ($t) {
                                $data = $t->getRawOriginal();
                                $data['is_plugin_custom_domain'] = true;
                                return $data;
                            }
                        }
                    }
                    return null;
                }
            );

            if ($tenantData) {

                if (isset($tenantData['modules']) && is_array($tenantData['modules'])) {
                    $tenantData['modules'] = json_encode($tenantData['modules']);
                }

                $tenant = new Tenant();
                $tenant->setRawAttributes($tenantData, true);
                $tenant->exists = true;
                self::$currentTenant = $tenant;
            }
        }
        $tenant = self::$currentTenant;

        // Jika ini adalah domain khusus plugin, blokir akses ke rute utama CMS
        if ($tenant && $tenant->getAttribute('is_plugin_custom_domain')) {
            if (function_exists('is_custom_web_route_matched') && !is_custom_web_route_matched()) {
                app()->instance('tenant', $tenant);
                abort(404);
            }

            // Intercept rute '/' karena bentrok dengan rute CMS WebController@home
            if (request()->path() === '/') {
                app()->instance('tenant', $tenant);
                $routes = config('modules.custom_web_route', []);
                foreach ($routes as $r) {
                    if (isset($r['path']) && ltrim($r['path'], '/') === '') {
                        $controller = app($r['controller']);
                        return $controller->{$r['function']}(request());
                    }
                }
            }
        }
        if (!$tenant) {
            $portal = config('app.url');
            $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Website Tidak Ditemukan</title>

    <style>

        :root{
            --bg:#f8fafc;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --primary:#0f766e;
            --border:#e2e8f0;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            min-height:100vh;

            display:flex;
            align-items:center;
            justify-content:center;

            padding:20px;

            background:
                radial-gradient(circle at top left,#dbeafe 0%,transparent 30%),
                radial-gradient(circle at bottom right,#ccfbf1 0%,transparent 30%),
                var(--bg);

            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                sans-serif;

            color:var(--text);
        }

        .wrapper{
            width:100%;
            max-width:540px;
        }

        .card{

            position:relative;

            background:rgba(255,255,255,0.92);

            backdrop-filter:blur(12px);

            border:1px solid rgba(255,255,255,0.6);

            border-radius:28px;

            padding:50px 40px;

            box-shadow:
                0 20px 60px rgba(15,23,42,0.08),
                0 8px 20px rgba(15,23,42,0.04);

            overflow:hidden;

            animation:fadeIn .4s ease;
        }

        .card::before{

            content:"";

            position:absolute;

            top:-120px;
            right:-120px;

            width:240px;
            height:240px;

            background:linear-gradient(
                135deg,
                rgba(13,148,136,.18),
                rgba(59,130,246,.10)
            );

            border-radius:50%;
        }

        .icon{

            width:82px;
            height:82px;

            margin:0 auto 25px;

            display:flex;
            align-items:center;
            justify-content:center;

            border-radius:24px;

            background:
                linear-gradient(
                    135deg,
                    #0f766e,
                    #14b8a6
                );

            box-shadow:
                0 10px 30px rgba(15,118,110,.25);

            font-size:36px;

            color:white;

            position:relative;
            z-index:2;
        }

        h1{

            font-size:30px;
            font-weight:800;

            text-align:center;

            margin-bottom:14px;

            letter-spacing:-0.5px;

            position:relative;
            z-index:2;
        }

        .desc{

            text-align:center;

            font-size:15px;
            line-height:1.8;

            color:var(--muted);

            position:relative;
            z-index:2;
        }

        .domain-box{

            margin-top:30px;

            background:#f8fafc;

            border:1px solid var(--border);

            border-radius:18px;

            padding:18px 20px;

            text-align:center;

            position:relative;
            z-index:2;
        }

        .domain-label{

            font-size:12px;

            color:#94a3b8;

            margin-bottom:8px;

            text-transform:uppercase;

            letter-spacing:1px;
        }

        .domain{

            font-size:18px;
            font-weight:700;

            color:var(--text);

            word-break:break-word;
        }

        .note{

            margin-top:24px;

            text-align:center;

            font-size:13px;

            color:#94a3b8;

            line-height:1.7;

            position:relative;
            z-index:2;
        }

        .footer{

            margin-top:35px;

            text-align:center;

            font-size:12px;

            color:#cbd5e1;

            position:relative;
            z-index:2;
        }

        @keyframes fadeIn{

            from{
                opacity:0;
                transform:translateY(15px);
            }

            to{
                opacity:1;
                transform:translateY(0);
            }

        }

        @media(max-width:640px){

            .card{
                padding:40px 25px;
                border-radius:24px;
            }

            h1{
                font-size:24px;
            }

            .domain{
                font-size:16px;
            }

        }

    </style>
</head>

<body>

    <div class="wrapper">

        <div class="card">

            <div class="icon">
                🌐
            </div>

            <h1>
                Website Tidak Ditemukan
            </h1>

            <div class="desc">

                Domain yang Anda akses saat ini belum terdaftar,
                dinonaktifkan, atau belum diarahkan ke sistem.

            </div>

            <div class="domain-box">

                <div class="domain-label">
                    DOMAIN
                </div>

                <div class="domain">
                    {$host}
                </div>

            </div>

            <div class="note">

                Pastikan penulisan domain sudah benar
                atau hubungi administrator website terkait
                untuk informasi lebih lanjut.

            </div>

            <div class="footer">
                Terima Kasih Telah Menggunakan Layanan Kami
            </div>

        </div>

    </div>

</body>
</html>
HTML;
            return response(minify_all_one_line($html), 404)
                ->header('Content-Type', 'text/html')
                ->header('X-Tenant-Status', 'not-found')
                ->header('X-Tenant-Domain', $host)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->header('X-Frame-Options', 'SAMEORIGIN')
                ->header('X-Content-Type-Options', 'nosniff');
        }

        if (config('modules.multitenant_installed') && !is_main_domain() && $tenant->status == 'suspended') {
            $view = view('cms::backend.suspended')->render();
            return response(minify_all_one_line($view), 503)->header('Content-Type', 'text/html');
        }
        app()->singleton('default.options', function () {
            return Cache::rememberForever(
                "tenant:master:" . parse_url(config('app.url'), PHP_URL_HOST) . ":options",
                fn() => Option::withoutGlobalScope('tenant')->WhereNull('tenant_id')->pluck('value', 'name')->toArray()
            );
        });
        app()->instance('tenant', $tenant);
        URL::forceRootUrl($request->getSchemeAndHttpHost());
        app()->singleton('tenant.options', function () use ($tenant) {
            return Cache::rememberForever(
                "tenant:{$tenant->domain}:options",
                fn() => Option::pluck('value', 'name')
                    ->toArray()
            );
        });

        // Plugin Access Check
        if (config('modules.multisite_enabled')) {
            $path = $request->path();
            $adminPrefix = admin_path();
            $pluginName = null;
            $isAdminRoute = false;

            if (str_starts_with($path, $adminPrefix . '/')) {
                $segments = explode('/', $path);
                if (isset($segments[1])) {
                    $pluginName = $segments[1];
                    $isAdminRoute = true;
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
                    if (is_main_domain()) {
                        if (!$isAdminRoute) {
                            abort(404);
                        }
                    } else {
                        $allowedPlugins = is_string($tenant->plugins) ? json_decode($tenant->plugins, true) : ($tenant->plugins ?? []);
                        if (!is_array($allowedPlugins) || !in_array($pluginName, $allowedPlugins)) {
                            abort(404);
                        }
                    }
                }
            }
        }

        $response = $next($request);
        return $response;
    }
}
