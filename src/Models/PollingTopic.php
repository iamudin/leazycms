<?php
namespace Leazycms\Web\Models;

use Leazycms\Web\Models\BaseModel;



class PollingTopic extends BaseModel
{
   
    protected $fillable = ['title','description','duration','status','keyword'];

    public function options()
    {
    return $this->hasMany(PollingOption::class);
    }

}
