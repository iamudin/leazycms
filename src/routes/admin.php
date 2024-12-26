<?php
use Illuminate\Support\Facades\Route;
use Leazycms\Web\Http\Controllers\TagController;
use Leazycms\Web\Http\Controllers\PostController;
use Leazycms\Web\Http\Controllers\UserController;
use Leazycms\Web\Http\Controllers\PanelController;
use Leazycms\Web\Http\Controllers\PollingController;
use Leazycms\Web\Http\Controllers\CategoryController;

Route::post('files/upload_image_summernote', [PostController::class, 'uploadImageSummernote'])->name('upload_image_summernote');


Route::get('comments', [PanelController::class, 'comments'])->name('comments');
Route::get('files', [PanelController::class, 'files'])->name('files');
foreach (get_module() as $value) {
    Route::controller(PostController::class)->group(function () use ($value) {
        if (in_array('index', $value->route)) {
            Route::get($value->name, 'index')->name($value->name);
            Route::post($value->name, 'datatable')->name($value->name . '.datatable');
        }
        if (in_array('create', $value->route)) {

            Route::get($value->name . '/create', 'create')->name($value->name . '.create');
        }
        if (in_array('update', $value->route)) {

            Route::get($value->name . '/{id}/edit', 'edit')->name($value->name . '.edit');
            Route::put($value->name . '/{post}/edit', 'update')->name($value->name . '.update');
        }
        if (in_array('show', $value->route)) {
            Route::get($value->name . '/{id}/show', 'show')->name($value->name . '.show');
        }
        if (in_array('delete', $value->route)) {
            Route::delete($value->name . '/{post}/edit', 'destroy')->name($value->name . '.destroyer');
            Route::match(['get','post'],$value->name . '/{post}/restore', 'restore')->name($value->name . '.restore');
        }

    });
    if ($value->form->category) {


        Route::controller(CategoryController::class)->group(function () use ($value) {
            Route::get($value->name . '/category', 'index')->name($value->name . '.category');
            Route::post($value->name . '/category', 'datatable')->name($value->name . '.category.datatable');
            Route::get($value->name . '/category/create', 'create')->name($value->name . '.category.create');
            Route::post($value->name . '/category/create', 'store')->name($value->name . '.category.store');
            Route::get($value->name . '/category/{category}/edit', 'edit')->name($value->name . '.category.edit');
            Route::put($value->name . '/category/{category}/edit', 'update')->name($value->name . '.category.update');
            Route::delete($value->name . '/category/{category}/edit', 'destroy')->name($value->name . '.category.destroy');
        });
    }
}
Route::controller(PanelController::class)->group(function () {
    Route::get('dashboard', 'index')->name('panel.dashboard');
    Route::post('dashboard', 'visitor')->name('visitor.data');
    Route::match(['get', 'post'], 'appearance', 'appearance')->name('appearance');
    Route::match(['get', 'post'], 'appearance/editor', 'editorTemplate')->name('appearance.editor');
    Route::match(['get', 'post'], 'setting', 'setting')->name('setting');
    Route::match(['get', 'post'], 'option', 'option')->name('option');
    Route::match(['get', 'post'], 'backup', 'backup_restore')->name('backup');
});
Route::controller(UserController::class)->group(function () {
    Route::get('role', 'roleIndex')->name('role');
    Route::post('role', 'roleUpdate')->name('role.update');
    Route::get('user', 'index')->name('user');
    Route::post('user', 'datatable')->name('user.datatable');
    Route::get('user/create', 'create')->name('user.create');
    Route::post('user/create', 'store')->name('user.store');
    Route::get('user/{user}/edit', 'edit')->name('user.edit');
    Route::put('users/{user}/edit', 'update')->name('user.update');
    Route::delete('user/{user}/edit', 'destroy')->name('user.destroy');
    Route::match(['get', 'post'], 'account', 'account')->name('user.account');
});
Route::controller(TagController::class)->group(function () {
    Route::get('tags', 'index')->name('tag');
    Route::get('tags/create', 'create')->name('tag.create');
    Route::post('tags/create', 'store')->name('tag.store');
    Route::post('tags', 'datatable')->name('tag.datatable');
    Route::get('tags/{tag}/edit', 'edit')->name('tag.edit');
    Route::put('tags/{tag}/update', 'update')->name('tag.update');
    Route::delete('tags/{tag}/edit', 'destroy')->name('tag.destroy');
});
Route::controller(PollingController::class)->group(function () {
    Route::get('polling', 'index')->name('polling');
    Route::get('polling/create', 'create')->name('polling.create');
    Route::post('polling/create', 'store')->name('polling.store');
    Route::post('polling', 'datatable')->name('polling.datatable');
    Route::get('polling/{polling}/edit', 'edit')->name('polling.edit');
    Route::get('polling/{polling}/option', 'indexOption')->name('polling.option.index');
    Route::post('polling/{polling}/option', 'storeOption')->name('polling.option.store');
    Route::get('polling/{polling}/option/{option:id}/edit', 'editOption')->name('polling.option.edit');
    Route::put('polling/{polling}/option/{option}/edit', 'updateOption')->name('polling.option.update');
    Route::delete('polling/option/{option}/delete', 'destroyOption')->name('polling.option.destroy');
    Route::put('polling/{polling}/update', 'update')->name('polling.update');
    Route::delete('polling/{polling}/edit', 'destroy')->name('polling.destroy');
});
Route::get('/', function () {
    return to_route('login');
});
