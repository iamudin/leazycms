<?php
namespace Leazycms\Web\Http\Controllers;
use Leazycms\Web\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class ExtController extends Controller
{
    public function service_worker()
    {
        $script = view('cms::layouts.sw')->render();
        return Response::make($script)
            ->header('Content-Type', 'application/javascript')
            ->header('Content-Disposition', 'inline; filename="service-worker.js"');
    }
    public function manifest()
    {
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
                    'src' => get_option('pwa_icon_512') ?? noimage(),
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

    private function sitemapHostKey(): string
    {
        if (config('modules.multisite_enabled')) {
            return request()->getHost();
        }
        return 'default';
    }

    private function sitemapBaseUrl(): string
    {
        return rtrim(request()->getSchemeAndHttpHost(), '/');
    }

    private function sitemapStorageDir(): string
    {
        return storage_path('app/sitemaps/' . $this->sitemapHostKey());
    }

    private function cleanupPublicSitemaps(): void
    {
        $main = public_path('sitemap.xml');
        if (File::exists($main)) {
            File::delete($main);
        }

        $parts = glob(public_path('sitemap_*.xml')) ?: [];
        foreach ($parts as $file) {
            if (is_string($file) && File::exists($file)) {
                File::delete($file);
            }
        }
    }

    private function generateSitemaps(): void
    {
        $baseUrl = $this->sitemapBaseUrl();
        $dir = $this->sitemapStorageDir();

        File::ensureDirectoryExists($dir);

        $type = collect(get_module())
            ->where('active', true)
            ->where('public', true)
            ->where('web.detail', true);

        if (config('modules.multisite_enabled')) {
            $tenantModules = app()->bound('tenant') ? (app('tenant')->modules ?? []) : [];
            $type = $type->whereIn('name', array_merge($tenantModules, default_menu()));
        }

        $typeNames = $type->pluck('name')->filter()->values()->all();

        $post = Post::whereIn('type', $typeNames)
            ->published()
            ->select('updated_at', 'url', 'type')
            ->get();

        $lastmod = $post->max('updated_at');
        $lastmodIso = $lastmod ? $lastmod->toIso8601String() : now()->toIso8601String();

        $type_index = [
            [
                'loc' => $baseUrl . '/',
                'priority' => '1.0',
                'lastmod' => $lastmodIso,
            ]
        ];

        foreach ($type as $row) {
            if ($row->web->index) {
                $lst = $post->where('type', $row->name)->sortByDesc('updated_at')->first();
                $type_index[] = [
                    'loc' => $baseUrl . '/' . ltrim((string) $row->name, '/'),
                    'priority' => '0.80',
                    'lastmod' => $lst?->updated_at?->toIso8601String() ?: $lastmodIso,
                ];
            }
        }

        $post_index = [];
        foreach ($post as $row) {
            $post_index[] = [
                'loc' => $baseUrl . '/' . ltrim((string) $row->url, '/'),
                'priority' => $row->type == 'halaman' ? '0.64' : '0.80',
                'lastmod' => $row->updated_at->toIso8601String(),
            ];
        }

        $urls = array_merge($type_index, $post_index);
        $chunkedUrls = array_chunk($urls, 50000);
        $sitemaps = [];

        foreach ($chunkedUrls as $index => $chunk) {
            $filename = "sitemap_" . ($index + 1) . ".xml";
            $sitemapContent = view('cms::layouts.sitemap-xml', ['urls' => $chunk])->render();
            $sitemapContentWithDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $sitemapContent;
            File::put($dir . DIRECTORY_SEPARATOR . $filename, $sitemapContentWithDeclaration);

            $sitemaps[] = [
                'loc' => $baseUrl . '/' . $filename,
                'lastmod' => now()->toIso8601String(),
            ];
        }

        $sitemapIndexContent = view('cms::layouts.sitemap-index', ['sitemaps' => $sitemaps])->render();
        $sitemapIndexWithDeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $sitemapIndexContent;
        File::put($dir . DIRECTORY_SEPARATOR . 'sitemap.xml', $sitemapIndexWithDeclaration);
    }

    public function sitemap_xml()
    {
        abort_if(function_exists('is_custom_web_route_matched') && is_custom_web_route_matched(), 404);
        $this->cleanupPublicSitemaps();

        $hostKey = $this->sitemapHostKey();
        $dir = $this->sitemapStorageDir();
        $path = $dir . DIRECTORY_SEPARATOR . 'sitemap.xml';

        Cache::remember("sitemap:{$hostKey}", 60, function () {
            $this->generateSitemaps();
            return true;
        });

        if (!File::exists($path)) {
            $this->generateSitemaps();
        }

        return response(File::get($path))
            ->header('Content-Type', 'application/xml');
    }

    public function sitemap_part($part)
    {
        abort_if(function_exists('is_custom_web_route_matched') && is_custom_web_route_matched(), 404);
        $this->cleanupPublicSitemaps();

        $part = (int) $part;
        abort_if($part < 1, 404);

        $hostKey = $this->sitemapHostKey();
        $dir = $this->sitemapStorageDir();
        $path = $dir . DIRECTORY_SEPARATOR . "sitemap_{$part}.xml";

        Cache::remember("sitemap:{$hostKey}", 60, function () {
            $this->generateSitemaps();
            return true;
        });

        if (!File::exists($path)) {
            $this->generateSitemaps();
        }

        abort_if(!File::exists($path), 404);

        return response(File::get($path))
            ->header('Content-Type', 'application/xml');
    }

    public function validate_file(\Illuminate\Http\Request $request)
    {
        if (!$request->hasFile('file')) {
            return Response::json(['success' => false, 'message' => 'Tidak ada file.']);
        }

        $file = $request->file('file');

        if (is_array($file)) {
            $file = $file[0]; // If array of files, just check the first one for simplicity, or we can loop. Client will send one by one.
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $accept = $request->input('accept');

        // 1. Validasi Global (Selalu Berjalan Pertama)
        $allowedExts = function_exists('flc_ext') ? flc_ext() : explode(',', get_option('allow_file_type') ?? '');
        $allowedMimesRaw = function_exists('allow_mime') ? allow_mime() : '';
        $allowedMimes = is_array($allowedMimesRaw) ? $allowedMimesRaw : explode(',', $allowedMimesRaw);
        // Bersihkan whitespace dari array mimes
        $allowedMimes = array_map('trim', $allowedMimes);

        if (!empty($allowedExts) && !in_array($ext, $allowedExts)) {
            return Response::json(['success' => false, 'message' => 'Ekstensi file diblokir oleh keamanan sistem.']);
        }
        if (!empty($allowedMimes) && !in_array($mime, $allowedMimes)) {
            return Response::json(['success' => false, 'message' => 'Tipe MIME file diblokir oleh keamanan sistem.']);
        }

        // 2. Validasi Spesifik (Atribut Accept) telah dipindahkan ke sisi client-side
        // untuk menghemat bandwidth. Server hanya fokus pada keamanan global.
        // 3. Validasi Ukuran (Skip isi teks jika lebih dari 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return Response::json(['success' => true]);
        }

        // 4. Validasi Isi Kode Berbahaya
        $content = file_get_contents($file->getRealPath());
        $dangerousStrings = \Leazycms\Web\Middleware\RateLimit::DANGEROUS_FUNCTIONS;

        foreach ($dangerousStrings as $str) {
            if (stripos($content, $str) !== false) {
                return Response::json(['success' => false, 'message' => 'File ditolak karena mengandung pola mencurigakan: ' . $str]);
            }
        }

        return Response::json(['success' => true]);
    }
}
