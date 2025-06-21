<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MohZubiri\ESadad\Http\Controllers\ESadadPaymentController;

// Get route configuration from package config
$routeConfig = config('esadad.route', []);
$prefix = $routeConfig['prefix'] ?? 'esadad';
$middleware = array_merge(['web'], $routeConfig['middleware'] ?? []);

/*
|--------------------------------------------------------------------------
| e-SADAD Payment Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the e-SADAD payment gateway.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
*/

Route::group([
    'prefix' => $prefix,
    'middleware' => $middleware,
    'as' => 'esadad.',
    'namespace' => 'MohZubiri\\ESadad\\Http\\Controllers',
], function () {
    // Payment form
    Route::get('payment', [ESadadPaymentController::class, 'showPaymentForm'])
        ->name('payment.form');
    
    // Process payment (will redirect to e-SADAD)
    Route::post('process', [ESadadPaymentController::class, 'processPayment'])
        ->name('payment.process');
    
    // OTP verification
    Route::get('verify-otp', [ESadadPaymentController::class, 'showOtpForm'])
        ->name('otp.form');
        
    Route::post('verify-otp', [ESadadPaymentController::class, 'verifyOtp'])
        ->name('otp.verify');
    
    // Payment success/failure pages
    Route::get('success', [ESadadPaymentController::class, 'paymentSuccess'])
        ->name('payment.success');
        
    Route::get('failure', [ESadadPaymentController::class, 'paymentFailure'])
        ->name('payment.failure');
    
    // Callback from e-SADAD (should be accessible without CSRF protection)
    Route::match(['get', 'post'], 'callback', [ESadadPaymentController::class, 'handleCallback'])
        ->withoutMiddleware(['web'])
        ->name('payment.callback');
});
