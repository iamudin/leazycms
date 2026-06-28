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
                $btn .= '<a target="_blank" href="http://' . $row->domain . '" class="btn btn-info btn-sm fa fa-globe" title="Kunjungi Website"></a>';
                $btn .= $row->id !== 1 ? '<a target="_blank" href="' . route('tenant.login', $row->id) . '" class="btn btn-primary btn-sm fa fa-sign-in" title="Auto Login ke Admin"></a>' : '';
                $btn .= '<a href="' . route('tenant.edit', $row->id) . '" class="btn btn-warning btn-sm fa fa-edit" title="Edit Tenant"></a>';
                $btn .= $row->id !== 1 ? '<button onclick="deleteAlert(\'' . route('tenant.destroy', $row->id) . '\')" class="btn btn-danger btn-sm fa fa-trash" title="Hapus Tenant"></button>' : '';
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
            ->rawColumns(['action', 'status', 'theme'])
            ->toJson();
    }

    public function create()
    {
        $themes = Theme::where('status', 'active')->get();
        $modules = collect(get_module())->whereNotIn("name", default_menu())->pluck('title', 'name');
        $availablePlugins = [];
        if (File::exists(resource_path('views/plugins'))) {
            $availablePlugins = array_map('basename', File::directories(resource_path('views/plugins')));
        }
        return view('cms::backend.tenants.form', ['tenant' => null, 'admin' => null, 'themes' => $themes, 'modules' => $modules, 'availablePlugins' => $availablePlugins]);
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
            'plugins' => 'nullable|array',
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
            'plugins' => $request->plugins,
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
        $availablePlugins = [];
        if (File::exists(resource_path('views/plugins'))) {
            $availablePlugins = array_map('basename', File::directories(resource_path('views/plugins')));
        }
        return view('cms::backend.tenants.form', compact('tenant', 'admin', 'options', 'themes', 'modules', 'availablePlugins'));
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
            'plugins' => 'nullable|array',
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
            'plugins' => $request->plugins,
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
        Cache::forget("tenant:{$tenant->domain}:options");

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
        Option::whereTenantId($tenantId)->delete();
        Cache::forget("tenant:{$domain}");
        Cache::forget("tenant:{$domain}:options");
        $tenant->delete();
        return response()->json(['success' => 'Tenant berhasil dihapus']);
    }
}
