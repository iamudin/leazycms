<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TrackVisitor
{
    public function handle($request, Closure $next)
    {
        if (!$this->shouldTrack($request)) {
            return $next($request);
        }

$domain = $request->getHost();
$today = now()->toDateString();
$sessionId = session()->getId();
$path = trim($request->path(), '/');

// ===============================
// PAGE TTL KEY (5 menit)
// ===============================
$pageKey = "page_{$domain}_{$path}_{$sessionId}";

// Kalau halaman ini belum dihitung dalam 5 menit
if (!Cache::has($pageKey)) {

    Cache::put($pageKey, true, now()->addMinutes(5));

    // ===============================
    // UNIQUE PER DOMAIN (per hari)
    // ===============================
    $visitorKey = "visitor_{$domain}_{$today}_{$sessionId}";
    $isUniqueVisitor = Cache::has($visitorKey) ? 0 : 1;

    if ($isUniqueVisitor) {
        Cache::put($visitorKey, true, now()->endOfDay());
    }

    // ===============================
    // INSERT / UPDATE (1 QUERY)
    // ===============================
    DB::statement("
        INSERT INTO visitor_stats (`domain`,`date`,`total`,`unique`,`created_at`,`updated_at`)
        VALUES (?, ?, 1, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            total = total + 1,
            `unique` = `unique` + ?,
            updated_at = NOW()
    ", [$domain, $today, $isUniqueVisitor, $isUniqueVisitor]);
}
        $onlineKey = "online_{$domain}_{$sessionId}";

        if (!Cache::has($onlineKey)) {

            Cache::put($onlineKey, true, now()->addMinutes(2));

            DB::table('online_users')->updateOrInsert(
                ['session_id' => $sessionId],
                [
                    'domain' => $domain,
                    'last_activity' => now(),
                    'ip' => $request->ip()
                ]
            );
          
        }
        if (Cache::add('online_cleanup_lock', true, 60)) {

            DB::table('online_users')
                ->where('last_activity', '<', now()->subMinutes(5))
                ->delete();
        }
        return $next($request);
    }

    private function shouldTrack($request)
    {
        if (!$request->isMethod('get'))
            return false;
        $ua = strtolower($request->userAgent());
        if (str_contains($ua, 'bot') || str_contains($ua, 'crawler')) {
            return false;
        }

        return true;
    }
}