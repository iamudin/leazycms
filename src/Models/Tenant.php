<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;

class Tenant extends Model
{
    use Fileable;

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
    public function options()
    {
        return $this->hasMany(Option::class, 'tenant_id')->withoutGlobalScope('tenant');
    }
}
