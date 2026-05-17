<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Leazycms\Web\Models\Option;
use Leazycms\Web\Models\Tenant;
use Redis;
class IdentifyTenant
{
    protected static $currentTenant = null;

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        if (self::$currentTenant === null) {
            $tenantData = Cache::rememberForever(
                "tenant:$host",
                fn() => ($t = Tenant::where(['domain' => $host, 'status' => 'active'])->first()) ? $t->getRawOriginal() : null
            );

            if ($tenantData) {
                // Safeguard: Jika cache lama masih menyimpan array, konversi balik ke string JSON
                // agar Eloquent Casting tidak error saat memproses setRawAttributes
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

        if (!$tenant) {
            $portal = config('app.url');
            $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Website Tidak Ditemukan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --primary: #2563eb;
            --bg: #f1f5f9;
            --text: #0f172a;
            --muted: #64748b;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            max-width: 420px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.4s ease-in-out;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 22px;
            color: var(--text);
            margin-bottom: 10px;
        }

        p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .domain {
            font-weight: 600;
            color: var(--text);
        }

        .actions {
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            margin: 5px;
            transition: 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-outline {
            border: 1px solid #cbd5f5;
            color: var(--primary);
        }

        .btn-outline:hover {
            background: #eef2ff;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon">🌐</div>
        <h1>Website Tidak Ditemukan</h1>

        <p>
            Domain <span class="domain">{$host}</span> belum terdaftar atau tidak aktif di sistem kami.
        </p>

        <p>
            Pastikan alamat yang Anda akses sudah benar, atau hubungi administrator jika ini adalah website resmi.
        </p>

        <div class="actions">
            <a href="{$portal}" class="btn btn-primary">Kembali ke Portal</a>
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
            app()->singleton('default.options', function ()  {
            return Cache::rememberForever(
            "default:options",
            fn() => Option::withoutGlobalScope('tenant')->WhereNull('tenant_id')->pluck('value', 'name')->toArray()
        );
    });
        app()->instance('tenant', $tenant);
        URL::forceRootUrl($request->getSchemeAndHttpHost());
        app()->singleton('tenant.options', function () use ($tenant) {
            return Cache::rememberForever(
                "tenant:{$tenant->id}:options",
                fn() =>Option::pluck('value', 'name')
                    ->toArray()
            );
        });
        $response = $next($request);
        return $response;
    }
}
