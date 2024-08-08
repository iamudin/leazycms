<?php

namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'url',
        'slug',
        'description',
        'visited'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class)->select((new Post)->selected);
    }
}
