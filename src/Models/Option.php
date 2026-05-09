<?php
namespace Leazycms\Web\Models;
use Leazycms\Web\Models\BaseModel;


class Option extends BaseModel
{

    public $timestamps = false;
    protected $fillable = ['name','value','autoload'];

}
