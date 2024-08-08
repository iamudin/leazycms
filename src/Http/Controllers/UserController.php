<?php
namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Http\Request;
use Leazycms\Web\Models\User;
use Leazycms\Web\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Closure;
class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array {
        return [
            new Middleware('auth'),
            function (Request $request, Closure $next) {
                if(!$request->user()->isAdmin()){
                    return redirect()->route('panel.dashboard')->send()->with('danger','Akses hanya admin');
                }
                return $next($request);
            },
        ];

    }
    public function index()
    {

        return view('cms::backend.users.index');
    }
    public function account(Request $request){
        $user = $request->user();
        if($request->isMethod('post')){
            $data= $request->validate([
                'photo'=> 'nullable|file|mimetypes:image/jpeg,image/png',
                'name'=> 'required|string',
                'username'=>'required|string|regex:/^[a-zA-Z\p{P}]+$/u|'.Rule::unique('users')->ignore($user->id),
                'email'=> 'required|string|regex:/^[a-zA-Z\p{P}]+$/u|'.Rule::unique('users')->ignore($user->id),
                'password'=>'nullable|string|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',
            ]);
            $data['photo'] = $user->photo;
            if($request->hasFile('photo')){
                $data['photo'] = upload_media($user,$request->file('photo'),'author_photo','user');
            }
            if($pass = $request->password){
                $data['password'] = bcrypt($pass);
            }else{
                $data['password'] = $user->password;

            }

            User::findOrFail($user->id)->update($data);
            return back()->with('success','Berhasil perbarui Akun');
        }
        return view('cms::backend.users.account',
        ['user'=>$user]);

     }
    public function datatable(Request $request)
    {
        $data = User::withCount('posts')->where('level','!=','admin');
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<a target="_blank" href="' .url($row->url).'"  class="btn btn-info btn-sm fa fa-globe"></a>';
                $btn .= '<a href="' . route('user.edit', $row->id).'"  class="btn btn-warning btn-sm fa fa-edit"></a>';
                $btn .= $row->posts->count() ==0 ? '<button onclick="deleteAlert(\'' . route('user.destroy', $row->id).'\')" class="btn btn-danger btn-sm fa fa-trash"></button>':'';
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('name', function ($row) {
                return '<span class="text-primary">'.$row->name.'</span><br><small class="text-muted">'.$row->email.'</small>';
            })
            ->addColumn('role', function ($row) {
                return $row->name;
            })
            ->addColumn('username', function ($row) {
                return $row->username;
            })
            ->addColumn('status', function ($row) {
                $active = '<span class="badge bg-success text-white">Aktif</span>';
                $nonactive = '<span class="badge bg-danger text-white">Diblokir</span>';
                return $row->status=='active' ? $active : $nonactive;
            })
            ->addColumn('photo', function ($row) {
                return '<img src="'.$row->userphoto.'" height="50" class="rounded">';
            })
            ->rawColumns(['action','name','status','photo','role','username'])
            ->toJson();
}
public function create(){
    return view('cms::backend.users.form',['role'=>null,'user'=>null]);
}
public function store(Request $request){
    $data = $request->validate([
        'name'=>'required|string|regex:/^[a-zA-Z\s\p{P}]+$/u',
        'foto'=> 'nullable|mimetypes:image/jpeg,image/png',
        'email'=>'required|email|'.Rule::unique('users'),
        'username'=>'required|string|regex:/^[a-zA-Z\p{P}]+$/u|'.Rule::unique('users'),
        'status'=>'required|string|in:active,blocked',
        'level'=>'required|string|in:'.get_option('roles'),
        'password'=>'required|string|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',
    ]);
    $data['slug'] = $slug = str($request->name)->slug();
    $data['url'] = 'author/'.$slug;
    $data['level'] = $request->level;
    $data['password'] = bcrypt($request->password);
    $data['host'] = $request->getHost();
    $data = User::create($data);
    if($request->hasFile('photo')){
        $data->update(['photo'=>upload_media($data,$request->file('photo'),'author_photo','user')]);
    }
    return to_route('user.edit',$data->id)->with('success','User berhasil ditambah');
}
public function edit(User $user){
    return view('cms::backend.users.form',['user'=>$user]);
}
public function update(Request $request, User $user){
    $data = $request->validate([
        'name'=>'required|string|regex:/^[a-zA-Z\s\p{P}]+$/u',
        'foto'=> 'nullable|mimetypes:image/jpeg,image/png',
        'email'=>'required|email|'.Rule::unique('users')->ignore($user->id),
        'username'=>'required|string|regex:/^[a-zA-Z\p{P}]+$/u|'.Rule::unique('users')->ignore($user->id),
        'status'=>'required|string|in:active,blocked',
        'level'=>'required|string|in:'.get_option('roles'),
        'password'=>'nullable|string|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',
    ]);
    $data['slug'] = $slug = str($request->name)->slug();
    $data['url'] = 'author/'.$slug;
    $data['host'] = $request->getHost();
    if($request->password){
    $data['password'] = bcrypt($request->password);
    }
    $user->update($data);
    if($request->hasFile('photo')){
        $user->update(['photo'=>upload_media($user,$request->file('photo'),'author_photo','user')]);
    }
    return back()->with('success','User  berhasil diupdate');
}
public function destroy(User $user){
    $user->delete();
}

public function roleIndex(){
    abort_if(!get_option('roles'),404);
    $role = Role::get();
    return view('cms::backend.users.role',compact('role'));
}
public function roleUpdate(Request $request){
    $role = array();
    foreach($request->except('_token') as $key =>$r){
        $data = explode('_',$key);
        foreach($data as $n=>$r){
        if($n==0){
            $k['level'] = $r;
        }elseif($n==1){
            $k['module'] = $r;

        }else{
            $k['action'] = $r;

        }
        }
        array_push($role,$k);
    }
    Role::whereNotNull('id')->delete();
    foreach($role as $r){
        Role::create($r);
    }
        return back()->with('success','Hak akses diperbarui');
}
}

