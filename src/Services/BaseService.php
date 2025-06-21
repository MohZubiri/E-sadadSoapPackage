<?php

namespace MohZubiri\ESadad\Services;

abstract class BaseService
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new service instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }
}
