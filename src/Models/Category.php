<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;

class Category extends Model
{
    use Fileable;

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
    return $this->hasMany(Post::class);
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
        return $this->icon ? $this->icon : noimage();
    }

    function scopePublished($query)
    {
        return $query->whereStatus('publish');
    }

}
