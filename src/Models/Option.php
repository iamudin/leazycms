<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FileManager\Traits\Fileable;
class Option extends Model
{
    use Fileable;
    public $timestamps = false;
    protected $fillable = ['name','value','autoload'];
    public function medias()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id')->whereType('media')->whereParentType('option');
    }
}
