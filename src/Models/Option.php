<?php
namespace Leazycms\Web\Models;
use Leazycms\Web\Models\BaseModel;
use Leazycms\FLC\Traits\Fileable;

class Option extends BaseModel
{
    use Fileable;
    public $timestamps = false;
    protected $fillable = ['name','value','autoload'];

}
