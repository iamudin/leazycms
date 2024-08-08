<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['user_id', 'activity','type'];

    function user(){
        return $this->belongsTo(User::class);
    }
}
