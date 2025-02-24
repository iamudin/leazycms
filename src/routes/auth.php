<?php
use Illuminate\Support\Facades\Route;
use Leazycms\Web\Http\Controllers\Auth\LoginController;
Route::controller(LoginController::class)->group(function ()  {
    Route::get(admin_path(), 'loginForm')->name('login');
    Route::get( 'secure/image/captcha.jpg', 'generateCaptcha')->name('captcha');
    Route::post( admin_path(), 'loginSubmit')->name('login.submit');
    Route::match(['post', 'get'], admin_path().'/logout',  'logout')->name('logout');
});
