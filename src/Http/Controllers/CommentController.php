<?php
namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class CommentController extends Controller implements HasMiddleware
{
    public static function middleware(): array {
        return [
            new Middleware('auth')
        ];
    }
    public function index(){
        return view('cms::categories.index');
    }
}

