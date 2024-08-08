<?php
namespace Leazycms\Web\Http\Controllers;
use Leazycms\Web\Models\Tag;
use Leazycms\Web\Models\Post;
use Leazycms\Web\Models\User;
use Illuminate\Http\Request;
use Leazycms\Web\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Leazycms\Web\Http\Controllers\VisitorController;

class WebController extends Controller
{
    protected $visited;
    public function __construct(Request $request)
    {
        if (!config('modules.current.detail_visited') ) {
            $this->visited = (new VisitorController)->visitor_counter();
        }
    }

    public function home(Request $req)
    {
        return get_option('home_page') && get_option('home_page') != 'default' && View::exists('custom_view.' . get_option('home_page')) ? view('custom_view.' . get_option('home_page')) : view('cms::layouts.master');
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
        $modul = current_module();;
        config(['modules.page_name' => 'Daftar ' . $modul->title]);
        $data = array(
            'index' => $index= $modul->web->auto_query ? $post->index($modul->name, true) : [],
            'module' => $modul,
        );
     return view('cms::layouts.master', $data);
    }
    public function tags($slug)
    {
        $tag = Tag::select('name', 'visited', 'id')->whereSlug($slug)->first();
        abort_if(empty($tag), 404);
        config(['modules.page_name' => '#' . $tag->name]);

        if ($this->visited) {
            $tag->increment('visited');
        }
        $post = Post::select((new Post)->selected)->whereHas('tags', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->whereStatus('publish')->paginate(get_option('post_perpage'));

        $data = array(
            'index' => $post,
            'tag' => $tag
        );

        return view('cms::layouts.master', $data);
    }
    public function author(Request $request, $u = null)
    {
        $user = User::whereSlug($u)->first();
        abort_if(empty($user), 404);
        config(['modules.page_name' => 'Author: ' . $user->name]);
        $data = [
            'index' => $user->posts()->paginate(10)
        ];
        return view('cms::layouts.master', $data);
    }
    public function detail(Request $request, Post $post, $slug = false)
    {

        $modul = get_module(get_post_type() ?? 'halaman');
        $detail = $post->detail(get_post_type() ?? 'halaman', $slug);
        abort_if(empty($detail), '404');
        if ($request->comment_sender) {
            $detail->comments()->create([
                'name' => strip_tags($request->name),
                'email' => strip_tags($request->email),
                'content' => nl2br(strip_tags($request->content)),
                'link' => strip_tags($request->link),
            ]);
            $request->session()->regenerateToken();
            return back()->with('success', 'Tanggapan Berhasil Dikirim');
        }
        if ($detail->slug != $slug) {
            return redirect($detail->url);
        }

        config(['modules.data' => $detail]);
        (new VisitorController)->visitor_counter();

        if ($detail->redirect_to) {
            return redirect($detail->redirect_to);
        }

        if ($detail->mime == 'html') {
            return view('custom_view.' . _us($request->getHost()) . '.' . $detail->id, compact('detail'));
        }
        if ($modul->web->history) {
            $history = $post->history($detail->id, $detail->created_at);
        }
        $data = array(
            'module' => $modul,
            'category' => $detail->category ?? null,
            'detail' => $detail,
            'history' => isset($history) ? $history : null
        );
        return view('cms::layouts.master', $data);
    }
    public function category($slug = null)
    {
        $modul = get_module(get_post_type());
        $category = Category::where('slug', 'like', $slug . '%')->whereType($modul->name)->whereStatus('publish')->select('name', 'slug', 'url')->first();
        abort_if(!$category, '404');
        if ($category->slug != $slug)
            return redirect($category->url);

        config(['modules.page_name' => 'Daftar ' . $modul->title . ' di kategori ' . $category->name]);
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
        }else{
            abort_if(empty($slug), '404');
        }
        $query = str_replace('-', ' ', str($slug)->slug());
        $type = collect(get_module())->where('public', true)->where('web.detail', true)->where('web.index', true)->pluck('name')->toArray();
        $index = Post::select((new Post)->selected)->wherein('type', $type)
            ->where('title', 'like', '%' . $query . '%')
            ->orwhere('keyword', 'like', '%' . $query . '%')
            ->orwhere('description', 'like', '%' . $query . '%')
            ->where('status', 'publish')
            ->whereNotIn('type', ['halaman'])
            ->latest('created_at')
            ->paginate(get_option('post_perpage'));
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
        $post_parent = $post->where('type', $modul->post_parent[1])
            ->where('slug', 'like', $slug . '%')->select('id', 'title', 'slug')->first();
        abort_if(empty($post_parent), '404');
        if ($post_parent->slug != $slug)
            return redirect(get_post_type() . '/' . $request->segment(2) . '/' . $post_parent->slug);
        $title = $post_parent->title;
        $post_name = $modul->title;
        config(['modules.page_name' => 'Daftar ' . $post_name . ' ' . $title]);
        $index = $post->index_child($post_parent->id, get_post_type());
        $data = array('index' => $index, 'title' => $post_name . ' ' . $title, 'icon' => $modul->icon, 'post_type' => get_post_type());
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
