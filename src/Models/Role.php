<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;
    protected $fillable = ['level', 'module','action'];
}
