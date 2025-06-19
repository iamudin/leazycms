<?php
namespace Leazycms\Web\Http\Controllers;
use Leazycms\Web\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class ExtController extends Controller
{
    public function service_worker(){
        $script = view('cms::layouts.sw')->render();
        dd($script);
        return Response::make($script)
            ->header('Content-Type', 'application/javascript')
            ->header('Content-Disposition', 'inline; filename="service-worker.js"');
    }
    public function manifest(){
        $manifest = [
            'name' => get_option('pwa_name'),
            'short_name' => get_option('pwa_short_name'),
            'description' => get_option('pwa_description'),
            'start_url' => url('/'),
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#000000',
            'icons' => [
                [
                    'src' =>  get_option('pwa_icon_512') ?? noimage(),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ];

        return Response::json($manifest)
    ->header('Content-Type', 'application/json')
    ->header('Content-Disposition', 'inline; filename="site.manifest"');
    }
    public function sitemap_xml()
    {
        // Cek apakah sitemap sudah ada di cache
        $sitemap = Cache::remember('sitemap', 60, function () {
            $type = collect(get_module())
                ->where('active', true)
                ->where('public', true)
                ->where('web.detail', true);

            $post = Post::whereIn('type', $type->pluck('name')->toArray())
                ->published()
                ->select('updated_at', 'url', 'type')
                ->get();

            $lastmod = Post::select('updated_at')->latest('updated_at')->first()?->updated_at;

            $type_index = [
                [
                    'loc' => url('/'),
                    'priority' => '1.0',
                    'lastmod' => $lastmod ? $lastmod->toIso8601String() : now()->toIso8601String(),
                ]
            ];

            foreach ($type as $row) {
                if ($row->web->index) {
                    $lst = $post->where('type', $row->name)->sortByDesc('updated_at')->first();
                    $a['loc'] = url($row->name);
                    $a['priority'] = '0.80';
                    $a['lastmod'] = $lst ? $lst->updated_at->toIso8601String() : $lastmod->toIso8601String();
                    $type_index[] = $a;
                }
            }

            $post_index = [];
            foreach ($post as $row) {
                $a['loc'] = url($row->url);
                $a['priority'] = $row->type == 'halaman' ? '0.64' : '0.80';
                $a['lastmod'] = $row->updated_at->toIso8601String();
                $post_index[] = $a;
            }

            $urls = array_merge($type_index, $post_index);

            // Pisahkan URL ke dalam beberapa file dengan maksimal 50.000 URL per file
            $chunkedUrls = array_chunk($urls, 50000);
            $sitemaps = [];

            foreach ($chunkedUrls as $index => $chunk) {
                $filename = "sitemap_" . ($index + 1) . ".xml";
                $sitemapContent = view('cms::layouts.sitemap-xml', ['urls' => $chunk])->render();
                File::put(public_path($filename), $sitemapContent);
                $sitemaps[] = [
                    'loc' => url($filename),
                    'lastmod' => now()->toIso8601String(),
                ];
            }

            // Generate indeks sitemap utama
            $sitemapIndexContent = view('cms::layouts.sitemap-index', ['sitemaps' => $sitemaps])->render();
            File::put(public_path('sitemap.xml'), $sitemapIndexContent);

            return $sitemapIndexContent;
        });

        // Kembalikan file sitemap.xml sebagai respons
        return Response::file(public_path('sitemap.xml'), [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'inline; filename="sitemap.xml"',
        ]);
    }

}
