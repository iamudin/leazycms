<?php

namespace Leazycms\Web\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Leazycms\Web\Support\DeviceDetector;

class AnalyticsService
{

    public function track(array $data)
    {

        if ($this->shouldIgnore($data)) {
            return;
        }

        $domain = $data['domain'];

        $now = now();
        $ip = $data['ip'];
        $date = $now->toDateString();

        $page = '/' . ltrim($data['path'], '/');

        $device = DeviceDetector::detect($data['user_agent']);

        $referrer = $this->extractReferrerHost($data['referer']);

        $visitorKey = $this->resolveVisitorKey($data);

        DB::transaction(function () use ($ip,$domain, $data, $now, $date, $page, $device, $referrer, $visitorKey) {

            $isNewDailyVisitor = $this->upsertVisitor(
                ip:$ip,
                domain: $domain,
                visitorKey: $visitorKey,
                sessionId: $data['session_id'],
                currentPage: $page,
                device: $device,
                referrer: $referrer,
                now: $now,
                today: $date
            );

            $this->incrementDaily($domain, $date, 'page_view', $page);

            $this->incrementDaily($domain, $date, 'device', $device);

            if ($referrer) {
                $this->incrementDaily($domain, $date, 'referrer', $referrer);
            }

            if (get_post_type() == 'search') {

                $keyword = str(request()->segment(2))->headline();

                $this->incrementDaily($domain, $date, 'search', $keyword);
            }

            if ($isNewDailyVisitor) {

                $this->incrementDaily($domain, $date, 'unique_total', 'site');

           
            }

        });

    }

    protected function shouldIgnore($data): bool
    {

        if ($data['is_ajax'])
            return true;

        if ($data['is_post'])
            return true;

        $ua = strtolower($data['user_agent']);

        if (str_contains($ua, 'bot') || str_contains($ua, 'crawler')) {
            return true;
        }

        return false;

    }

    protected function resolveVisitorKey($data): string
    {

        $sessionId = $data['session_id'];

        $ip = get_client_ip() ?? '0.0.0.0';

        $ua = substr((string) $data['user_agent'], 0, 255);

        return hash('sha256', $sessionId . '|' . $ip . '|' . $ua);

    }

    protected function extractReferrerHost(?string $referer): ?string
    {

        if (!$referer)
            return null;

        $host = str($referer)->limit(191);

        return $host ? strtolower($host) : null;

    }

    protected function upsertVisitor(
        string $ip,
        string $domain,
        string $visitorKey,
        ?string $sessionId,
        ?string $currentPage,
        ?string $device,
        ?string $referrer,
        Carbon $now,
        string $today
    ): bool {

        $visitor = DB::table('analytics_visitors')
            ->where('domain', $domain)
            ->where('visitor_key', $visitorKey)
            ->first();

        if (!$visitor) {

            DB::table('analytics_visitors')->insert([
                'domain' => $domain,
                'ip' => $ip,
                'visitor_key' => $visitorKey,
                'session_id' => $sessionId,
                'current_page' => $currentPage,
                'device' => $device,
                'referrer' => $referrer,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'last_seen_date' => $today,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            return true;

        }

        $isNewDailyVisitor = $visitor->last_seen_date !== $today;

        DB::table('analytics_visitors')
            ->where('domain', $domain)
            ->where('visitor_key', $visitorKey)
            ->update([
                'session_id' => $sessionId,
                'current_page' => $currentPage,
                'device' => $device,
                'referrer' => $referrer,
                'last_seen_at' => $now,
                'last_seen_date' => $today,
                'updated_at' => $now
            ]);

        return $isNewDailyVisitor;

    }

    protected function incrementDaily(
        string $domain,
        string $date,
        string $type,
        string $key,
        int $count = 1
    ): void {

        $exists = DB::table('analytics_daily')
            ->where('domain', $domain)
            ->where('date', $date)
            ->where('type', $type)
            ->where('key', $key)
            ->exists();

        if ($exists) {

            DB::table('analytics_daily')
                ->where('domain', $domain)
                ->where('date', $date)
                ->where('type', $type)
                ->where('key', $key)
                ->increment('count', $count);

            return;

        }

        DB::table('analytics_daily')->insert([
            'domain' => $domain,
            'date' => $date,
            'type' => $type,
            'key' => $key,
            'count' => $count
        ]);

    }

}