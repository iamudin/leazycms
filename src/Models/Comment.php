<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Comment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'name',
        'link',
        'email',
        'content',
        'user_data',
        'pinned',
        'status'
    ];
    protected $casts=[
        'comment_data' => 'array',
    ];

public function post()
  {
  return $this->belongsTo(Post::class);
  }
  public function user()
  {
  return $this->belongsTo(User::class);
  }
}
