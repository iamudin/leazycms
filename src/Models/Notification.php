<?php
namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'url',
        'is_read'
    ];
    public function notificationable()
    {
        return $this->morphTo();
    }
    function user(){
        return $this->belongsTo(User::class);
    }
    function get_unread_notifications(){
        if(auth()->user()->isAdmin()){
            return self::where('is_read',false)->whereNull('user_id')->latest()->get();
        }else{
            return self::whereBelongsTo(auth()->user())->where('is_read',false)->latest()->get();
        }
    }
    function mark_as_read(){
        $this->update(['is_read'=>true]);
    }

    function get_read_notifications(){
        if(auth()->user()->isAdmin()){
            return self::where('is_read',true)->latest()->get();
        }else{
            return self::whereBelongsTo(auth()->user())->where('is_read',true)->latest()->get();
        }
    }
}
