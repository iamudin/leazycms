<?php
namespace Leazycms\Web\Models;
use App\Models\BaseModel;
use Illuminate\Support\Str;

class OneTimeToken extends BaseModel
{
    protected $fillable = ['user_id', 'token', 'expires_at'];

    public static function generate($userId, $ttlMinutes = 5)
    {
        return self::create([
            'user_id' => $userId,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes($ttlMinutes),
        ])->token;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
