<?php
namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Option;
use Leazycms\Web\Models\Tenant;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Jika multisite belum aktif
        if (!config('modules.multisite_enabled')) {
            return $next($request);
        }

        $host = strtolower($request->getHost());

        $tenant = Tenant::where('domain', $host)->first();

        // ❌ Tenant tidak ditemukan
        if (!$tenant) {

            $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Website Tidak Ditemukan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            text-align: center;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            max-width: 400px;
        }
        h1 {
            font-size: 22px;
            margin-bottom: 10px;
        }
        p {
            color: #555;
        }
        .domain {
            font-weight: bold;
            color: #111;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Website tidak ditemukan</h1>
        <p>Domain <span class="domain">{$host}</span> tidak terdaftar di sistem.</p>
    </div>
</body>
</html>
HTML;

            return response($html, 404)
                ->header('Content-Type', 'text/html')
                ->header('X-Tenant-Status', 'not-found')
                ->header('X-Tenant-Domain', $host)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->header('X-Frame-Options', 'SAMEORIGIN')
                ->header('X-Content-Type-Options', 'nosniff');
        }

        // ✅ Set tenant
        app()->instance('tenant', $tenant);

        // Force URL sesuai domain
        \URL::forceRootUrl($request->getSchemeAndHttpHost());
        $tenantId = $tenant->id;
        // dd( Option::pluck('value', 'name') ->toArray());
app()->singleton('tenant.options', function () use ($tenantId) { return cache()->remember("options_{$tenantId}", 3600, function ()  { return Option::pluck('value', 'name') ->toArray(); }); }); 
        $response = $next($request);

        // Optional debug header
        if (config('app.debug')) {
            $response->headers->set('X-Tenant-ID', $tenant->id);
            $response->headers->set('X-Tenant-Domain', $tenant->domain);
        }

        return $response;
    }
}