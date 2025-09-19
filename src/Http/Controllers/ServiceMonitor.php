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

        // Simpan ID supaya bisa dipasangkan setelah pool
        $siteIds = $sites->pluck('id')->all();

        
        $responses = Http::pool(function ($pool) use ($sites) {
            return $sites->map(
                fn($site) =>
                $pool->withHeaders([
                    'User-Agent' => enc64(md5($site->title))
                ])->timeout(6)->connectTimeout(3)->get("http://{$site->title}/". enc64(md5($site->title)))
            )->all();
        });

        // Gabungkan ID -> response
        $responses = array_combine($siteIds, $responses);


        // Susun hasil akhir
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
                $entry += [
                    'maintenance' => $json['maintenance'],
                    'editor_template_enabled' => $json['editor_template_enabled'],
                    'user_count' => $json['user_count'] ?? null,
                    'api_key' => $json['api_key'],
                    'active_modules' => $json['active_modules'] ?? null,
                ];
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
