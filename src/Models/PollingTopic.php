<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;
class PollingTopic extends Model
{
    use Fileable;
    protected $fillable = ['title','description','duration','status','keyword'];

    public function options()
    {
    return $this->hasMany(PollingOption::class);
    }

}
