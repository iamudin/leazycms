<?php
namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Tag;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Yajra\DataTables\DataTables;


class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array {
        return [
            new Middleware('auth')
        ];
    }
    public function index(){
        return view('cms::backend.tags.index',['tag'=>null]);
    }
    public function datatable(Request $request)
    {
        $data = Tag::withCount('posts');
        if($request->edit_id){
            $data = $data->whereNotIn('id',[$request->edit_id]);
        }
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a target="_blank" href="' .url($row->url).'"  class="btn btn-info btn-sm fa fa-globe"></a>';
                $btn .= '<a href="' . route('tag.edit', $row->id).'"  class="btn btn-warning btn-sm fa fa-edit"></a>';
                $btn .=  $row->posts->count() == 0 ?'<button onclick="deleteAlert(\'' . route('tag.destroy', $row->id).'\')" class="btn btn-danger btn-sm fa fa-trash"></button>':'';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('name', function ($row) {
                $status = $row->status=='draft' ? '<br><span class="badge badge-warning">Draft</span>': '';
                return '<span class="text-primary">'.$row->name.'</span>'.$status;
            })
            ->rawColumns(['action','name'])
            ->toJson();
}
    public function edit(Tag $tag){
        return view('cms::backend.tags.index',['tag'=>$tag]);
    }
    public function create(){
        return view('cms::backend.tags.form',['tag'=>'']);
    }
    public function store(Request $request){
        $data = $request->validate([
            'name'=> 'required|string|'.Rule::unique('tags'),
            'status'=> 'required|in:draft,publish',
            'description'=> 'required|string'
        ]);
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $request->name);
        $data['url'] = 'tags/'.$name;
        $data['name'] = $name;
        $data['slug'] = $name;
        Tag::create($data);
        return to_route('tag')->with('success','Tag diperbaharui');
    }
    public function update(Request $request, Tag $tag){
        $data = $request->validate([
            'name'=> 'required|string|'.Rule::unique('tags')->ignore($tag->id),
            'description'=> 'required|string',
            'status'=> 'required|in:draft,publish'
        ]);
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $request->name);
        $data['url'] = 'tags/'.$name;
        $data['name'] = $name;
        $data['slug'] = $name;
        $tag->update($data);
        return to_route('tag')->with('success','Tag diperbaharui');
    }
    public function destroy(Tag $tag){
        if($tag->posts->count()){
            return route('dashboard');
        }
        $tag->delete();

    }
}

