<?php
namespace Leazycms\WebHttp\Controllers;

use Closure;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class ApiController extends Controller implements HasMiddleware
{
    public static function middleware(): array {
        return [
            function (Request $request, Closure $next) {

                return $next($request);
            },
        ];
    }
    public function site_info(Request $request)
    {
        $referer = $request->header('referer');
        $origin = $request->header('origin');
        return $referer.' sumber '.$origin;
    }
}

