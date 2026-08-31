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
    public static $currentTenant = null;

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
                function () use ($host) {
                    $t = Tenant::whereDomain($host)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                    if ($t) {
                        return $t->getRawOriginal();
                    }

                    // Cek apakah host adalah parked_domain milik tenant
                    if (class_exists(\Leazycms\Web\Models\Option::class)) {
                        $parkedOption = \Leazycms\Web\Models\Option::withoutGlobalScope('tenant')
                            ->where('name', 'parked_domain')
                            ->where('value', $host)
                            ->whereNotNull('tenant_id')
                            ->first();

                        if ($parkedOption) {
                            $t = Tenant::where('id', $parkedOption->tenant_id)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                            if ($t) {
                                $data = $t->getRawOriginal();
                                $data['is_parked_domain'] = true;
                                $data['matched_parked_domain'] = $host;
                                return $data;
                            }
                        }

                        // Fallback: Cek custom domain plugin
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

        // Jika tenant memiliki parkir domain dan pengunjung mengakses subdomain lama di frontend, alihkan ke parked domain
        if (
            config('modules.multisite_enabled')
            && !is_main_domain()
            && $tenant
            && !$tenant->getAttribute('is_plugin_custom_domain')
            && ($request->isMethod('GET') || $request->isMethod('HEAD'))
            && !$request->ajax()
            && !$request->wantsJson()
            && !$request->isXmlHttpRequest()
        ) {
            $adminPrefix = function_exists('admin_path') ? admin_path() : null;
            $firstSegment = $request->segment(1);

            // Jangan redirect request backend/admin, api, livewire, captcha
            if (
                (!$adminPrefix || $firstSegment !== $adminPrefix)
                && !in_array($firstSegment, ['api', 'captcha', 'livewire', '_debugbar', 'sanctum'])
            ) {
                $parkedDomain = Cache::rememberForever(
                    "tenant:{$tenant->id}:parked_domain",
                    function () use ($tenant) {
                        return Option::withoutGlobalScope('tenant')
                            ->where('tenant_id', $tenant->id)
                            ->where('name', 'parked_domain')
                            ->value('value') ?: '';
                    }
                );

                if (!empty($parkedDomain) && $host !== $parkedDomain) {
                    $scheme = $request->secure() ? 'https://' : 'http://';
                    $queryString = $request->getQueryString();
                    $targetUrl = $scheme . $parkedDomain . '/' . ltrim($request->path(), '/') . ($queryString ? '?' . $queryString : '');
                    return redirect()->away($targetUrl, 301);
                }
            }
        }

        // Jika ini adalah domain khusus plugin, blokir akses ke rute utama CMS
        if ($tenant && $tenant->getAttribute('is_plugin_custom_domain')) {
            if (function_exists('is_custom_web_route_matched') && !is_custom_web_route_matched()) {
                app()->instance('tenant', $tenant);
                abort(404);
            }

        }
        if (!$tenant) {
            $isCustomRoute = function_exists('is_custom_web_route_matched') ? is_custom_web_route_matched() : false;
            if ($isCustomRoute) {
                return $next($request);
            }

            $mainHost = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHttpHost();
            $masterOptions = \Illuminate\Support\Facades\Cache::rememberForever(
                "tenant:master:{$mainHost}:options",
                function () {
                    if (class_exists(\Leazycms\Web\Models\Option::class)) {
                        return \Leazycms\Web\Models\Option::withoutGlobalScope('tenant')->whereNull('tenant_id')->pluck('value', 'name')->toArray();
                    }
                    return [];
                }
            );

            $brandName = !empty($masterOptions['brand_name']) ? $masterOptions['brand_name'] : (!empty($masterOptions['site_title']) ? $masterOptions['site_title'] : config('app.name', 'LeazyCMS'));
            $brandUrl = !empty($masterOptions['brand_url']) ? $masterOptions['brand_url'] : (config('app.url') ?: ('https://' . $mainHost));
            $brandLogo = !empty($masterOptions['brand_logo']) ? $masterOptions['brand_logo'] : (!empty($masterOptions['favicon']) ? $masterOptions['favicon'] : null);

            $safeBrandName = htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8');
            $safeBrandUrl = htmlspecialchars($brandUrl, ENT_QUOTES, 'UTF-8');
            $safeHost = htmlspecialchars($host, ENT_QUOTES, 'UTF-8');

            if ($brandLogo) {
                $logoSrc = (filter_var($brandLogo, FILTER_VALIDATE_URL) || str_starts_with($brandLogo, '//'))
                    ? $brandLogo
                    : (rtrim(config('app.url'), '/') . '/' . ltrim('media/' . $brandLogo, '/'));
                $safeLogoSrc = htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8');
                $brandLogoHtml = '<img src="' . $safeLogoSrc . '" alt="' . $safeBrandName . '" class="brand-logo-img" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="brand-logo-fallback" style="display:none;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg></div>';
            } else {
                $brandLogoHtml = '<div class="brand-logo-fallback"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg></div>';
            }

            $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, minimum-scale=1, width=device-width">
    <title>Error 404 (Not Found)!! - {$safeBrandName}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, code { font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif; font-size: 15px; line-height: 1.6; }
        html { background: #ffffff; color: #202124; min-height: 100%; }
        body { margin: 6% auto 0; max-width: 760px; padding: 30px 24px; position: relative; }
        .error-container { display: flex; align-items: flex-start; justify-content: space-between; gap: 40px; }
        .error-content { flex: 1; min-width: 0; }
        .brand-header { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; color: #202124; margin-bottom: 28px; }
        .brand-logo-img { width: 34px; height: 34px; object-fit: contain; border-radius: 6px; }
        .brand-logo-fallback { width: 34px; height: 34px; border-radius: 6px; background: #1a73e8; color: #ffffff; display: flex; align-items: center; justify-content: center; }
        .brand-title { font-size: 20px; font-weight: 700; color: #202124; letter-spacing: -0.3px; }
        h1 { font-size: 20px; font-weight: 700; color: #202124; margin-bottom: 14px; }
        h1 span { font-weight: 400; color: #5f6368; }
        p { margin: 12px 0 16px; color: #3c4043; font-size: 14px; line-height: 1.6; }
        p ins { color: #5f6368; text-decoration: none; }
        code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; color: #202124; font-size: 13.5px; font-family: monospace; }
        .brand-action-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 18px; color: #1a73e8; font-weight: 500; font-size: 14px; text-decoration: none; }
        .brand-action-link:hover { text-decoration: underline; }
        .robot-art { flex-shrink: 0; width: 180px; }
        @media screen and (max-width: 680px) {
            body { margin-top: 20px; padding: 20px; }
            .error-container { flex-direction: column-reverse; align-items: flex-start; gap: 24px; }
            .robot-art { width: 130px; }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <a href="{$safeBrandUrl}" class="brand-header" title="{$safeBrandName}">
                {$brandLogoHtml}
                <span class="brand-title">{$safeBrandName}</span>
            </a>

            <h1><b>404.</b> <span>That’s an error.</span></h1>

            <p>Domain <code>{$safeHost}</code> tidak ditemukan atau belum terdaftar pada server ini. <ins>That’s all we know.</ins></p>

       
        </div>

        <div class="robot-art">
            <svg width="100%" viewBox="0 0 160 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Antenna -->
                <circle cx="80" cy="20" r="5" fill="#ea4335"/>
                <line x1="80" y1="25" x2="80" y2="40" stroke="#70757a" stroke-width="3"/>
                <!-- Head -->
                <rect x="42" y="40" width="76" height="50" rx="8" fill="#dadce0" stroke="#70757a" stroke-width="3"/>
                <!-- Eyes -->
                <circle cx="62" cy="62" r="7" fill="#4285f4"/>
                <circle cx="64" cy="60" r="2.5" fill="#ffffff"/>
                <line x1="92" y1="56" x2="102" y2="66" stroke="#ea4335" stroke-width="3" stroke-linecap="round"/>
                <line x1="102" y1="56" x2="92" y2="66" stroke="#ea4335" stroke-width="3" stroke-linecap="round"/>
                <!-- Mouth -->
                <path d="M66 78 Q80 86 94 78" stroke="#70757a" stroke-width="3" fill="none" stroke-linecap="round"/>
                <!-- Neck -->
                <rect x="74" y="90" width="12" height="8" fill="#9aa0a6"/>
                <!-- Body -->
                <rect x="36" y="98" width="88" height="60" rx="10" fill="#dadce0" stroke="#70757a" stroke-width="3"/>
                <!-- Chest Screen/Gears -->
                <rect x="48" y="108" width="40" height="26" rx="4" fill="#ffffff" stroke="#9aa0a6" stroke-width="2"/>
                <line x1="53" y1="117" x2="83" y2="117" stroke="#34a853" stroke-width="2" stroke-linecap="round"/>
                <line x1="53" y1="124" x2="73" y2="124" stroke="#fbbc05" stroke-width="2" stroke-linecap="round"/>
                <!-- Buttons -->
                <circle cx="104" cy="114" r="4" fill="#ea4335"/>
                <circle cx="104" cy="126" r="4" fill="#4285f4"/>
                <!-- Arms -->
                <path d="M36 112 Q20 120 28 138" stroke="#70757a" stroke-width="4" stroke-linecap="round" fill="none"/>
                <path d="M124 112 Q140 120 132 138" stroke="#70757a" stroke-width="4" stroke-linecap="round" fill="none"/>
                <!-- Legs/Wheels -->
                <rect x="52" y="158" width="14" height="14" rx="3" fill="#70757a"/>
                <rect x="94" y="158" width="14" height="14" rx="3" fill="#70757a"/>
            </svg>
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
        if (!app()->bound('tenant.options')) {
            app()->singleton('tenant.options', function () use ($tenant) {
                return Cache::rememberForever(
                    "tenant:{$tenant->domain}:options",
                    fn() => Option::pluck('value', 'name')
                        ->toArray()
                );
            });
        }
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
