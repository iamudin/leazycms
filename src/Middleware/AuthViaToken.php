<?php
namespace Leazycms\Web\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Leazycms\Web\Models\OneTimeToken;

class AuthViaToken
{
public function handle($request, Closure $next)
{
$bearer = $request->bearerToken();
if ($bearer) {
$token = OneTimeToken::where('token', $bearer)
->where('expires_at', '>', now())
->first();

if ($token) {
Auth::login($token->user); // login guard web
$token->delete(); // HAPUS setelah dipakai
}
}

return $next($request);
}
}