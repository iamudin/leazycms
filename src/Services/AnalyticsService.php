<?php

namespace Leazycms\Web\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
        $tenantId = app()->has('tenant') ? tenant()->id : null;

        $now = now();
        $ip = $data['ip'];
        $date = $now->toDateString();

        $page = str('/' . ltrim($data['path'], '/'))->limit(150);

        $device = DeviceDetector::detect($data['user_agent']);
        $user_agent = str($data['user_agent'])->limit(150);
        $referrer = $this->extractReferrerHost($data['referer']);

        $visitorKey = $this->resolveVisitorKey($data);

        DB::transaction(function () use ($ip, $domain, $tenantId, $data, $now, $date, $page, $device, $referrer, $visitorKey, $user_agent) {

            $visitorResult = $this->upsertVisitor(
                ip: $ip,
                domain: $domain,
                tenantId: $tenantId,
                visitorKey: $visitorKey,
                sessionId: $data['session_id'],
                currentPage: $page,
                device: $device,
                user_agent: $user_agent,
                referrer: $referrer,
                now: $now,
                today: $date
            );

            $this->incrementDaily($domain, $tenantId, $date, 'page_view', $page);

            $this->incrementDaily($domain, $tenantId, $date, 'device', $device);

            if ($referrer) {
                $this->incrementDaily($domain, $tenantId, $date, 'referrer', $referrer);
            }

            if (get_post_type() == 'search') {

                $keyword = str(request()->segment(2))->headline();
                if(!empty($keyword)){
                    $this->incrementDaily($domain, $tenantId, $date, 'search', $keyword);
                }
            }

            if (!empty($visitorResult['is_new_daily'])) {

                $this->incrementDaily($domain, $tenantId, $date, 'unique_total', 'site');

                if (!empty($visitorResult['country'])) {
                    $this->incrementDaily($domain, $tenantId, $date, 'country', $visitorResult['country']);
                }
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
        ?int $tenantId,
        string $visitorKey,
        ?string $sessionId,
        ?string $currentPage,
        ?string $device,
        ?string $user_agent,
        ?string $referrer,
        Carbon $now,
        string $today
    ): array {

        $visitor = DB::table('analytics_visitors')
            ->where('domain', $domain)
            ->where('visitor_key', $visitorKey)
            ->first();

        $dailyKey = "daily_visitor_{$domain}_{$today}_{$visitorKey}";
        $isNewDaily = !Cache::has($dailyKey);
        if ($isNewDaily) {
            Cache::put($dailyKey, true, now()->endOfDay());
        }

        if (!$visitor) {

            $location = get_ip_info($ip);
            $country = $location['country'] ?? null;
            $region = $location['region'] ?? null;
            $city = $location['city'] ?? null;

            $payload = [
                'domain' => $domain,
                'ip' => $ip,
                'country' => $country,
                'region' => $region,
                'city' => $city,
                'visitor_key' => $visitorKey,
                'session_id' => $sessionId,
                'current_page' => $currentPage,
                'device' => $device,
                'user_agent' => $user_agent,
                'referrer' => $referrer,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'last_seen_date' => $today,
                'created_at' => $now,
                'updated_at' => $now
            ];

            if ($tenantId !== null) {
                $payload['tenant_id'] = $tenantId;
            }

            DB::table('analytics_visitors')->insert($payload);

            return [
                'is_new_daily' => true,
                'country' => $country,
            ];
        }

        if ($visitor->last_seen_date !== $today) {
            $isNewDaily = true;
        }

        $country = $visitor->country ?? null;
        $region = $visitor->region ?? null;
        $city = $visitor->city ?? null;

        if (empty($country) && $ip) {
            $location = get_ip_info($ip);
            $country = $location['country'] ?? null;
            $region = $location['region'] ?? null;
            $city = $location['city'] ?? null;
        }

        $update = [
            'ip' => $ip,
            'country' => $country,
            'region' => $region,
            'city' => $city,
            'session_id' => $sessionId,
            'current_page' => $currentPage,
            'device' => $device,
            'referrer' => $referrer,
            'last_seen_at' => $now,
            'last_seen_date' => $today,
            'updated_at' => $now
        ];

        if ($tenantId !== null) {
            $update['tenant_id'] = $tenantId;
        }

        DB::table('analytics_visitors')
            ->where('domain', $domain)
            ->where('visitor_key', $visitorKey)
            ->update($update);

        return [
            'is_new_daily' => $isNewDaily,
            'country' => $country,
        ];
    }

    protected function incrementDaily(
        string $domain,
        ?int $tenantId,
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

        $payload = [
            'domain' => $domain,
            'date' => $date,
            'type' => $type,
            'key' => $key,
            'count' => $count
        ];

        if ($tenantId !== null) {
            $payload['tenant_id'] = $tenantId;
        }

        DB::table('analytics_daily')->insert($payload);
    }

}
