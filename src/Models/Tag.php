<?php

namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'url',
        'slug',
        'status',
        'description',
        'visited'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class)->select((new Post)->selected);
    }
}
