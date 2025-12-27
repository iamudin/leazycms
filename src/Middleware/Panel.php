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
    

}
