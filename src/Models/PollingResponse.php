<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;
class PollingResponse extends Model
{
    protected $fillable = ['polling_option_id','ip','reference'];
    public function option()
    {
        return $this->belongsTo(User::class);
    }
}
