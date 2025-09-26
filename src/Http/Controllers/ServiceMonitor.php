<?php

namespace Leazycms\Web\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ServiceMonitor
{

    public function fetchAll(): array
    {
        $sites = query()
            ->onType('sites')
            ->published()
            ->select('id', 'title', 'data_field')
            ->get();

        $siteIds = $sites->pluck('id')->all();

        $responses = Http::pool(function ($pool) use ($sites) {
            return $sites->map(
                fn($site) =>
                $pool->withHeaders([
                    'User-Agent' => $site->field && isset($site->field->api_key) ? $site->field->api_key : null
                ])->timeout(6)->connectTimeout(3)->get("http://{$site->title}/" . ($site->field && isset($site->field->api_key) ? $site->field->api_key : null), [
                            'type' => 'info',
                        ])
            )->all();
        });

        $responses = array_combine($siteIds, $responses);

        $results = [];
        foreach ($sites as $site) {
            $resp = $responses[$site->id] ?? null;

            $entry = [
                'id' => $site->id,
                'domain' => $site->title,
                'http_code' => $resp?->status(),
                'time' => $resp?->transferStats?->getTransferTime(),
                'fetched_at' => now()->toISOString(),
            ];

            if ($resp?->successful()) {
                $json = $resp->json();

                // Pastikan response sesuai format (array dan punya field wajib)
                if (is_array($json) && isset($json['maintenance'], $json['editor_template_enabled'], $json['api_key'])) {
                    $entry += [
                        'maintenance' => $json['maintenance'],
                        'editor_template_enabled' => $json['editor_template_enabled'],
                        'user_count' => $json['user_count'] ?? null,
                        'cms_version' => $json['cms_version'] ?? null,
                        'theme_version' => $json['theme_version'] ?? null,
                        'api_key' => $json['api_key'],
                        'active_modules' => $json['active_modules'] ?? null,
                    ];
                } else {
                    // skip kalau format salah → langsung lanjut ke site berikutnya
                    continue;
                }
            } else {
                $entry['error'] = $resp?->reason() ?? 'no_response';
            }

            $results[] = $entry;
        }

        return [
            'meta' => [
                'generated_at' => now()->toISOString(),
                'count' => count($results),
            ],
            'data' => $results,
        ];
    }

}
