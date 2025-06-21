<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use YourVendor\ESadad\Http\Controllers\ESadadController;

// Get route configuration from package config
$routeConfig = config('esadad.route', []);
$prefix = $routeConfig['prefix'] ?? 'esadad';
$middleware = array_merge(['web'], $routeConfig['middleware'] ?? []);

Route::group([
    'prefix' => $prefix,
    'middleware' => $middleware,
    'as' => 'esadad.',
    'namespace' => 'YourVendor\\ESadad\\Http\\Controllers',
], function () {
    /*
|--------------------------------------------------------------------------
| e-SADAD Payment Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the e-SADAD payment gateway.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
        ->name('payment.form');
    
    // Process payment (will redirect to e-SADAD)
    Route::post('process', [ESadadController::class, 'processPayment'])
        ->name('payment.process');
    
    // OTP verification
    Route::get('verify-otp', [ESadadController::class, 'showOtpForm'])
        ->name('otp.form');
        
    Route::post('verify-otp', [ESadadController::class, 'verifyOtp'])
        ->name('otp.verify');
    
    // Payment success/failure pages
    Route::get('success', [ESadadController::class, 'paymentSuccess'])
        ->name('payment.success');
        
    Route::get('failure', [ESadadController::class, 'paymentFailure'])
        ->name('payment.failure');
    
    // Callback from e-SADAD (should be accessible without CSRF protection)
    Route::match(['get', 'post'], 'callback', [ESadadController::class, 'handleCallback'])
        ->withoutMiddleware(['web'])
        ->name('payment.callback');
});
