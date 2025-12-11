<?php
namespace Leazycms\Web\Exceptions;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Leazycms\Web\Http\Controllers\NotFoundController;
use Leazycms\Web\Http\Controllers\AppMasterController;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function render($request, Throwable $exception): \Symfony\Component\HttpFoundation\Response
    {
       
        if ($exception instanceof NotFoundHttpException) {
        return (new NotFoundController)->error404();
        }

        return parent::render($request, $exception);
    }
}
