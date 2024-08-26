<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
    use SoftDeletes;

    protected $fillable=[
        'type','url','status','name','description','slug','icon','sort'
      ];

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
    function scopePublished($query)
    {
        return $query->whereStatus('publish');
    }

}
