<?php
use Illuminate\Support\Facades\Route;
use Leazycms\Web\Http\Controllers\TagController;
use Leazycms\Web\Http\Controllers\PostController;
use Leazycms\Web\Http\Controllers\UserController;
use Leazycms\Web\Http\Controllers\PanelController;
use Leazycms\Web\Http\Controllers\EmailController;
use Leazycms\Web\Http\Controllers\PollingController;
use Leazycms\Web\Http\Controllers\AppMasterController;
use Leazycms\Web\Http\Controllers\CategoryController;

Route::post('files/upload_image_summernote', [PostController::class, 'uploadImageSummernote'])->name('upload_image_summernote');
Route::match(['get','post','delete'],'comments/{comment?}', [PanelController::class, 'comments'])->name('comments');
Route::get('comments-get/{post?}', [PanelController::class, 'get_comments'])->name('comments.get');
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
            Route::post($value->name . '/bulkaction', 'bulkaction')->name($value->name . '.bulkaction');
            Route::match(['get','post'],$value->name . '/{post}/restore', 'restore')->name($value->name . '.restore');
        }

        Route::post('/post/status', [PostController::class, 'updateStatus'])->name('post.status');

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
Route::controller(EmailController::class)->group(function () {
    Route::get('email', 'index')->name('email.index');
    Route::post('email', 'data')->name('email.data');
    Route::get('email/create', 'create')->name('email.create');
    Route::get('email/{email}/edit', 'edit')->name('email.edit');
    Route::put('email/{email}/edit', 'update')->name('email.update');
    Route::post('email/create', 'store')->name('email.store');
    Route::delete('email/{email}/edit', 'destroy')->name('email.destroy');
    
});
Route::controller(PanelController::class)->group(function () {
    Route::get('dashboard', 'index')->name('panel.dashboard');
    Route::get('logs', 'logs')->name('panel.logs');
    Route::match(['post','get'],'apikey', 'apikey')->name('apikey');
    Route::post('dashboard', 'visitor')->name('visitor.data');
    Route::get('admin_path/{path}', 'admin_path')->name('admin_path_changer');
    Route::match(['get', 'post'], 'appearance', 'appearance')->name('appearance');
    Route::match(['get', 'post'], 'cache', 'cache')->name('cache-manager');
    Route::match(['get', 'post'], 'appearance/editor', 'editorTemplate')->name('appearance.editor');
    Route::match(['get', 'put'], 'setting', 'setting')->name('setting');
    Route::match(['get', 'post'], 'option/{slug}', 'option')->name('option');
    Route::match(['get', 'post'], 'backup', 'backup_restore')->name('backup');
    Route::match(['get', 'post'], 'menu-target', 'menu_target')->name('menu-target');
    Route::match(['get', 'put'], 'profile', 'profile')->name('profile');
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
if(config('modules.app_master')){
    Route::controller(AppMasterController::class)->group(function () {
        Route::get('site-monitor', 'index')->name('app.master.index');
        Route::get('site-monitor/update', 'update')->name('app.master.update');
        Route::get('site-monitor/datatable', 'datatable')->name('app.master.datatable');
        Route::get('site-monitor/fetch', 'fetch')->name('app.master.fetch');
        Route::get('site-monitor/refresh', 'refresh')->name('app.master.refresh');
    });
}
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
if($custom = config('modules.custom_menu')){
    foreach($custom as $menu){
        if($menu['method']=='resource'){
            Route::resource($menu['path'], $menu['controller']);
        }else{
            Route::match(is_array($menu['method']) ? $menu['method'] : [$menu['method']], $menu['path'], [$menu['controller'], $menu['function']])->name($menu['name']);

        }
    }
}
