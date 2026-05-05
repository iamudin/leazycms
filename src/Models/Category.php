<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Leazycms\FLC\Traits\Fileable;
use Leazycms\Web\Models\BaseModel;

class Category extends BaseModel
{
    use Fileable,SoftDeletes;

    protected $fillable=[
        'type','url','status','name','description','slug','icon','sort','visited'
      ];
      public static function boot()
      {
          parent::boot();

          static::deleting(function ($post) {
                  foreach($post->files as $row){
                      $row->deleteFile();
                  }
          });

      }
    public function posts()
    {
    return $this->hasMany(Post::class)->selectedColumn();
    }
    function scopeWithCountPosts($query)
    {
        return $query->withCount(['posts' => function($q) {
            $q->published();
        }]);
    }
    function scopeOnType($query,$type)
    {
        return $query->whereType($type);
    }
    function getThumbnailAttribute()
    {
        return $this->icon && media_exists($this->icon) ? $this->icon : noimage();
    }
  public function getLinkAttribute()
    {
        return url($this->url);
    }

    function scopePublished($query)
    {
        return $query->whereStatus('publish');
    }

}
