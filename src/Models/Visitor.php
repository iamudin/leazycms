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

    public function logs()
    {
        return $this->hasMany(VisitorLog::class);
    }
}
