<?php
namespace Leazycms\Web\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'url',
        'is_read'
    ];
    public function notificationable()
    {
        return $this->morphTo();
    }
    function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeForCurrentTenant($query)
    {
        if (config('modules.multisite_enabled')) {
            $hosts = array_values(array_filter(array_unique([
                tenant()?->domain,
                request()->getHost(),
            ])));

            if (!empty($hosts)) {
                $query->where(function ($q) use ($hosts) {
                    foreach ($hosts as $host) {
                        $q->orWhere('url', 'like', "%//{$host}/%")
                          ->orWhere('url', 'like', "%//{$host}:%")
                          ->orWhere('url', 'like', "%//{$host}")
                          ->orWhere('url', 'like', "%//www.{$host}/%")
                          ->orWhere('url', 'like', "%//www.{$host}:%");
                    }
                });
            }
        }

        return $query;
    }

    function get_unread_notifications(){
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        if ($user->isAdmin()) {
            return self::where('is_read', false)
                ->where(function ($q) use ($user) {
                    $q->where(function ($sub) {
                        $sub->whereNull('user_id')->forCurrentTenant();
                    })->orWhere('user_id', $user->id);
                })
                ->latest()
                ->get();
        }

        return self::whereBelongsTo($user)->where('is_read', false)->latest()->get();
    }

    function mark_as_read(){
        $this->update(['is_read'=>true]);
    }

    function get_read_notifications(){
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        if ($user->isAdmin()) {
            return self::where('is_read', true)
                ->where(function ($q) use ($user) {
                    $q->where(function ($sub) {
                        $sub->whereNull('user_id')->forCurrentTenant();
                    })->orWhere('user_id', $user->id);
                })
                ->latest()
                ->get();
        }

        return self::whereBelongsTo($user)->where('is_read', true)->latest()->get();
    }
}
