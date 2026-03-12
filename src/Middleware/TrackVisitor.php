<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Leazycms\Web\Services\AnalyticsService;

class TrackVisitor
{
    public function __construct(
        protected AnalyticsService $analytics
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {

            $data = [
                'domain' => $request->getHost(),
                'path' => $request->path(),
                'ip' => get_client_ip(),
                'session_id' => $request->session()->getId(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'is_post' => $request->isMethod('post'),
                'is_ajax' => $request->ajax(),
            ];

            $hash = md5(
        $data['domain'] .
                $data['path'] .
                $data['ip'] .
                $data['user_agent']
            );

            $cacheKey = "analytics_lock_" . $hash;

            if (!Cache::has($cacheKey)) {

                Cache::put($cacheKey, true, now()->addMinutes(3));

                dispatch(function () use ($data) {
                      if ($post = config('modules.data')) {
                $post->timestamps = false;
                $post->increment('visited');
            }
                    app(AnalyticsService::class)->track($data);
                })->afterResponse();

            }

        } catch (\Throwable $e) {
        }

        return $response;
    }
}
// use Closure;
// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Route;

// class TrackVisitor
// {
//     public function handle($request, Closure $next)
//     {
//         $response = $next($request);
//         if (!$this->shouldTrack($request)) {
//             return $response;
//         }

// $domain = $request->getHost();
// $today = now()->toDateString();
// $sessionId = session()->getId();
// $path = trim($request->path(), '/');

// // ===============================
// // PAGE TTL KEY (5 menit)
// // ===============================
// $pageKey = "page_{$domain}_{$path}_{$sessionId}";

// // Kalau halaman ini belum dihitung dalam 5 menit
// if (!Cache::has($pageKey)) {

//     Cache::put($pageKey, true, now()->addMinutes(5));

//     // ===============================
//     // UNIQUE PER DOMAIN (per hari)
//     // ===============================
//     $visitorKey = "visitor_{$domain}_{$today}_{$sessionId}";
//     $isUniqueVisitor = Cache::has($visitorKey) ? 0 : 1;

//     if ($isUniqueVisitor) {
//         Cache::put($visitorKey, true, now()->endOfDay());
//     }

//     // ===============================
//     // INSERT / UPDATE (1 QUERY)
//     // ===============================
//     DB::statement("
//         INSERT INTO visitor_stats (`domain`,`date`,`total`,`unique`,`created_at`,`updated_at`)
//         VALUES (?, ?, 1, ?, NOW(), NOW())
//         ON DUPLICATE KEY UPDATE
//             total = total + 1,
//             `unique` = `unique` + ?,
//             updated_at = NOW()
//     ", [$domain, $today, $isUniqueVisitor, $isUniqueVisitor]);
//             if ($post = config('modules.data')) {
//                 $post->timestamps = false;
//                 $post->increment('visited');
//             }
// }
      
//         $onlineKey = "online_{$domain}_{$sessionId}";

//         if (!Cache::has($onlineKey)) {

//             Cache::put($onlineKey, true, now()->addMinutes(1));

//             DB::table('online_users')->upsert(
//                 [
//                     [
//                         'session_id' => $sessionId,
//                         'domain' => $domain,
//                         'last_activity' => now(),
//                         'ip' => $request->ip()
//                     ]
//                 ],
//                 ['session_id'], // unique key
//                 ['last_activity', 'domain', 'ip']
//             );
          
//         }
//         if (Cache::add('online_cleanup_lock', true, 180)) {

//             DB::table('online_users')
//                 ->where('last_activity', '<', now()->subMinutes(3))
//                 ->limit(50)
//                 ->delete();
//         }
//         return $response;
//     }

//     private function shouldTrack($request)
//     {
   
//         if (!config('modules.installed') || strpos(request()->headers->get('referer'), admin_path()) !== false || is_local() || Route::is('formaster'))
//             return false;
//         if (!$request->isMethod('get'))
//             return false;
//         $ua = strtolower($request->userAgent());
//         if (str_contains($ua, 'bot') || str_contains($ua, 'crawler')) {
//             return false;
//         }

//         return true;
//     }
// }