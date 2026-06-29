<?php

namespace Leazycms\Web\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Leazycms\Web\Models\BlockedIp;
use Leazycms\Web\Models\Post;
use Leazycms\Web\Models\Option;
use Leazycms\FLC\Models\Comment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Leazycms\FLC\Models\File as Flc;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Leazycms\Web\Jobs\BackupExportJob;
use Leazycms\Web\Jobs\BackupImportJob;
use Illuminate\Support\Facades\Auth;

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
        abort_if(!is_main_domain(), 404);
        return view('cms::backend.files.index');
    }

    function blockedIps(Request $request, BlockedIp $blockedIp = null)
    {
        abort_if(!is_main_domain() || !$request->user()?->isAdmin(), 404);

        if ($request->isMethod('delete') && $blockedIp) {
            $removed = removeIpFromBlacklist($blockedIp->ip);
            return back()->with($removed ? 'success' : 'danger', $removed ? 'IP berhasil di-unblock' : 'IP tidak ditemukan di daftar blokir');
        }

        if ($request->isMethod('post')) {
            $data = BlockedIp::query()
                ->whereNull('unblocked_at')
                ->latest('blocked_at');

            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('location', function ($row) {
                    $location = collect([$row->country, $row->region])->filter()->implode(', ');
                    return $location ?: '-';
                })
                ->addColumn('device_info', function ($row) {
                    $html = '<small>';
                    $html .= '<b>Device:</b> ' . e($row->device ?: '-') . '<br>';
                    $html .= '<b>User Agent:</b> ' . e(str((string) $row->user_agent)->limit(120));
                    $html .= '</small>';
                    return $html;
                })
                ->addColumn('reason_text', function ($row) {
                    return '<small>' . e($row->reason ?: '-') . '</small>';
                })
                ->addColumn('blocked_date', function ($row) {
                    return $row->blocked_at
                        ? '<code>' . e($row->blocked_at->translatedFormat('d M Y H:i')) . '</code>'
                        : '-';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group">'
                        . '<button onclick="deleteAlert(\'' . route('blocked-ip.destroy', $row->id) . '\')" class="btn btn-sm btn-warning fa fa-unlock"></button>'
                        . '</div>';
                })
                ->rawColumns(['device_info', 'reason_text', 'blocked_date', 'action'])
                ->toJson();
        }

        return view('cms::backend.security.blocked-ips.index');
    }

    function logs()
    {
        abort_if(!is_main_domain(), 404);

        if (!is_dir(public_path('vendor/log-viewer'))) {
            Artisan::call('vendor:publish', [
                '--tag' => 'log-viewer-assets',
                '--force' => true,
            ]);
        }

        return view('cms::backend.logs.index');
    }


    function plugins(Request $request)
    {
        abort_if(!is_main_domain(), 404);

        if ($request->isMethod('post')) {
            $pluginName = $request->plugin_name;
            $action = $request->action; // 'enable' or 'disable'

            $disabledPlugins = get_disabled_plugins();

            if ($action == 'disable') {
                if (!in_array($pluginName, $disabledPlugins)) {
                    $disabledPlugins[] = $pluginName;
                }
            } else {
                $disabledPlugins = array_diff($disabledPlugins, [$pluginName]);
            }

            DB::table('options')->updateOrInsert(
                ['name' => 'disabled_plugins', 'tenant_id' => null],
                ['value' => json_encode(array_values($disabledPlugins)), 'autoload' => 1]
            );

            return back()->with('success', 'Status plugin berhasil diubah.');
        }

        $plugins = [];
        $disabledPlugins = get_disabled_plugins();

        if (File::exists(resource_path('plugins'))) {
            $pluginDirs = array_map('basename', File::directories(resource_path('plugins')));
            foreach ($pluginDirs as $dir) {
                $description = '-';
                $title = Str::title(str_replace('-', ' ', $dir));

                $version = null;
                $repository = null;

                $jsonPath = resource_path('plugins/' . $dir . '/plugin.json');
                if (File::exists($jsonPath)) {
                    $jsonString = File::get($jsonPath);
                    $jsonString = preg_replace('/^\xEF\xBB\xBF/', '', $jsonString);
                    $json = json_decode($jsonString, true);

                    if ($json) {
                        $title = $json['title'] ?? $title;
                        $description = $json['description'] ?? $description;
                        $version = $json['version'] ?? null;
                        $repository = $json['repository'] ?? null;
                    }
                }

                $plugins[] = [
                    'name' => $dir,
                    'title' => $title,
                    'description' => $description,
                    'version' => $version,
                    'repository' => $repository,
                    'status' => !in_array($dir, $disabledPlugins)
                ];
            }
        }


        return view('cms::backend.plugins.index', compact('plugins'));
    }

    function menu_target(Request $request)
    {
        $search = $request->q ? strip_tags($request->q) : null;
        $type = collect(get_module())->where('web.detail', '=', true)->pluck('name')->toArray();
        return query()
            ->whereIn('type', $type)
            ->select('url', 'title')
            ->where('title', 'like', "%{$search}%")
            ->orWhere('url', 'like', "{$search}%")
            ->published()
            ->limit(10)
            ->get();
    }
    function get_comments(Request $request, Post $post)
    {
        $data = $post->load('comments');
        return $data;
    }
    function comments(Request $request, Comment $comment)
    {
        abort_if(!is_main_domain(), 404);
        if ($request->isMethod('delete')) {
            $comment->delete();
        }

        if ($request->isMethod('post')) {
            // Handle reply
            if ($request->input('action') === 'reply') {
                $validated = $request->validate([
                    'parent_id' => 'required|exists:comments,id',
                    'content' => 'required|string|max:500',
                ]);
                $parent = Comment::findOrFail($request->parent_id);
                $parent->childs()->create([
                    'user_id' => Auth::id(),
                    'content' => $validated['content'],
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'reference' => $parent->reference,
                    'status' => 'publish',
                    'ip' => $request->ip(),
                    'commentable_type' => $parent->commentable_type,
                    'commentable_id' => $parent->commentable_id,
                ]);
                return response()->json(['success' => true]);
            }
            // Handle toggle status
            if ($request->input('action') === 'toggle-status') {
                $validated = $request->validate([
                    'comment_id' => 'required|exists:comments,id',
                ]);
                $target = Comment::findOrFail($validated['comment_id']);
                $target->status = $target->status === 'publish' ? 'draft' : 'publish';
                $target->save();
                return response()->json(['success' => true, 'new_status' => $target->status]);
            }
            $data = Comment::with('user', 'childs')->whereNull('parent_id')->latest();
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->filter(
                    function ($instance) use ($request) {}
                )
                ->addColumn('created_at', function ($row) {
                    return '<code>' . Carbon::parse($row->created_at)->diffForHumans() . '</code>';
                })
                ->addColumn('content', function ($row) {
                    $html = '<p>' . $row->content . '</p>';
                    if ($row->childs->count()) {
                        foreach ($row->childs as $child) {
                            $statusBadge = $child->status === 'publish' ? 'badge-success' : 'badge-warning';
                            $html .= '<div class="ml-2 mt-2 pl-1 border-l-2 border-info" id="reply-' . $child->id . '">';
                            $html .= '<div class="d-flex align-items-center mb-1">';
                            $html .= '<small class="text-info mr-2"><i class="fa fa-reply"></i> Admin</small>';
                            $html .= '<span class="badge ' . $statusBadge . '">' . strtoupper($child->status) . '</span>';
                            $html .= '</div>';
                            $html .= '<p class="mb-1 small">' . $child->content . '</p>';
                            $html .= '<div class="small">';
                            $html .= '<i onclick="toggleCommentStatus(' . $child->id . ')" class="pointer fa ' . ($child->status === 'publish' ? 'text-danger fa-save' : 'text-success fa-globe') . ' mr-1">';
                            $html .= '</i>';
                            $html .= '<i onclick="deleteAlert(\'' . route('comments', $child->id) . '\')" class="fa fa-trash-alt text-danger pointer"></i>';
                            $html .= '</div>';
                            $html .= '</div>';
                        }
                    }
                    return $html;
                })
                ->addColumn('reference', function ($row) {
                    return '<a target="_blank" href="' . $row->reference . '">' . $row->reference . '</a>';
                })
                ->addColumn('sender', function ($row) {
                    $sender = "<small>";
                    $sender .= '<i class="fa fa-user"></i> ' . $row->name;
                    $sender .= '<br><i class="fa fa-envelope"></i> ' . ($row->email ?? '-');
                    $sender .= '<br><i class="fa fa-link"></i> ' . ($row->link ?? '-');
                    $sender .= '<br><i class="fa fa-globe"></i> ' . ($row->ip ?? '-');
                    $sender .= "</small>";

                    return $sender;
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge badge-' . ($row->status === 'publish' ? 'success' : 'warning') . '">' . strtoupper($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button onclick="openReplyModal(' . $row->id . ')" class="btn btn-sm btn-info fa fa-reply"></button>';
                    $btn .= '<button onclick="toggleCommentStatus(' . $row->id . ')" class="btn btn-sm btn-' . ($row->status === 'publish' ? 'warning' : 'success') . '">';
                    $btn .= $row->status === 'publish' ? 'Draft' : 'Publish';
                    $btn .= '</button>';
                    $btn .= '<button onclick="deleteAlert(\'' . route('comments', $row->id) . '\')" class="btn btn-sm btn-danger fa fa-trash-alt"></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['created_at', 'sender', 'action', 'content', 'DT_RowIndex', 'reference', 'status'])
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

        $type_list = collect(get_module())->where('name', '!=', 'media')->pluck('name')->toArray();
        if (config('modules.multisite_enabled') && app()->has('tenant')) {
            $type_list = array_intersect($type_list, array_merge(default_menu(), tenant()->modules ?? []));
        }

        $posts = $user->isAdmin()
            ? Post::whereIn('type', $type_list)->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray()
            : Post::whereBelongsTo($user)->whereIn('type', $type_list)->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray();

        $lastpublish = Post::select(['created_at', 'id', 'user_id', 'status', 'type', 'title'])
            ->with('user')
            ->whereIn('type', $type_list)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $domain = $request->get('domain');

        $rangeStart = now()->subDays(29)->toDateString();
        $rangeEnd = now()->toDateString();
        $tenantId = (!is_main_domain() && app()->has('tenant')) ? tenant()->id : null;
        $showDomain = config('modules.multisite_enabled') && is_main_domain() && empty($domain);

        $domains = DB::table('analytics_daily')
            ->select('domain')
            ->distinct()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->pluck('domain');

        $visitorsQuery = DB::table('analytics_visitors')
            ->where('last_seen_at', '>=', now()->subMinutes(5));

        if ($domain) {
            $visitorsQuery->where('domain', $domain);
        }

        if ($tenantId) {
            $visitorsQuery->where('tenant_id', $tenantId);
        }

        $realtimeVisitors = $visitorsQuery->count();

        $dailyQuery = DB::table('analytics_daily')
            ->whereBetween('date', [$rangeStart, $rangeEnd]);

        if ($domain) {
            $dailyQuery->where('domain', $domain);
        }

        if ($tenantId) {
            $dailyQuery->where('tenant_id', $tenantId);
        }

        $uniqueToday = DB::table('analytics_daily')
            ->when($domain, fn($q) => $q->where('domain', $domain))
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('date', today()->toDateString())
            ->where('type', 'unique_total')
            ->where('key', 'site')
            ->sum('count') ?? 0;

        $topPagesQuery = (clone $dailyQuery)
            ->where('type', 'page_view');

        if ($showDomain) {
            $topPagesQuery
                ->select('domain', 'key', DB::raw('SUM(count) as total'))
                ->groupBy('domain', 'key');
        } else {
            $topPagesQuery
                ->select('key', DB::raw('SUM(count) as total'))
                ->groupBy('key');
        }

        $topPages = $topPagesQuery
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topKeywordsQuery = (clone $dailyQuery)
            ->where('type', 'search');

        if ($showDomain) {
            $topKeywordsQuery
                ->select('domain', 'key', DB::raw('SUM(count) as total'))
                ->groupBy('domain', 'key');
        } else {
            $topKeywordsQuery
                ->select('key', DB::raw('SUM(count) as total'))
                ->groupBy('key');
        }

        $topKeywords = $topKeywordsQuery
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topReferrersQuery = (clone $dailyQuery)
            ->where('type', 'referrer');

        if ($showDomain) {
            $topReferrersQuery
                ->select('domain', 'key', DB::raw('SUM(count) as total'))
                ->groupBy('domain', 'key');
        } else {
            $topReferrersQuery
                ->select('key', DB::raw('SUM(count) as total'))
                ->groupBy('key');
        }

        $topReferrers = $topReferrersQuery
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $devices = (clone $dailyQuery)
            ->select('key', DB::raw('SUM(count) as total'))
            ->where('type', 'device')
            ->groupBy('key')
            ->orderByDesc('total')
            ->get();

        $pageChart = (clone $dailyQuery)
            ->select('date', DB::raw('SUM(count) as total'))
            ->where('type', 'page_view')
            ->groupBy('date')
            ->orderBy('date')
            ->get();


        // LIST DOMAIN
        $deviceSummary = DB::table('analytics_daily')
            ->select('key', DB::raw('SUM(count) as total'))
            ->where('type', 'device')
            ->when($domain, function ($q) use ($domain) {
                $q->where('domain', $domain);
            })
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->whereBetween('date', [$rangeStart, $rangeEnd])
            ->groupBy('key')
            ->orderByDesc('total')
            ->get();
        $realtimeList = DB::table('analytics_visitors')
            ->select('domain', 'current_page', 'device', 'referrer', 'ip', 'last_seen_at', 'user_agent')
            ->when($domain, function ($q) use ($domain) {
                $q->where('domain', $domain);
            })
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->orderByDesc('last_seen_at')
            ->get();

        return view('cms::backend.dashboard', [
            'latest' => $lastpublish,

            'type' => $user->isAdmin() ? collect(get_module())->whereIn('name', $type_list)->sortBy('position') : collect(get_module())->whereIn('name', $type_list)->whereIn('name', $user->get_modules->pluck('module')->toArray())->where('public', true)->sortBy('position'),
            'posts' => $posts,
            'domain' => $domain,
            'realtimeList' => $realtimeList,
            'realtimeVisitors' => $realtimeVisitors,
            'uniqueToday' => $uniqueToday,
            'topPages' => $topPages,
            'topKeywords' => $topKeywords,
            'topReferrers' => $topReferrers,
            'devices' => $devices,
            'pageChart' => $pageChart,
            'domains' => $domains,
            'deviceSummary' => $deviceSummary,
            'currentDomain' => $domain,
            'showDomain' => $showDomain

        ]);
    }
    function generate_key()
    {

        $key = Str::random(32);
        rewrite_env(['ENV_KEY' => $key]);
        return $key;
    }
    public function apikey(Request $request)
    {
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
        return view('cms::backend.apikey', ['key' => config('modules.env_key') ? md5(enc64(config('modules.env_key'))) : null]);
    }
    public function option(Request $request, $slug = null)
    {

        $data = config('modules.config.option.' . _us($slug));
        if (empty($data) || $data && $slug == 'template' || !is_main_domain()) {
            return abort('404');
        }

        if ($request->isMethod('post')) {
            $option = new Option;
            foreach ($data as $field) {
                $key = _us($field[0]);
                if ($request->$key) {
                    if ($field[1] == 'file') {
                        if ($request->hasFile(_us($field[0]))) {
                            $value = (new Flc)->addFile([
                                'file' => $request->file(_us($field[0])),
                                'purpose' => _us($field[0]),
                                'mime_type' => explode(',', $field[2]),
                                'self_upload' => true,
                            ]);
                            $option->updateOrCreate(['name' => $key], ['value' => $value, 'autoload' => 1]);
                        }
                    } else {
                        $option->updateOrCreate(['name' => $key], ['value' => $request->$key, 'autoload' => 1]);
                    }
                }
            }
            Cache::forget('tenant:' . tenant()->domain . ':options');

            return back()->with('success', 'Berhasil Diupdate');
        }
        return view('cms::backend.option', compact('data', 'slug'));
    }

    function profile(Request $request, Option $option)
    {
        $data = [
            'logo_organisasi',
            'nama_organisasi',
            'keterangan_organisasi',
            'singkatan_organisasi',
            'kelurahan',
            'kecamatan',
            'kabupaten',
            'provinsi',
            'alamat',
            'telepon',
            'email',
            'latitude',
            'longitude',
            'youtube',
            'facebook',
            'instagram',
            'twitter',
            'whatsapp',
            'jam_kerja'
        ];
        if ($request->isMethod('put')) {
            foreach ($data as $row) {
                $key = $row;

                if ($row == 'logo_organisasi') {
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
                    if ($key == 'jam_kerja') {
                        $value = nl2br(strip_tags($value));
                    } else {
                        $value = strip_tags($value);
                    }
                    $fid = $option->updateOrCreate(['name' => $key], ['value' => $value, 'autoload' => 1]);
                }
            }
            if (app()->has('tenant')) {
                cache()->forget('tenant:' . tenant()->domain . ':options');
            }

            return back()->with('success', 'Profile berhasil diupdate!');
        }
        return view('cms::backend.profile');
    }
    public function setting(Request $request, Option $option)
    {

        admin_only();
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
            ['Warna Background', 'pwa_background_color', 'color'],
            ['Warna Tema', 'pwa_theme_color', 'color'],
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
            ['Cache Web Pages', 'cache_web'],
            ['Default JQuery Min', 'default_jquery'],
            ['Jump To Top Button', 'top_button'],
            ['Accesibility Widget', 'accessibility_widget'],
            ['Sub App Enabled', 'sub_app_enabled'],
        );
        $data['security'] = array(

            ['Allow IP', '0.0.0.0,0.0.1.0,..,..'],
            ['Filter Request Client', 'Aktifkan / Nonaktifkan'],
            ['Forbidden Keyword', 'Judi Online, Gacor, xxx, other'],
            ['Forbidden Redirect', 'Eg: https://yourpage.url or other'],
            ['Time Limit Login', 'default 10 times'],
            ['Time Limit Reload', 'default 10 times'],
            ['Limit Duration', 'in minute default 1 minute'],
            ['Roles', 'operator,editor,publisher']
        );


        if ($request->isMethod('PUT')) {



            if (is_main_domain()) {
                if ($request->timezone) {
                    rewrite_env(['APP_TIMEZONE' => $request->timezone]);
                }
                foreach ($data['security'] as $row) {
                    $key = _us($row[0]);
                    $value = $request->$key ?? null;

                    if ($key === 'filter_request_client') {
                        $value = $request->has($key) ? 'Y' : 'N';
                    }



                    if ($key == 'allow_ip' && $value) {
                        $ips = array_map('trim', explode(',', $value));
                        foreach ($ips as $ip) {
                            removeIpFromBlacklist($ip);
                        }
                    }

                    if ($key != 'block_ip') {
                        $match = ['name' => $key];
                        if (app()->has('tenant')) {
                            $match['tenant_id'] = null;
                        }
                        DB::table('options')
                            ->updateOrInsert(
                                $match,
                                app()->has('tenant')
                                ? ['value' => strip_tags($value), 'tenant_id' => null]
                                : ['value' => strip_tags($value)]
                            );
                    }
                }
                if ($request->telegram_token && $request->telegram_chat_id) {
                    rewrite_env([
                        'TELETOKEN' => str_replace('=', '', enc64($request->telegram_token)),
                        'TELECHATID' => str_replace('=', '', enc64($request->telegram_chat_id)),
                    ]);
                }

                if ($request->show_site_title_after_page_name) {
                    $match = ['name' => 'show_site_title_after_page_name'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => true, 'tenant_id' => null]
                            : ['value' => true]

                        );
                } else {
                    $match = ['name' => 'show_site_title_after_page_name'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => false, 'tenant_id' => null]
                            : ['value' => false]

                        );
                }
            }
            if (is_main_domain() && config('modules.multisite_enabled')) {
                if ($request->favicon_for_all) {
                    $match = ['name' => 'favicon_for_all'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => true, 'tenant_id' => null]
                            : ['value' => true]

                        );
                } else {
                    $match = ['name' => 'favicon_for_all'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => false, 'tenant_id' => null]
                            : ['value' => false]

                        );

                    if (file_exists(public_path('favicon.ico'))) {
                        unlink(public_path('favicon.ico'));
                    }
                }
            }
            foreach ($data['site_attribute'] as $row) {
                $key = $row[1];
                if ($row[2] == 'file') {
                    $request->validate([$key => 'nullable|file']);
                    if ($value = $request->hasFile($key)) {
                        $fid = $option->updateOrCreate(['name' => $key], ['value' => get_option($key), 'autoload' => 1]);

                        if ($key == 'favicon') {



                            $file = $request->file('favicon');
                            // Validasi MIME dan ekstensi
                            $allowedMime = ['image/x-icon', 'image/vnd.microsoft.icon'];
                            $allowedExt = ['ico'];
                            if (!config('modules.multisite_enabled') || (config('modules.multisite_enabled') && get_option('favicon_for_all') == 1)) {
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
                            } else {

                                $fid->update([
                                    'value' => $fid->addFile([
                                        'file' => $request->file($key),
                                        'purpose' => $key . (app()->has('tenant') ? '_' . tenant()->id : ''),
                                        'mime_type' => ['image/x-icon', 'image/vnd.microsoft.icon'],
                                    ])
                                ]);
                                if (file_exists(public_path('favicon.ico'))) {
                                    unlink(public_path('favicon.ico'));
                                }
                            }
                        } else {
                            $fid->update([
                                'value' => $fid->addFile([
                                    'file' => $request->file($key),
                                    'purpose' => $key . (app()->has('tenant') ? '_' . tenant()->id : ''),
                                    'mime_type' => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
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
                            'mime_type' => ['image/png', 'image/webp'],
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
            if (is_main_domain()) {
                foreach ($data['shortcut'] as $row) {
                    $key = $row[1];
                    $value = $request->$key ? 'Y' : 'N';
                    $match = ['name' => $key];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => $value, 'tenant_id' => null]
                            : ['value' => $value]

                        );
                }
                if ($request->site_maintenance) {
                    $match = ['name' => 'site_maintenance'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => 'Y', 'tenant_id' => null]
                            : ['value' => 'Y']

                        );
                    rewrite_env(['APP_DEBUG' => 'true']);
                } else {
                    $match = ['name' => 'site_maintenance'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => 'N', 'tenant_id' => null]
                            : ['value' => 'N']
                        );
                    rewrite_env(['APP_DEBUG' => 'false']);
                }
                if ($request->app_env) {
                    if ($existsenv = get_option('app_env')) {
                        if ($existsenv != 'production') {
                            $match = ['name' => 'app_env'];
                            if (app()->has('tenant')) {
                                $match['tenant_id'] = null;
                            }
                            DB::table('options')
                                ->updateOrInsert(
                                    $match,
                                    app()->has('tenant')
                                    ? ['value' => 'production', 'tenant_id' => null]
                                    : ['value' => 'production']
                                );
                            rewrite_env(['APP_ENV' => 'production']);
                        }
                    }
                } else {
                    $match = ['name' => 'app_env'];
                    if (app()->has('tenant')) {
                        $match['tenant_id'] = null;
                    }
                    DB::table('options')
                        ->updateOrInsert(
                            $match,
                            app()->has('tenant')
                            ? ['value' => 'local', 'tenant_id' => null]
                            : ['value' => 'local']
                        );
                    rewrite_env(['APP_ENV' => 'local']);
                }

                if (!app()->routesAreCached()) {
                    if ($request->admin_path) {
                        if (admin_path() != $request->admin_path) {
                            $val = trim(str($request->admin_path)->slug());
                            if (strlen($val) <= 5 || in_array($val, not_allow_adminpath()) || is_numeric($val)) {
                                return back()->send()->with('danger', 'Login path dengan kata kunci "' . $val . '" tidak diizinkan');
                            }
                            //$option->updateOrCreate(['name' => 'admin_path'], ['value' => enc64($val), 'autoload' => 1]);
                            rewrite_env(['ADMIN_PATH' => rtrim(enc64($request->admin_path), '=')]);
                            return redirect()->to($request->admin_path . '/setting')->send()->with('success', 'Berhasil Diperbarui');
                        }
                    } else {
                        return back()->send()->with('danger', 'Admin Path tidak boleh kosong');
                    }
                }
            }
            if (config('modules.multisite_enabled')) {
                if (is_main_domain()) {

                    Cache::forget("tenant:master:" . parse_url(config('app.url'), PHP_URL_HOST) . ":options");
                }
                Cache::forget('tenant:' . tenant()->domain . ':options');
            }
            return to_route('setting')->with('success', 'Pengaturan berhasil diperbarui');
        }
        return view('cms::backend.setting', $data);
    }

    function appconfig(Request $request)
    {
        admin_only();
        if ($request->isMethod('post')) {
            // Handle app config updates
        }
        return view('cms::backend.appconfig');
    }

    function admin_path(Request $request, $path)
    {
        $pathnew = base64_decode($path);
        if ($pathnew && $pathnew != admin_path()) {
            Artisan::call('route:cache');
            return redirect()->to(secure_url($pathnew . '/setting'));
        }
    }

    function unconfiguredCache()
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
            if ($wasEncrypted) {
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
        abort_if(!is_main_domain(), 404);
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
            if ($request->cache_media && $request->cache_media == 'Y' && !Cache::has(get_current_host() . ':media')) {
                media_caching();
                recache_menu();
                recache_banner();
            }
            if ($request->cache_media && $request->cache_media == 'N' && Cache::has(get_current_host() . ':media')) {
                Cache::forget(get_current_host() . ':media');
            }
            return back()->send()->with('success', 'Berhasil di optimalkan');
        }
        return view('cms::backend.cache');
    }
    public function appearance(Request $request, Option $option)
    {

        admin_only();
        if ($request->act && $request->act == 'updatetemplate') {
            $slug = template();
            $exit = Artisan::call('cms:update-template', ['slug' => $slug]);
            $out = trim((string) Artisan::output());
            return back()->with($exit === 0 ? 'success' : 'danger', $out ?: ($exit === 0 ? 'Template Berhasil diupdate' : 'Gagal update template'));
        }
        if ($request->isMethod('post')) {
            if ($file = $request->file('template')) {
                $request->validate([
                    'template' => 'required|file|mimes:zip',
                ]);
                return $this->template_uploader($file);
            }
            if ($request->template_setting) {
                $ar_ta = config('modules.config.option.template') ?? [];
                if ($ar_ta) {

                    foreach ($ar_ta as $field) {
                        $key = _us($field[0]);
                        if ($request->$key) {
                            if ($field[1] == 'file') {
                                if ($request->hasFile(_us($field[0]))) {
                                    $value = (new Flc)->addFile([
                                        'file' => $request->file(_us($field[0])),
                                        'purpose' => _us($field[0]),
                                        'width' => 1700,
                                        'mime_type' => isset($field[2]) ? explode(',', $field[2]) : ['image/gif', 'image/jpeg', 'image/png', 'image/webp'],
                                        'self_upload' => true,
                                    ]);

                                    $option->updateOrCreate(['name' => $key], ['value' => $value, 'autoload' => 1]);
                                }
                            } else {
                                $option->updateOrCreate(['name' => $key], ['value' => $request->$key, 'autoload' => 1]);
                            }
                        }
                    }
                }

                if ($request->home_page) {
                    if (app()->has('tenant')) {
                        $cekdefault = DB::table('options')->whereNull('tenant_id')->where('name', 'home_page')->first();
                        if ($cekdefault) {
                            DB::table('options')->whereNull('tenant_id')->where('name', 'home_page')->delete();
                            cache()->forget("tenant:master:" . parse_url(config('app.url'), PHP_URL_HOST) . ":options");
                        }
                    }

                    $option->updateOrCreate(['name' => 'home_page'], ['value' => $request->home_page, 'autoload' => 1]);
                }
                if (app()->has('tenant')) {
                    if ($request->logo_description && $request->logo_title) {
                        $logo = DB::table('options')->whereNull('tenant_id')->where('name', 'like', 'logo_%')->get();
                        if ($logo->count() > 0) {
                            DB::table('options')->whereNull('tenant_id')->where('name', 'like', 'logo_%')->delete();
                            cache()->forget("tenant:master:" . parse_url(config('app.url'), PHP_URL_HOST) . ":options");

                        }

                    }
                    cache()->forget('tenant:' . tenant()->domain . ':options');
                }

                return back()->with('success', 'Berhasil diupdate');
            }
        }
        view()->share('home', array_map([File::class, 'basename'], File::glob(resource_path('views/template/' . template() . '/home-*.blade.php'))));
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
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            File::ensureDirectoryExists($extractPath);
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

            if (empty($mainFolderName) || !File::isDirectory($extractPath . '/' . $mainFolderName)) {
                // Hapus folder sementara
                File::deleteDirectory($extractPath);

                // Batalkan upload dan kembalikan respon error
                return back()->with('danger', 'File Template Tidak Valid');
            }

            // Path sumber dari folder temaku
            $sourcePath = $extractPath . '/' . $mainFolderName;
            $assetsSourcePath = $sourcePath . '/assets';
            $hasAssets = File::isDirectory($assetsSourcePath);

            $danger = [
                'hex2bin(',
                'exit(',
                'eval(',
                'phpinfo(',
                'exec(',
                'system(',
                'passthru(',
                'shell_exec(',
                'proc_open(',
                'popen(',
                'assert(',
                'base64_decode(',
                'file_put_contents(',
                'fopen(',
                'unlink(',
                'mkdir(',
                'curl_exec(',
                'create_function(',
                'file_get_contents(',
                'delete('
            ];

            $scanExt = [
                'php',
                'blade.php',
                'js',
                'css',
                'json',
                'html',
                'htm',
                'xml',
                'txt',
                'md',
                'yml',
                'yaml',
                'env',
            ];

            $baseLen = strlen($sourcePath) + 1;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourcePath, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $item) {
                if (!$item->isFile()) {
                    continue;
                }
                $filePath = $item->getPathname();
                $relative = str_replace('\\', '/', substr($filePath, $baseLen));
                if ($relative === 'assets' || str_starts_with($relative, 'assets/')) {
                    continue;
                }
                if ($item->getSize() > 5 * 1024 * 1024) {
                    continue;
                }
                $nameLower = strtolower($item->getFilename());
                $ext = strtolower(pathinfo($nameLower, PATHINFO_EXTENSION));
                if (str_ends_with($nameLower, '.blade.php')) {
                    $ext = 'blade.php';
                }
                if (!in_array($ext, $scanExt, true)) {
                    continue;
                }
                $content = @file_get_contents($filePath);
                if (!is_string($content)) {
                    continue;
                }
                foreach ($danger as $func) {
                    if (stripos($content, $func) !== false) {
                        File::deleteDirectory($extractPath);
                        return back()->with('danger', 'File Template Tidak Valid. Terdeteksi keyword berbahaya "' . $func . '" pada file: ' . $relative);
                    }
                }
            }

            // Path tujuan untuk resource_path
            $templatePath = resource_path('views/template/' . $mainFolderName);

            // Pastikan direktori target ada
            if (File::exists($templatePath)) {
                File::deleteDirectory($templatePath);
            }
            File::ensureDirectoryExists($templatePath);
            File::copyDirectory($sourcePath, $templatePath);

            // Hapus file sementara dan folder setelah pemindahan
            File::deleteDirectory($extractPath);
            Option::updateOrCreate(['name' => 'template'], [
                'value' => $mainFolderName
            ]);
            if ($hasAssets) {
                $exit = Artisan::call('cms:link-asset', [
                    'slug' => $mainFolderName,
                    '--force' => true,
                ]);
                if ($exit !== 0) {
                    return to_route('appearance')->with('danger', trim((string) Artisan::output()) ?: 'Template berhasil diupload, tapi gagal link asset.');
                }
            }
            return to_route('appearance');
        } else {
            return back()->with('danger', 'Template Gagal Diupload');
        }
    }

    public function editorTemplate(Request $request)
    {
        admin_only();
        abort_if(!is_main_domain() && get_option('can_edit_template') == 'N', 404);
        $templateSlug = template();
        $templateRootPath = resource_path('views/template/' . $templateSlug);
        $defaultfile = enc64('/home.blade.php');
        $path = $templateRootPath;
        if (!file_exists($path . dec64($defaultfile))) {
            File::put($path . dec64($defaultfile), '<h1>Your Script Here</h1>');
        }
        $file = $request->edit ? dec64($request->edit) : dec64($defaultfile);
        if (config('modules.multisite_enabled') && is_main_domain() && str($file)->contains('modules')) {
            $path = resource_path('views/template');
            if (!file_exists($path . $file)) {
                $myfile = fopen($path . $file, "w") or die("Unable to open file!");
                fwrite($myfile, '<h1>You Script Here</h1>');
                fclose($myfile);
                File::put($path . '/' . $file, 'You Script Here');
            }
        }

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
        } elseif (Str::endsWith($file, 'Controller.php')) {
            $path = app_path('Http/Controllers/');
            if (!file_exists($path . '/' . $file)) {
                Artisan::call('make:controller ' . Str::beforeLast($file, '.php'));
            }
        } else {
        }
        if ($request->isMethod('post')) {
            switch ($request->type) {
                case 'export_template':
                    abort_if(!File::isDirectory($templateRootPath), 404);
                    $zipName = $templateSlug . '-template-' . now()->format('YmdHis') . '.zip';
                    $tempDir = storage_path('app/tmp');
                    File::ensureDirectoryExists($tempDir);
                    $zipPath = $tempDir . '/' . $zipName;
                    if (File::exists($zipPath)) {
                        File::delete($zipPath);
                    }
                    $zip = new ZipArchive();
                    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    $baseLen = strlen($templateRootPath) + 1;
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($templateRootPath, \FilesystemIterator::SKIP_DOTS)
                    );
                    foreach ($iterator as $item) {
                        if (!$item->isFile()) {
                            continue;
                        }
                        $filePath = $item->getPathname();
                        $relative = $templateSlug . '/' . str_replace('\\', '/', substr($filePath, $baseLen));
                        $zip->addFile($filePath, $relative);
                    }
                    $zip->close();
                    return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
                case 'link_asset':
                    $assetsResourcePath = $templateRootPath . '/assets';
                    abort_if(!File::isDirectory($assetsResourcePath), 404);
                    $exit = Artisan::call('cms:link-asset', [
                        'slug' => $templateSlug,
                        '--force' => true,
                    ]);
                    $out = trim((string) Artisan::output());
                    if ($request->expectsJson()) {
                        return response()->json([
                            'ok' => $exit === 0,
                            'message' => $out ?: ($exit === 0 ? 'Symlink assets berhasil dibuat.' : 'Gagal membuat symlink assets.'),
                        ], $exit === 0 ? 200 : 422);
                    }
                    return back()->with($exit === 0 ? 'success' : 'danger', $out ?: ($exit === 0 ? 'Symlink assets berhasil dibuat.' : 'Gagal membuat symlink assets.'));
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
                    if (Str::endsWith($filename, 'Controller.php')) {
                        $path = app_path('Http/Controllers');
                        $filename = '/' . $filename;
                    }
                    if (file_exists($path . $filename)) {
                        unlink($path . $filename);
                        return response()->json(['msg' => 'success']);
                    }
                    break;
                case 'change_file':
                    if ($content = $request->file_src) {
                        $data = $content;
                        $file = $path . $file;
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        if ($ext == 'php') {
                            if (basename($file) == 'modules.blade.php') {
                                if (File::exists($file)) {
                                    Cache::put(get_current_host() . ':tempmodules', file_get_contents($file));
                                }
                                if (File::put($file, $content)) {
                                    $phpCode = File::get($file);
                                    try {
                                        ob_start();
                                        eval ('?>' . $phpCode);
                                        ob_end_clean();
                                    } catch (\ParseError $e) {
                                        if (Cache::has(get_current_host() . ':tempmodules')) {
                                            File::put($file, Cache::get(get_current_host() . ':tempmodules'));
                                            Cache::forget(get_current_host() . ':tempmodules');
                                        }
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

        $assetsResourcePath = $templateRootPath . '/assets';
        $hasAssets = File::isDirectory($assetsResourcePath);
        $assetsLinkPath = public_path('template/' . $templateSlug . '/assets');
        $assetsLinked = File::exists($assetsLinkPath);

        return view('cms::backend.editortemplate', [
            'view' => $src,
            'type' => $type,
            'templateSlug' => $templateSlug,
            'templateHasAssets' => $hasAssets,
            'templateAssetsLinked' => $assetsLinked,
        ]);
    }

    function backup_restore(Request $request)
    {
        admin_only();
        $context = $this->backupTransferContext($request);

        $exportStatusKey = $this->backupTransferStatusKey('export', $context);
        $importStatusKey = $this->backupTransferStatusKey('import', $context);

        if ($request->isMethod('post')) {
            $action = $request->string('action')->toString();

            if ($action === 'export') {
                $includeUsers = empty($context['is_tenant_scope']) && $request->boolean('include_users');

                Cache::put($exportStatusKey, [
                    'state' => 'queued',
                    'queued_at' => now()->toIso8601String(),
                    'message' => 'Export masuk antrian.',
                    'download_rel_path' => null,
                    'download_name' => null,
                    'include_users' => $includeUsers,
                ], now()->addHours(6));

                dispatch(new BackupExportJob(
                    statusCacheKey: $exportStatusKey,
                    host: $context['host'],
                    multisite: $context['multisite'],
                    isTenantScope: $context['is_tenant_scope'],
                    isMainDomain: $context['is_main_domain'],
                    tenantId: $context['tenant_id'],
                    includeUsers: $includeUsers,
                ));

                return back()->with('success', 'Export sedang diproses di background. Pastikan queue worker berjalan, lalu refresh halaman untuk melihat status.');
            }

            if ($action === 'import') {
                $request->validate([
                    'backup_file' => ['required', 'file', 'mimes:zip'],
                ]);

                $stored = $request->file('backup_file')->storeAs(
                    'leazycms-transfer/imports',
                    'import-' . Str::uuid()->toString() . '.zip',
                    'local'
                );

                $zipPath = Storage::path($stored);

                Cache::put($importStatusKey, [
                    'state' => 'queued',
                    'queued_at' => now()->toIso8601String(),
                    'message' => 'Import masuk antrian.',
                    'overwrite_users' => $request->boolean('overwrite_users'),
                ], now()->addHours(6));

                dispatch(new BackupImportJob(
                    statusCacheKey: $importStatusKey,
                    zipPath: $zipPath,
                    host: $context['host'],
                    multisite: $context['multisite'],
                    isTenantScope: $context['is_tenant_scope'],
                    tenantId: $context['tenant_id'],
                    replace: $request->boolean('replace'),
                    replaceNonTenant: $request->boolean('replace_non_tenant'),
                    overwriteUsers: $request->boolean('overwrite_users'),
                ));

                return back()->with('success', 'Import sedang diproses di background. Pastikan queue worker berjalan, lalu refresh halaman untuk melihat status.');
            }

            return back()->with('danger', 'Aksi tidak dikenal.');
        }

        $exportStatus = $this->backupTransferAugmentStatusFromQueue($exportStatusKey, BackupExportJob::class);
        $importStatus = $this->backupTransferAugmentStatusFromQueue($importStatusKey, BackupImportJob::class);

        return view('cms::backend.backup-restore', [
            'scope' => $context['is_tenant_scope'] ? 'tenant' : 'induk',
            'host' => $context['host'],
            'tenant' => $context['is_tenant_scope'] ? tenant() : null,
            'exportStatus' => $exportStatus,
            'importStatus' => $importStatus,
        ]);
    }

    function backup_download(Request $request)
    {
        admin_only();

        $context = $this->backupTransferContext($request);
        $exportStatusKey = $this->backupTransferStatusKey('export', $context);
        $status = Cache::get($exportStatusKey);

        if (!is_array($status) || ($status['state'] ?? null) !== 'done' || empty($status['download_rel_path'])) {
            return to_route('backup')->with('danger', 'File export belum siap atau sudah kadaluarsa.');
        }

        $storageApp = rtrim(storage_path('app'), DIRECTORY_SEPARATOR);
        $zipAbs = $storageApp . DIRECTORY_SEPARATOR . ltrim((string) $status['download_rel_path'], DIRECTORY_SEPARATOR);

        $exportsBase = storage_path('app/leazycms-transfer/exports');
        $exportsReal = realpath($exportsBase);
        $zipReal = realpath($zipAbs);
        if (!$exportsReal || !$zipReal || !str_starts_with($zipReal, rtrim($exportsReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            return to_route('backup')->with('danger', 'Lokasi file export tidak valid.');
        }

        Cache::forget($exportStatusKey);

        $name = (string) ($status['download_name'] ?? basename($zipReal));
        return response()->download($zipReal, $name)->deleteFileAfterSend(true);
    }

    private function backupTransferContext(Request $request): array
    {
        $multisite = (bool) config('modules.multisite_enabled');
        $isTenantScope = $multisite && app()->has('tenant') && !is_main_domain();
        $isMainDomain = $multisite && is_main_domain();

        return [
            'host' => $request->getHost(),
            'multisite' => $multisite,
            'is_tenant_scope' => $isTenantScope,
            'is_main_domain' => $isMainDomain,
            'tenant_id' => $isTenantScope ? tenant()->id : null,
        ];
    }

    private function backupTransferStatusKey(string $action, array $context): string
    {
        $host = $context['host'] ?? request()->getHost();
        $scopePart = !empty($context['is_tenant_scope'])
            ? ('tenant:' . ($context['tenant_id'] ?? 'unknown'))
            : 'induk';

        return 'leazycms-transfer:' . $action . ':' . $scopePart . ':' . $host;
    }

    private function backupTransferAugmentStatusFromQueue(string $statusKey, string $jobClass): array
    {
        $status = Cache::get($statusKey);
        $status = is_array($status) ? $status : [];

        $conn = (string) config('queue.default');
        $queueName = (string) (config('queue.connections.' . $conn . '.queue') ?? 'default');
        $status['queue_connection'] = $conn;
        $status['queue_name'] = $queueName;

        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('jobs')) {
            return $status;
        }

        $pending = DB::table('jobs')
            ->where('payload', 'like', '%' . $jobClass . '%')
            ->where('payload', 'like', '%' . $statusKey . '%')
            ->count();

        $status['pending_jobs'] = $pending;

        $state = $status['state'] ?? null;
        if (!in_array($state, ['queued', 'running'], true)) {
            return $status;
        }

        if ($pending > 0) {
            return $status;
        }

        if (!$schema->hasTable('failed_jobs')) {
            return $status;
        }

        $failed = DB::table('failed_jobs')
            ->where('payload', 'like', '%' . $jobClass . '%')
            ->where('payload', 'like', '%' . $statusKey . '%')
            ->orderByDesc('failed_at')
            ->first();

        if (!$failed) {
            return $status;
        }

        $msg = (string) ($failed->exception ?? '');
        if ($msg !== '' && str_contains($msg, "\n")) {
            $msg = explode("\n", $msg, 2)[0] ?? $msg;
        }

        $updated = array_merge($status, [
            'state' => 'failed',
            'finished_at' => now()->toIso8601String(),
            'message' => $msg !== '' ? $msg : 'Job gagal (lihat failed_jobs).',
        ]);

        Cache::put($statusKey, $updated, now()->addHours(6));
        return $updated;
    }


    public function uploadPlugin(Request $request)
    {
        abort_if(!is_main_domain(), 403);

        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:50000',
        ]);

        $file = $request->file('plugin_file');
        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) === true) {
            $extractPath = storage_path('app/temp_plugins/' . time());
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }
            $zip->extractTo($extractPath);
            $zip->close();

            // Validate that the ZIP has exactly one root folder
            $extractedDirs = File::directories($extractPath);
            if (count($extractedDirs) !== 1) {
                File::deleteDirectory($extractPath);
                return back()->with('danger', 'Format ZIP tidak valid. ZIP harus berisi tepat satu folder utama plugin.');
            }

            $pluginFolder = $extractedDirs[0];
            $pluginName = basename($pluginFolder);

            // Deteksi nama asli dari plugin.json jika tersedia
            $jsonPath = $pluginFolder . '/plugin.json';
            if (File::exists($jsonPath)) {
                $jsonString = File::get($jsonPath);
                $jsonString = preg_replace('/^\xEF\xBB\xBF/', '', $jsonString);
                $json = json_decode($jsonString, true);
                if ($json && isset($json['name'])) {
                    $pluginName = $json['name'];
                }
            }

            $targetPath = resource_path('plugins/' . $pluginName);

            // Jika plugin sudah ada, timpa
            if (File::exists($targetPath)) {
                File::deleteDirectory($targetPath);
            }

            File::moveDirectory($extractedDirs[0], $targetPath);
            File::deleteDirectory($extractPath);

            // Run migration if any new ones were added
            Artisan::call('migrate', ['--force' => true]);

            return back()->with('success', 'Plugin berhasil diinstal.');
        }

        return back()->with('danger', 'Gagal mengekstrak file plugin.');
    }

    public function updatePlugin(Request $request)
    {
        $request->validate([
            'plugin_name' => 'required|string',
            'download_url' => 'required|url',
        ]);

        $pluginName = $request->plugin_name;
        $downloadUrl = $request->download_url;
        $targetPath = resource_path('plugins/' . $pluginName);

        if (!File::exists($targetPath)) {
            return back()->with('danger', 'Plugin tidak ditemukan.');
        }

        try {
            // Kita pakai CURL atau file_get_contents dengan header User-Agent karena GitHub memblokir req tanpa User-Agent
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: PHP\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $zipContent = file_get_contents($downloadUrl, false, $context);

            $tempZipPath = storage_path('app/temp_plugins/' . time() . '.zip');

            if (!File::exists(storage_path('app/temp_plugins'))) {
                File::makeDirectory(storage_path('app/temp_plugins'), 0755, true);
            }

            File::put($tempZipPath, $zipContent);

            $zip = new \ZipArchive;
            if ($zip->open($tempZipPath) === true) {
                $extractPath = storage_path('app/temp_plugins/ext_' . time());
                if (!File::exists($extractPath)) {
                    File::makeDirectory($extractPath, 0755, true);
                }
                $zip->extractTo($extractPath);
                $zip->close();

                $extractedDirs = \Illuminate\Support\Facades\File::directories($extractPath);
                if (count($extractedDirs) !== 1) {
                    \Illuminate\Support\Facades\File::deleteDirectory($extractPath);
                    \Illuminate\Support\Facades\File::delete($tempZipPath);
                    return back()->with('danger', 'Format ZIP dari GitHub tidak valid.');
                }

                \Illuminate\Support\Facades\File::deleteDirectory($targetPath);
                \Illuminate\Support\Facades\File::moveDirectory($extractedDirs[0], $targetPath);
                \Illuminate\Support\Facades\File::deleteDirectory($extractPath);
                \Illuminate\Support\Facades\File::delete($tempZipPath);

                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

                return back()->with('success', 'Plugin berhasil diupdate.');
            } else {
                \Illuminate\Support\Facades\File::delete($tempZipPath);
                return back()->with('danger', 'Gagal membuka file ZIP hasil unduhan.');
            }
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal mengunduh atau mengupdate plugin: ' . $e->getMessage());
        }
    }
}
