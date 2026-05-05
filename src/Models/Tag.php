<?php

namespace Leazycms\Web\Models;

use Leazycms\Web\Models\BaseModel;

class Tag extends BaseModel
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
