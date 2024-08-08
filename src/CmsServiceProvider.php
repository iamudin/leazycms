<?php
namespace Leazycms\Web;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Leazycms\Web\Middleware\Web;
use Leazycms\Web\Middleware\Panel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Leazycms\Web\Exceptions\NotFoundHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Leazycms\Web\Middleware\RateLimit;

class CmsServiceProvider extends ServiceProvider
{
    protected function registerRoutes()
    {

        Route::group([
            'prefix' => admin_path(),
            'middleware' => ['web', 'admin'],
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
        });
        Route::group([
            'middleware' => 'web',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/auth.php');
        });
        Route::group([
            'middleware' => 'web',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
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
    public function boot(Request $req)
    {
        load_default_module();
        $this->registerMiddleware();
        $this->registerResources();
        $this->registerMigrations();
        $this->defineAssetPublishing();
        $this->cmsHandler($req);
        $this->registerRoutes();

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
        Carbon::setLocale(config('app.locale'));
        Carbon::setFallbackLocale(config('app.fallback_locale'));
        Config::set('auth.providers.users.model', 'Leazycms\Web\Models\User');

        if (DB::connection()->getPDO() && $this->checkAllTables()) {
            if (empty(Cache::get('option'))) {
                recache_option();
            }
            if (empty(Cache::has('menu'))) {
                recache_menu();
            }
            if (empty(Cache::has('media'))) {
                recache_media();
            }
            if ((get_option('site_maintenance') && get_option('site_maintenance') == 'Y') || (!$this->app->environment('production') && env('APP_DEBUG')==true)) {
                Config::set(['app.debug' => true]);
            } else {
                Config::set(['app.debug' => false]);
            }
            if (get_module('domain') && $domain = query()->detail_by_title('domain', (new Request)->getHttpHost())) {
                Config::set('modules.domain', $domain);
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
                config(['modules.config' => $config]);
            }
        }
            config(['modules.version' => leazycms_version()]);
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
        return (Schema::hasTable('users') && Schema::hasTable('posts') && Schema::hasTable('categories') && Schema::hasTable('visitors') && Schema::hasTable('comments') && Schema::hasTable('tags') && Schema::hasTable('roles') && Schema::hasTable('logs') && Schema::hasTable('options')) ? true : false;
    }
}
