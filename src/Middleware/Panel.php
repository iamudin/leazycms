<?php

namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Http\Request;

class Panel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(strpos($request->fullUrl(),'notifreader') === false && in_array($request->user()->level,collect(config('modules.extension_module'))->pluck('path')->toArray())){
            return to_route($request->user()->level.'.dashboard');
        }

        $admin_path = admin_path();
        foreach (get_module() as $modul) {
            if ($request->is($admin_path . '/' . $modul->name)) {
                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => $modul->title,
                    ]
                ]);
            }

            if ($request->is($admin_path . '/' . $modul->name . '/*/edit')) {
                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => 'Edit ' . $modul->title,
                    ]
                ]);
            }
            if ($request->is($admin_path . '/' . $modul->name . '/*/show')) {

                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => 'Lihat ' . $modul->title,
                    ]
                ]);
            }


            if ($request->is($admin_path . '/' . $modul->name . '/category/*/edit')) {
                $title = 'Edit Kategori ' . $modul->title;
                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => $title,
                    ]
                ]);
            }
            if ($request->is($admin_path . '/' . $modul->name . '/category/create')) {
                $title = 'Tambah Kategori ' . $modul->title;
                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => $title,
                    ]
                ]);
            }
            if ($request->is($admin_path . '/' . $modul->name . '/category')) {
                $title = 'Kategori ' . $modul->title;
                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' =>  $title,
                    ]
                ]);
            }

            if ($request->is($admin_path . '/' . $modul->name . '/create')) {

                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => 'Tambah ' . $modul->title,
                    ]
                ]);
            }
            if ($request->is($admin_path . '/' . $modul->name . '/bulkaction')) {

                config([
                    'modules.current' => [
                        'post_type' => $modul->name,
                        'title_crud' => 'Tambah ' . $modul->title,
                    ]
                ]);
            }

        }
        isNotInSession($request);
        foreach ($request->allFiles() as $file) {
            // Handle multiple file input (array of files)
            if (is_array($file)) {
                foreach ($file as $subfile) {
                    if (!$this->isFileSafe($subfile)) {
                        return response()->json(['error' => 'Malicious file detected.'], 400);
                    }
                }
            } else {
                if (!$this->isFileSafe($file)) {
                    return response()->json(['error' => 'Malicious file detected.'], 400);
                }
            }
        }
            $dangerousFunctions = [
            'eval', ' system',' exec', 'passthru', 'shell_exec',
            'proc_open', 'popen', 'assert', 'base64_decode',
            'file_put_contents', 'fopen', 'curl_exec', 'create_function','file_get_contents', 'unlink', 'mkdir', 'curl_exec', 'create_function'
        ];

        // Dapatkan semua konten dari request
        $content = $request->getContent();

        // Scan konten terhadap nama fungsi berbahaya
        foreach ($dangerousFunctions as $function) {
            if (stripos($content, $function) !== false) {
                return response()->json([
                    'error' => 'Request contains potentially dangerous code: ' . $function
                ], 400);
            }
        }
        $response = $next($request);
        if (str($response->headers->get('Content-Type'))->lower() == 'text/html; charset=utf-8') {

            $content = $response->getContent();
            if(strpos($request->fullUrl(),'edit')===false){
            $content = preg_replace_callback('/<img\s+([^>]*?)src=["\']([^"\']*?)["\']([^>]*?)>/', function ($matches) {
                $attributes = $matches[1] . 'data-src="' . $matches[2] . '" ' . $matches[3];
                if (strpos($attributes, 'class="') !== false) {
                    $attributes = preg_replace('/class=["\']([^"\']*?)["\']/', 'class="$1 lazyload" ', $attributes);
                } else {
                    $attributes .= ' class="lazyload"';
                }
                return '<img ' . $attributes . ' src="/shimmer.gif">';
            }, $content);
        }
                if (in_array($request->segment(2),['docs','appearance'])) {
                    $content = isPrePanel($content);
                } else {
                    $content = minify_all_one_line( $content);
                }
            $response->setContent($content);
        }

        return $response;
    }
    protected function isFileSafe($file): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        $path = $file->getRealPath();

        // Skip scanning untuk file video/audio (mp4/mp3)
        $mime = $file->getMimeType();
        $skipScan = [
            'video/mp4',
            'audio/mpeg',
            'audio/mp3',
        ];

        if (in_array($mime, $skipScan)) {
            return true; // Aman, tidak perlu scan konten
        }

        // Skip file besar (lebih dari 5MB)
        if (filesize($path) > 5 * 1024 * 1024) {
            return true;
        }

        $content = file_get_contents($path);

        // Deteksi tag PHP
        if (preg_match('/<\?(php|=)/i', $content)) {
            return false;
        }

        // Cek fungsi berbahaya hanya untuk file teks
        $danger = [
            'eval',
            ' exec',
            ' system',
            'passthru',
            'shell_exec',
            'proc_open',
            'popen',
            'assert',
            'base64_decode',
            'file_put_contents',
            'fopen',
            'unlink',
            'mkdir',
            'curl_exec',
            'create_function',
            'file_get_contents',
        ];

        foreach ($danger as $func) {
            if (stripos($content, $func) !== false) {
                return false;
            }
        }

        return true;
    }

}
