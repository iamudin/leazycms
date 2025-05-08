<?php
namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Category;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array {
        return [
            new Middleware('auth')
        ];
    }
    public function index(Request $request)
    {
        $request->user()->hasRole('category'.get_post_type(),__FUNCTION__);
        return view('cms::backend.categories.index',['category'=>null]);
    }
    public function datatable(Request $request)
    {
        $data = Category::whereType(get_post_type())->withCount('posts')->orderBy('sort');
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a target="_blank" href="' .url($row->url).'"  class="btn btn-info btn-sm fa fa-globe"></a>';
                $btn .= '<a href="' . route(get_post_type().'.category.edit', $row->id).'"  class="btn btn-warning btn-sm fa fa-edit"></a>';
                $btn .= !$row->posts()->exists() ? '<button onclick="deleteAlert(\'' . route(get_post_type().'.category.destroy', $row->id).'\')" class="btn btn-danger btn-sm fa fa-trash"></button>':'';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('name', function ($row) {
                return '<span class="text-primary">'.$row->name.'</span>';
            })
            ->rawColumns(['action','name'])
            ->toJson();
}
public function create(Request $request){
   return to_route(get_post_type().'.category');
}
public function store(Request $request){
    $request->user()->hasRole('category'.get_post_type(),'create');

    $data = $request->validate([
        'name'=>'required|max:100|min:5|string|regex:/^[0-9a-zA-Z\s\p{P}]+$/u|'. Rule::unique('categories')->where('type',get_post_type()),
        'icon'=> 'nullable|mimetypes:image/jpeg,image/png',
        'sort'=>'nullable|numeric',
        'description'=>'nullable|string|regex:/^[a-zA-Z\s\p{P}]+$/u|max:200|',
        'status'=>'required|string|in:publish,draft',
    ]);
    $data['slug'] = $slug = str($request->name)->slug();
    $data['type'] = get_post_type();
    $data['url'] = get_post_type().'/category/'.$slug;
    $data = Category::create($data);
        if($request->hasFile('icon')){
            $fname = $data->addFile([
                'file'=>$request->file('icon'),
                'puprose'=>'categoryicon_'.$data->id,
                'mime_type'=>['image/jpeg','image/png']
            ]);
            $data->update([
                'icon'=> $fname

            ]);
        }
    return back()->with('success','Kategori '.current_module()->title.' berhasil ditambah');
}
public function edit(Request $request,Category $category){
    $request->user()->hasRole('category'.get_post_type(),'update');
    return view('cms::backend.categories.index',['category'=>$category]);
}
public function update(Request $request, Category $category){
    $request->user()->hasRole('category'.get_post_type(),'update');

    $data = $request->validate([
        'name'=>'required|max:100|min:5|string|regex:/^[0-9a-zA-Z\s\p{P}]+$/u|'.Rule::unique('categories')->where('type',get_post_type())->ignore($category->id),
        'icon'=> 'nullable|mimetypes:image/jpeg,image/png',
        'sort'=>'nullable|numeric',
        'description'=>'max:200|nullable|string|regex:/^[a-zA-Z\s\p{P}]+$/u',
        'status'=>'required|string|in:publish,draft',
    ]);
    $data['slug'] = $slug = str($request->name)->slug();
    $data['type'] = get_post_type();
    $data['url'] = get_post_type().'/category/'.$slug;
    if($request->hasFile('icon')){
        $fname = $category->addFile([
            'file'=>$request->file('icon'),
            'puprose'=>'categoryicon_'.$category->id,
            'mime_type'=>['image/jpeg','image/png']
        ]);
        $data['icon'] = $fname;

    }
    $category->update($data);

    return to_route(get_post_type().'.category')->with('success','Kategori '.current_module()->title.' berhasil ditambah');
}
public function destroy(Request $request,Category $category){
    $request->user()->hasRole('category'.get_post_type(),'delete');
    $category->delete();
}
}

