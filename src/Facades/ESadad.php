<?php

namespace MohZubiri\ESadad\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MohZubiri\ESadad\ESadad processPayment(array $data)
 * @method static \MohZubiri\ESadad\ESadad verifyOtp(string $otp)
 *
 * @see \MohZubiri\ESadad\ESadad
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
