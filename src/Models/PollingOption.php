<?php
namespace Leazycms\Web\Models;
use Leazycms\Web\Models\BaseModel;

class PollingOption extends BaseModel
{
   
    protected $fillable = ['name','image','sort','polling_topic_id','status'];
   
    public function responses()
    {
    return $this->hasMany(PollingResponse::class);
    }
    public function topic()
    {
        return $this->belongsTo(PollingTopic::class);
    }
}
