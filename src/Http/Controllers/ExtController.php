<?php
namespace Leazycms\Web\Http\Controllers;
use Illuminate\Support\Facades\Response;
use Leazycms\Web\Models\Post;
use App\Http\Controllers\Controller;

class ExtController extends Controller
{
    public function service_worker(){
        $script = view('cms::layouts.sw')->render();
    return Response::make($script, 200, ['Content-Type' => 'application/javascript']);
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

        return Response::json($manifest)->header('Content-Type', 'application/json');
    }
    public function sitemap_xml(){

            $type = collect(get_module())->where('active',true)->where('public',true)->where('web.detail',true);
            $post = Post::whereIn('type',$type->pluck('name')->toArray())->published()->select('updated_at','url','type')->get();
            $lastmod = Post::select('updated_at')->latest('updated_at')->first()?->updated_at;

            $type_index = [
                [
                    'loc'=>url('/'),
                    'priority'=>'1.0',
                    'lastmod' => $lastmod->toIso8601String()
                ]
            ];
            foreach($type as $row){
            if($row->web->index){
                $lst = $post->where('type',$row->name)->sortByDesc('updated_at')->first();
                $a['loc'] = url($row->name);
                $a['priority'] = '0.80';
                $a['lastmod'] = $lst ? $lst->updated_at->toIso8601String() : $lastmod->toIso8601String();
                $type_index[] = $a;
            }
            }
            $post_index = [];
            foreach($post as $row){
                    $a['loc'] = url($row->url);
                    $a['priority'] = $row->type=='halaman' ? '0.64' : '0.80';
                    $a['lastmod'] = $row->updated_at->toIso8601String();
                $post_index[] = $a;
            }
            $urls = array_merge($type_index,$post_index);
            $sitemap = view('cms::layouts.sitemap-xml', compact('urls'))->render();

            return response($sitemap, 200)->header('Content-Type', 'application/xml');
    }

}
