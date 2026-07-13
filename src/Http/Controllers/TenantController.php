<?php

namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Leazycms\Web\Models\Option;
use Leazycms\Web\Models\Tenant;
use Leazycms\Web\Models\Theme;
use Leazycms\Web\Models\User;
use Yajra\DataTables\DataTables;

class TenantController extends Controller implements HasMiddleware
{


    public function loginToTenant(Tenant $tenant)
    {

        $admin = User::where('host', $tenant->domain)->where('level', 'admin')->first();
        if (!$admin) {
            return back()->with('danger', 'Admin tenant tidak ditemukan.');
        }

        $token = Str::random(64);
        DB::table('one_time_tokens')->updateOrInsert(
            ['user_id' => $admin->id],
            [
                'token' => $token,
                'expires_at' => now()->addMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
                'tenant_id' => $tenant->id // Set manual tenant_id
            ]
        );

        $protocol = request()->secure() ? 'https://' : 'http://';
        $redirectUrl = $protocol . $tenant->domain . '/login-token/' . $token;

        return redirect($redirectUrl);
    }
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            function (Request $request, Closure $next) {
                abort_if(!config('modules.multisite_enabled'), 404);
                if (!is_main_domain()) {
                    abort(404);
                }
                if (!$request->user()->isAdmin()) {
                    return redirect()->route('panel.dashboard')->with('danger', 'Akses hanya admin');
                }
                return $next($request);
            },
        ];
    }

    public function index()
    {
        return view('cms::backend.tenants.index');
    }

    public function datatable(Request $request)
    {
        $data = Tenant::query()->with('themeSelected')->with('admin')->latest();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('theme', function ($row) {
                return $row->custom_theme ? '<span class="text-primary">' . (Str::upper(str_replace('-' . $row->id, '', $row->theme)) ?? '-') . ' (Custom)</span>' : (str($row->themeSelected?->path)->upper() ?? '-');
            })
            ->addColumn('admin', function ($row) {
                return $row->admin?->name ?? '-';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a target="_blank" href="http://' . $row->domain . '" class="btn btn-info btn-sm" title="Kunjungi Website"><i class="fa fa-globe"></i></a>';
                $btn .= $row->id !== 1 ? '<a target="_blank" href="' . route('tenant.login', $row->id) . '" class="btn btn-primary btn-sm " title="Auto Login ke Admin"> <i class="fa fa-sign-in"></i> </a>' : '';
                $btn .= '<a href="' . route('tenant.edit', $row->id) . '" class="btn btn-warning btn-sm fa fa-edit" title="Edit Tenant"></a>';
                $btn .= $row->id !== 1 ? '<button onclick="deleteAlert(\'' . route('tenant.destroy', $row->id) . '\')" class="btn btn-danger btn-sm " title="Hapus Tenant"><i class="fa fa-trash-o"></i></button>' : '';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('status', function ($row) {
                $badges = [
                    'active' => '<span class="badge badge-success">Aktif</span>',
                    'inactive' => '<span class="badge badge-danger">Nonaktif</span>',
                    'suspended' => '<span class="badge badge-warning">Suspended</span>',
                    'maintenance' => '<span class="badge badge-danger">Maintenance</span>',
                ];

                return $badges[$row->status] ?? '-';
            })
            ->addColumn('resource', function ($row) {
                $fileStats = \Leazycms\FLC\Models\File::where('host', $row->domain)
                    ->selectRaw('COUNT(id) as total_files, SUM(file_size) as total_size')
                    ->first();
                $totalSize = $fileStats->total_size ?? 0;
                $fileCount = $fileStats->total_files ?? 0;

                $formattedSize = '0 B';
                if ($totalSize > 0) {
                    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $power = floor(log($totalSize, 1024));
                    $formattedSize = number_format($totalSize / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
                }

                $html = '<div style="font-size:12px; min-width:120px;">';
                $html .= '<span class="badge badge-info mb-1"><i class="fa fa-hdd-o"></i> ' . $formattedSize . '</span> ';
                $html .= '<span class="badge badge-secondary mb-1"><i class="fa fa-file-o"></i> ' . number_format($fileCount) . ' file</span>';

                $limitMB = $row->disk_space;
                if ($limitMB > 0) {
                    $limitBytes = $limitMB * 1024 * 1024;
                    $percentage = round(($totalSize / $limitBytes) * 100, 1);
                    // Cap percentage at 100 for the progress bar width, but show actual if over 100%
                    $barWidth = min(100, $percentage);
                    $color = $percentage > 90 ? 'danger' : ($percentage > 75 ? 'warning' : 'success');
                    
                    $html .= '<div class="progress mt-1" style="height: 6px; border-radius: 3px;">';
                    $html .= '<div class="progress-bar bg-' . $color . '" role="progressbar" style="width: ' . $barWidth . '%;"></div>';
                    $html .= '</div>';
                    $html .= '<small class="text-muted d-block mt-1">' . $percentage . '% of ' . $limitMB . ' MB</small>';
                }

                $html .= '</div>';
                
                return $html;
            })
            ->rawColumns(['action', 'status', 'theme', 'resource'])
            ->toJson();
    }

    public function create()
    {
        $themes = Theme::where('status', 'active')->get();
        $modules = collect(get_module())->whereNotIn("name", default_menu())->pluck('title', 'name');
        return view('cms::backend.tenants.form', ['tenant' => null, 'admin' => null, 'themes' => $themes, 'modules' => $modules]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'required|string|max:100|unique:tenants,domain',
            'status' => 'required|in:active,inactive,suspended,maintenance',
            'theme' => 'required_unless:custom_theme,1',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'admin_username' => 'required|string|min:5|unique:users,username',
            'modules' => 'nullable|array',
            'disk_space' => 'nullable|integer|min:0',
            'admin_password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',
        ], [
            'admin_password.regex' => 'Password admin harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.',
        ]);

        $domain = $request->domain;
        if (filter_var($domain, FILTER_VALIDATE_URL)) {
            $domain = parse_url($domain, PHP_URL_HOST);
        }

        $theme = $request->theme;

        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $domain,
            'status' => $request->status,
            'theme' => $theme ?? '',
            'modules' => $request->modules,
            'disk_space' => $request->disk_space,
            'custom_theme' => $request->custom_theme ? 1 : 0,
        ]);

        if ($request->custom_theme && $theme && !Str::endsWith($theme, '-' . $tenant->id)) {
            $sourcePath = resource_path('views/template/' . $theme);
            $targetThemeName = $theme . '-' . $tenant->id;
            $targetPath = resource_path('views/template/' . $targetThemeName);

            if (File::exists($sourcePath)) {
                if (!File::exists($targetPath)) {
                    File::copyDirectory($sourcePath, $targetPath);
                }
                $tenant->update(['theme' => $targetThemeName]);
                $theme = $targetThemeName; // Update local variable for option saving
            }
        }

        // Save theme to options table as 'template'
        if ($theme) {
            DB::table('options')->updateOrInsert(
                [
                    'name' => 'template',
                    'tenant_id' => $tenant->id
                ],
                ['value' => $theme, 'autoload' => 1]
            );
        }

        // Create Admin for Tenant
        DB::table('users')->insert([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'username' => $request->admin_username,
            'password' => bcrypt($request->admin_password),
            'host' => $domain,
            'level' => 'admin',
            'status' => 'active',
            'tenant_id' => $tenant->id, // Set manual tenant_id
            'slug' => str($request->admin_name)->slug(),
            'url' => 'author/' . str($request->admin_name)->slug(),
        ]);

        // Save Options
        $this->saveTenantOptions($tenant, $request);

        Cache::forget("tenant:{$domain}");

        return to_route('tenant.index')->with('success', 'Tenant dan akun admin berhasil ditambah');
    }

    public function edit(Tenant $tenant)
    {
        $themes = Theme::where('status', 'active')->get();
        $admin = User::where('host', $tenant->domain)->where('level', 'admin')->first();
        $modules = collect(get_module())->whereNotIn("name", default_menu())->pluck('title', 'name');
        $options = Option::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->pluck('value', 'name')->toArray();
        return view('cms::backend.tenants.form', compact('tenant', 'admin', 'options', 'themes', 'modules'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $admin = User::where('host', $tenant->domain)->where('level', 'admin')->first();
        $isMainDomain = parse_url(config('app.url'), PHP_URL_HOST) == $tenant->domain;

        $rules = [
            'name' => 'required|string|max:100',
            'domain' => 'required|string|max:100|unique:tenants,domain,' . $tenant->id,
            'theme' => 'required_unless:custom_theme,1',
            'modules' => 'nullable|array',
            'disk_space' => 'nullable|integer|min:0',
        ];

        if (!$isMainDomain) {
            $rules['status'] = 'required|in:active,inactive,suspended,maintenance';
            $rules['admin_name'] = 'required|string|max:100';
            $rules['admin_email'] = ['required', 'email', Rule::unique('users', 'email')->ignore($admin?->id)];
            $rules['admin_username'] = ['required', 'string', 'min:5', Rule::unique('users', 'username')->ignore($admin?->id)];
            $rules['admin_password'] = 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/';
        }

        $request->validate($rules, [
            'admin_password.regex' => 'Password admin harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.',
        ]);

        $oldDomain = $tenant->domain;
        $domain = $request->domain;
        if (filter_var($domain, FILTER_VALIDATE_URL)) {
            $domain = parse_url($domain, PHP_URL_HOST);
        }

        $theme = $request->theme;
        $oldTheme = $tenant->theme;

        $tenant->update([
            'name' => $request->name,
            'domain' => $domain,
            'status' => $isMainDomain ? $tenant->status : $request->status,
            'theme' => $theme ?: $oldTheme,
            'modules' => $request->modules,
            'disk_space' => $request->disk_space,
            'custom_theme' => $request->custom_theme ? 1 : 0,
        ]);

        if ($request->custom_theme && $theme && !Str::endsWith($theme, '-' . $tenant->id)) {
            $sourcePath = resource_path('views/template/' . $theme);
            $targetThemeName = $theme . '-' . $tenant->id;
            $targetPath = resource_path('views/template/' . $targetThemeName);

            if (File::exists($sourcePath)) {
                if (!File::exists($targetPath)) {
                    File::copyDirectory($sourcePath, $targetPath);
                }
                $tenant->update(['theme' => $targetThemeName]);
                $theme = $targetThemeName; // Update local variable for option saving
            }
        }

        // Save theme to options table as 'template'
        DB::table('options')->updateOrInsert(
            ['name' => 'template', 'tenant_id' => $tenant->id],
            ['value' => $theme ?: $oldTheme, 'autoload' => 1]
        );

        // Update or Create Admin
        if (!$isMainDomain) {
            $userData = [
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'username' => $request->admin_username,
                'host' => $domain,
                'level' => 'admin',
                'status' => 'active',
                'slug' => str($request->admin_name)->slug(),
                'url' => 'author/' . str($request->admin_name)->slug(),
            ];

            if ($request->admin_password) {
                $userData['password'] = bcrypt($request->admin_password);
            }

            if ($admin) {
                $admin->update($userData);
            } else {
                User::create($userData);
            }
        }

        // Jika domain berubah, update semua user yang terkait dengan domain lama
        if ($oldDomain !== $domain) {
            User::where('host', $oldDomain)->update(['host' => $domain]);
        }

        // Save Options
        $this->saveTenantOptions($tenant, $request);

        Cache::forget("tenant:{$oldDomain}");
        Cache::forget("tenant:{$domain}");
        Cache::forget("tenant:{$tenant->id}:options");

        return to_route('tenant.index')->with('success', 'Tenant dan akun admin berhasil diupdate');
    }

    public static function deleteOptionsDefault()
    {
        DB::table('options')
            ->whereNull('tenant_id')
            ->whereIn('name', [
                'site_title',
                'site_url',
                'logo',
                'favicon',
                'preview',
                'template',
                'site_meta_keyword',
                'site_description',
                'address',
                'phone',
                'email',
                'fax',
                'latitude',
                'longitude',
                'facebook',
                'youtube',
                'instagram',
                'jam_kerja',
                'google_analytics_code',
                'pwa_name',
                'pwa_short_name',
                'pwa_description',
                'pwa_background_color',
                'pwa_theme_color',
                'pwa_icon_512',
                'pwa_icon_180',
                'pwa_icon_32',
                'pwa_icon_16',
            ])
            ->delete();
    }
    private function saveTenantOptions($tenant, $request)
    {

        if ($tenant->id == 1) {
            self::deleteOptionsDefault();
        }
        $options = $request->input('options', []);
        foreach ($options as $name => $value) {
            DB::table('options')->updateOrInsert(
                ['name' => $name, 'tenant_id' => $tenant->id],
                ['value' => $value, 'autoload' => 1]
            );
        }
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->id == 1) {
            return response()->json(['success' => 'Tenant utama tidak dapat dihapus']);
        }
        $domain = $tenant->domain;
        $tenantId = $tenant->id;
        query()->where('tenant_id', $tenantId)->forceDelete();
        Cache::forget("tenant:{$domain}");
        Cache::forget("tenant:{$tenantId}:options");
        $tenant->delete();
        return response()->json(['success' => 'Tenant berhasil dihapus']);
    }
}
