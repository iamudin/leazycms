<?php
namespace Leazycms\Web\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Leazycms\Web\Models\Notification;

class NotifReader extends Controller
{

      function notifreader($notification){
        $notification = Notification::whereId($notification)->first();
        if(empty($notification)){
        return to_route('login');

        }
        if(auth()->user()->isAdmin() || $notification->user_id == auth()->user()->id){

            $notification->mark_as_read();
            return redirect($notification->url);
        }
        return to_route('login');
    }
}

