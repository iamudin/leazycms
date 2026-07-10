<?php

namespace Leazycms\Web;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Leazycms\Web\Commands\AssetLink;
use Leazycms\Web\Commands\InstallCommand;
use Leazycms\Web\Commands\ResetPassword;
use Leazycms\Web\Commands\RouteListBlock;
use Leazycms\Web\Commands\ThemeUpdateCommand;
use Leazycms\Web\Commands\PluginUpdateCommand;
use Leazycms\Web\Commands\UpdateCMS;
use Leazycms\Web\Exceptions\NotFoundHandler;
use Leazycms\Web\Http\Controllers\VisitorStatsController;
use Leazycms\Web\Middleware\IdentifyTenant;
use Leazycms\Web\Middleware\Panel;
use Leazycms\Web\Middleware\RateLimit;
use Leazycms\Web\Middleware\TrackVisitor;
use Leazycms\Web\Middleware\Web;
use Opcodes\LogViewer\Facades\LogViewer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class CmsServiceProvider extends ServiceProvider
{

    protected function handle403()
    {
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

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return null;
                }

                if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                    return null;
                }

                if ($e instanceof ValidationException) {
                    return null;
                }

                if ($e instanceof AuthorizationException) {
                    return null;
                }

                if ($e instanceof ModelNotFoundException) {
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

                $content = error500Msg($requestId);
                if (function_exists('minify_all_one_line')) {
                    $content = minify_all_one_line($content);
                }

                return response($content, 500)
                    ->header('Content-Type', 'text/html')
                    ->header('X-Request-ID', $requestId)
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->header('Pragma', 'no-cache');
            });

        });
    }
    protected function registerRoutes()
    {
        $webroute = get_domain_routes();
        if ($webroute) {
            $grouped = collect($webroute)->groupBy(function ($wr) {
                return parse_url($wr['path'], PHP_URL_HOST);
            });

            foreach ($grouped as $host => $routes) {
                Route::domain($host)
                    ->middleware(['web', 'public', TrackVisitor::class])
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

        if (config('modules.multisite_enabled')) {
            Route::prefix(admin_path())
                ->middleware(['web', 'admin'])
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
                });
            Route::middleware(['web'])
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/routes/auth.php');
                });
            Route::middleware(['web'])
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                });
        } else {
            Route::prefix(admin_path())
                ->middleware(['web', 'admin'])
                ->domain(get_option('sub_app_enabled') && get_option('sub_app_enabled') == 'Y' ? parse_url(config('app.url'), PHP_URL_HOST) : null)
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
                });
            Route::middleware(['web'])
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/routes/auth.php');
                });

            Route::middleware(['web'])
                ->domain(get_option('sub_app_enabled') && get_option('sub_app_enabled') == 'Y' ? parse_url(config('app.url'), PHP_URL_HOST) : null)
                ->group(function () {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                });
        }
        Route::get('stats.webp', [VisitorStatsController::class, 'headerImage'])->name('stats');
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
        if (config('modules.multisite_enabled')) {
            $this->loadMigrationsFrom(__DIR__ . "/database/multisite");
        }

        // Load plugin migrations
        $pluginsPaths = glob(resource_path('plugins/*'), GLOB_ONLYDIR);
        if ($pluginsPaths) {
            foreach ($pluginsPaths as $pluginPath) {
                $migrationPath = $pluginPath . '/migrations';
                if (is_dir($migrationPath)) {
                    $this->loadMigrationsFrom($migrationPath);
                }
            }
        }
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
            __DIR__ . '/views/template' => resource_path('views/template')
        ], 'cms');
    }

    public function boot(Kernel $kernel)
    {

        Schema::defaultStringLength(191);
        load_default_module();
        if (config('modules.multisite_enabled')) {
            $kernel->prependMiddlewareToGroup('web', IdentifyTenant::class);
        }
        $kernel->appendMiddlewareToGroup('web', \Leazycms\Web\Middleware\CheckPluginAccess::class);
        @include(__DIR__ . "/Inc/Option.php");
        $kernel->appendMiddlewareToGroup('web', RateLimit::class);

        $this->registerResources();
        $this->registerMigrations();
        $this->defineAssetPublishing();
        $this->cmsHandler();
        $this->registerRoutes();
        $this->commands([
            InstallCommand::class,
            \Leazycms\Web\Commands\RegisterCloudCommand::class,
            RouteListBlock::class,
            ResetPassword::class,
            UpdateCMS::class,
            ThemeUpdateCommand::class,
            PluginUpdateCommand::class,
            AssetLink::class
        ]);
        $this->log_viewer();
        $this->handle500();
        $this->handle403();


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
            'user_id' => optional(Auth::user())->id,
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
    function log_viewer()
    {
        LogViewer::auth(function ($request) {
            return $request->user()
                && in_array($request->user()->level, [
                    'admin',
                ]);
        });

    }
    public function register()
    {
        // Custom Autoloader for Plugins in resources/plugins/
        spl_autoload_register(function ($class) {
            if (\Illuminate\Support\Str::startsWith($class, 'App\\Http\\Controllers\\Plugins\\')) {
                $relative = substr($class, strlen('App\\Http\\Controllers\\Plugins\\'));
                $parts = explode('\\', $relative);
                $pluginNamespace = array_shift($parts);
                $pluginDir = \Illuminate\Support\Str::kebab($pluginNamespace);
                $path = resource_path('plugins/' . $pluginDir . '/Controllers/' . implode('/', $parts) . '.php');

                if (file_exists($path)) {
                    require_once $path;
                }
            }

            if (\Illuminate\Support\Str::startsWith($class, 'App\\Models\\Plugins\\')) {
                $relative = substr($class, strlen('App\\Models\\Plugins\\'));
                $parts = explode('\\', $relative);
                $pluginNamespace = array_shift($parts);
                $pluginDir = \Illuminate\Support\Str::kebab($pluginNamespace);
                $path = resource_path('plugins/' . $pluginDir . '/Models/' . implode('/', $parts) . '.php');
                if (file_exists($path)) {
                    require_once $path;
                }
            }
        });

        $this->configure();
        $this->registerServices();
        $this->registerFunctions();

        if (config('modules.public_path')) {
            $this->app->usePublicPath(base_path() . '/' . config('modules.public_path'));
        }
    }

    protected function cmsHandler()
    {


        if (!config('modules.installed') && !$this->app->runningInConsole()) {
            $view = view('cms::backend.pre-install')->render();
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response(minify_all_one_line($view), 503)->header('Content-Type', 'text/html')
            );
        }

        Carbon::setLocale('ID');
        date_default_timezone_set(config('modules.timezone'));
        if (config('log-viewer.route_path')) {
            config(['log-viewer.timezone' => config('modules.timezone')]);
        }
        Config::set('auth.providers.users.model', 'Leazycms\Web\Models\User');

        if (
            !config('modules.multisite_enabled') && DB::connection()->getDatabaseName() &&
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
        $globalConfigFile = resource_path("views/template/modules.blade.php");

        // Di multisite, saat boot(), middleware belum jalan sehingga template() bernilai null.
        // Kita selesaikan pencarian tenant di sini, dan cache ke IdentifyTenant::$currentTenant
        // agar middleware nanti tinggal pakai (menghindari duplikasi cache hit).
        if (empty($templateName) && config('modules.multisite_enabled') && !app()->runningInConsole()) {
            $host = request()->getHost();

            if (\Leazycms\Web\Middleware\IdentifyTenant::$currentTenant === null) {
                $tenantData = \Illuminate\Support\Facades\Cache::rememberForever(
                    "tenant:$host",
                    function () use ($host) {
                        $t = \Leazycms\Web\Models\Tenant::whereDomain($host)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                        if ($t) {
                            return $t->getRawOriginal();
                        }

                        // Fallback custom domain plugin
                        if (class_exists(\Leazycms\Web\Models\Option::class)) {
                            $option = \Leazycms\Web\Models\Option::withoutGlobalScope('tenant')->where('value', $host)->where('name', 'like', '%-domain')->whereNotNull('tenant_id')->first();
                            if ($option) {
                                $t = \Leazycms\Web\Models\Tenant::where('id', $option->tenant_id)->whereIn('status', ['active', 'suspended', 'maintenance'])->first();
                                if ($t) {
                                    $data = $t->getRawOriginal();
                                    $data['is_plugin_custom_domain'] = true;
                                    return $data;
                                }
                            }
                        }
                        return null;
                    }
                );

                if ($tenantData) {
                    if (isset($tenantData['modules']) && is_array($tenantData['modules'])) {
                        $tenantData['modules'] = json_encode($tenantData['modules']);
                    }
                    $tenant = new \Leazycms\Web\Models\Tenant();
                    $tenant->setRawAttributes($tenantData, true);
                    $tenant->exists = true;
                    \Leazycms\Web\Middleware\IdentifyTenant::$currentTenant = $tenant;
                }
            }

            $currentTenant = \Leazycms\Web\Middleware\IdentifyTenant::$currentTenant;

            if ($currentTenant) {
                $options = \Illuminate\Support\Facades\Cache::rememberForever("tenant:{$currentTenant->domain}:options", function () use ($currentTenant) {
                    return \Leazycms\Web\Models\Option::where('tenant_id', $currentTenant->id)->pluck('value', 'name')->toArray();
                });

                // Bind options ke container agar middleware IdentifyTenant 
                // tidak perlu me-resolve ulang dan hit Cache yang kedua kalinya.
                app()->instance('tenant.options', $options);

                $templateName = $options['template'] ?? null;
            }
        }

        if (empty($templateName)) {
            $templateName = 'default';
        }

        $templateConfigFile = resource_path("views/template/{$templateName}/modules.blade.php");
        if (config('modules.multisite_enabled') && file_exists($globalConfigFile)) {
            try {
                ob_start();
                @include $globalConfigFile;
                ob_end_clean();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Global template config gagal diload: " . $e->getMessage());
            }
        }

        // Muat modules.blade.php spesifik untuk template aktif (override / config template)
        if (file_exists($templateConfigFile)) {
            try {

                ob_start();
                @include $templateConfigFile;
                ob_end_clean();
            } catch (\Throwable $e) {

                \Illuminate\Support\Facades\Log::warning("Template config gagal diload: " . $e->getMessage());
            }
        }

        // Auto-load plugin routes and views
        $disabledPlugins = get_disabled_plugins();

        $pluginsPaths = [
            resource_path('plugins')
        ];

        foreach ($pluginsPaths as $pluginsPath) {
            if (file_exists($pluginsPath)) {
                $pluginDirs = array_map('basename', \Illuminate\Support\Facades\File::directories($pluginsPath));
                foreach ($pluginDirs as $pluginName) {
                    if (!in_array($pluginName, $disabledPlugins)) {
                        // Register Views
                        $viewPath = $pluginsPath . '/' . $pluginName . '/views';
                        if (is_dir($viewPath)) {
                            $this->loadViewsFrom($viewPath, $pluginName);
                        }

                        $pluginRouteFile = $pluginsPath . '/' . $pluginName . '/routes/web.php';
                        if (file_exists($pluginRouteFile)) {
                            try {
                                ob_start();
                                @include $pluginRouteFile;
                                ob_end_clean();
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning("Plugin route config gagal diload [{$pluginName}]: " . $e->getMessage());
                            }
                        }
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
        $existingTables = Schema::getTableListing();
        $requiredTables = ['users', 'files', 'posts', 'categories', 'visitors', 'comments', 'tags', 'roles', 'logs', 'options'];
        return count(array_intersect($requiredTables, $existingTables)) === count($requiredTables);
    }


}
