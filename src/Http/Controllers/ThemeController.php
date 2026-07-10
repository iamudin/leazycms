<?php

namespace Leazycms\Web\Http\Controllers;

use Closure;
use ZipArchive;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Theme;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class ThemeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            function (Request $request, Closure $next) {
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
        return view('cms::backend.themes.index');
    }

    public function datatable()
    {
        $data = Theme::query()->with('tenants')->latest();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a href="' . route('theme.edit', $row->id) . '" class="btn btn-warning btn-sm " title="Edit"><i class="fa  fa-edit"></i></a>';
                $btn .= '<button onclick="updateTheme(' . $row->id . ')" class="btn btn-info btn-sm " title="Update dari Git"><i class="fa fa-refresh"></i></button>';
                $btn .= '<button onclick="deleteAlert(\'' . route('theme.destroy', $row->id) . '\')" class="btn btn-danger btn-sm " title="Hapus"><i class="fa fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('tenants', function ($row) {
                return $row->tenants->count();
            })
            ->addColumn('status', function ($row) {
                return $row->status == 'active' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>';
            })
            ->rawColumns(['action', 'status', 'tenants'])
            ->toJson();
    }

    public function create()
    {
        return view('cms::backend.themes.form', ['theme' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'path' => 'required|string|max:100|unique:themes,path',
            'git' => 'required|url',
            'status' => 'required|in:active,inactive',
        ]);

        $theme = Theme::create($request->all());

        $this->downloadFromGit($theme);

        return to_route('theme.index')->with('success', 'Tema berhasil ditambah');
    }

    public function edit(Theme $theme)
    {
        return view('cms::backend.themes.form', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'path' => 'required|string|max:100|unique:themes,path,' . $theme->id,
            'git' => 'required|url',
            'status' => 'required|in:active,inactive',
        ]);

        $theme->update($request->all());

        return to_route('theme.index')->with('success', 'Tema berhasil diupdate');
    }

    public function destroy(Theme $theme)
    {
        $themePath = resource_path('views/template/' . $theme->path);
        if (File::exists($themePath)) {
            File::deleteDirectory($themePath);
        }
        $theme->delete();
        return response()->json(['success' => 'Tema berhasil dihapus']);
    }

    public function updateFromGit(Theme $theme)
    {
        if ($this->downloadFromGit($theme)) {
            return response()->json(['success' => 'Tema berhasil diperbarui dari Git']);
        }
        return response()->json(['error' => 'Gagal memperbarui tema'], 500);
    }

    private function downloadFromGit(Theme $theme)
    {
        $themePath = resource_path('views/template/' . $theme->path);

        // Buat folder template jika belum ada
        if (!File::exists(resource_path('views/template'))) {
            File::makeDirectory(resource_path('views/template'), 0755, true);
        }

        try {
            // Contoh URL Git: https://github.com/user/repo
            // Kita butuh zip: https://github.com/user/repo/archive/refs/heads/main.zip
            $zipUrl = rtrim($theme->git, '/') . '/archive/refs/heads/main.zip';

            $response = Http::get($zipUrl);
            if ($response->failed()) {
                // Coba master jika main gagal
                $zipUrl = rtrim($theme->git, '/') . '/archive/refs/heads/master.zip';
                $response = Http::get($zipUrl);
            }

            if ($response->successful()) {
                $tempFile = tempnam(sys_get_temp_dir(), 'theme');
                file_put_contents($tempFile, $response->body());

                $zip = new ZipArchive;
                if ($zip->open($tempFile) === TRUE) {
                    $extractPath = sys_get_temp_dir() . '/extract_' . time();
                    $zip->extractTo($extractPath);
                    $zip->close();

                    // Folder hasil extract biasanya repo-main atau repo-master
                    $directories = File::directories($extractPath);
                    if (count($directories) > 0) {
                        $sourcePath = $directories[0];

                        if (File::exists($themePath)) {
                            File::deleteDirectory($themePath);
                        }
                        File::copyDirectory($sourcePath, $themePath);
                        File::deleteDirectory($extractPath);
                        unlink($tempFile);
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }
}
