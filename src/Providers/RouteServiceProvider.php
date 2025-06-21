<?php

declare(strict_types=1);

namespace YourVendor\ESadad\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Container\BindingResolutionException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the package's base directory.
     *
     * @var string
     */
    protected string $packagePath;

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
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * @var string|null
     */
    protected $namespace = 'YourVendor\\ESadad\\Http\\Controllers';

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
     * Load the given routes file with route group attributes.
     *
     * @param  string  $path
     * @param  array  $attributes
     * @return void
     */
    protected function loadRoutesFrom($path, $attributes = [])
    {
        $router = $this->app->make(Router::class);

        $router->group($attributes, function ($router) use ($path) {
            if (! is_file($path)) {
                return;
            }

            require $path;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerRoutes(): void
    {
        $router = $this->app->make(Router::class);
        
        $router->group([
            'prefix' => Config::get('esadad.route.prefix', 'esadad'),
            'as' => 'esadad.',
            'middleware' => Config::get('esadad.route.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom($this->getPackagePath('routes/web.php'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
