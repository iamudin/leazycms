<?php
namespace Leazycms\Web\Models;
use Leazycms\FLC\Traits\Fileable;
use Leazycms\Web\Models\BaseModel;

class PollingTopic extends BaseModel
{
    use Fileable;
    protected $fillable = ['title','description','duration','status','keyword'];

    public function options()
    {
    return $this->hasMany(PollingOption::class);
    }

}
