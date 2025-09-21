<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;
use Leazycms\Web\Http\Controllers\ServiceMonitor;
use Cache;
use Auth;
class AppMasterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['status','loginFromMonitor','get_login_token']),
        ];
    }
    // app/Http/Controllers/MasterController.php

    function get_login_token($request){
        $tokenValue = $request->query('token');
        if (!$tokenValue) {
            return response()->json(['msg' => 'Token Unavailable'],400);
        }
        if($tokenValue != md5(enc64(config('app.key')))){
            return response()->json(['msg' => 'Invalid Token'],400);
        }
        $user = \Leazycms\Web\Models\User::where('level','admin')->first();
        $token = \Leazycms\Web\Models\OneTimeToken::generate($user->id);
        return response()->json([
            'success' => true,
            'redirect_url' => url(md5(enc64(parse_url(config('app.url'), PHP_URL_HOST))) . "?type=auth&token=" . $token)
        ]);
     

    }
 

    public function loginFromMonitor($request)
    {
        $tokenValue = $request->query('token');
        if (!$tokenValue) {
            return redirect('/');
        }

        $token = \Leazycms\Web\Models\OneTimeToken::where('token', $tokenValue)
            ->where('expires_at', '>', now())
            ->first();
        if (!$token) {
            return redirect('/');
        }

        // login user
        Auth::login($token->user);
        $token->user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'active_session' => md5(md5($request->session()->id())),
        ]);
        // hapus token biar 1x pakai
        $token->delete();

        // redirect ke halaman tujuan
        return redirect()->route('panel.dashboard')
            ->with('success', 'Login otomatis dari Monitoring berhasil.');
    }
    public function index(ServiceMonitor $service)
    {
        if (!config('modules.app_master')) {
            return to_route('panel.dashboard');
        }
        // Cache biar tidak terlalu sering hit API
        return view('cms::backend.master.sites');
    }
    public function fetch(Request $request, ServiceMonitor $service)
    {
        if (!config('modules.app_master')) {
            return to_route('panel.dashboard');
        }
        if($request->type && in_array($request->type, ['autoauth'])) {
            if($request->type == 'autoauth'){
                $id = $request->query('id');
                $token = $request->query('token');
                $site = query()->onType(    'sites')->published()->findOrFail($id);
                if (!$site) {
                    return response()->json(['success' => false, 'message' => 'Site not found'], 404);
                }
                try {
                    if (!$token) {
                        return response()->json(['error' => 'Invalid API Key'], 401);
                    }
                    $response = Http::withHeaders([
                        'User-Agent' => md5(enc64($site->title))
                    ])
                        ->timeout(6)
                        ->connectTimeout(3)
                        ->get("http://{$site->title}/" . md5(enc64($site->title)), [
                            'token' => $token,
                            'type' => 'gettoken',
                        ]);
                    if ($response->failed()) {
                        return response()->json(['success' => false, 'message' => 'Failed to contact the site.'], 500);
                    }
                    $data = $response->json();

                    return response()->json($data);
                } catch (\Throwable $e) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
                }
            }
            return $this->status($request);
        }
        // Cache biar tidak terlalu sering hit API
        $data = Cache::remember('site_status', 15, fn() => $service->fetchAll());

        return response()->json($data);
    }

    public function status($request)
    {
        if ($request->type && in_array($request->type, ['maintenance', 'editor', 'auth','gettoken'])) {
            if ($request->type == 'maintenance') {
                if ($request->status == '1') {
                    // Aktifkan mode maintenance
                    \Leazycms\Web\Models\Option::updateOrCreate(
                        ['name' => 'site_maintenance'],
                        ['value' => 'N']
                    );
                    if (app()->configurationIsCached()) {
                        \Artisan::call('config:cache');
                    }
                } else {
                    // Nonaktifkan mode maintenance
                    \Leazycms\Web\Models\Option::updateOrCreate(
                        ['name' => 'site_maintenance'],
                        ['value' => 'Y']
                    );
                    if (app()->configurationIsCached()) {
                        \Artisan::call('config:cache');
                    }
                }
                return response()->json(['success' => true]);
            } elseif ($request->type == 'editor') {
                if ($request->status == '0') {
                    // Aktifkan mode editor
                    Cache::put('enablededitortemplate', true, 60 * 60 * 24 * 30); // Simpan 30 hari
                } else {
                    // Nonaktifkan mode editor
                    Cache::forget('enablededitortemplate');
                }
                return response()->json(['success' => true]);
            } elseif ($request->type == 'auth') {
                return $this->loginFromMonitor($request);

            } elseif ($request->type == 'gettoken') {
                return $this->get_login_token($request);
            }
        }

            $data = [
                'user_count' => \Leazycms\Web\Models\User::count(),
                'editor_template_enabled' => Cache::has('enablededitortemplate') ? true : false,
                'maintenance' => get_option('site_maintenance') == 'Y' ? true : false,
                'api_key' => md5(enc64(config('app.key'))),
                'active_modules' => collect(get_module())->pluck('title')->toArray(),
            ];
            return response()->json($data);
    }
        function update(Request $request)
        {
        if (!config('modules.app_master')) {
            return to_route('panel.dashboard');
        }
            $id = $request->id;
            $status = $request->status;
            $type = $request->type;
            $item = \Leazycms\Web\Models\Post::onType('sites')->find($id);
            if ($item) {
                if ($type == 'maintenance') {
                    Http::withHeaders([
                        'User-Agent' => md5(enc64($item->title))
                    ])
                        ->timeout(6)
                        ->connectTimeout(3)
                        ->get("http://{$item->title}/". md5(enc64($item->title)), [
                            'type' => 'maintenance',
                            'status' => $status,
                        ]);
                } elseif ($type == 'editor') {
                    Http::withHeaders([
                        'User-Agent' => md5(enc64($item->title))
                    ])
                        ->timeout(6)
                        ->connectTimeout(3)
                        ->get("http://{$item->title}/". md5(enc64($item->title)), [
                            'type' => 'editor',
                            'status' => $status,
                        ]);
                }
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'Site not found']);
        }
    public function refresh(ServiceMonitor $service)
    {
        if(!config('modules.app_master')){
            return to_route('panel.dashboard');
        }
        $data = $service->fetchAll();
        Cache::put('site_status', $data, 15);

        return response()->json($data);
    }

    function datatable(Request $request)
    {
        $data = \Leazycms\Web\Models\Post::onType('sites')->latest();
        return datatables()->of($data)
            ->addIndexColumn()
            ->addColumn('domain', function ($row) {
                return $row->title;
            })
            ->addColumn('domain', function ($row) {
                return $row->title;
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('app.master.edit', $row->id) . '" class="edit btn btn-primary btn-sm">Edit</a> ';
                $btn .= '<a href="' . route('app.master.upgrade', $row->id) . '" class="edit btn btn-warning btn-sm">Upgrade</a> ';
                return $btn;
            })
            ->rawColumns(['action'])
            ->toJson();
    }
}