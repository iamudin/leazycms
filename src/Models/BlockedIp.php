<?php

namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip',
        'country',
        'region',
        'device',
        'user_agent',
        'reason',
        'blocked_at',
        'unblocked_at',
        'unblocked_by',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'unblocked_at' => 'datetime',
    ];
}
