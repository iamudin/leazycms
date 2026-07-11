<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Tenant extends Model
{
   protected $casts = [
        'modules' => 'array',
        'plugins' => 'array',
   ];
    protected $fillable = ['name', 'domain', 'status','theme','modules','plugins','custom_theme', 'disk_space'];
    function themeSelected(){
        return $this->belongsTo(Theme::class,'theme','path');
    }
    function admin(){
        return $this->hasOne(User::class,'id','tenant_id');
    }
}
