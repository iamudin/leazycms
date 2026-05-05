<?php
namespace Leazycms\Web\Models;

use Leazycms\Web\Models\BaseModel;

class Role extends BaseModel
{
    public $timestamps = false;
    protected $fillable = ['level', 'module','action'];
}
