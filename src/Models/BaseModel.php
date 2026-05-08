<?php

namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;
use Leazycms\Web\Models\Trait\BelongsToTenant;

class BaseModel extends Model
{
    use BelongsToTenant, Fileable;
    protected $guarded = ['tenant_id'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {

            if (
                !method_exists($post, 'isForceDeleting')
                || $post->isForceDeleting()
            ) {

                foreach ($post->files as $row) {
                    $row->deleteFile();
                }
            }
        });
    }
    /**
     * Optional: proteksi field tenant_id
     */

    function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

}