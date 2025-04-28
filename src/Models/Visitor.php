<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
class Visitor extends Model
{
    protected $fillable = ['ip','user_id','post_id','ip_location','os','browser','session','device','page','reference','created_at','times'];
    public $timestamps = false;

}
