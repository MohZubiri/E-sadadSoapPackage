<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Arr;
use MohZubiri\ESadad\ESadad;

// Import Laravel helper functions
use function app;
use function config;
use function route;
use function base_path;
use function config_path;
use function resource_path;
use function public_path;

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return app()->basePath('config' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * @return string
     */
    function resource_path(string $path = ''): string
    {
        return app()->basePath('resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return app()->basePath('public' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @template T
     * @param  string|class-string<T>|null  $abstract
     * @param  array  $parameters
     * @return T|mixed|ApplicationContract
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('esadad')) {
    /**
     * Get the e-SADAD service instance.
     *
     * @return ESadad
     */
    function esadad()
    {
        return app(ESadad::class);
    }
}

/**
 * Add type hints for facades
 * 
 * These class aliases help the IDE understand Laravel's facades
 */
if (! interface_exists('Route')) {
    class_alias('Illuminate\Support\Facades\Route', 'Route');
}

if (! interface_exists('Log')) {
    class_alias('Illuminate\Support\Facades\Log', 'Log');
}

if (! interface_exists('Config')) {
    class_alias('Illuminate\Support\Facades\Config', 'Config');
}

if (! interface_exists('Date')) {
    class_alias('Illuminate\Support\Facades\Date', 'Date');
}

if (! interface_exists('App')) {
    class_alias('Illuminate\Support\Facades\App', 'App');
}

if (! function_exists('esadad_route')) {
    /**
     * Generate a URL to a named e-SADAD route.
     *
     * @param  string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function esadad_route($name, $parameters = [], $absolute = true)
    {
        return route('esadad.' . ltrim($name, '.'), $parameters, $absolute);
    }
}

if (! function_exists('esadad_config')) {
    /**
     * Get / set the specified e-SADAD configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function esadad_config($key = null, $default = null)
    {
        if (is_null($key)) {
            return config('esadad');
        }

        if (is_array($key)) {
            return config()->set('esadad', array_merge(
                config('esadad', []), $key
            ));
        }

        return config('esadad.'.$key, $default);
    }
}
