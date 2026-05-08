<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class PollingResponse extends Model
{
    protected $fillable = ['polling_option_id','ip','reference'];
    public function option()
    {
        return $this->belongsTo(User::class);
    }
}
