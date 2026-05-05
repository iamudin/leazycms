<?php

namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;
use Leazycms\Web\Models\Trait\BelongsToTenant;

class BaseModel extends Model
{
    use BelongsToTenant;

    /**
     * Optional: proteksi field tenant_id
     */
    protected $guarded = ['tenant_id'];
}