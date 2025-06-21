<?php

namespace YourVendor\ESadad\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \YourVendor\ESadad\ESadad processPayment(array $data)
 * @method static \YourVendor\ESadad\ESadad verifyOtp(string $otp)
 *
 * @see \YourVendor\ESadad\ESadad
 */
class ESadad extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'esadad';
    }
}
