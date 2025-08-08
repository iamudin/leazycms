<?php
namespace Leazycms\Web\Http\Controllers;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Tag;
use Leazycms\Web\Models\Post;
use Leazycms\Web\Models\User;
use Leazycms\Web\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Leazycms\Web\Models\PollingTopic;
use Leazycms\Web\Models\PollingResponse;
use Leazycms\Web\Http\Controllers\VisitorController;

class WebController extends Controller
{
    public function __construct(Request $request)
    {   initial_helper();

    }
    public function pollingsubmit(Request $request){
        $polling = PollingTopic::find($request->topic);
        if(empty($request->cookie('polling_'.$request->keyword))){
        PollingResponse::create([
            'polling_option_id' => $request->answer,
            'ip'=>$request->ip(),
            'reference' => $request->headers->get('referer') ?? '',
        ]);
            $cookieName = 'polling_'.$polling->keyword;
            $cookieValue = 'voted';
            $duration = $polling->duration;
            return response('')
            ->cookie($cookieName, $cookieValue, $duration);
        }
    }

    public function home()
    {
        $hp = get_option('home_page');
        if($hp!='default' && View::exists('template.'.template().'.'.str_replace('.blade.php','',$hp))){
            return view('template.'.template().'.'.str_replace('.blade.php','',$hp));
        }
        return view('cms::layouts.master');
    }

    public function api(Request $req, Post $post, $id = null)
    {
        abort_if(get_option('allow_api_request') && !in_array($req->ip(), explode(",", get_option('allow_api_request'))), 403);
        if ($id) {
            return response([
                'code' => 200,
                'status' => "success",
                'data' => $post->with('user')->whereStatus('publish')->findOrFail($id)
            ], 200);
        }
        return response([
            'code' => 200,
            'status' => "success",
            'data' => $post->index(get_post_type(), true)
        ], 200);
    }
    public function index(Post $post)
    {
        $modul = current_module();
        config(['modules.page_name' =>  $modul->title]);
        $data = array(
            'index' => $modul->web->auto_query ? $post->index($modul->name, get_option('post_perpage')) : [],
            'module' => $modul,
        );
     return view('cms::layouts.master', $data);
    }
    public function tags($slug)
    {
        $tag = Tag::select('name', 'visited', 'id')->whereSlug($slug)->first();
        abort_if(empty($tag), 404);
        config(['modules.page_name' =>$tag->name]);

        $tag->timestamps = false;
        $tag->increment('visited');

        $post = Post::select((new Post)->selected)->whereHas('tags', function ($query) use ($slug) {
            $query->where('slug', $slug)->whereStatus('publish');
        })->whereStatus('publish')->paginate(get_option('post_perpage'));

        $data = array(
            'index' => $post,
            'tag' => $tag
        );
        if(View::exists('template.'.template().'.tags.'.$tag->id)){
            return view('template.'.template().'.tags.'.$tag->id, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function author(Request $request, $u = null)
    {
        if($u){
            $user = User::whereSlug($u)->first();
            abort_if(empty($user), 404);
            config(['modules.page_name' => 'Author: ' . $user->name]);
            $data = [
                'index' => $user->posts()->paginate(10)
            ];
            return view('cms::layouts.master', $data);
        }else{
            $author = User::whereHas('posts')->where('status','active')->whereNotIn('level',['admin'])->get();
            config(['modules.page_name' => 'Author']);
            $data = [
                'author' => $author
            ];
            return view('cms::layouts.master', $data);
        }

    }
    public function detail(Request $request, Post $post, $slug = false)
    {
        $modul = get_module(get_post_type() ?? 'page');
        $detail = $post->detail(get_post_type() ?? 'page', $slug);
        abort_if(empty($detail), '404');
        if ($request->ajax() && $request->isMethod('post')) {
            $request->validate([
                'name'=>'required'
            ]);
           if(session()->get('captcha')==$request->captcha){

            $detail->addComment([
                'name' => strip_tags(substr($request->name,0,20)),
                'email' => strip_tags(substr($request->email,0,50) ?? null),
                'ip' => get_client_ip(),
                'content' => nl2br(strip_tags(substr($request->comment_content,0,500) ?? null)),
                'link' => strip_tags($request->link ?? null),
                'comment_meta' => $request->comment_meta ? cleanArrayValues($request->comment_meta) :[],
            ]);
                $request->session()->regenerateToken();
                return response()->json(['error' => 'None'], 200);
           }else{
                $request->session()->regenerateToken();
                return response()->json(['error' => 'Captcha'], 200);
           }
        }
        if ($detail->slug != $slug) {
            if($detail->shortcut == $slug){
                $detail->increment('shortcut_counter');
            }
            return redirect($detail->url);
        }

        config(['modules.data' => $detail]);
        (new VisitorController)->visitor_counter();

        if ($detail->redirect_to) {
            return redirect($detail->redirect_to);
        }

        $data = array(
            'module' => $modul,
            'category' => $detail->category ?? null,
            'detail' => $detail,
            'history' => $detail->history
        );
        if(View::exists('template.'.template().'.'.$detail->type.'.'.$detail->slug)){
        return view('template.'.template().'.'.$detail->type.'.'.$detail->slug, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function category($slug = null)
    {
        $modul = get_module(get_post_type());
        $category = Category::where('slug', 'like', $slug . '%')->whereType($modul->name)->whereStatus('publish')->select('name', 'slug', 'url','icon')->first();
        abort_if(!$category, '404');
        if ($category->slug != $slug)
            return redirect($category->url);

        config(['modules.page_name' => $modul->title . ' di kategori ' . $category->name]);
        $category->timestamps = false;
        $category->increment('visited');
        $data = array(
            'index' => (new Post)->index_by_category($modul->name, $slug),
            'category' => $category,
            'module' => $modul
        );
        return view('cms::layouts.master', $data);
    }
    public function search(Request $request,  $slug = null)
    {
        if ($request->isMethod('post') && $request->keyword){
            return redirect('search/' . str($request->keyword)->slug());
        }
        if(empty($slug)){
            return to_route('home');
        }
        $query = str_replace('-', ' ', str($slug)->slug());
        $type = collect(get_module())->where('public', true)->where('web.detail', true)->pluck('name')->toArray();
        $index = Post::select((new Post)->selected)->wherein('type', $type)
            ->where('title', 'like', '%' . $query . '%')
            ->orwhere('keyword', 'like', '%' . $query . '%')
            ->orwhere('description', 'like', '%' . $query . '%')
            ->orwhere('content', 'like', '%' . $query . '%')
            ->published()
            ->latest('created_at')
            ->paginate(20);
        $data = array(
            'keyword' => ucwords($query),
            'index' => $index
        );
        return view('cms::layouts.master', $data);
    }

    public function post_parent(Post $post, Request $request, $slug = null)
    {
        $modul = get_module(get_post_type());
        abort_if(empty($slug), '404');
        $post_parent = $post->where('type', $modul->post_parent[1])->where('slug', 'like', $slug . '%')->select('id', 'title', 'slug')->first();
        abort_if(empty($post_parent), '404');
        if ($post_parent->slug != $slug){
            return redirect($modul->name . '/' . $request->segment(2) . '/' . $post_parent->slug);
        }
        $title = $post_parent->title;
        $post_name = $modul->title;
        config(['modules.page_name' => $post_name . ' ' . $title]);
        $index = $post->index_child($modul->name,$post_parent->id,true);
        $data = array(
        'index' => $index,
        'title' => $post_name . ' ' . $title, 'icon' => $modul->icon,
        'post_type' => $modul->name
        );
        return view('views::layouts.master', $data);
    }
    public function archive(Request $request, Post $post, $year = null, $month = null, $date = null)
    {
        if ($year && !$month && !$date) {
            if (is_year($year)) {
                $periode = $year;
                $data = $post->whereType(get_post_type())->whereStatus('publish')->whereYear('created_at', $year)->paginate(get_option('post_perpage'));
            } else {
                return to_route(get_post_type() . '.archive', []);
            }
        } elseif ($year && $month && !$date) {

            if (is_year($year) && is_month($month)) {
                $periode = blnindo($month) . ' ' . $year;
                $data = $post->whereType(get_post_type())
                    ->whereStatus('publish')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->paginate(get_option('post_perpage'));
            } else {
                return to_route(get_post_type() . '.archive', [$year]);
            }
        } elseif ($year && $month && $date) {

            if (is_year($year) && is_month($month) && is_day($date)) {
                $periode = ((substr($date, 0, 1) == '0') ? substr($date, 1, 2) : $date) . ' ' . blnindo($month) . ' ' . $year;
                $data = $post->whereType(get_post_type())->whereStatus('publish')
                    ->whereDate('created_at', $year . '-' . $month . '-' . $date)
                    ->paginate(get_option('post_perpage'));
            } else {
                return to_route(get_post_type() . '.archive', [$year, $month]);
            }
        } else {
            return to_route(get_post_type() . '.archive', [date('Y')]);
        }

        $data = array(
            'title' => 'Arsip ' . get_module(get_post_type())->title . ' ' . $periode,
            'icon' => 'fa-archive',
            'index' => $data
        );
        config(['modules.page_name' => $data['title']]);
        return view('cms::layouts.master', $data);
    }
}
