<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;

class PollingOption extends Model
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
