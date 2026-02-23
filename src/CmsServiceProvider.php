<?php

namespace Leazycms\Web;
use Carbon\Carbon;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Leazycms\Web\Commands\AssetLink;
use Leazycms\Web\Commands\InstallCommand;
use Leazycms\Web\Commands\ResetPassword;
use Leazycms\Web\Commands\RouteListBlock;
use Leazycms\Web\Commands\ThemeUpdateCommand;
use Leazycms\Web\Commands\UpdateCMS;
use Leazycms\Web\Exceptions\NotFoundHandler;
use Leazycms\Web\Http\Controllers\VisitorStatsController;
use Leazycms\Web\Middleware\Panel;
use Leazycms\Web\Middleware\RateLimit;
use Leazycms\Web\Middleware\Web;
use Opcodes\LogViewer\Facades\LogViewer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class CmsServiceProvider extends ServiceProvider
{

    protected function handle500()
    {
        $this->app->afterResolving(ExceptionHandler::class, function ($handler) {

            $handler->renderable(function (Throwable $e, $request) {

                if (config('app.debug')) {
                    return null;
                }

                // Jika HttpException tapi bukan 500 → biarkan default
                if (
                    $e instanceof HttpExceptionInterface &&
                    $e->getStatusCode() !== 500
                ) {
                    return null;
                }

                // Generate unique request ID
                $requestId = (string) Str::uuid();

                // Log error lengkap
                Log::error('Server Error Occurred', [
                    'request_id' => $requestId,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]);

                // Jika request API / JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Server error occurred.',
                        'request_id' => $requestId,
                    ], 500)->header('X-Request-ID', $requestId);
                }

                return response($this->error500Msg($requestId), 500)
                    ->header('Content-Type', 'text/html')
                    ->header('X-Request-ID', $requestId);
            });

        });
    }
protected function registerRoutes()
    {
        $webroute = get_domain_routes();
        Route::prefix(admin_path())
            ->middleware(['web', 'admin'])
            ->domain(config('app.sub_app_enabled') ? parse_url(config('app.url'), PHP_URL_HOST) : null)
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
            });
        Route::middleware(['web'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/auth.php');
            });

        Route::middleware(['web'])
            ->domain(config('app.sub_app_enabled') || $webroute ? parse_url(config('app.url'), PHP_URL_HOST) : null)
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            });
        if ($webroute) {
            $grouped = collect($webroute)->groupBy(function ($wr) {
                return parse_url($wr['path'], PHP_URL_HOST);
            });

            foreach ($grouped as $host => $routes) {

                Route::domain($host)
                    ->middleware(['web', 'public'])
                    ->group(function () use ($routes) {

                        foreach ($routes as $wr) {

                            $uri = parse_url($wr['path'], PHP_URL_PATH) ?? '/';

                            Route::match(
                                is_array($wr['method']) ? $wr['method'] : [$wr['method']],
                                ltrim($uri, '/'),
                                [$wr['controller'], $wr['function']]
                            )->name($wr['name']);

                        }
                    });
            }
        }
        Route::get('stats.png', [VisitorStatsController::class, 'headerImage']);
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
  
    public function boot(Kernel $kernel)
    {

        $this->handle500();
        Schema::defaultStringLength(191);
        load_default_module();
        $kernel->appendMiddlewareToGroup('web', RateLimit::class);
        $this->registerResources();
        $this->registerMigrations();
        $this->defineAssetPublishing();
        $this->cmsHandler();
        $this->registerRoutes();
        $this->commands([
            InstallCommand::class,RouteListBlock::class,ResetPassword::class,UpdateCMS::class,ThemeUpdateCommand::class,AssetLink::class
        ]);
        $this->log_viewer();
        $this->handle500();

    }
  function log_viewer(){
    //  config(['logging.default' => 'daily']);
        LogViewer::auth(function ($request) {
            return $request->user()
                && in_array($request->user()->level, [
                    'admin',
                ]);
        });
  
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
 
    protected function cmsHandler()
    {
        Carbon::setLocale('ID');
        config(['app.timezone' => config('modules.timezone')]);
        if(config('log-viewer.route_path')){
        config(['log-viewer.timezone' => config('modules.timezone')]);
        }
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

        }
            $this->loadTemplateConfig();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    protected function loadTemplateConfig()
    {
        $templateName = template();
        $configFile = resource_path("views/template/{$templateName}/modules.blade.php");

        if (file_exists($configFile)) {
            try {
                ob_start();
                // suppress error pakai @ agar tidak fatal
                @include $configFile;
                ob_end_clean();
            } catch (\Throwable $e) {
                // kalau ada error, jangan lakukan apa-apa
                Log::warning("Template config gagal diload: " . $e->getMessage());
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

    protected function error500Msg($requestId){
        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Server Error</title>
    <style>
        body {
            margin:0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center;
        }
        .card {
            background:#1e293b;
            padding:40px;
            border-radius:16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            max-width:500px;
            width:90%;
        }
        h1 {
            margin:0 0 10px;
            font-size:28px;
        }
        p {
            opacity:0.8;
            margin-bottom:20px;
        }
        .request-id {
            background:#0f172a;
            padding:10px 15px;
            border-radius:8px;
            font-family: monospace;
            font-size:14px;
            color:#38bdf8;
            word-break: break-all;
        }
        .footer {
            margin-top:25px;
            font-size:12px;
            opacity:0.6;
        }
    </style>
</head>
<body>
    <div class='card'>
        <h1>⚠ Server Error</h1>
        <p>Something went wrong on our side.</p>
        <div class='request-id'>
            Request ID: {$requestId}
        </div>
        <div class='footer'>
            Please contact administrator and provide this ID.
        </div>
    </div>
</body>
</html>";
    }
}
