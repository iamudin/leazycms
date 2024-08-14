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
    public function medias()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id')->whereType('media');
    }

}
