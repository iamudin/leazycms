<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Leazycms\Web\Models\Category;
use Leazycms\Web\Models\PollingResponse;
use Leazycms\Web\Models\PollingTopic;
use Leazycms\Web\Models\Post;
use Leazycms\Web\Models\Tag;
use Leazycms\Web\Models\User;

class WebController extends Controller
{

    public function pollingsubmit(Request $request)
    {
        $polling = PollingTopic::select('id', 'keyword', 'duration')->find($request->topic);
        if ($polling && empty($request->cookie('polling_' . $request->keyword))) {
            PollingResponse::create([
                'polling_option_id' => $request->answer,
                'ip' => $request->ip(),
                'reference' => $request->headers->get('referer') ?? '',
            ]);
            $cookieName = 'polling_' . $polling->keyword;
            $cookieValue = 'voted';
            $duration = $polling->duration;
            return response('')
                ->cookie($cookieName, $cookieValue, $duration);
        }
    }

    public function home(Request $request)
    {
        if ($request->isMethod('post') && $request->has('_validate_file')) {
            $referer = $request->headers->get('referer');
            if ($referer && str_starts_with($referer, url('/'))) {
                return app(\Leazycms\Web\Http\Controllers\ExtController::class)->validate_file($request);
            }
        }

        $hp = get_option('home_page');
        if ($hp != 'default' && View::exists('template.' . template() . '.' . str_replace('.blade.php', '', $hp))) {
            return view('template.' . template() . '.' . str_replace('.blade.php', '', $hp));
        }
        return view('cms::layouts.master');
    }

    public function api(Request $req, Post $post, $id = null)
    {
        $allowIp = get_option('allow_ip');
        if (empty($allowIp) || ($allowIp && !in_array(get_client_ip(), explode(",", $allowIp)))) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        if ($id) {
            return response([
                'code' => 200,
                'status' => "success",
                'data' => $post->selectedColumn()->with('user')->whereStatus('publish')->findOrFail($id)
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
        config(['modules.page_name' => $modul->title]);
        $perPage = $modul->web->post_perpage ?? get_option('post_perpage') ?? 10;
        $data = array(
            'index' => $modul->web->auto_query ? $post->index($modul->name, $perPage) : [],
            'module' => $modul,
        );

        return view('cms::layouts.master', $data);
    }
    public function tags($slug)
    {
        $tag = Tag::select('name', 'visited', 'id', 'slug', 'url')->whereSlug(str($slug)->lower())->first();
        if (empty($tag)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }

        if ($tag->name != $slug) {
            return redirect($tag->url);
        }
        config(['modules.page_name' => $tag->name]);

        $tag->timestamps = false;
        $tag->increment('visited');

        $post = Post::selectedColumn()->whereHas('tags', function ($query) use ($slug) {
            $query->where('slug', $slug)->whereStatus('publish');
        })->whereStatus('publish')->paginate(get_option('post_perpage'));

        $data = array(
            'index' => $post,
            'tag' => $tag
        );
        if (View::exists('template.' . template() . '.tags.' . $tag->slug)) {
            return view('template.' . template() . '.tags.' . $tag->slug, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function author(Request $request, $u = null)
    {
        if ($u) {
            $user = User::select('id', 'name', 'url', 'photo', 'slug')->whereSlug($u)->first();
            if (empty($user)) {
                return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
            }
            config(['modules.page_name' => 'Author: ' . $user->name]);
            $data = [
                'index' => $user->posts()->paginate(10)
            ];
            return view('cms::layouts.master', $data);
        } else {
            $author = User::select('id', 'name', 'url', 'photo', 'slug')
                ->whereHas('posts')
                ->where('status', 'active')
                ->whereNotIn('level', ['admin'])
                ->get();
            config(['modules.page_name' => 'Author']);
            $data = [
                'author' => $author
            ];
            return view('cms::layouts.master', $data);
        }

    }
    public function detail(Request $request, Post $post, $name = null)
    {
        $slug = Str::of($name)
            ->replaceMatches('/[^\p{L}\p{N}-]/u', '')
            ->toString();
        if (strlen($slug) < 5) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        $postType = get_post_type() ?? 'page';
        $modul = get_module($postType);
        $detail = $post->detail($postType, $slug);
        if (empty($detail)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }

        if ($request->ajax() && $request->isMethod('post')) {
            $request->validate([
                'name' => 'required'
            ]);
            if (Session::get('captcha') == $request->captcha) {

                $detail->addComment([
                    'name' => strip_tags(substr($request->name, 0, 20)),
                    'email' => strip_tags(substr($request->email, 0, 50) ?? null),
                    'ip' => get_client_ip(),
                    'content' => nl2br(strip_tags(substr($request->comment_content, 0, 500) ?? null)),
                    'link' => strip_tags($request->link ?? null),
                    'comment_meta' => $request->comment_meta ? cleanArrayValues($request->comment_meta) : [],
                ]);
                $request->session()->regenerateToken();
                return response()->json(['error' => 'None'], 200);
            } else {
                $request->session()->regenerateToken();
                return response()->json(['error' => 'Captcha'], 200);
            }
        }
        if ($detail->slug != $name) {
            if ($detail->shortcut == $slug) {
                $detail->increment('shortcut_counter');
            }
            return redirect($detail->url);
        }
        if (config('modules.multisite_enabled') && is_main_domain() && $detail->tenant_id) {
            if ($detail->tenant_id != tenant()->id) {
                return redirect('http://' . $detail->tenant->domain . '/' . $detail->url);
            }
        }

        config(['modules.data' => $detail]);
        if ($detail->redirect_to) {
            return redirect($detail->redirect_to);
        }

        $data = array(
            'module' => $modul,
            'category' => $detail->category ?? null,
            'detail' => $detail,
            'history' => $detail->history
        );

        if (!empty($detail->password)) {

            $sessionKey = "post_access_{$detail->id}";

            // Kalau belum submit
            if (!$request->isMethod('post')) {
                if (Session::has($sessionKey)) {
                    $expiredAt = Session::get($sessionKey);
                    if (Carbon::now()->lt($expiredAt)) {

                    } else {

                        Session::forget($sessionKey);
                        return redirect()->to($request->url());
                    }
                } else {
                    return response(protectedContentView($slug));
                }
            } else {


                // Validasi input
                $request->validate([
                    'secret_key' => 'required|digits:4'
                ]);

                // Cek password
                if ($request->secret_key !== dec64($detail->password)) {
                    return response(protectedContentView(
                        $slug,
                        null,
                        'Kode salah, coba lagi.'
                    ));
                }

                session([
                    $sessionKey => Carbon::now()->addMinutes(1)
                ]);
                return redirect()->to($request->url());
            }
        }

        if (View::exists('template.' . template() . '.' . $detail->type . '.' . $detail->slug)) {
            return view('template.' . template() . '.' . $detail->type . '.' . $detail->slug, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function category($slug = null)
    {
        $modul = get_module(get_post_type());
        $category = Category::where('slug', 'like', $slug . '%')
            ->whereType($modul->name)
            ->whereStatus('publish')
            ->whereHas('posts')
            ->first();

        if (!$category) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        if ($category->slug != $slug) {
            return redirect($category->url);
        }
        config(['modules.page_name' => $modul->title . ' di kategori ' . $category->name]);
        $category->timestamps = false;
        $category->increment('visited');
        $perPage = $modul->web?->post_perpage ?? get_option('post_perpage');
        $data = array(
            'index' => query()->index_by_category($modul->name, $slug, $perPage),
            'category' => $category,
            'module' => $modul
        );
        return view('cms::layouts.master', $data);
    }
    public function search(Request $request, $slug = null)
    {
        if ($request->isMethod('post') && $request->keyword) {
            return redirect('search/' . str($request->keyword)->slug());
        }
        if (empty($slug)) {
            return to_route('home');
        }
        $query = str_replace('-', ' ', str($slug)->slug());
        $modules = get_module();
        $type = collect($modules)
            ->where('public', true)
            ->where('web.detail', true)
            ->where('web.index', true)
            ->pluck('name')->toArray();

        $index = Post::selectedColumn()
            ->whereIn('type', $type)
            ->where('type', '!=', 'page')
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('keyword', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(get_option('post_perpage'));

        $data = array(
            'keyword' => ucwords($query),
            'index' => $index
        );
        return view('cms::layouts.master', $data);
    }

    public function post_parent(Request $request, $slug = null)
    {
        $modul = get_module(get_post_type());
        if (empty($slug)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        $post_parent = query()->onType($modul->form->post_parent[1])->where('slug', 'like', $slug . '%')
            ->select('id', 'title', 'slug')->published()->first();
        if (empty($post_parent)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        if ($post_parent->slug != $slug) {
            return redirect($modul->name . '/' . $request->segment(2) . '/' . $post_parent->slug);
        }
        $title = $post_parent->title;
        $post_name = $modul->title;
        config(['modules.page_name' => $modul->title . ' di ' . $modul->form->post_parent[0] . ' ' . $title]);
        $index = query()->index_child($modul->name, $post_parent->id, true);
        $data = array(
            'index' => $index,
            'title' => $post_name . ' ' . $title,
            'icon' => $modul->icon,
            'module' => $modul
        );
        return view('cms::layouts.master', $data);
    }
    public function archive(Request $request, Post $post, $year = null, $month = null, $date = null)
    {
        $type = get_post_type();
        $module = get_module($type);
        if ($year > date('Y')) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }

        $perPage = $module->web?->post_perpage ?? get_option('post_perpage');

        if ($year && !$month && !$date) {
            $periode = $year;
            $data = $post->onType($type)->published()->whereYear('created_at', $year)->paginate($perPage);
        } elseif ($year && $month && !$date) {

            $periode = blnindo($month) . ' ' . $year;
            $data = $post->onType($type)
                ->published()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->paginate($perPage);

        } elseif ($year && $month && $date) {


            $periode = ((substr($date, 0, 1) == '0') ? substr($date, 1, 2) : $date) . ' ' . blnindo($month) . ' ' . $year;
            $data = $post->onType($type)
                ->published()
                ->whereDate('created_at', $year . '-' . $month . '-' . $date)
                ->paginate($perPage);

        }

        $data = array(
            'title' => 'Arsip ' . $module->title . ' ' . $periode,
            'icon' => 'fa-archive',
            'index' => $data
        );
        config(['modules.page_name' => $data['title']]);
        return view('cms::layouts.master', $data);
    }
}
