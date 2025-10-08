<?php
namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{

    protected $fillable = [
        'visitor_id',
        'page',
        'reference',
        'post_id',
        'status_code',
        'tried',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
