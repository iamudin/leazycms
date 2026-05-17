<?php

namespace Leazycms\Web\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Leazycms\FLC\Traits\Commentable;
use Leazycms\Web\Models\BaseModel;

class Post extends BaseModel
{
    use SoftDeletes, Commentable;
    protected static $requestCache = [];
    public $selected = ['id', 'description', 'redirect_to', 'short_content', 'type', 'category_id', 'user_id', 'title', 'created_at', 'updated_at', 'deleted_at', 'parent_id', 'media', 'media_description', 'url', 'slug', 'data_field', 'data_loop', 'pinned', 'sort', 'status', 'shortcut', 'shortcut_counter', 'custom_page', 'visited', 'allow_comment', 'password'];

    protected $fillable = [
        'custom_page',
        'slug_edited',
        'short_content',
        'title',
        'slug',
        'content',
        'url',
        'media',
        'media_description',
        'keyword',
        'description',
        'parent_id',
        'category_id',
        'user_id',
        'pinned',
        'parent_type',
        'type',
        'redirect_to',
        'status',
        'allow_comment',
        'mime',
        'data_field',
        'data_loop',
        'created_at',
        'sort',
        'password',
        'deleteable',
        'shortcut',
        'visited'
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
                foreach ($post->files as $row) {
                    $row->deleteFile();
                }
            }

            if (config('cache.default') === 'redis') {
                try {
                    Cache::tags(["type_{$post->type}", 'categories', 'tags', 'authors'])->flush();
                } catch (\Exception $e) {
                    \Log::warning('Cache flush error on delete: ' . $e->getMessage());
                }
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
                        Cache::put('thumbnail:' . $post->slug, $src);
                        break;
                    }
                }
            }
            if (config('cache.default') === 'redis') {
                try {
                    Cache::tags(["type_{$post->type}", 'categories', 'tags', 'authors'])->flush();
                } catch (\Exception $e) {
                    \Log::warning('Cache flush error on save: ' . $e->getMessage());
                }
            }
        });

        static::saved(function ($post) {
            if (!empty($post->media) && media_exists($post->media)) {
                // Jika ada media baru, hapus cache thumbnail karena sudah tidak diperlukan
                Cache::forget('thumbnail:' . $post->slug);
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'url', 'photo']);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->select(['id', 'name', 'url', 'slug']);
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
        if ($this->media) {
            return media($this->media)->url();
        }
        // Cek cache
        $cacheKey = 'thumbnail:' . $this->slug;
        return Cache::get($cacheKey, noimage());
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
        return $this->created_at->format('F');
        ;
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
    function scopeOnType($query, $type)
    {
        return $query->whereType($type);
    }
    function scopeFieldFilter($query, $arr)
    {

        foreach ($arr as $key => $value) {
            $query = $query->where('data_field->' . $key, $value);
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
        return $query->select(array_merge($this->selected, app()->has('tenant') ? ['tenant_id'] : []));
    }
    function scopeLikeSlug($query, $slug)
    {
        return $query->where('slug', 'like', $slug . '%');
    }
    function cachedpost($type = null)
    {
        if (!$type) return collect([]);
        $cacheKey = "cachedpost:{$type}";
        if (app()->has('tenant')) {
            $cacheKey .= ':tenant:' . tenant()->id;
        }
        if (isset(self::$requestCache[$cacheKey])) {
            return self::$requestCache[$cacheKey];
        }
        $data = Cache::get($type, []);
        return self::$requestCache[$cacheKey] = collect($data);
    }

    public function categories($type)
    {
        $cacheKey = "categories_list:{$type}";
        $tags = ['categories', "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type) {
            return Category::select('id', 'name', 'url', 'slug', 'icon', 'description')
                ->onType($type)
                ->published()
                ->orderBy('sort', 'ASC')
                ->get();
        });
    }



    function index_limit($type, $limit)
    {
        $module = get_module($type);
        $category = $module?->form?->category ? ['category'] : [];
        $cacheKey = "posts:{$type}:limit:{$limit}";
        $tags = $this->cacheTag($type);

        return $this->getCached($cacheKey, $tags, function () use ($type, $limit, $category) {
            return $this->selectedColumn()
                ->with(array_merge(['user'], $category))
                ->withTenant()
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->limit($limit)
                ->get();
        });
    }
    function index_author($type = false)
    {
        $typeKey = $type ?: 'all';
        $cacheKey = "authors:{$typeKey}";
        $tags = $type ? ['authors', "type_{$type}"] : ['authors'];

        return $this->getCached($cacheKey, $tags, function () use ($type) {
            if ($type) {
                return User::whereHas('posts')->withCount([
                    'posts' => function ($q) use ($type) {
                        $q->published()->onType($type);
                    }
                ])->get();
            }

            return User::whereHas('posts', function ($q) {
                $q->published();
            })
                ->withCountPosts()
                ->get();
        });
    }
    function index_sort_by_category($type, $sortby = 'sort', $sort = 'ASC')
    {
        $sort = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';
        $cacheKey = "categories:{$type}:sortby:{$sortby}:order:{$sort}";
        $tags = ['categories', "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $sortby, $sort) {
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
            ->withCountPosts()
            ->orderBy('sort', 'ASC')
            ->get();
    }
    private static function cacheTag($type)
    {
        return ["posts", "type_{$type}"];
    }

    private function getCached($cacheKey, array $tags, \Closure $callback)
    {
        if (app()->has('tenant')) {
            $cacheKey .= ":tenant:" . tenant()->id;
        }

        if (array_key_exists($cacheKey, self::$requestCache)) {
            return self::$requestCache[$cacheKey];
        }

        if (config('cache.default') !== 'redis') {
            return self::$requestCache[$cacheKey] = $callback();
        }

        try {
            $cached = Cache::tags($tags)->remember($cacheKey, now()->addMinutes(30), function () use ($callback) {
                $result = $callback();
                // Serialize sebagai array agar tidak corrupt di phpredis
                return [
                    '__type' => $result instanceof \Illuminate\Database\Eloquent\Collection ? 'collection' : (is_null($result) ? 'null' : 'model'),
                    '__class' => $result instanceof \Illuminate\Database\Eloquent\Model ? get_class($result) : ($result instanceof \Illuminate\Database\Eloquent\Collection && $result->isNotEmpty() ? get_class($result->first()) : null),
                    '__data' => $result instanceof \Illuminate\Database\Eloquent\Collection ? $result->map->getAttributes()->toArray() : ($result instanceof \Illuminate\Database\Eloquent\Model ? $result->getAttributes() : null),
                    '__relations' => $result instanceof \Illuminate\Database\Eloquent\Collection
                        ? $result->map(fn($m) => collect($m->getRelations())->map(fn($r) => $r instanceof \Illuminate\Database\Eloquent\Model ? ['__class' => get_class($r), '__data' => $r->getAttributes()] : ($r instanceof \Illuminate\Database\Eloquent\Collection ? $r->map(fn($rm) => ['__class' => get_class($rm), '__data' => $rm->getAttributes()])->toArray() : null))->toArray())->toArray()
                        : ($result instanceof \Illuminate\Database\Eloquent\Model ? collect($result->getRelations())->map(fn($r) => $r instanceof \Illuminate\Database\Eloquent\Model ? ['__class' => get_class($r), '__data' => $r->getAttributes()] : ($r instanceof \Illuminate\Database\Eloquent\Collection ? $r->map(fn($rm) => ['__class' => get_class($rm), '__data' => $rm->getAttributes()])->toArray() : null))->toArray() : null),
                ];
            });

            $result = $this->hydrateFromCache($cached);
        } catch (\Exception $e) {
            \Log::warning("Cache getCached error [{$cacheKey}]: " . $e->getMessage());
            $result = $callback();
        }

        return self::$requestCache[$cacheKey] = $result;
    }

    /**
     * Hydrate cached array data back into Eloquent models/collections.
     */
    private function hydrateFromCache($cached)
    {
        if (!is_array($cached) || !isset($cached['__type'])) {
            return $cached;
        }

        if ($cached['__type'] === 'null') {
            return null;
        }

        $class = $cached['__class'];

        if ($cached['__type'] === 'model') {
            if (!$class || !class_exists($class)) {
                return null;
            }
            $model = (new $class)->newInstance([], true);
            $model->setRawAttributes($cached['__data'] ?? [], true);
            if (!empty($cached['__relations'])) {
                $this->hydrateRelations($model, $cached['__relations']);
            }
            return $model;
        }

        if ($cached['__type'] === 'collection') {
            if (!$class || !class_exists($class) || empty($cached['__data'])) {
                return new \Illuminate\Database\Eloquent\Collection();
            }
            $models = collect($cached['__data'])->map(function ($attrs, $index) use ($class, $cached) {
                $model = (new $class)->newInstance([], true);
                $model->setRawAttributes($attrs, true);
                if (!empty($cached['__relations'][$index])) {
                    $this->hydrateRelations($model, $cached['__relations'][$index]);
                }
                return $model;
            });
            return new \Illuminate\Database\Eloquent\Collection($models->all());
        }

        return $cached;
    }

    /**
     * Hydrate relations from cached array data.
     */
    private function hydrateRelations($model, array $relations)
    {
        foreach ($relations as $name => $data) {
            if (is_null($data)) {
                continue;
            }
            // Single relation (e.g. belongsTo)
            if (isset($data['__class']) && class_exists($data['__class'])) {
                $related = (new $data['__class'])->newInstance([], true);
                $related->setRawAttributes($data['__data'] ?? [], true);
                $model->setRelation($name, $related);
            }
            // Collection relation (e.g. hasMany)
            elseif (is_array($data)) {
                $relatedModels = collect($data)->map(function ($item) {
                    if (is_array($item) && isset($item['__class']) && class_exists($item['__class'])) {
                        $related = (new $item['__class'])->newInstance([], true);
                        $related->setRawAttributes($item['__data'] ?? [], true);
                        return $related;
                    }
                    return null;
                })->filter();
                $model->setRelation($name, new \Illuminate\Database\Eloquent\Collection($relatedModels->all()));
            }
        }
    }

    function index_category($type, $justIndex = false)
    {
        $mode = $justIndex ? 'justIndex' : 'withCount';
        $cacheKey = "categories:{$type}:{$mode}";
        $tags = ['categories', "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $justIndex) {
            $query = Category::select('id', 'name', 'url', 'slug', 'icon', 'description')
                ->onType($type)
                ->published()
                ->orderBy('sort', 'ASC');

            if (!$justIndex) {
                $query->whereHas('posts', function ($q) {
                    $q->published()->withTenant();
                })->withCountPosts();
            }

            return $query->get();
        });
    }

    function index_skip($type, $skip, $limit)
    {
        $module = get_module($type);
        $category = $module?->form?->category ? ['category'] : [];
        $cacheKey = "posts:{$type}:skip:{$skip}:limit:{$limit}";
        $tags = ["posts", "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $skip, $limit, $category) {
            return $this->selectedColumn()
                ->with(array_merge(['user'], $category))
                ->withTenant()
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->skip($skip)
                ->take($limit)
                ->get();
        });
    }

    function index_tags($type = false)
    {
        $typeKey = $type ? $type : 'all';
        $cacheKey = "tags:{$typeKey}";
        $tags = ['tags'];
        if ($type) {
            $tags[] = "type_{$type}";
        }

        return $this->getCached($cacheKey, $tags, function () use ($type) {
            return $this->runTagQuery($type);
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

    function index_sort($type, $order = 'asc', $limit = false)
    {
        $order = $order !== 'asc' ? 'desc' : 'asc';
        $limitKey = $limit ? "limit:{$limit}" : "all";
        $cacheKey = "posts:{$type}:sort:{$order}:{$limitKey}";
        $tags = ["posts", "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $order, $limit) {
            $query = $this->selectedColumn()
                ->with('user')
                ->withTenant()
                ->onType($type)
                ->published()
                ->orderBy('sort', $order);

            return $limit ? $query->take($limit)->get() : $query->get();
        });
    }

    function index_sort_by_parent($type, $order = 'asc')
    {
        $order = $order !== 'asc' ? 'desc' : 'asc';
        $cacheKey = "posts:{$type}:parent_sort:{$order}";
        $tags = ["posts", "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $order) {
            return $this->select('id', 'user_id')
                ->withTenant()
                ->with('childs')
                ->onType($type)
                ->published()
                ->orderBy('sort', $order)
                ->get();
        });
    }

    public function index($type, $paginate = null)
    {
        if ($paginate !== null) {
            return $this->selectedColumn()
                ->with('user', 'category')
                ->withTenant()
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->paginate($paginate);
        }

        $cacheKey = "posts:{$type}:index:all";
        $tags = $this->cacheTag($type);

        return $this->getCached($cacheKey, $tags, function () use ($type) {
            return $this->selectedColumn()
                ->with('user', 'category')
                ->withTenant()
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->get();
        });
    }

    public function index_popular($type, $limit)
    {
        $cacheKey = "posts:{$type}:popular:limit:{$limit}";
        $tags = ["posts", "type_{$type}"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $limit) {
            return $this->selectedColumn()
                ->with('user')
                ->withTenant()
                ->onType($type)
                ->published()
                ->orderBy('visited', 'desc')
                ->take($limit)
                ->get();
        });
    }


    function index_pinned($limit, $type = false)
    {
        $typeKey = $type ? $type : 'all';
        $cacheKey = "posts:pinned:{$typeKey}:limit:{$limit}";
        $tags = $type ? ["posts", "type_{$type}"] : ["posts"];

        return $this->getCached($cacheKey, $tags, function () use ($type, $limit) {
            $query = $this->selectedColumn()
                ->withTenant()
                ->pinned()
                ->published()
                ->latest();

            if ($type) {
                $query->onType($type);
            }

            return $query->take($limit)->get();
        });
    }
    function index_by_tag($type, $tag, $limit = false, $paginate = false)
    {
        if ($paginate) {
            return $this->selectedColumn()
                ->onType($type)
                ->published()
                ->whereHas('tags', function ($query) use ($tag) {
                    $query->where('tags.slug', $tag)->where('tags.status', 'publish');
                })
                ->latest()
                ->paginate(get_option('post_perpage'));
        }

        $limitKey = $limit ? "limit:{$limit}" : 'all';
        $cacheKey = "posts:{$type}:tag:{$tag}:{$limitKey}";
        $tags = ['posts', "type_{$type}", 'tags'];

        return $this->getCached($cacheKey, $tags, function () use ($type, $tag, $limit) {
            $q = $this->selectedColumn()
                ->onType($type)
                ->published()
                ->whereHas('tags', function ($query) use ($tag) {
                    $query->where('tags.slug', $tag)->where('tags.status', 'publish');
                })
                ->latest();

            return $limit ? $q->take($limit)->get() : $q->get();
        });
    }

    function index_by_category($type, $slug, $paginate = false)
    {
        if ($paginate) {
            return $this->selectedColumn()
                ->with('user')
                ->whereHas('category', function ($q) use ($slug, $type) {
                    $q->where('slug', $slug)->whereType($type)->whereStatus('publish');
                })
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->paginate($paginate);
        }

        $cacheKey = "posts:{$type}:category:{$slug}";
        $tags = ['posts', "type_{$type}", 'categories'];

        return $this->getCached($cacheKey, $tags, function () use ($type, $slug) {
            return $this->selectedColumn()
                ->with('user')
                ->whereHas('category', function ($q) use ($slug, $type) {
                    $q->where('slug', $slug)->whereType($type)->whereStatus('publish');
                })
                ->onType($type)
                ->published()
                ->latest('created_at')
                ->get();
        });
    }


    function index_recent($type, $except = null)
    {
        $exceptKey = $except ? "except:{$except}" : 'noexcept';
        $cacheKey = "posts:{$type}:recent:{$exceptKey}";
        $tags = $this->cacheTag($type);

        return $this->getCached($cacheKey, $tags, function () use ($type, $except) {
            $query = $this->selectedColumn()
                ->withTenant()
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

    function index_child($type, $id, $perpage = false)
    {
        if ($perpage) {
            return $this->select($this->selected)
                ->with('user')
                ->withTenant()
                ->onType($type)
                ->published()
                ->where('parent_id', $id)
                ->latest('created_at')
                ->paginate(get_option('post_perpage'));
        }

        $cacheKey = "posts:{$type}:child:{$id}";
        $tags = $this->cacheTag($type);

        return $this->getCached($cacheKey, $tags, function () use ($type, $id) {
            return $this->select($this->selected)
                ->with('user')
                ->withTenant()
                ->onType($type)
                ->published()
                ->where('parent_id', $id)
                ->latest('created_at')
                ->get();
        });
    }
    function detail_by_title($type, $title)
    {
        $cacheKey = "posts:{$type}:detail_title:" . md5($title);
        $tags = $this->cacheTag($type);

        return $this->getCached($cacheKey, $tags, function () use ($type, $title) {
            return $this->with('user')->whereTitle($title)->onType($type)->published()->first();
        });
    }

    function detail($type, $name = false, $cache = false)
    {
        $module = get_module($type);
        $with = [];

        if ($module?->form?->category) {
            $with[] = 'category';
        }

        $with[] = 'user';

        if (!$cache) {
            return $this->runDetailQuery($type, $name, $with);
        }

        $nameKey = $name ? "slug:{$name}" : 'first';
        $cacheKey = "posts:{$type}:detail:{$nameKey}";
        $tags = $this->cacheTag($type);

        return $this->getCached($cacheKey, $tags, function () use ($type, $name, $with) {
            return $this->runDetailQuery($type, $name, $with);
        });
    }
    private function runDetailQuery($type, $name, $with)
    {
        $query = $this->with($with)->withTenant();
        if (config('modules.multisite_enabled') && $this->tenant_id !== null) {
            $query->where('tenant_id', tenant()->id);
        }

        if ($name) {

            // Jika type adalah page → aktifkan shortcut
            if ($type === 'page') {

                return $query
                    ->where(function ($q) use ($type, $name) {

                        // slug tetap dikunci ke type page
                        $q->where(function ($sub) use ($type, $name) {
                            $sub->onType($type)
                                ->where('slug', 'like', $name . '%');
                        });

                        // hanya cek shortcut jika 6 digit angka
                        if (strlen($name) === 6) {
                            $q->orWhere('shortcut', $name);
                        }

                    })
                    ->published()
                    ->first();
            }

            // Jika bukan page → hanya slug sesuai type
            return $query
                ->onType($type)
                ->published()
                ->where('slug', 'like', $name . '%')
                ->first();
        }

        return $query
            ->onType($type)
            ->published()
            ->first();
    }
    function getShareToAttribute()
    {
        return view()->make('cms::share.button', ['url' => $this->shortcut ? url($this->shortcut) : URL::full()]);
    }

    public function getHistoryAttribute()
    {
        $module = get_module($this->type);

        if (!$module?->web?->history) {
            return null;
        }

        $cacheKey = "posts:{$this->type}:history:{$this->id}";
        $tags = $this->cacheTag($this->type);

        return $this->getCached($cacheKey, $tags, function () {
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
