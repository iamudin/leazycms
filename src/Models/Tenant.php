<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Tenant extends Model
{
   protected $casts = [
        'modules' => 'array',
   ];
    protected $fillable = ['name', 'domain', 'status','theme','modules','custom_theme'];
    function themeSelected(){
        return $this->belongsTo(Theme::class,'theme','path');
    }
    function admin(){
        return $this->hasOne(User::class,'id','tenant_id');
    }
}
