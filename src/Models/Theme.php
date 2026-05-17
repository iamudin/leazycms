<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Theme extends Model
{
    protected $fillable = ['path', 'name', 'status','preview','git'];

    function tenants(){
        return $this->hasMany(Tenant::class,'theme','path');
    }

}
