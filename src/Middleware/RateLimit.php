<?php
namespace Leazycms\Web\Middleware;
use Closure;
use Illuminate\Http\Request;

class RateLimit
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
        if(!config('modules.installed') && strpos($request->fullUrl(), 'install') === false ){
            if(env('SESSION_DRIVER')!='file' || env('QUEUE_CONNECTION')!= 'sync' || env('CACHE_STORE')!='file'){
                $cfg['SESSION_DRIVER'] = 'file';
                $cfg['QUEUE_CONNECTION'] = 'sync';
                $cfg['CACHE_STORE'] = 'file';
                $cfg['APP_URL'] = 'http://'.$request->getHttpHost();
                rewrite_env($cfg);
            }
            return redirect('https://'.$request->getHttpHost().'/install');
        }
        $modules = collect(get_module())->where('name', '!=', 'halaman')->where('public', true);
        foreach ($modules as $modul) {
            $attr['post_type'] = $modul->name;

            if ($modul->web->index && $request->is($modul->name)) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'index';
                $attr['view_path'] = $modul->name . '.index';
                config([
                    'modules.current' => $attr
                ]);
            }
            if ($modul->web->detail && $request->is($modul->name . '/*') && empty($request->segment(3))) {
                $attr['detail_visited'] = true;
                $attr['view_type'] = 'detail';
                $attr['view_path'] = $modul->name . '.detail';
                config([
                    'modules.current' => $attr
                ]);
            }
            if ($modul->form->category && $request->is($modul->name . '/category/*')) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'category';
                $attr['view_path'] = $modul->name . '.category';
                config([
                    'modules.current' => $attr
                ]);
            }

            if ($modul->web->archive && ($request->is($modul->name . '/archive') || $request->is($modul->name . '/archive/*') || $request->is($modul->name . '/archive/*/*') || $request->is($modul->name . '/archive/*/*/*'))) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'archive';
                $attr['view_path'] = $modul->name . '.archive';
                config([
                    'modules.current' => $attr
                ]);
            }
            if ($modul->form->post_parent && ($request->is($modul->name . '/' . $modul->form->post_parent[1]) || $request->is($modul->name . '/' . $modul->form->post_parent[1] . '/*'))) {
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'post_parent';
                $attr['view_path'] = $modul->name . '.post_parent';
                config([
                    'modules.current' => $attr
                ]);
            }
        }


        if ($request->is('*') && !in_array($request->segment(1), array_merge([admin_path()],$modules->pluck('name')->toArray()))) {
            $attr['post_type'] = 'halaman';
            $attr['detail_visited'] = true;
            $attr['view_type'] = 'detail';
            $attr['view_path'] = 'halaman.detail';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('search') || $request->is('search/*')) {
            $attr['post_type'] = null;
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'search';
            $attr['view_path'] = 'search';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('author') || $request->is('author/*')) {
            if($request->is('author')){
                $attr['post_type'] = null;
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'author.index';
                $attr['view_path'] = 'author.index';
                config([
                    'modules.current' => $attr
                ]);
            }else{
                $attr['post_type'] = null;
                $attr['detail_visited'] = false;
                $attr['view_type'] = 'author.detail';
                $attr['view_path'] = 'author.detail';
                config([
                    'modules.current' => $attr
                ]);
            }

        }
        if ($request->is('tags/*')) {
            $attr['post_type'] = null;
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'tags';
            $attr['view_path'] = 'tags';
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is(['sitemap.xml','swk.js','site.manifest'])) {
            $attr['detail_visited'] = false;
            config([
                'modules.current' => $attr
            ]);
        }
        if ($request->is('/')) {
            $attr['post_type'] = null;
            $attr['detail_visited'] = false;
            $attr['view_type'] = 'home';
            $attr['view_path'] = 'home';
            config([
                'modules.current' => $attr
            ]);
        }
        if($o = config('modules.current.detail_visited')){
            ratelimiter($request,get_option('time_limit_reload'));
    }
    forbidden($request,config('modules.current.detail_visited'));
    return $next($request);

    }

}
