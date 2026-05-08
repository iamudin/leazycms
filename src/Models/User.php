<?php

namespace Leazycms\Web\Models;

use App\Models\User as BaseUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Leazycms\FLC\Traits\Fileable;
use Leazycms\Web\Models\Trait\BelongsToTenant;


class User extends BaseUser
{
    use SoftDeletes, Fileable,BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'level',
        'status',
        'photo',
        'email',
        'user_data',
        'host',
        'url',
        'slug',
        'active_session',
        'last_login_ip',
        'last_login_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'username',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_data' => 'array',
        'last_login_at' => 'datetime'

    ];


    public function posts()
    {
        return $this->hasMany(Post::class)->select((new Post)->selected);
    }
    public function getPhotoUserAttribute()
    {
        if ($this->photo && media_exists($this->photo)) {
            return app()->has('tenant') && !is_null($this->tenant_id) && $this->tenant_id !== tenant()->id ? 'http://' . $this->tenant?->domain . $this->photo : $this->photo;
        }
        return noimage();
    }
   function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    function scopeWithCountPosts($query)
    {
        return $query->withCount('posts');
    }
    public function isActive()
    {
        return $this->status == '1';
    }
    public function isAdmin()
    {
        return $this->level == 'admin';
    }
    public function isOperator()
    {
        return $this->level == 'operator';
    }
    public function isUser()
    {
        return $this->level == 'user';
    }
    public function roles()
    {
        return $this->hasMany(Role::class, 'level', 'level');
    }
    public function hasRole($module, $action, $noredirect = false)
    {
        if (!$this->isAdmin() && $this->roles->where('module', $module)->where('action', $action)->where('level', $this->level)->count() == 0) {
            if ($action == 'delete') {
                return true;
            }
            if ($noredirect) {
                return true;
            }
            return redirect(route('panel.dashboard'))->send()->with('danger', 'Akses terbatas')->send();
        }


    }


    public function get_modules()
    {
        return $this->roles()->where('action', 'index');
    }
}
