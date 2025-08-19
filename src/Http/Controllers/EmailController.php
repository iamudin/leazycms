<?php
namespace Leazycms\Web\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Controllers\Controller;
use Leazycms\Web\Models\Notification;

class EMailController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth')
        ];
    }
    function index()
    {
        return view('cms::backend.email.index');
    }

    function data(Request $request){
        
    }
}

