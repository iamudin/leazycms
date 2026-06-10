<?php

namespace Leazycms\Web\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Leazycms\Web\Models\BlockedIp;

class BlocklistService
{
    public function getBlacklistIps(): array
    {
        return Cache::rememberForever('ip_blacklist_cache', function () {
            $blockedIps = [];

            try {
                if (Schema::hasTable('blocked_ips')) {
                    $blockedIps = BlockedIp::query()
                        ->whereNull('unblocked_at')
                        ->pluck('ip')
                        ->filter()
                        ->values()
                        ->all();
                }
            } catch (\Throwable $e) {
            }

            $path = storage_path('app/security/ip-blacklist.json');
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $json = json_decode($content, true);
                if (is_array($json)) {
                    $blockedIps = array_merge($blockedIps, $json);
                }
            }

            return array_values(array_unique(array_filter($blockedIps)));
        });
    }

    public function getBlacklistUserAgents(): array
    {
        return Cache::rememberForever('user_agent_blacklist_cache', function () {
            try {
                if (Schema::hasTable('blocked_ips')) {
                    return BlockedIp::query()
                        ->whereNull('unblocked_at')
                        ->whereNotNull('user_agent')
                        ->pluck('user_agent')
                        ->filter(fn($agent) => is_string($agent) && trim($agent) !== '')
                        ->values()
                        ->all();
                }
            } catch (\Throwable $e) {
            }

            return [];
        });
    }

    public function detectClientDevice(?string $userAgent = null): string
    {
        $agent = strtolower((string) $userAgent);

        if ($agent === '') {
            return 'Unknown';
        }

        if (str_contains($agent, 'bot') || str_contains($agent, 'crawler') || str_contains($agent, 'spider')) {
            return 'Bot';
        }

        if (str_contains($agent, 'tablet') || str_contains($agent, 'ipad')) {
            return 'Tablet';
        }

        if (str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone')) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    public function sessionBlacklistCacheKey(?string $sessionId): ?string
    {
        if (!is_string($sessionId) || trim($sessionId) === '') {
            return null;
        }

        return 'session_blacklist_' . sha1($sessionId);
    }

    public function addSessionToBlacklist(?string $sessionId): void
    {
        $cacheKey = $this->sessionBlacklistCacheKey($sessionId);
        if (!$cacheKey) {
            return;
        }

        Cache::put($cacheKey, true, now()->addDays(30));
    }

    public function isSessionBlacklisted(?string $sessionId): bool
    {
        $cacheKey = $this->sessionBlacklistCacheKey($sessionId);
        return $cacheKey ? Cache::has($cacheKey) : false;
    }

    private function ipSessionEscalationKey(string $ip): string
    {
        return 'blocked_ip_sessions_' . sha1($ip);
    }

    public function trackBlockedSessionForIp(string $ip, ?string $sessionId): int
    {
        if (!is_string($sessionId) || trim($sessionId) === '') {
            return 0;
        }

        $cacheKey = $this->ipSessionEscalationKey($ip);
        $sessions = Cache::get($cacheKey, []);
        if (!is_array($sessions)) {
            $sessions = [];
        }

        $fingerprint = sha1($sessionId);
        if (!in_array($fingerprint, $sessions, true)) {
            $sessions[] = $fingerprint;
        }

        Cache::put($cacheKey, array_values(array_unique($sessions)), now()->addDays(30));

        return count($sessions);
    }

    public function addIpToBlacklist(string $ip, ?string $reason = null, ?string $userAgent = null): void
    {
        $path = storage_path('app/security/ip-blacklist.json');

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        if (!file_exists($path)) {
            file_put_contents($path, json_encode([]));
        }

        $ips = json_decode(file_get_contents($path), true);
        if (!is_array($ips)) {
            $ips = [];
        }

        if (!in_array($ip, $ips, true)) {
            $ips[] = $ip;
            file_put_contents($path, json_encode(array_values(array_unique($ips)), JSON_PRETTY_PRINT));
        }

        try {
            if (Schema::hasTable('blocked_ips')) {
                $location = get_ip_info($ip);
                $record = BlockedIp::firstOrNew(['ip' => $ip]);
                $record->country = $location['country'] ?? null;
                $record->region = $location['region'] ?? null;
                $record->device = $this->detectClientDevice($userAgent);
                $record->user_agent = $userAgent;
                $record->reason = $reason ?: 'Auto blacklist';
                $record->blocked_at = now();
                $record->unblocked_at = null;
                $record->unblocked_by = null;
                $record->save();
            }
        } catch (\Throwable $e) {
        }

        $this->flushCaches();
    }

    public function removeIpFromBlacklist(string $ip, ?int $unblockedBy = null): bool
    {
        $path = storage_path('app/security/ip-blacklist.json');
        $changed = false;

        if (file_exists($path)) {
            $ips = json_decode(file_get_contents($path), true);
            if (!is_array($ips)) {
                $ips = [];
            }

            $originalCount = count($ips);
            $ips = array_values(array_filter(
                $ips,
                fn($blockedIp) => $blockedIp !== $ip
            ));

            $changed = $originalCount !== count($ips);
            file_put_contents($path, json_encode($ips, JSON_PRETTY_PRINT));
        }

        try {
            if (Schema::hasTable('blocked_ips')) {
                $updated = BlockedIp::query()
                    ->where('ip', $ip)
                    ->whereNull('unblocked_at')
                    ->update([
                        'unblocked_at' => now(),
                        'unblocked_by' => $unblockedBy,
                    ]);
                $changed = $changed || $updated > 0;
            }
        } catch (\Throwable $e) {
        }

        $this->flushCaches();
        Cache::forget($this->ipSessionEscalationKey($ip));

        return $changed;
    }

    public function isBlocked(string $ip, ?string $sessionId, ?string $userAgent): bool
    {
        return in_array($ip, $this->getBlacklistIps(), true)
            || $this->isSessionBlacklisted($sessionId);
    }

    public function handleForbidden(Request $request): void
    {
        $ip = get_client_ip();
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        if ($this->isBlocked($ip, $sessionId, (string) $request->userAgent())) {
            abort(403, 'Access Denied (Blocked IP / Session)');
        }

        $filterRequestClient = (string) get_option('filter_request_client', 'N');
        if ($filterRequestClient !== 'Y') {
            return;
        }

        $rawKeywords = get_option('forbidden_keyword') ?? 'xxx,porn';
        $cleanedKeywords = str_replace(' ', '', $rawKeywords);
        $keywords = explode(',', $cleanedKeywords);

        if (Str::contains(strtolower($request->fullUrl()), array_unique(array_merge($keywords, forbidden_keyword())))) {
            $cacheKey = 'attack_attempt_ip_' . $ip;
            $sessionCacheKey = $sessionId ? 'attack_attempt_session_' . sha1($sessionId) : null;

            $countIp = Cache::increment($cacheKey);
            $countSession = 0;

            if ($countIp === 1) {
                Cache::put($cacheKey, 1, now()->addMinutes(30));
            }

            if ($sessionCacheKey) {
                $countSession = Cache::increment($sessionCacheKey);
                if ($countSession === 1) {
                    Cache::put($sessionCacheKey, 1, now()->addMinutes(30));
                }
            }

            $count = max($countIp, $countSession);

            if ($count >= 3) {
                $this->addSessionToBlacklist($sessionId);
                $distinctBlockedSessions = $this->trackBlockedSessionForIp($ip, $sessionId);
                $shouldPromoteIp = !$sessionId || $distinctBlockedSessions >= 2;

                if ($shouldPromoteIp) {
                    $this->addIpToBlacklist(
                        $ip,
                        'Forbidden keyword terdeteksi berulang dari beberapa session pada URL: ' . $request->fullUrl(),
                        $request->userAgent()
                    );
                    $message = "
<b>⛔ IP AUTO BLACKLISTED</b>

<b>📍 IP:</b> <code>{$ip}</code>
<b>🪪 Session:</b> <code>" . e($sessionId ?: '-') . "</code>
<b>🧩 Session Berbeda:</b> <code>{$distinctBlockedSessions}x</code>
<b>🔁 Total Attempt:</b> <code>{$count}x</code>

<b>Status:</b> 🚫 Permanently Blocked
";
                } else {
                    $message = "
<b>⚠️ SESSION AUTO BLACKLISTED</b>

<b>📍 IP:</b> <code>{$ip}</code>
<b>🪪 Session:</b> <code>" . e($sessionId ?: '-') . "</code>
<b>🧩 Session Berbeda dari IP ini:</b> <code>{$distinctBlockedSessions}x</code>
<b>🔁 Total Attempt:</b> <code>{$count}x</code>

<b>Status:</b> ⛔ Session diblokir, IP belum diblokir
";
                }
            } else {
                $url = e($request->fullUrl());
                $method = e($request->method());
                $userAgent = e($request->userAgent());
                $time = now()->format('Y-m-d H:i:s');

                $message = "
<b>🚨 SECURITY ALERT - TERDETEKSI SERANGAN</b>

<b>📍 IP Address:</b> <code>{$ip}</code>
<b>🪪 Session:</b> <code>" . e($sessionId ?: '-') . "</code>
<b>🕒 Waktu:</b> <code>{$time}</code>
<b>🌐 Method:</b> <code>{$method}</code>

<b>🔎 Keyword:</b>
<code>" . $request->fullUrl() . "</code>

<b>🔁 Total Attempt:</b>
<code>{$count}x</code>

<b>🔗 URL:</b>
<code>{$url}</code>

<b>🖥 User Agent:</b>
<code>{$userAgent}</code>

<b>Status:</b> ❌ Request Diblokir (403)
";
            }

            if ($message) {
                dispatch(function () use ($message) {
                    sendTelegramBotMessage($message);
                })->afterResponse();
            }

            abort(403);
        }
    }

    private function flushCaches(): void
    {
        Cache::forget('ip_blacklist_cache');
        Cache::forget('user_agent_blacklist_cache');
    }
}
