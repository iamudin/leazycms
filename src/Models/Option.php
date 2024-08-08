<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Option extends Model
{
    public $timestamps = false;
    protected $fillable = ['name','value','autoload'];
    public function medias()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id')->whereType('media')->whereParentType('option');
    }
}
