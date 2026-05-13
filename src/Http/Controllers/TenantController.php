<?php

namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Leazycms\Web\Models\Option;
use Leazycms\Web\Models\Tenant;
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
        $data = Tenant::query()->where('domain', '<>', parse_url(main_domain(), PHP_URL_HOST))->latest();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a target="_blank" href="http://' . $row->domain . '" class="btn btn-info btn-sm fa fa-globe" title="Kunjungi Website"></a>';
                $btn .= '<a target="_blank" href="' . route('tenant.login', $row->id) . '" class="btn btn-primary btn-sm fa fa-sign-in" title="Auto Login ke Admin"></a>';
                $btn .= '<a href="' . route('tenant.edit', $row->id) . '" class="btn btn-warning btn-sm fa fa-edit" title="Edit Tenant"></a>';
                $btn .= '<button onclick="deleteAlert(\'' . route('tenant.destroy', $row->id) . '\')" class="btn btn-danger btn-sm fa fa-trash" title="Hapus Tenant"></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('status', function ($row) {
                return $row->status == 'active' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>';
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    public function create()
    {
        return view('cms::backend.tenants.form', ['tenant' => null, 'admin' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'required|string|max:100|unique:tenants,domain',
            'status' => 'required|in:active,inactive',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'admin_username' => 'required|string|min:5|unique:users,username',
            'admin_password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',
        ], [
            'admin_password.regex' => 'Password admin harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.',
        ]);

        $domain = $request->domain;
        if (filter_var($domain, FILTER_VALIDATE_URL)) {
            $domain = parse_url($domain, PHP_URL_HOST);
        }

        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $domain,
            'status' => $request->status,
        ]);

        // Create Admin for Tenant
        User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'username' => $request->admin_username,
            'password' => bcrypt($request->admin_password),
            'host' => $domain,
            'level' => 'admin',
            'status' => 'active',
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
        $admin = User::where('host', $tenant->domain)->where('level', 'admin')->first();
        $options = Option::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->pluck('value', 'name')->toArray();
        return view('cms::backend.tenants.form', compact('tenant', 'admin', 'options'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $admin = User::where('host', $tenant->domain)->where('level', 'admin')->first();

        $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'required|string|max:100|unique:tenants,domain,' . $tenant->id,
            'status' => 'required|in:active,inactive',
            'admin_name' => 'required|string|max:100',
            'admin_email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin?->id)],
            'admin_username' => ['required', 'string', 'min:5', Rule::unique('users', 'username')->ignore($admin?->id)],
            'admin_password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',
        ], [
            'admin_password.regex' => 'Password admin harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, serta simbol.',
        ]);

        $oldDomain = $tenant->domain;
        $domain = $request->domain;
        if (filter_var($domain, FILTER_VALIDATE_URL)) {
            $domain = parse_url($domain, PHP_URL_HOST);
        }

        $tenant->update([
            'name' => $request->name,
            'domain' => $domain,
            'status' => $request->status,
        ]);

        // Update or Create Admin
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

    private function saveTenantOptions($tenant, $request)
    {
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
        $domain = $tenant->domain;
        $tenantId = $tenant->id;
        $tenant->delete();
        Cache::forget("tenant:{$domain}");
        Cache::forget("tenant:{$tenantId}:options");
        return response()->json(['success' => 'Tenant berhasil dihapus']);
    }
}
