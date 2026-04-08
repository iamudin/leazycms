<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leazycms\Web\Services\AnalyticsService;

class TrackVisitor
{
    public function __construct(
        protected AnalyticsService $analytics
    ) {
    }
//ok
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (is_local()) {
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
                        if (mt_rand(1, 100) === 1) {
                        $this->cleanupVisitors();
                        }
                        app(AnalyticsService::class)->track($data);
                    })->afterResponse();

                }

            } catch (\Throwable $e) {
            }
        }

        return $response;
    }
    protected function cleanupVisitors(): void
{
    DB::table('analytics_visitors')
        ->where('last_seen_at', '<', now()->subMinutes(5))
        ->delete();
}
}