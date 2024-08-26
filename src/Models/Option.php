<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;
class Option extends Model
{
    use Fileable;
    public $timestamps = false;
    protected $fillable = ['name','value','autoload'];

}
