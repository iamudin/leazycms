<?php
namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Leazycms\Web\Models\Category;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth')
        ];
    }
    public function index(Request $request)
    {
        $request->user()->hasRole('category' . get_post_type(), __FUNCTION__);
        $dothing = !$request->user()->hasRole('category' . get_post_type(), 'create', 'noredirect') ? true : false;
        return view('cms::backend.categories.index', ['category' => null, 'dothing' => $dothing]);
    }
    public function datatable(Request $request)
    {
        $isMainDomain = is_main_domain();
        $data = Category::whereType(get_post_type())->withCount('posts')->orderBy('sort');
        if (config('modules.multisite_enabled')) {
            $data = $data->withTenant();
        }
        return DataTables::of($data)
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {

                if ($search = $request->search) {
                    if (config('modules.multisite_enabled')) {
                        $instance->whereHas('tenant', function ($query) use ($search) {
                            $query->where('domain', 'like', "{$search}%");
                        });
                    } else {
                        $instance->whereHas('name', 'like', "%{$search}%");
                    }
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a target="_blank" href="' . url($row->url) . '"  class="btn btn-info btn-sm fa fa-globe"></a>';
                $btn .= auth()->user()->isAdmin() || !auth()->user()->hasRole('category' . $row->type, 'update', true) ? '<a href="' . route(get_post_type() . '.category.edit', $row->id) . '"  class="btn btn-warning btn-sm fa fa-edit"></a>' : null;
                $btn .= !$row->posts()->exists() ? '<button onclick="deleteAlert(\'' . route(get_post_type() . '.category.destroy', $row->id) . '\')" class="btn btn-danger btn-sm fa fa-trash"></button>' : '';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('name', function ($row) {
                return '<span class="text-primary">' . $row->name . '</span>';
            })
            ->addColumn('thumbnail', function ($row) {
                return '<img class="rounded lazyload" src="/shimmer.gif" style="width:100%" data-src="' . $row->thumbnail . '?size=small"/>';

            })
            ->addColumn('created_at', function ($row) use ($isMainDomain) {
                return "<small>" . $row->created_at->translatedFormat('d F Y H:i') . "</small>" . (config('modules.multisite_enabled') && $isMainDomain ? '<br><small>' . $row->tenant?->domain . '</small>' : '');

            })
            ->rawColumns(['action', 'name', 'thumbnail', 'created_at'])
            ->toJson();
    }
    public function create(Request $request)
    {
        return to_route(get_post_type() . '.category');
    }
    public function store(Request $request)
    {
        $request->user()->hasRole('category' . get_post_type(), 'create');
        $role = config('modules.multisite_enabled') ? Rule::unique('categories')->where('type', get_post_type())->where('tenant_id', tenant()->id) : Rule::unique('categories')->where('type', get_post_type());
        $data = $request->validate([
            'name' => 'required|max:100|min:3|string|regex:/^[0-9a-zA-Z\s\p{P}]+$/u|' . $role,
            'icon' => 'nullable|mimetypes:image/jpeg,image/png,image/webp',
            'sort' => 'nullable|numeric',
            'description' => 'nullable|string|regex:/^[a-zA-Z\s\p{P}]+$/u|max:200|',
            'status' => 'required|string|in:publish,draft',
        ]);
        $data['slug'] = $slug = str($request->name)->slug();
        $data['type'] = get_post_type();
        $data['url'] = get_post_type() . '/category/' . $slug;
        $data = Category::create($data);
        if ($request->hasFile('icon')) {
            $fname = $data->addFile([
                'file' => $request->file('icon'),
                'puprose' => 'categoryicon_' . $data->id,
                'mime_type' => ['image/jpeg', 'image/png']
            ]);
            $data->update([
                'icon' => $fname

            ]);
        }
        return back()->with('success', 'Kategori ' . current_module()->title . ' berhasil ditambah');
    }
    public function edit(Request $request, Category $category)
    {
        $request->user()->hasRole('category' . get_post_type(), 'update');
        $dothing = !$request->user()->hasRole('category' . get_post_type(), 'update', 'noredirect') ? true : false;

        return view('cms::backend.categories.index', ['category' => $category, 'dothing' => $dothing]);
    }
    public function update(Request $request, Category $category)
    {
        $request->user()->hasRole('category' . get_post_type(), 'update');
        $role = config('modules.multisite_enabled') ? Rule::unique('categories')->where('type', get_post_type())->where('tenant_id', tenant()->id)->ignore($category->id) : Rule::unique('categories')->where('type', get_post_type())->ignore($category->id);
        $data = $request->validate([
            'name' => 'required|max:100|min:3|string|' . $role,
            'icon' => 'nullable|mimetypes:image/jpeg,image/png,image/webp',
            'sort' => 'nullable|numeric',
            'description' => 'max:200|nullable|string|regex:/^[a-zA-Z\s\p{P}]+$/u',
            'status' => 'required|string|in:publish,draft',
        ]);
        $data['slug'] = $slug = str($request->name)->slug();
        $data['type'] = get_post_type();
        $data['url'] = get_post_type() . '/category/' . $slug;
        if ($request->hasFile('icon')) {
            $fname = $category->addFile([
                'file' => $request->file('icon'),
                'puprose' => 'categoryicon_' . $category->id,
                'mime_type' => ['image/jpeg', 'image/png']
            ]);
            $data['icon'] = $fname;

        }
        $category->update($data);

        return to_route(get_post_type() . '.category')->with('success', 'Kategori ' . current_module()->title . ' berhasil ditambah');
    }
    public function destroy(Request $request, Category $category)
    {
        if (!$request->user()->hasRole('category' . get_post_type(), 'delete')) {
            $category->forceDelete();
        }
    }
}

