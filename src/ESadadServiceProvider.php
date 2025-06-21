<?php

namespace YourVendor\ESadad\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use YourVendor\ESadad\Console\Commands\InstallCommand;
use YourVendor\ESadad\ESadad;
use YourVendor\ESadad\Services\EsadadConnectionService;
use YourVendor\ESadad\Services\EsadadPreperingService;
use YourVendor\ESadad\Services\EsadadSignatureService;

class ESadadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/esadad.php', 'esadad'
        );

        // Register the main ESadad service
        $this->app->singleton('esadad', function ($app) {
            $config = $app['config']->get('esadad', []);
            
            // Create service instances
            $connectionService = new EsadadConnectionService();
            $preparingService = new EsadadPreperingService();
            $signatureService = new EsadadSignatureService(
                $config['key_file_path'] ?? '',
                $config['key_file_password'] ?? '',
                $config['key_file_alias'] ?? '',
                $config['key_Verifier_Alias'] ?? '',
                $config['key_encrypt_Alias'] ?? ''
            );
            
            return new ESadad($connectionService, $preparingService, $signatureService, $config);
        });

        // Register the ESadad facade
        $this->app->alias('esadad', ESadad::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/esadad.php' => config_path('esadad.php'),
        ], 'esadad-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/esadad'),
        ], 'esadad-views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../../resources/assets' => public_path('vendor/esadad'),
        ], 'esadad-assets');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'esadad');

        // Load routes
        $this->loadRoutes();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Load the package routes.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $routeConfig = config('esadad.route', []);
        $middleware = array_merge(['web'], $routeConfig['middleware'] ?? []);
        $prefix = $routeConfig['prefix'] ?? 'esadad';
        
        Route::group([
            'namespace' => 'YourVendor\\ESadad\\Http\\Controllers',
            'prefix' => $prefix,
            'middleware' => $middleware,
            'as' => 'esadad.',
        ], function () {
            require __DIR__.'/../../routes/web.php';
        });
    }
}
