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
use Leazycms\Web\Middleware\TrackVisitor;
use Leazycms\Web\Middleware\Web;
use Opcodes\LogViewer\Facades\LogViewer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class CmsServiceProvider extends ServiceProvider
{

protected function handle403(){
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->renderable(function (Throwable $e, $request) {

            if (config('app.debug')) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 403) {
                return $this->render403($request, $e);
            }

            return null;
        });
}
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

                return response( preg_replace('/\s+/', ' ',error500Msg($requestId)), 500)
                    ->header('Content-Type', 'text/html')
                    ->header('X-Request-ID', $requestId)
                    ->header('Cache-Control', 'public, max-age=3600')
                    ->header('Expires', gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT')
                    ->send();;
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
                    ->middleware(['web', 'public',TrackVisitor::class])
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
        $this->handle403();
        $this->handle500();

    }
    protected function render403($request, Throwable $e)
    {
        $requestId = (string) Str::uuid();

        Log::warning('Forbidden Access Attempt', [
            'request_id' => $requestId,
            'message' => $e->getMessage(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => optional(auth()->user())->id,
        ]);

        // Jika API / JSON
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 403,
                'message' => 'Access forbidden.',
                'request_id' => $requestId,
            ], 403)->header('X-Request-ID', $requestId);
        }

        return response(
            preg_replace('/\s+/', ' ', error403Msg($requestId)),
            403
        )
            ->header('Content-Type', 'text/html')
            ->header('X-Request-ID', $requestId);
    }
  function log_viewer(){
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
        date_default_timezone_set(config('modules.timezone'));
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

   
}
