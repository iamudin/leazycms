<?php

namespace Leazycms\Web\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Post;
use Leazycms\Web\Models\Option;
use Leazycms\FLC\Models\Comment;
use Leazycms\Web\Models\Visitor;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Leazycms\FLC\Models\File as Flc;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class PanelController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth')
        ];
    }

    function files()
    {
        return view('cms::backend.files.index');
    }

    function menu_target(Request $request)
    {
        $search = $request->q ? strip_tags($request->q) : null;
        $type = collect(get_module())->where('web.detail', '=',true)->pluck('name')->toArray();
        return query()
            ->whereIn('type', $type)
            ->select('url', 'title')
            ->where('title', 'like', "%{$search}%")
            ->orWhere('url', 'like', "{$search}%")
            ->published()
            ->limit(10)
            ->get();
    }
    function get_comments(Request $request,Post $post){
        $data = $post->load('comments');
        return $data;

    }
    function comments(Request $request,Comment $comment)
    {
        if($request->isMethod('delete')){
            $comment->delete();
        }

        if($request->isMethod('post')){
        $data = Comment::with('user')->latest();
         return Datatables::of($data)
            ->addIndexColumn()
            ->filter(
                function ($instance) use ($request) {

                }
            )
            ->addColumn('created_at', function ($row) {
                return '<code>' . Carbon::parse($row->created_at)->diffForHumans() . '</code>';
            })
           ->addColumn('content', function ($row) {
                return '<p>'.$row->content.'</p>';
            })
             ->addColumn('reference', function ($row) {
                return '<a target="_blank" href="'.$row->reference.'">'.$row->reference.'</a>';
            })
            ->addColumn('sender', function ($row) {
                $sender = "<small>";
                $sender .= '<i class="fa fa-user"></i> '.$row->name;
                $sender .= '<br><i class="fa fa-envelope"></i> '.($row->email ?? '-');
                $sender .= '<br><i class="fa fa-link"></i> '.($row->link ?? '-');
                $sender .= '<br><i class="fa fa-globe"></i> '.($row->ip ?? '-');
                $sender .= "</small>";
                
                return $sender;
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<button onclick="deleteAlert(\''.route('comments',$row->id).'\')" class="btn btn-sm btn-danger fa fa-trash-alt"></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['created_at','sender','action','content','DT_RowIndex','reference'])
            ->toJson();
        }

        return view('cms::backend.comments.index');
    }
    protected function toDashboard($request)
    {
        if (!$request->segment(2))
            return to_route('panel.dashboard')->send();
    }
    function index(Request $request)
    {
        $user = $request->user();
        $posts = $user->isAdmin() ? Post::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray() : Post::whereBelongsTo($user)->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray();
        $da = array();
        for ($i = 0; $i <= 6; $i++) {
            array_push($da, date("Y-m-d", strtotime("-" . $i . " days")));
        }

        $weekago = json_decode(json_encode(collect($da)->sort()), true);

        // Melakukan query untuk menghitung pengunjung berdasarkan tanggal
        $visitorCounts = Visitor::whereIn(DB::raw('DATE(created_at)'), $da)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date')
            ->toArray();

        // Pastikan bahwa array $visitorCounts berisi semua tanggal yang diinginkan
        $visitorCounts = array_replace(array_fill_keys($da, 0), $visitorCounts);
        $type = collect(get_module())->where('name', '!=', 'media')->pluck('name')->toArray();
        $lastpublish = Post::select(['created_at', 'id', 'user_id', 'status', 'type', 'title'])->with('user')->whereIn('type', $type)->latest('created_at')->limit(5)->get();
        return view('cms::backend.dashboard', [
            'latest' => $lastpublish,
            'weekago' => $weekago,
            'type' => $user->isAdmin() ? collect(get_module()) : collect(get_module())->whereIn('name', $user->get_modules->pluck('module')->toArray())->where('public', true),
            'posts' => $posts,
            'visitor' => $visitorCounts
        ]);
    }
    function generate_key(){

        $key = Str::random(32);
        rewrite_env(['ENV_KEY'=> $key]);
        return $key;
    }
    public function apikey(Request $request){
        admin_only();
  

        if ($request->isMethod('post')) {

            $envKey = config('modules.env_key');

            $wasEncrypted = file_exists(base_path('.env.encrypted'));
            // Jika terenkripsi → decrypt dulu
            if ($wasEncrypted) {
             Artisan::call('env:decrypt', [
                    '--force' => true,
                    '--key' => $envKey,
                ]);
            }

            if (app()->configurationIsCached()) {
                Artisan::call('config:clear');
                $key = $this->generate_key();
                Artisan::call('config:cache');
                if ($wasEncrypted) {
                    Artisan::call('env:encrypt', [
                        '--force' => true,
                        '--key' => $key,
                    ]);
                    $envFile = base_path('.env');
                    if (file_exists($envFile)) {
                        unlink($envFile);
                    }
                }

            } else {
                $this->generate_key();
            }

            

            return to_route('apikey')->with('success', 'APP_KEY berhasil digenerate ulang!');
        }
        return view('cms::backend.apikey', ['key'=>config('modules.env_key') ? md5(enc64(config('modules.env_key'))) : null]);
    }
    public function visitor(Request $request)
    {
        $da = array();
        for ($i = 0; $i <= 6; $i++) {
            array_push($da, date("Y-m-d", strtotime("-" . $i . " days")));
        }

        $data = Visitor::whereIn(DB::raw('DATE(created_at)'), $da)->latest('created_at');
        return Datatables::of($data)
            ->addIndexColumn()
            ->filter(
                function ($instance) use ($request) {

                    if ($time = $request->timevisit) {
                        $instance->whereDate('created_at', $time);
                    }
                    if ($search = $request->search) {
                        $instance->where('page','like', '%'.$search.'%')->orWhere('reference','like', '%'.$search.'%');
                    }
                }
            )
            ->addColumn('created_at', function ($row) {
                return '<code>' . Carbon::parse($row->created_at)->diffForHumans() . '</code>';
            })
            ->addColumn('ip_location', function ($row) {
                $city = json_decode($row->ip_location)->city ?? null;
                $country = json_decode($row->ip_location)->country ?? null;
                $region = json_decode($row->ip_location)->region ?? null;
                $code = json_decode($row->ip_location)->countryCode ?? null;
                $ipinfo = $row->ip_location ? $region . ', ' . $city . '<br><img style="display:inline" height="10" src="' . url('backend/images/flags/' . str($code)->upper() . '.svg') . '"> ' . $country : 'N/A';
                return '<span class="badge badge-info">' . $row->ip . '</span><br><small>' . $ipinfo . '</small>';
            })
            ->addColumn('reference', function ($row) {
                return str($row->reference)->limit(70);
            })
            ->addColumn('times', function ($row) {
                return $row->times;
            })
            ->addColumn('status', function ($row) {
                return $row->status == '200' ? '<span class="badge badge-success">200</span>' : '<span class="badge badge-danger">404</span>';
            })
            ->addColumn('page', function ($row) {
                return '<a href="' . $row->page . '">' . str($row->page)->limit(70) . '</a>';
            })
            ->rawColumns(['created_at', 'ip_location', 'reference', 'page','status'])
            ->toJson();
    }
    public function option(Request $request, $slug=null)
    {

        $data = config('modules.config.option.'._us($slug));
        if (empty($data)) {
            return to_route('panel.dashboard');
        }

        if ($request->isMethod('post')) {
            $option = new Option;
                foreach ($data as $field) {
                    $key = _us($field[0]);
                    if ($request->$key) {
                        if($field[1]=='file'){
                            if($request->hasFile(_us($field[0]))){
                            $value = (new Flc)->addFile([
                                    'file'=>$request->file(_us($field[0])),
                                    'purpose'=> _us($field[0]),
                                    'mime_type'=> explode(',',$field[2]),
                                    'self_upload'=> true,
                                    ]);
                           $option->updateOrCreate(['name' => $key], ['value' => $value, 'autoload' => 1]);
                            }
                        }else{
                            $option->updateOrCreate(['name' => $key], ['value' => $request->$key, 'autoload' => 1]);
                        }

                    }
            }
            return back()->with('success', 'Berhasil Diupdate');
        }
        return view('cms::backend.option',compact('data','slug'));
    }
    public function setting(Request $request, Option $option)
    {

        admin_only();
        $data['web_type'] = config('modules.config.web_type');
        $data['option'] =  [
            ['Nama', 'text'],
            ['Deskripsi', 'text'],
            ['Alamat', 'text'],
            ['Telepon', 'text'],
            ['Whatsapp', 'text'],
            ['Fax', 'text'],
            ['Email', 'text'],
            ['Latitude', 'text'],
            ['Longitude', 'text'],
            ['Link Maps', 'text'],
            ['Facebook', 'text'],
            ['Youtube', 'text'],
            ['Instagram', 'text'],
            ['Twitter', 'text'],
            ['Icon', 'file'],
        ];
        $data['site_attribute'] = array(
            ['Alamat Situs Web', 'site_url', 'text'],
            ['Nama Situs Web', 'site_title', 'text'],
            ['Deskripsi Situs Web', 'site_description', 'text'],
            ['SEO Meta Keyword', 'site_meta_keyword', 'text'],
            ['SEO Meta Description', 'site_meta_description', 'text'],
            ['Google Analytics Code', 'google_analytics_code', 'text'],
            ['Postingan Perhalaman', 'post_perpage', 'number'],
            ['Logo', 'logo', 'file'],
            ['Favicon (Gambar PNG/JPG rasio 1:1 maks 2mb)', 'favicon', 'file'],
            ['Preview', 'preview', 'file'],
        );
        $data['pwa'] = array(
            ['Nama Aplikasi', 'pwa_name', 'text'],
            ['Singkatan', 'pwa_short_name', 'text'],
            ['Deskripsi', 'pwa_description', 'text'],
            ['Warna Background', 'pwa_background_color', 'text'],
            ['Warna Tema', 'pwa_theme_color', 'text'],
            ['Icon (format png ukuran 512px * 512px)', 'pwa_icon_512', 'file'],
            ['Icon (format png ukuran 180px * 180px)', 'pwa_icon_180', 'file'],
            ['Icon (format png ukuran 32px * 32px)', 'pwa_icon_32', 'file'],
            ['Icon (format png ukuran 16px * 16px)', 'pwa_icon_16', 'file'],
        );
        $data['shortcut'] = array(
            ['Control + F5', 'ctrl_f5'],
            ['Control + U', 'ctrl_u'],
            ['Control + R', 'ctrl_r'],
            ['Control + P', 'ctrl_p'],
            ['Control + S', 'ctrl_s'],
            ['Right Click', 'right_click'],
            ['Frame Embed', 'frame_embed'],
            ['Preloader Effect', 'preload'],
            ['Default JQuery Min', 'default_jquery'],
        );
        $data['security'] = array(

            ['Block IP', '0.0.0.0,0.0.1.0,..,..'],
            ['Allow IP', '0.0.0.0,0.0.1.0,..,..'],
            ['Forbidden Keyword', 'Judi Online, Gacor, xxx, other'],
            ['Forbidden Redirect', 'Eg: https://yourpage.url or other'],
            ['Time Limit Login', 'default 10 times'],
            ['Time Limit Reload', 'default 10 times'],
            ['Limit Duration', 'in minute default 1 minute'],
            ['Roles', 'operator,editor,publisher']
        );

        $data['home'] = array_map([File::class, 'basename'], File::glob(resource_path('views/template/' . template() . '/home-*.blade.php')));
        if ($request->isMethod('POST')) {

            if ($hp = $request->home_page) {
                if (in_array($hp, array_merge(['default'], $data['home']))) {
                    $fid = $option->updateOrCreate(['name' => 'home_page'], ['value' => $hp, 'autoload' => 1]);
                }
            }
            foreach ($data['option'] as $row) {
                $key = _us($row[0]);

                if ($row[1] == 'file') {
                    $fid = $option->updateOrCreate(['name' => $key], ['value' => get_option($key), 'autoload' => 1]);
                    if ($request->hasFile($key)) {
                        $fid->update([
                            'value' => $fid->addFile([
                                'file' => $request->file($key),
                                'purpose' => $key,
                                'mime_type' => ['image/png', 'image/jpeg'],
                            ])
                        ]);
                    }
                } else {
                    $value = $request->$key;
                    $fid = $option->updateOrCreate(['name' => $key], ['value' => strip_tags($value), 'autoload' => 1]);
                }
            }
            foreach (array_merge($data['security'], [['Site Maintenance', '']]) as $row) {
                $key = _us($row[0]);
                $value = $request->$key ?? null;

                if ($key == 'block_ip') {
                    $request->validate(['block_ip' => 'nullable|ip']);
                }

                $option->updateOrCreate(['name' => $key], ['value' => strip_tags($value), 'autoload' => 1]);
            }

            foreach ($data['site_attribute'] as $row) {
                $key = $row[1];
                if ($row[2] == 'file') {
                    $request->validate([$key => 'nullable|file']);
                    $fid = $option->updateOrCreate(['name' => $key], ['value' => get_option($key), 'autoload' => 1]);
                    if ($value = $request->hasFile($key)) {

                        if ($key == 'favicon') {
                            if ($request->hasFile('favicon')) {
                                $file = $request->file('favicon');

                                // Validasi MIME dan ekstensi
                                $allowedMime = ['image/x-icon', 'image/vnd.microsoft.icon'];
                                $allowedExt = ['ico'];

                                $mime = $file->getMimeType();
                                $ext = strtolower($file->getClientOriginalExtension());

                                if (!in_array($mime, $allowedMime) || !in_array($ext, $allowedExt)) {
                                    return back()->with('danger', 'Hanya file .ico yang diperbolehkan.');
                                }

                                // Cek ukuran gambar
                                $size = getimagesize($file->getRealPath());
                                if ($size === false) {
                                    return back()->with('danger', 'File favicon tidak valid.');
                                }

                                if ($size[0] !== 64 || $size[1] !== 64) {
                                    return back()->with('danger', 'Favicon harus berukuran tepat 64x64 piksel.');
                                }

                                // Simpan ke public/favicon.ico
                                $destination = public_path('favicon.ico');
                                if (file_exists($destination)) {
                                    unlink($destination);
                                }

                                $file->move(public_path(), 'favicon.ico');

                            }

                        } else {

                            $fid->update([
                                'value' => $fid->addFile([
                                    'file' => $request->file($key),
                                    'purpose' => $key,
                                    'mime_type' => ['image/png', 'image/jpeg'],
                                ])
                            ]);
                        }
                    }
                } else {
                    $value = $request->$key;
                    $option->updateOrCreate(['name' => $key], ['value' => strip_tags($value), 'autoload' => 1]);
                }
            }

            foreach ($data['pwa'] as $row) {
                $key = $row[1];
                if ($row[2] == 'file') {
                    $request->validate([$key => 'nullable|file|mimetypes:image/png,image/webp']);

                    $fid = $option->updateOrCreate(['name' => $key], ['value' => get_option($key), 'autoload' => 1]);
                    if ($value = $request->hasFile($key)) {
                        $res = explode('_', $key)[count(explode('_', $key)) - 1];
                        $filename = $fid->addFile([
                            'file' => $request->file($key),
                            'purpose' => $key,
                            'mime_type' => ['image/png','image/webp'],
                            'width' => $res,
                            'height' => $res
                        ]);
                        $fid->update([
                            'value' => $filename
                        ]);
                    }
                } else {
                    $value = $request->$key;
                    $option->updateOrCreate(['name' => $key], ['value' => strip_tags($value), 'autoload' => 1]);
                }
            }
            foreach ($data['shortcut'] as $row) {
                $key = $row[1];
                $value = $request->$key;
                if ($value && in_array($value, ['Y', 'N'])) {
                    $option->updateOrCreate(['name' => $key], ['value' => strip_tags($value), 'autoload' => 1]);
                }
            }

            if ($request->app_env && in_array($request->app_env, ['production', 'local'])) {
                $app_env = $request->app_env;
                if ($existsenv = get_option('app_env')) {
                    if ($existsenv != $app_env) {
                        $option->updateOrCreate(['name' => 'app_env'], ['value' => $app_env, 'autoload' => 1]);
                        rewrite_env(['APP_ENV' => $app_env]);
                    }
                } else {
                    $option->updateOrCreate(['name' => 'app_env'], ['value' => $app_env, 'autoload' => 1]);
                    rewrite_env(['APP_ENV' => $app_env]);
                }
            }
            if (!app()->routesAreCached()) {
                if ($request->admin_path) {
                if (get_option('admin_path') != $request->admin_path) {
                    $val = trim(str($request->admin_path)->slug());
                    if (strlen($val) <= 5 || in_array($val, not_allow_adminpath()) || is_numeric($val)) {
                        return back()->send()->with('danger', 'Login path dengan kata kunci "' . $val . '" tidak diizinkan');
                    }
                    $option->updateOrCreate(['name' => 'admin_path'], ['value' => $val, 'autoload' => 1]);
                    return redirect()->to($request->admin_path . '/setting')->send()->with('success', 'Berhasil Diperbarui');
                }else{
                    return back()->send()->with('success', 'Berhasil Diperbarui');
                }
            }else{
                return back()->send()->with('danger', 'Admin Path tidak boleh kosong');

            }
        }

            return to_route('setting')->with('success', 'Pengaturan berhasil diperbarui');
        }
        return view('cms::backend.setting', $data);
    }
    function admin_path(Request $request,$path){
        $pathnew = base64_decode($path);
        if ($pathnew && $pathnew != admin_path()) {
            Artisan::call('route:cache');
            return redirect()->to(secure_url($pathnew.'/setting'));
        }
    }

    function unconfiguredCache(){

        $envKey = config('modules.env_key');

        $wasEncrypted = file_exists(base_path('.env.encrypted'));
        // Jika terenkripsi → decrypt dulu
        if ($wasEncrypted) {
            Artisan::call('env:decrypt', [
                '--force' => true,
                '--key' => $envKey,
            ]);
        }

        if (app()->configurationIsCached()) {
            Artisan::call('config:clear');
        } 
    }
    function reconfiguredCache()
    {

        $envKey = config('modules.env_key');

        $wasEncrypted = file_exists(base_path('.env.encrypted'));
        // Jika terenkripsi → decrypt dulu
        if ($wasEncrypted) {
            Artisan::call('env:decrypt', [
                '--force' => true,
                '--key' => $envKey,
            ]);
        }

        if (!app()->configurationIsCached()) {
            Artisan::call('config:cache');
            if($wasEncrypted){
                $envFile = base_path('.env');
                if (file_exists($envFile)) {
                    unlink($envFile);
                }
            }
        }
    }
    public function cache(Request $request)
    {
        admin_only();
        if ($request->isMethod('post')) {
            if ($request->cache_config && $request->cache_config == 'Y' && !app()->configurationIsCached()) {
                $this->reconfiguredCache();
            }

            if ($request->cache_config && $request->cache_config == 'N' && app()->configurationIsCached()) {
                $this->unconfiguredCache();
            }
            if ($request->cache_route && $request->cache_route == 'Y' && !app()->routesAreCached()) {
                Artisan::call('route:cache');
            }
            if ($request->cache_route && $request->cache_route == 'N' && app()->routesAreCached()) {
                Artisan::call('route:clear');
            }
            if ($request->cache_media && $request->cache_media == 'Y' && !cache()->has('media')) {
                media_caching();
                recache_menu();
                regenerate_cache();
                recache_banner();
            }
            if ($request->cache_media && $request->cache_media == 'N' && cache()->has('media')) {
                Cache::forget('media');
            }
            return back()->send()->with('success', 'Berhasil di optimalkan');
        }
        return view('cms::backend.cache');
    }
    public function appearance(Request $request, Option $option)
    {

        admin_only();
        if($request->act && $request->act=='updatetemplate'){
            Artisan::call('cms:update-template '.template());
            return back()->send()->with('success','Template Berhasil diupdate');
        }
        if ($request->isMethod('post')) {
            if ($file = $request->file('template')) {
                $request->validate([
                    'template' => 'required|file|mimes:zip',
                ]);
                return $this->template_uploader($file);
            }
            if ($request->template_setting) {
                $ar_ta = config('modules.config.option.template_asset') ?? null;
                   foreach ($ar_ta as $field) {
                    $key = _us($field[0]);
                    if ($request->$key) {
                        if($field[1]=='file'){
                            if($request->hasFile(_us($field[0]))){
                            $value = (new Flc)->addFile([
                                    'file'=>$request->file(_us($field[0])),
                                    'purpose'=> _us($field[0]),
                                    'width'=> 1700,
                                    'mime_type'=> explode(',',$field[2]),
                                    'self_upload'=> true,
                                    ]);
                           $option->updateOrCreate(['name' => $key], ['value' => $value, 'autoload' => 1]);
                            }
                        }else{
                            $option->updateOrCreate(['name' => $key], ['value' => $request->$key, 'autoload' => 1]);
                        }

                    }
            }
                return back()->send()->with('success', 'Berhasil diupdate');
            }
        }
        return view('cms::backend.appearance');
    }
    public function template_uploader($file)
    {
        // Simpan file zip secara sementara
        $zipFilePath = $file->getRealPath();

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath) === TRUE) {
            // Ekstrak file ZIP ke direktori sementara
            $extractPath = storage_path('app/temp');
            $zip->extractTo($extractPath);
            $zip->close();

            // Dapatkan nama folder utama di dalam ZIP (temaku)
            $mainFolderName = '';
            $extractedFolder = scandir($extractPath);
            foreach ($extractedFolder as $folder) {
                if ($folder !== '.' && $folder !== '..') {
                    $mainFolderName = $folder;
                    break;
                }
            }

            // Cek apakah folder induk dan subfolder assets ada
            if (empty($mainFolderName) || !File::exists($extractPath . '/' . $mainFolderName . '/assets')) {
                // Hapus folder sementara
                File::deleteDirectory($extractPath);

                // Batalkan upload dan kembalikan respon error
                return back()->with('danger', 'File Template Tidak Valid');
            }

            // Path sumber dari folder temaku
            $sourcePath = $extractPath . '/' . $mainFolderName;

            // Path tujuan untuk resource_path
            $templatePath = resource_path('views/template/' . $mainFolderName . '/');

            // Pastikan direktori target ada
            File::ensureDirectoryExists($templatePath);

            // Pindahkan semua file dan folder kecuali "assets" ke resource_path('template')
            $items = new \FilesystemIterator($sourcePath);
            foreach ($items as $item) {
                $itemName = $item->getFilename();
                if ($itemName !== 'assets') {
                    $targetPath = $templatePath . '/' . $itemName;
                    if ($item->isDir()) {
                        File::copyDirectory($item->getPathname(), $targetPath);
                    } else {
                        File::copy($item->getPathname(), $targetPath);
                    }
                }
            }

            // Pindahkan isi folder assets ke public_path('template/temaku')
            $assetsSourcePath = $sourcePath . '/assets';
            $assetsDestinationPath = public_path('template/' . $mainFolderName);

            if (File::exists($assetsSourcePath)) {
                File::ensureDirectoryExists($assetsDestinationPath);
                File::copyDirectory($assetsSourcePath, $assetsDestinationPath);
            }
            $assetsResourcePath = $templatePath . '/assets';
            if (File::exists($assetsResourcePath)) {
                File::deleteDirectory($assetsResourcePath);
            }
            // Hapus file sementara dan folder setelah pemindahan
            File::deleteDirectory($extractPath);
            \Leazycms\Web\Models\Option::where('name','template')->update([
                    'value'=>$mainFolderName
                ]);
            return to_route('appearance');
        } else {
            return back()->with('danger', 'Template Gagal Diupload');
        }
    }

    public function editorTemplate(Request $request)
    {

        if(!is_local() && !Cache::has('enablededitortemplate')){
            return to_route('appearance');
        }
        admin_only();
        $defaultfile = enc64('/home.blade.php');
        $path = resource_path('views/template/' . template());
        if (!file_exists($path . dec64($defaultfile))) {
            File::put($path . dec64($defaultfile), '<h1>Your Script Here</h1>');
        }
        $file = $request->edit ? dec64($request->edit) : dec64($defaultfile);

        if ($file == '/styles.css') {
            $file = '/styles.css';
            $path = public_path('template/' . template());
            if (!is_dir($path)) {
                mkdir($path);
            }
            if (!file_exists($path . $file)) {
                File::put($path . $file, 'html,body{}');
            }
        } elseif ($file == '/scripts.js') {
            $file = '/scripts.js';
            $path = public_path('template/' . template());
            if (!is_dir($path)) {
                mkdir($path);
            }
            if (!file_exists($path . $file)) {
                File::put($path . $file, '/*You JS Here*/');
            }
        } else {
        }
        if ($request->isMethod('post')) {
            switch ($request->type) {
                case 'create_dir':
                    $dir = str($request->dirname)->slug();
                    if (!is_dir($path . '/' . $dir)) {
                        mkdir($path . '/' . $dir);
                        return response()->json(['msg' => 'success']);
                    }
                    break;
                case 'create_file':
                    $filepath = $request->filepath ?? null;
                    $filename = $request->filename == 'index' ? 'index.blade.php' : str($request->filename)->slug() . '.blade.php';
                    if (!file_exists($path . $filepath . '/' . $filename)) {
                        $myfile = fopen($path . $filepath . '/' . $filename, "w") or die("Unable to open file!");
                        fwrite($myfile, '<h1>You Script Here</h1>');
                        fclose($myfile);
                        File::put($path . $filepath . '/' . $filename, 'You Script Here');

                        return response()->json(['msg' => 'success']);
                    }
                    break;
                case 'delete_file':
                    $filename = $request->filename;
                    if (strpos($filename, 'modules.blade.php') !== false) {
                        return to_route('appearance')->with('danger', 'Action denied!');
                    }
                    if (file_exists($path . $filename)) {
                        unlink($path . $filename);
                        return response()->json(['msg' => 'success']);
                    }
                    break;
                case 'change_file':
                    if ($content = $request->file_src) {
                        $data = $content;
                        $file = $path  . $file;
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if ($ext == 'php') {
                            if (basename($file) == 'modules.blade.php') {
                                Cache::put('tempmodules', file_get_contents($file));
                                if (File::put($file,  $content)) {
                                    $phpCode = File::get($file);
                                    try {
                                        ob_start();
                                        eval('?>' . $phpCode);
                                        ob_end_clean();
                                    } catch (\ParseError $e) {
                                        File::put($file, Cache::get('tempmodules'));
                                        return back()->with('danger', 'PHP script modules is wrong!');
                                    }
                                } else {
                                    return back()->with('danger', 'Failed write modules script!');
                                }
                            } else {
                                try {
                                    File::put($file, $content);
                                } catch (\Exception $e) {
                                    return back()->with('danger', 'Failed write file : ' . $e->getMessage());
                                }
                            }
                        } else {
                            $myfile = fopen($file, "w") or die("Unable to open file!");
                            fwrite($myfile, $data);
                            fclose($myfile);
                        }
                        return response()->json(['msg' => 'success', 'file' => $file]);
                    }
                    break;


            }
        }
        $src = $file && file_exists($path . $file) && is_file($path . $file) ? (file_get_contents($path . $file) ? file_get_contents($path . $file) : 'Here You Script') : null;
        if (!$src) {
            return to_route('appearance.editor')->with('danger', 'Source tidak ditemukan!');
        }
        $type = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'php' => 'application/x-httpd-php',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'json' => 'application/json',
            default => 'application/x-httpd-php'
        };

        return view('cms::backend.editortemplate', ['view' => $src, 'type' => $type]);
    }

    function backup_restore(Request $request)
    {
        return to_route('panel.dashboard');
        try {
            Artisan::call('backup:list');
            $output = Artisan::output();

            // Pisahkan hasil output menjadi baris dan kolom
            $lines = explode(PHP_EOL, $output);
            $data = [];

            foreach ($lines as $line) {
                if (strpos($line, '|') !== false) {
                    $data[] = array_map('trim', explode('|', $line));
                }
            }

            // Simpan ke dalam file atau database, atau kembalikan sebagai hasil command
            // Storage::put('backup_list.json', json_encode($data));


            return $this->downloadLatestBackup();

            return response()->json(['message' => 'Backup successfully created.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Backup failed: ' . $e->getMessage()], 500);
        }
        return view('cms::backend.backup-restore');
    }

    function downloadLatestBackup()
    {
        // Dapatkan semua destinasi backup yang dikonfigurasi
        $backupDestinations = BackupDestinationFactory::createFromArray(config('backup.backup.destination.disks'));

        // Asumsikan hanya ada satu disk tujuan backup, ambil yang pertama
        $backupDestination = $backupDestinations[0];
        $backupFiles = $backupDestination->backupFiles();

        // Dapatkan file backup terbaru
        $latestBackupFile = $backupFiles->sortByDesc->date()->first();

        if (!$latestBackupFile) {
            return redirect()->back()->with('error', 'No backup files found.');
        }

        // Siapkan file untuk diunduh
        $disk = Storage::disk($backupDestination->diskName());
        $filePath = $latestBackupFile->path();
        $fileName = $latestBackupFile->fileName();

        if ($disk->exists($filePath)) {
            return $disk->download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File not found.');
    }


}
