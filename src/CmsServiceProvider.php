<?php

namespace Leazycms\Web;
use Carbon\Carbon;
use Leazycms\Web\Commands\ThemeUpdateCommand;
use Leazycms\Web\Middleware\Web;
use Illuminate\Support\Facades\DB;
use Leazycms\Web\Middleware\Panel;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Leazycms\Web\Middleware\RateLimit;
use Illuminate\Support\ServiceProvider;
use Leazycms\Web\Commands\InstallCommand;
use Leazycms\Web\Exceptions\NotFoundHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Leazycms\Web\Commands\ResetPassword;
use Leazycms\Web\Commands\RouteListBlock;
use Leazycms\Web\Commands\UpdateCMS;
class CmsServiceProvider extends ServiceProvider
{
    protected function registerRoutes()
    {
        Route::prefix(admin_path())
        ->middleware(['web', 'admin'])
        ->domain(config('app.sub_app_enabled') ? parse_url(config('app.url'), PHP_URL_HOST):null)
        ->group(function () {
            $this->loadRoutesFrom(__DIR__.'/routes/admin.php');
        });
        Route::middleware(['web'])
        ->group(function () {
            $this->loadRoutesFrom(__DIR__.'/routes/auth.php');
        });

        Route::middleware(['web'])
        ->domain(config('app.sub_app_enabled') ? parse_url(config('app.url'), PHP_URL_HOST):null)
        ->group(function () {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        });
    }
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'cms');
    }
    protected function configure()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/modules.php", "modules");
    }
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . "/database/migrations");
    }
    protected function registerServices()
    {
        $this->app->singleton('public', Web::class);
        $this->app->singleton('admin', Panel::class);
        $this->app->singleton(ExceptionHandler::class, NotFoundHandler::class);
    }
    public function defineAssetPublishing()
    {
        $this->publishes([
            __DIR__ . '/public' => public_path('/'),
            __DIR__ . '/views/errors' => resource_path('views/errors'),
            __DIR__ . '/views/template' => resource_path('views/template')
        ], 'cms');
    }
    public function boot()
    {

        
        Schema::defaultStringLength(191);
        load_default_module();
        $this->registerMiddleware();
        $this->registerResources();
        $this->registerMigrations();
        $this->defineAssetPublishing();
        $this->cmsHandler();
        $this->registerRoutes();
        $this->commands([
            InstallCommand::class,RouteListBlock::class,ResetPassword::class,UpdateCMS::class,ThemeUpdateCommand::class
        ]);
    }
  
    public function register()
    {

        $this->configure();
        $this->registerServices();
        $this->registerFunctions();

        if (config('modules.public_path')) {
            $this->app->usePublicPath(base_path() . '/' . config('modules.public_path'));
        }
    }
    protected function registerMiddleware()
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', RateLimit::class);
    }
    protected function cmsHandler()
    {
        Carbon::setLocale('ID');
        config(['app.timezone' => config('modules.timezone')]);
        Config::set('auth.providers.users.model', 'Leazycms\Web\Models\User');

        if (
            DB::connection()->getDatabaseName() &&
            (config('modules.installed', false) ? true : $this->checkAllTables())
        ) {
            try {
                if (!config('modules.option')) {
                    $options = \Leazycms\Web\Models\Option::pluck('value', 'name')->toArray();
                    config(['modules.option' => $options]);
                }

                if (
                    (get_option('site_maintenance') && get_option('site_maintenance') == 'Y')
                    || (!$this->app->environment('production') && config('app.debug') == true)
                ) {
                    Config::set(['app.debug' => true]);
                } else {
                    Config::set(['app.debug' => false]);
                }
            } catch (\Exception $e) {
                return abort(500, $e->getMessage());
            }

            $this->loadTemplateConfig();
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    protected function loadTemplateConfig()
    {
        $templateName = template();
        $configFile = resource_path("views/template/{$templateName}/modules.blade.php");

        if (file_exists($configFile)) {
            ob_start();
            include $configFile;
            ob_end_clean();
            if (isset($config)) {
                foreach($config ?? [] as $key=>$row){
                    if(!config('modules.config.'.$key)){
                        config(['modules.config.'.$key => $row]);
                    }
                }
            }
        }

    }
    /**
     * Summary of register
     * @return void
     */
    protected function registerFunctions()
    {
        require_once(__DIR__ . "/Inc/Helpers.php");
    }


    protected function checkAllTables()
    {
        return (Schema::hasTable('users') && Schema::hasTable('files') && Schema::hasTable('posts') && Schema::hasTable('categories') && Schema::hasTable('visitors') && Schema::hasTable('comments') && Schema::hasTable('tags') && Schema::hasTable('roles') && Schema::hasTable('logs') && Schema::hasTable('options')) ? true : false;
    }
}
