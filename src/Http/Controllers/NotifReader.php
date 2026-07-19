<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Leazycms\Web\Models\Notification;

class NotifReader extends Controller
{

    function notifreader($notification)
    {
        $notification = Notification::whereId($notification)->first();
        abort_if(empty($notification), 404);
        $notification->mark_as_read();
        return redirect($notification->url);

    }
}

