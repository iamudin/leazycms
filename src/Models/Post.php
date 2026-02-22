<?php

namespace Leazycms\Web\Models;

use Leazycms\FLC\Traits\Fileable;
use Leazycms\FLC\Traits\Commentable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes,Fileable,Commentable;
    public $selected = ['id','description','redirect_to','short_content','type','category_id','user_id','title','created_at','updated_at','deleted_at','parent_id','media','media_description','url','slug','data_field','pinned','sort','status','shortcut','shortcut_counter','custom_page','visited','allow_comment'];

    protected $userselectcolumn = ['id','name','url','photo'];
    protected $categoryselectcolumn = ['id','name','url','slug'];
    protected $fillable = [
        'custom_page','slug_edited','short_content','title', 'slug', 'content', 'url', 'media', 'media_description', 'keyword', 'description', 'parent_id', 'category_id', 'user_id', 'pinned', 'parent_type', 'type', 'redirect_to', 'status', 'allow_comment', 'mime', 'data_field', 'data_loop', 'created_at','sort','password','deleteable','shortcut','visited'
    ];
    protected $casts = [
        'data_field' => 'array',
        'data_loop' => 'array',
        'allow_comment' => 'string',
        'pinned' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            if ($post->isForceDeleting()) {
                $post->tags()->detach();
                foreach($post->files as $row){
                    $row->deleteFile();
                }
            }

             if (config('cache.default') === 'redis') {
            Cache::tags(["type_{$post->type}"])->flush();
        }
        });

        static::saving(function ($post) {
            if (empty($post->media) && !empty($post->content) || $post->media && !media_exists($post->media)) {
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $post->content);
                $imgs = $dom->getElementsByTagName('img');
        
                foreach ($imgs as $img) {
                    $src = $img->getAttribute('src');
                    if (strpos($src, 'data:image') !== 0) {
                        // Simpan ke dalam cache langsung saat saving
                        Cache::put('thumbnail_' . $post->slug, $src);
                        break;
                    }
                }
            }
            if (config('cache.default') === 'redis') {
            Cache::tags(["type_{$post->type}"])->flush();
        }
        });
        
        static::saved(function ($post) {
            if (!empty($post->media) && media_exists($post->media)) {
                // Jika ada media baru, hapus cache thumbnail karena sudah tidak diperlukan
                Cache::forget('thumbnail_' . $post->slug);
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class)->select($this->userselectcolumn);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class)->select($this->categoryselectcolumn);
    }
   
    public function childs()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id')->select($this->selected);
    }
    public function child()
    {
        return $this->hasOne(Post::class, 'parent_id', 'id')->select($this->selected);
    }

    public function getThumbnailAttribute()
    {
       
    if ($this->media && media_exists($this->media)) {
        return $this->media;
    }
    // Cek cache
    $cacheKey = 'thumbnail_' . $this->slug;
    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }
    return noimage();
    }
    public function getThumbnailTextAttribute()
    {
        return $this->media_description ? $this->media_description : null;
    }
    public function getCreatedAttribute()
    {
        return $this->created_at->translatedFormat('d F Y H:i T');
    }

    public function getUpdatedAttribute()
    {
        return $this->updated_at->format('d F Y H:i T');
    }
    public function getDateAttribute()
    {
        return $this->created_at->format('d');
    }
    public function getYearAttribute()
    {
        return $this->created_at->format('Y');
    }
    public function getLinkAttribute()
    {
        return url($this->url);
    }
    public function getMonthAttribute()
    {
        return $this->created_at->format('F');;
    }
    public function getFieldAttribute()
    {
        return json_decode(json_encode($this->data_field));
    }
    public function getDataAttribute()
    {
        return collect(json_decode(json_encode($this->data_loop)));
    }
    public function getThumbnailDescriptionAttribute()
    {
        return $this->media_description;
    }

    function count($type)
    {
        return $this->onType($type)->published()->count();
    }
    function scopeOnType($query,$type)
    {
        return $query->whereType($type);
    }
    function scopeFieldFilter($query,$arr)
    {

        foreach($arr as $key=>$value){
            $query = $query->where('data_field->'.$key, $value);
        }
        return $query;
    }
    function scopePublished($query)
    {
        return $query->whereStatus('publish');
    }
    function scopePinned($query)
    {
        return $query->wherePinned('Y');
    }

    function scopeWithCountVisitors($query)
    {
        return $query->withCount('visitors');
    }

    function scopeSelectedColumn($query)
    {
        return $query->select($this->selected);
    }
    function scopeLikeSlug($query,$slug)
    {
        return $query->where('slug','like',$slug.'%');
    }
    function cachedpost($type = false)
    {
        return $type ? Cache::get($type) : [];
    }
    public function categories($type)
    {
        return collect(Cache::get('category_' . $type))->sortBy('sort');
    }



    function index_limit($type, $limit)
    {
        $module = get_module($type);
        $category = $module?->form?->category ? ['category'] : [];

        // Jika bukan redis → langsung query biasa
        if (config('cache.default') !== 'redis') {
            return $this->selectedColumn()
                ->with(array_merge(['user'], $category))
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->limit($limit)
                ->get();
        }

        // Jika redis aktif → pakai cache
        $cacheKey = "posts:{$type}:limit:{$limit}";

        return Cache::tags(self::cacheTag($type))
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $limit, $category) {

                return $this->selectedColumn()
                    ->with(array_merge(['user'], $category))
                    ->onType($type)
                    ->published()
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();

            });
    }
    function index_author($type=false)
    {
           if($type){
            return User::whereHas('posts')->withCount(['posts' => function($q) use($type){
                $q->published()->onType($type);
            }])->get();
           } else{
            return User::whereHas('posts',function ($q){
                $q->published();
            })
            ->withCountPosts()
            ->get();
           }

    }
    function index_sort_by_category($type, $sortby = 'sort', $sort = 'ASC')
    {
        $sort = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';

        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {
            return $this->runSortByCategoryQuery($type, $sortby, $sort);
        }

        $cacheKey = "categories:{$type}:sortby:{$sortby}:order:{$sort}";

        return Cache::tags(['categories', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $sortby, $sort) {
                return $this->runSortByCategoryQuery($type, $sortby, $sort);
            });
    }
    private function runSortByCategoryQuery($type, $sortby, $sort)
    {
        return Category::withWhereHas('posts', function ($q) use ($sortby, $sort) {
            $q->with('user')
                ->published()
                ->orderBy($sortby, $sort);
        })
            ->onType($type)
            ->published()
            ->orderBy('sort', 'ASC')
            ->get();
    }
    private static function cacheTag($type)
    {
        return ["posts", "type_{$type}"];
    }

    function index_category($type, $justIndex = false)
    {
        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {

            if ($justIndex) {
                return Category::onType($type)
                    ->published()
                    ->orderBy('sort', 'ASC')
                    ->get();
            }

            return Category::whereHas('posts', function ($q) {
                $q->published();
            })
                ->withCountPosts()
                ->onType($type)
                ->published()
                ->orderBy('sort', 'ASC')
                ->get();
        }

        // Key unik
        $mode = $justIndex ? 'justIndex' : 'withCount';
        $cacheKey = "categories:{$type}:{$mode}";

        return Cache::tags(['categories', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $justIndex) {

                if ($justIndex) {
                    return Category::onType($type)
                        ->published()
                        ->orderBy('sort', 'ASC')
                        ->get();
                }

                return Category::whereHas('posts', function ($q) {
                    $q->published();
                })
                    ->withCountPosts()
                    ->onType($type)
                    ->published()
                    ->orderBy('sort', 'ASC')
                    ->get();
            });
    }

    function index_skip($type, $skip, $limit)
    {
        $module = get_module($type);
        $category = $module?->form?->category ? ['category'] : [];

        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {
            return $this->selectedColumn()
                ->with(array_merge(['user'], $category))
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->skip($skip)
                ->take($limit)
                ->get();
        }

        // Cache key unik berdasarkan parameter
        $cacheKey = "posts:{$type}:skip:{$skip}:limit:{$limit}";

        return Cache::tags(['posts', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $skip, $limit, $category) {

                return $this->selectedColumn()
                    ->with(array_merge(['user'], $category))
                    ->onType($type)
                    ->published()
                    ->latest('created_at')
                    ->skip($skip)
                    ->take($limit)
                    ->get();
            });
    }
    private function runTagQuery($type)
    {
        if ($type) {
            return Tag::whereStatus('publish')
                ->whereHas('posts', function ($q) use ($type) {
                    $q->published()->onType($type);
                })
                ->withCount([
                    'posts as posts_count' => function ($query) use ($type) {
                        $query->published()->onType($type);
                    }
                ])
                ->get();
        }

        return Tag::whereStatus('publish')
            ->whereHas('posts', function ($q) {
                $q->published();
            })
            ->get();
    }
    function index_tags($type = false)
    {
        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {
            return $this->runTagQuery($type);
        }

        $typeKey = $type ? $type : 'all';
        $cacheKey = "tags:{$typeKey}";

        $tags = ['tags'];

        if ($type) {
            $tags[] = "type_{$type}";
        }

        return Cache::tags($tags)
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type) {
                return $this->runTagQuery($type);
            });
    }

    function index_sort($type, $order = 'asc', $limit = false)
    {
        $order = $order !== 'asc' ? 'desc' : 'asc';

        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {
            $query = $this->selectedColumn()
                ->with('user')
                ->onType($type)
                ->published()
                ->orderBy('sort', $order);

            return $limit ? $query->take($limit)->get() : $query->get();
        }

        // Key unik
        $limitKey = $limit ? "limit:{$limit}" : "all";
        $cacheKey = "posts:{$type}:sort:{$order}:{$limitKey}";

        return Cache::tags(['posts', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $order, $limit) {

                $query = $this->selectedColumn()
                    ->with('user')
                    ->onType($type)
                    ->published()
                    ->orderBy('sort', $order);

                return $limit ? $query->take($limit)->get() : $query->get();
            });
    }

    function index_sort_by_parent($type, $order = 'asc')
    {
        $order = $order !== 'asc' ? 'desc' : 'asc';

        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {
            return $this->select('id', 'user_id')
                ->with('childs')
                ->onType($type)
                ->published()
                ->orderBy('sort', $order)
                ->get();
        }

        // Cache key unik
        $cacheKey = "posts:{$type}:parent_sort:{$order}";

        return Cache::tags(['posts', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $order) {

                return $this->select('id', 'user_id')
                    ->with('childs')
                    ->onType($type)
                    ->published()
                    ->orderBy('sort', $order)
                    ->get();

            });
    }
    public function index($type, $paginate = null)
    {
        $q = $this->selectedColumn()
        ->with('user', 'category')
        ->onType($type)
        ->published()
        ->latest('created_at');
        if ($paginate===null)
        return $q->get();
        return $q->paginate($paginate);

    }

    public function index_popular($type, $limit)
    {
        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {
            return $this->selectedColumn()
                ->with('user')
                ->onType($type)
                ->published()
                ->orderBy('visited', 'desc')
                ->take($limit)
                ->get();
        }

        $cacheKey = "posts:{$type}:popular:limit:{$limit}";

        return Cache::tags(['posts', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $limit) {

                return $this->selectedColumn()
                    ->with('user')
                    ->onType($type)
                    ->published()
                    ->orderBy('visited', 'desc')
                    ->take($limit)
                    ->get();

            });
    }


    function index_pinned($limit, $type = false)
    {
        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {

            $query = $this->selectedColumn()
                ->pinned()
                ->published()
                ->latest();

            if ($type) {
                $query->onType($type);
            }

            return $query->take($limit)->get();
        }

        // Tentukan key
        $typeKey = $type ? $type : 'all';
        $cacheKey = "posts:pinned:{$typeKey}:limit:{$limit}";

        // Tentukan tag
        $tags = ['posts'];

        if ($type) {
            $tags[] = "type_{$type}";
        }

        return Cache::tags($tags)
            ->remember($cacheKey, now()->addMinutes(30), function () use ($limit, $type) {

                $query = $this->selectedColumn()
                    ->pinned()
                    ->published()
                    ->latest();

                if ($type) {
                    $query->onType($type);
                }

                return $query->take($limit)->get();
            });
    }
    function index_by_tag($type,$tag,$limit=false,$paginate=false){
        $q = $this->selectedColumn()->onType($type)->published()->whereHas('tags', function ($query)  use($tag){
            $query->where('tags.slug', $tag)->where('tags.status','publish');
        })->latest();
        if($limit){
            return $q->take($limit)->get();
        }
        if($paginate){
            return $q->paginate(get_option('post_perpage'));
        }
        return $q->get();
    }
    function index_by_category($type, $slug, $paginate = false)
    {
      
            return $paginate ? $this->selectedColumn()->with('user')
            ->whereHas('category', function ($q) use ($slug,$type) {
                $q->where('slug', $slug)->whereType($type)->whereStatus('publish');
            })->onType($type)->published()->latest('created_at')->paginate($paginate) :
            $this->selectedColumn()->with('user')->WhereHas('category', function ($q) use ($slug,$type) {
                    $q->where('slug', $slug)->whereType($type)->whereStatus('publish');
                })->onType($type)->published()->latest('created_at')
                ->get();
        
    }


    function index_recent($type, $except = null)
    {
        // Kalau bukan redis → query normal
        if (config('cache.default') !== 'redis') {

            $query = $this->selectedColumn()
                ->onType($type)
                ->published();

            if ($except) {
                $query->whereNotIn('id', [$except]);
            }

            return $query->with('user')
                ->latest('created_at')
                ->take(5)
                ->get();
        }

        // Buat key unik (except bisa null)
        $exceptKey = $except ? "except:{$except}" : "noexcept";
        $cacheKey = "posts:{$type}:recent:{$exceptKey}";

        return Cache::tags(['posts', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $except) {

                $query = $this->selectedColumn()
                    ->onType($type)
                    ->published();

                if ($except) {
                    $query->whereNotIn('id', [$except]);
                }

                return $query->with('user')
                    ->latest('created_at')
                    ->take(5)
                    ->get();
            });
    }
    function index_child($type, $id,$perpage=false)
    {
        if (get_module($type)?->cache) {
            return $this->cachedpost($type)->where('parent_id', $id);
        } else {
            $q = $this->select($this->selected)
            ->with('user')
            ->onType($type)
            ->published()
            ->where('parent_id', $id)
            ->latest('created_at');
            if($perpage){
              return  $q->paginate(get_option('post_perpage'));
            }else{
               return $q->get();
            }
        }
    }
    function detail_by_title($type, $title)
    {
        return $this->with('user')->whereTitle($title)->onType($type)->published()->first();
    }

    function detail($type, $name = false, $cache = false)
    {
        $module = get_module($type);
        $with = [];

        if ($module?->form?->category) {
            $with[] = 'category';
        }

        $with[] = 'user';

        // Jika cache tidak diminta atau bukan redis → query normal
        if (!$cache || config('cache.default') !== 'redis') {

            return $this->runDetailQuery($type, $name, $with);
        }

        $nameKey = $name ? "slug:{$name}" : "first";
        $cacheKey = "posts:{$type}:detail:{$nameKey}";

        return Cache::tags(['posts', "type_{$type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($type, $name, $with) {
                return $this->runDetailQuery($type, $name, $with);
            });
    }
    private function runDetailQuery($type, $name, $with)
    {
        $query = $this->with($with);

        if ($name) {

            // Jika type adalah page → aktifkan shortcut
            if ($type === 'page') {

                return $query
                    ->where(function ($q) use ($type, $name) {

                        // slug tetap dikunci ke type page
                        $q->where(function ($sub) use ($type, $name) {
                            $sub->onType($type)
                                ->where('slug','like', $name.'%');
                        })

                            // shortcut bebas semua type
                            ->orWhere('shortcut', $name);

                    })
                    ->first();
            }

           // Jika bukan page → hanya slug sesuai type
            return $query
                ->onType($type)
                ->where('slug','like', $name.'%')
                ->first();
        }

        return $query
            ->onType($type)
            ->published()
            ->first();
    }
    function getShareToAttribute()
    {
        return view()->make('cms::share.button',['url'=>$this->shortcut ? url($this->shortcut) : url()->full()]);
    }

    public function getHistoryAttribute()
    {
        $module = get_module($this->type);

        if (!$module?->web?->history) {
            return null;
        }

        // Kalau bukan redis → langsung jalankan
        if (config('cache.default') !== 'redis') {
            return $this->runHistoryQuery();
        }

        $cacheKey = "posts:{$this->type}:history:{$this->id}";

        return Cache::tags(['posts', "type_{$this->type}"])
            ->remember($cacheKey, now()->addMinutes(30), function () {
                return $this->runHistoryQuery();
            });
    }
    private function runHistoryQuery()
    {
        $previous = self::select('url', 'media', 'title')
            ->published()
            ->where('id', '!=', $this->id)
            ->onType($this->type)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        $next = self::select('url', 'media', 'title')
            ->published()
            ->where('id', '!=', $this->id)
            ->onType($this->type)
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at', 'asc')
            ->first();

        return (object) [
            'previous' => $previous ? [
                'url' => url($previous->url),
                'title' => $previous->title,
                'thumbnail' => $previous->thumbnail
            ] : [],
            'next' => $next ? [
                'url' => url($next->url),
                'title' => $next->title,
                'thumbnail' => $next->thumbnail
            ] : [],
        ];
    }
}
