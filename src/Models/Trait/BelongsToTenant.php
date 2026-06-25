<?php
namespace Leazycms\Web\Models\Trait;

trait BelongsToTenant
{
    public function scopeWithTenant($query)
    {
        if (config('modules.multisite_enabled')) {
            $query->with(['tenant' => function ($q) {
                $q->select('id', 'domain')->whereIn('status', ['active','maintenance','suspended']);
            }])->where(function ($q) {
                $q->whereHas('tenant', function ($sub) {
                    $sub->whereIn('status', ['active','maintenance','suspended']);
                })->orWhereNull('tenant_id');
            });
        }

        return $query;
    }
    protected static function bootBelongsToTenant()
    {
        // ❗ jika multisite tidak aktif → skip semua
        if (!config('modules.multisite_enabled')) {
            return;
        }

        // 🔹 Scope
        static::addGlobalScope('tenant', function ($builder) {

            $model = $builder->getModel();

            // hanya untuk tabel options
            if ($model->getTable() !== 'options'  && is_main_domain()) {
                return;
            }

            if (app()->has('tenant')) {
                $builder->where('tenant_id', tenant()->id);
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
                if(is_main_domain()) {
                    return;
                }
                $model->tenant_id = tenant()->id;
            }
        });

        // 🔹 Update validation
        static::updating(function ($model) {

            if (!app()->has('tenant')) {
                return;
            }

            // skip kalau main domain (super admin)
            if (is_main_domain()) {
                return;
            }

            // ambil tenant asli dari database (bukan dari request)
            $originalTenantId = $model->tenant_id;
            if ($originalTenantId !== tenant()->id) {
                abort(403, 'Forbidden: Anda tidak memiliki akses ke data ini');
            }
        });

        // 🔹 Delete validation
        static::deleting(function ($model) {
            if (app()->has('tenant')) {
                if(is_null($model->tenant_id) || !is_null($model->tenant_id) && is_main_domain()) {
                  return;
                }
                if ($model->tenant_id !== tenant()->id) {
                    abort(403, 'Tidak diizinkan');
                }
            }
        });
    }
}
