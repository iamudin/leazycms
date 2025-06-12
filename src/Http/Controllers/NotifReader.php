<?php
namespace Leazycms\Web\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Controllers\Controller;
use Leazycms\Web\Models\Notification;

class NotifReader extends Controller  implements HasMiddleware

{
    public static function middleware(): array {
        return [
            new Middleware('auth')
        ];
    }
      function notifreader($notification){
        $notification = Notification::whereId($notification)->first();
        if(empty($notification)){
        return to_route('login');

        }
        if(auth()->user()->isAdmin() || $notification->user_id == auth()->id){
            $notification->mark_as_read();
            return redirect($notification->url);
        }
        return to_route('login');

    }
}

