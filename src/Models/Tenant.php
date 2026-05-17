<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Tenant extends Model
{
   protected $casts = [
        'modules' => 'array',
   ];
    protected $fillable = ['name', 'domain', 'status','theme','modules'];
    function theme(){
        return $this->hasOne(Theme::class,'path','theme');
    }
    function admin(){
        return $this->hasOne(User::class,'id','tenant_id');
    }
}
