<?php
namespace Leazycms\Web\Models;
use Illuminate\Database\Eloquent\Model;
use Leazycms\FLC\Traits\Fileable;

class Tenant extends Model
{
    use Fileable;

   protected $casts = [
        'modules' => 'array',
        'plugins' => 'array',
   ];
    protected $fillable = ['name', 'domain', 'status','theme','modules','plugins','custom_theme', 'disk_space'];
    function themeSelected(){
        return $this->belongsTo(Theme::class,'theme','path');
    }
    function admin(){
        return $this->hasOne(User::class,'id','tenant_id');
    }
    public function options()
    {
        return $this->hasMany(Option::class, 'tenant_id')->withoutGlobalScope('tenant');
    }
    public function posts()
    {
        return $this->hasMany(Post::class, 'tenant_id');
    }

    public static function resolveByHost(?string $host): ?self
    {
        if (empty($host)) {
            return null;
        }

        $tenantData = \Illuminate\Support\Facades\Cache::rememberForever(
            "tenant:{$host}",
            function () use ($host) {
                $t = self::whereDomain($host)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                if ($t) {
                    return $t->getRawOriginal();
                }

                if (class_exists(Option::class)) {
                    // 1. Cek apakah host adalah parked_domain milik tenant
                    $parkedOption = Option::withoutGlobalScope('tenant')
                        ->where('name', 'parked_domain')
                        ->where('value', $host)
                        ->whereNotNull('tenant_id')
                        ->first();

                    if ($parkedOption) {
                        $status = Option::withoutGlobalScope('tenant')
                            ->where('tenant_id', $parkedOption->tenant_id)
                            ->where('name', 'parked_domain_status')
                            ->value('value');

                        if ($status === 'verified' || is_null($status)) {
                            $t = self::where('id', $parkedOption->tenant_id)
                                ->whereIn('status', ['active', 'suspended', 'maintenance'])
                                ->first();
                            if ($t) {
                                $data = $t->getRawOriginal();
                                $data['is_parked_domain'] = true;
                                $data['matched_parked_domain'] = $host;
                                return $data;
                            }
                        }
                    }

                    // 2. Fallback: Cek custom domain plugin
                    $option = Option::withoutGlobalScope('tenant')
                        ->where('value', $host)
                        ->where('name', 'like', '%-domain')
                        ->whereNotNull('tenant_id')
                        ->first();

                    if ($option) {
                        $t = self::where('id', $option->tenant_id)
                            ->whereIn('status', ['active', 'suspended', 'maintenance'])
                            ->first();
                        if ($t) {
                            $data = $t->getRawOriginal();
                            $data['is_plugin_custom_domain'] = true;
                            return $data;
                        }
                    }
                }

                return null;
            }
        );

        if (!$tenantData) {
            return null;
        }

        if (isset($tenantData['modules']) && is_array($tenantData['modules'])) {
            $tenantData['modules'] = json_encode($tenantData['modules']);
        }
        if (isset($tenantData['plugins']) && is_array($tenantData['plugins'])) {
            $tenantData['plugins'] = json_encode($tenantData['plugins']);
        }

        $tenant = new self();
        $tenant->setRawAttributes($tenantData, true);
        $tenant->exists = true;

        return $tenant;
    }
}
