<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;
class PollingOption extends Model
{
    use Fileable;
    protected $fillable = ['name','image','sort','polling_topic_id','status'];
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
                foreach($post->files as $row){
                    $row->deleteFile();
                }
        });

    }
    public function responses()
    {
    return $this->hasMany(PollingResponse::class);
    }
    public function topic()
    {
        return $this->belongsTo(PollingTopic::class);
    }
}
