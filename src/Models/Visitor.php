<?php
namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'user_agent',
        'session',
        'ip',
        'country_code',
        'country',
        'region',
        'city',
        'device',
        'browser',
        'os',
        'domain',
        'status',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];
    public function lastLog()
    {
        return $this->hasOne(VisitorLog::class, 'visitor_id')
            ->latest('created_at');
    }

    public function logs()
    {
        return $this->hasMany(VisitorLog::class);
    }
}
