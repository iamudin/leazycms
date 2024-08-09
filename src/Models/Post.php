<?php

namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    public $selected = ['id','short_content','type','category_id','user_id','title','created_at','updated_at','parent_id','media','url','data_field','pinned','sort','status'];

    protected $userselectcolumn = ['id','name','url','slug'];
    protected $categoryselectcolumn = ['id','name','url','status','slug'];
    protected $fillable = [
        'short_content','title', 'slug', 'content', 'url', 'media', 'media_description', 'keyword', 'description', 'parent_id', 'category_id', 'user_id', 'pinned', 'parent_type', 'type', 'redirect_to', 'status', 'allow_comment', 'mime', 'data_field', 'data_loop', 'created_at','sort','password','deleteable'
    ];
    protected $casts = [
        'data_field' => 'array',
        'data_loop' => 'array',
        'allow_comment' => 'string',
        'pinned' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class)->select($this->userselectcolumn);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }
    public function post_parent()
    {
        return $this->belongsTo(Post::class, 'parent_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class)->select($this->categoryselectcolumn);
    }

    public function childs()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id')->select($this->selected)->whereNotIn('type', ['media']);
    }
    public function medias()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id')->whereType('media')->whereParentType('post');
    }
    public function get_media($slug)
    {
        return collect(Cache::get('media'))->where('slug', $slug)->first();
    }
    public function getThumbnailAttribute()
    {
        return $this->media ? '/'.$this->media : noimage();
    }
    public function getCreatedAttribute()
    {
        return $this->created_at->translatedFormat('d F Y H:i T');
    }
    public function getVisitedAttribute()
    {
        return $this->visitors_count;
    }
    public function getUpdatedAttribute()
    {
        return $this->updated_at->translatedFormat('d F Y H:i T');
    }
    public function getDateAttribute()
    {
        return $this->created_at->translatedFormat('d');
    }
    public function getYearAttribute()
    {
        return $this->updated_at->translatedFormat('Y');
    }
    public function getLinkAttribute()
    {
        return url($this->url);
    }
    public function getMonthAttribute()
    {
        return $this->updated_at->translatedFormat('F');;
    }
    public function getFieldAttribute()
    {
        return json_decode(json_encode($this->data_field));
    }
    public function getDatadAttribute()
    {
        return json_decode(json_encode($this->data_loop));
    }
    public function getThumbnailDescriptionAttribute()
    {
        return $this->media_description;
    }

    function count($type)
    {
        return $this->whereType($type)->whereStatus('publish')->count();
    }
    function cachedpost($type = false)
    {
        return $type ? Cache::get($type) : [];
    }
    public function categories($type)
    {
        return collect(Cache::get('category_' . $type))->sortBy('sort');
    }

    function comment_by_post($type, $slug, $limit = false)
    {
        if ($limit) {
            Comment::withWhereHas('post', function ($q) use ($slug, $type) {
                $q->whereType($type)->whereStatus('publish')->whereSlug($slug);
            })->whereStatus('publish')->limit($limit)->get();
        } else {
            return Comment::withWhereHas('post', function ($q) use ($slug, $type) {
                $q->whereType($type)->whereStatus('publish')->whereSlug($slug);
            })->whereStatus('publish')->get();
        }
    }

    function index_limit($type, $limit)
    {
        if (get_module($type)?->cache) {
            return collect($this->cachedpost($type)->values())->take($limit);
        } else {
            return $this->select($this->selected)->with('user', 'category')->where('type', $type)->whereStatus('publish')->latest('created_at')->limit($limit)->get();
        }
    }

    function index_category($type)
    {
        if (get_module($type)?->cache) {
            return collect($this->categories($type)->values());
        } else {
            return Category::withCount('posts')->whereType($type)->whereStatus('publish')->orderBy('sort')->get();
        }
    }
    function index_skip($type, $skip, $limit)
    {
        if (get_module($type)?->cache) {
            return collect($this->cachedpost($type)->values())->skip($skip)->take($limit);
        } else {
            return $this->select($this->selected)->whereType($type)->whereStatus('publish')->offset($skip)->limit($limit)->latest()->get();
        }
    }
    function index_sort($type,$order)
    {
        if (get_module($type)?->cache) {
            return $order=='asc'? collect($this->cachedpost($type)->values())->sortBy($order) : collect($this->cachedpost($type)->values())->sortByDesc($order);
        } else {
            $order = in_array(str($order)->lower(),['asc','desc']) ? $order : 'asc';
            return $this->select($this->selected)->whereType($type)->whereStatus('publish')->orderBy('sort',$order)->get();
        }
    }
    function index_sort_by_parent($type,$order=false)
    {
        $order = $order && in_array(str($order)->lower(),['asc','desc']) ? $order : null;
            return $this->select('id','user_id')->with('childs','user')->whereType($type)->whereStatus('publish')->orderBy('sort',$order ?? 'desc')->get();

    }
    public function index($type, $paginate = false)
    {
        return $this->select($this->selected)->withCount('visitors')->with('user', 'category')->whereType($type)->whereStatus('publish')->latest('created_at')->paginate(get_option('post_perpage') ?? 10);
    }
    public function index_popular($type)
    {
        return $this->select($this->selected)->withCount('visitors')->whereType($type)->whereStatus('publish')->orderBy('visitors_count', 'desc')->limit('5')->get();
    }

    function index_pinned($limit, $type = false)
    {
        return $type ? $this->cachedpost($type)->where('pinned', 1)->take($limit)->values() : $this->select($this->selected)->withCount('visitors')->where('pinned', 1)->whereStatus('publish')->limit($limit)->orderBy('created_at', 'desc')->get();
    }
    function index_by_category($type, $slug, $limit = false)
    {
        // dd(get_module($type)->cache);
        $modul = get_module($type);
        if ($modul && $modul->cache) {
            $cek = $this->categories($type) ? collect($this->categories($type))->where('slug', $slug)->first() : null;
            return $cek && collect($cek->posts)->count() > 0 ? ($limit ? collect($cek->posts)->take($limit)->sortByDesc('created_at') : collect($cek->posts))->sortByDesc('created_at') : collect([]);
        } else {
            return $limit ? $this->select($this->selected)->with('user')->WhereHas('category', function ($q) use ($slug,$type) {
                $q->where('slug', $slug)->whereType($type)->whereStatus('publish');
            })->whereType($type)->whereStatus('publish')->latest('created_at')->limit($limit)->get() :
            $this->select($this->selected)->with('user')->WhereHas('category', function ($q) use ($slug,$type) {
                    $q->where('slug', $slug)->whereType($type)->whereStatus('publish');
                })->whereType($type)->whereStatus('publish')->latest('created_at')->paginate(get_option('post_perpage'));
        }
    }

    function index_recent($type, $except = null)
    {
        if (get_module($type)->cache) {
            return $except ? $this->cachedpost($type)->whereNotIn('id', [$except])->take(5) : $this->cachedpost($type)->take(5);
        } else {
            return $except ? $this->select($this->selected)->whereType($type)->whereStatus('publish')->whereNotIn('id', [$except])->latest('id')->limit(5)->get() : $this->select($this->selected)->whereType($type)->whereStatus('publish')->latest('id')->limit(5)->get();
        }
    }

    function index_child($type, $id)
    {
        if (get_module($type)->cache) {
            return $this->cachedpost($type)->where('parent_id', $id);
        } else {
            return $this->select($this->selected)->whereType($type)->whereStatus('publish')->where('parent_id', $id)->latest('created_at')->get();
        }
    }
    function detail_by_title($type, $title)
    {
        return $this->where('title', $title)->whereType($type)->whereStatus('publish')->first();
    }
    function detail($type, $name = false)
    {
        if ($name) {
            if (get_module($type)->form->category) {
                $with[] = 'category';
                $with[] = 'user';
            }
            return $this->where('type', $type)
            ->where('slug', 'like', $name . '%')
            ->where('status', 'publish')
            ->with($with ?? ['user'])
            ->withCount('visitors')
            ->first();

        } else {
            if (get_module($type)->cache) {
                return collect($this->cachedpost($type))->first();
            } else {
                return $this->whereType($type)->whereStatus('publish')->latest('id')->first();
            }
        }
    }
    function history($post_id, $currenttime)
    {
        $type = get_post_type();
        if (get_module($type)->web->history) {
            $cekpre = collect($this->cachedpost($type))->where('id', '!=', $post_id)->where('type', get_post_type())->where('created_at', '<', $currenttime)->first();
            $ceknex = collect($this->cachedpost($type))->where('id', '!=', $post_id)->where('type', get_post_type())->where('created_at', '>', $currenttime)->sortBy('created_at')->first();
            //add new change post_thumbnail to thumbnail
            return json_decode(json_encode([
                'next' => $ceknex ? ['url' => url($ceknex->url), 'title' => $ceknex->title, 'thumbnail' => $ceknex->media] : array(),
                'previous' => $cekpre ? ['url' => url($cekpre->url), 'title' => $cekpre->title, 'thumbnail' => $cekpre->media] : array(),

            ]));
        } else {
            return false;
        }
    }
}
