<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;
use Leazycms\Web\Http\Controllers\ServiceMonitor;
use Cache;
class AppMasterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['status'])
        ];
    }

    public function index(ServiceMonitor $service)
    {
        // Cache biar tidak terlalu sering hit API
        return view('cms::backend.master.sites');
    }
    public function fetch(ServiceMonitor $service)
    {
        // Cache biar tidak terlalu sering hit API
        $data = Cache::remember('site_status', 15, fn() => $service->fetchAll());

        return response()->json($data);
    }

    public function status($request)
    {
        if ($request->type && in_array($request->type, ['maintenance', 'editor'])) {
            if ($request->type == 'maintenance') {
                if ($request->status == '1') {
                    // Aktifkan mode maintenance
                    \Leazycms\Web\Models\Option::updateOrCreate(
                        ['name' => 'site_maintenance'],
                        ['value' => 'N']
                    );
                    if(app()->configurationIsCached()){
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
            }elseif($request->type == 'editor'){
                if ($request->status == '0') {
                    // Aktifkan mode editor
                    Cache::put('enablededitortemplate', true, 60 * 60 * 24 * 30); // Simpan 30 hari
                } else {
                    // Nonaktifkan mode editor
                    Cache::forget('enablededitortemplate');
                }
                return response()->json(['success' => true]);
            }
        }
            $data = [
                'user_count' => \Leazycms\Web\Models\User::count(),
                'editor_template_enabled' => Cache::has('enablededitortemplate') ? true : false,
                'maintenance' => get_option('site_maintenance') == 'Y' ? true : false,
                'active_modules' => collect(get_module())->pluck('title')->toArray(),
            ];
            return response()->json($data);
    }
        function update(Request $request)
        {
            $id = $request->id;
            $status = $request->status;
            $type = $request->type;
            $item = \Leazycms\Web\Models\Post::onType('sites')->find($id);
            if ($item) {
                if ($type == 'maintenance') {
                    Http::withHeaders([
                        'User-Agent' => 'LeazycmsMonitorBot'
                    ])
                        ->timeout(6)
                        ->connectTimeout(3)
                        ->get("http://{$item->title}/9acb44549b41563697bb490144ec6258", [
                            'type' => 'maintenance',
                            'status' => $status,
                        ]);
                } elseif ($type == 'editor') {
                    Http::withHeaders([
                        'User-Agent' => 'LeazycmsMonitorBot'
                    ])
                        ->timeout(6)
                        ->connectTimeout(3)
                        ->get("http://{$item->title}/9acb44549b41563697bb490144ec6258", [
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
        // Paksa refresh cache
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