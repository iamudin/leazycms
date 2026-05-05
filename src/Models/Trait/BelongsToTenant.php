<?php
namespace Leazycms\Web\Models\Trait;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        // ❗ jika multisite tidak aktif → skip semua
        if (!config('modules.multisite_enabled')) {
            return;
        }

        // 🔹 Scope
        static::addGlobalScope('tenant', function ($query) {
            if (app()->has('tenant')) {
                if(is_main_domain()) {
                $query->where('tenant_id', tenant()->id)->orWhereNull('tenant_id');

                }else{
                    $query->where('tenant_id', tenant()->id);

                }
            }
        });

        // 🔹 Create
        static::creating(function ($model) {
            if (app()->has('tenant')) {
                $model->tenant_id = tenant()->id;
            }
        });

        // 🔹 Save (anti manipulasi tenant_id)
        static::saving(function ($model) {
            if (app()->has('tenant')) {
                $model->tenant_id = tenant()->id;
            }
        });

        // 🔹 Update validation
        static::updating(function ($model) {
            if (app()->has('tenant')) {
                if ($model->tenant_id !== tenant()->id) {
                    abort(403, 'Akses ditolak');
                }
            }
        });

        // 🔹 Delete validation
        static::deleting(function ($model) {
            if (app()->has('tenant')) {
                if(is_null($model->tenant_id) && is_main_domain()) {
                  return;
                }
                if ($model->tenant_id !== tenant()->id) {
                    abort(403, 'Tidak diizinkan');
                }
            }
        });
    }
}