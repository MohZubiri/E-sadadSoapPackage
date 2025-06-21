<?php

declare(strict_types=1);

namespace MohZubiri\ESadad\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use MohZubiri\ESadad\Commands\InstallCommand;
use MohZubiri\ESadad\ESadad;
use MohZubiri\ESadad\Http\Controllers\ESadadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Router;
use MohZubiri\ESadad\Services\EsadadConnectionService;
use MohZubiri\ESadad\Services\EsadadPreperingService;
use MohZubiri\ESadad\Services\EsadadSignatureService;

class ESadadServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * The path to the package's base directory.
     *
     * @var string
     */
    protected $packagePath;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->packagePath = dirname(__DIR__, 2);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return ['esadad', ESadad::class];
    }

    /**
     * Get the package base directory.
     *
     * @param  string  $path
     * @return string
     */
    protected function getPackagePath(string $path = ''): string
    {
        return dirname(__DIR__, 2).($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/esadad.php', 'esadad'
        );

        $this->app->singleton('esadad', function (Container $app): ESadad {
            /** @var array{merchant_id: string, terminal_id: string, encryption_key: string, callback_url: string, sandbox: bool} $config */
            $config = $app->make('config')->get('esadad', [
                'merchant_id' => '',
                'terminal_id' => '',
                'encryption_key' => '',
                'callback_url' => '',
                'sandbox' => true,
            ]);

            return new ESadad(
                $config['merchant_id'],
                $config['terminal_id'],
                $config['encryption_key'],
                $config['callback_url'],
                $config['sandbox']
            );
        });

        $this->app->alias('esadad', ESadad::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the package's migrations
        $this->loadMigrationsFrom($this->packagePath . '/database/migrations');

        // Register the package's views
        $this->loadViewsFrom($this->packagePath . '/resources/views', 'esadad');

        // Register the package's routes
        $this->registerRoutes();

        // Publish configuration file
        $this->publishes([
            $this->packagePath . '/config/esadad.php' => config_path('esadad.php'),
        ], 'esadad-config');

        // Publish views
        $this->publishes([
            $this->packagePath . '/resources/views' => resource_path('views/vendor/esadad'),
        ], 'esadad-views');

        // Publish assets
        $this->publishes([
            $this->packagePath . '/public' => public_path('vendor/esadad'),
        ], 'esadad-assets');

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $routeConfig = [
            'prefix' => config('esadad.route.prefix', 'esadad'),
            'namespace' => 'MohZubiri\\ESadad\\Http\\Controllers',
            'middleware' => array_merge(['web'], config('esadad.route.middleware', [])),
            'as' => 'esadad.',
        ];

        Route::group($routeConfig, function () {
            $routesPath = __DIR__ . '/../routes/web.php';
            if (file_exists($routesPath)) {
                $this->loadRoutesFrom($routesPath);
            } else {
                // Fallback to package path
                $fallbackPath = $this->packagePath . '/src/routes/web.php';
                if (file_exists($fallbackPath)) {
                    $this->loadRoutesFrom($fallbackPath);
                }
            }
        });
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            \MohZubiri\ESadad\Console\Commands\InstallCommand::class,
        ]);
    }
}
