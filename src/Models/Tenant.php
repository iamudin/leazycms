<?php 
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Tenant extends Model
{
    protected $fillable = ['name', 'domain', 'status'];
}