# Laravel e-SADAD Payment Gateway

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mohzubiri/laravel-esadad.svg?style=flat-square)](https://packagist.org/packages/mohzubiri/laravel-esadad)
[![Total Downloads](https://img.shields.io/packagist/dt/mohzubiri/laravel-esadad.svg?style=flat-square)](https://packagist.org/packages/mohzubiri/laravel-esadad)
[![License](https://img.shields.io/packagist/l/mohzubiri/laravel-esadad.svg?style=flat-square)](https://github.com/MohZubiri/E-sadadSoapPackage/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/mohzubiri/laravel-esadad.svg?style=flat-square)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%7C11.x%7C12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)

A Laravel package for integrating with the e-SADAD payment gateway. This package provides a simple and clean API to process payments through the e-SADAD payment system. The package is now more stable and includes several improvements for better integration with Laravel applications, including full support for Laravel 12.

## Features

- Easy integration with Laravel applications
- Support for all e-SADAD payment operations
- Clean and modern UI for payment forms
- Configurable routes and views
- Comprehensive error handling and logging
- Support for multiple currencies
- Secure transaction processing

## Requirements

- PHP 8.1 or higher (PHP 8.3 supported)
- Laravel 10.0 or higher
- Required PHP Extensions:
  - OpenSSL
  - cURL
  - JSON
  - XML
  - SOAP
  - Mbstring
  - Fileinfo
- Java (for signature generation)
- Composer for dependency management

## Installation

### For Laravel Applications

1. Install the package via Composer:

```bash
composer require mohzubiri/laravel-esadad:^1.0.14
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="MohZubiri\\ESadad\\Providers\\ESadadServiceProvider" --tag="esadad-config"
```

3. Publish the views (optional, only if you want to customize them):

```bash
php artisan vendor:publish --provider="MohZubiri\\ESadad\\Providers\\ESadadServiceProvider" --tag="esadad-views"
```

4. Publish the assets (CSS, JS, images):

```bash
php artisan vendor:publish --provider="MohZubiri\\ESadad\\Providers\\ESadadServiceProvider" --tag="esadad-assets"
```

5. Run the migrations:

```bash
php artisan migrate
```

Or use the install command for a guided installation:

```bash
php artisan esadad:install
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mohzubiri@example.com instead of using the issue tracker.

## Credits

- [Mohammed Zubiri](https://github.com/MohZubiri)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

### Manual Installation (For Package Development)

1. Clone the repository:
```bash
git clone https://github.com/MohZubiri/E-sadadSoapPack.git
```

2. Add the repository to your Laravel application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/laravel-esadad"
        }
    ]
}
```

3. Require the package:
```bash
composer require mohzubiri/laravel-esadad:@dev
```

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
ESADAD_MERCHANT_CODE=your_merchant_code
ESADAD_MERCHANT_PASSWORD=your_merchant_password
ESADAD_KEY_PASSWORD=your_keystore_password
ESADAD_KEY_ALIAS=your_key_alias
ESADAD_VERIFIER_ALIAS=your_verifier_alias
ESADAD_ENCRYPT_ALIAS=your_encrypt_alias
ESADAD_CURRENCY_CODE=your_currency_code  # Default: 886 (Yemeni Riyal)
```

### Configuration File

After publishing the configuration file, you can find it at `config/esadad.php`. Here's a quick overview of the available options:

```php
return [
    'key_file_path' => storage_path('app/keys/education.jks'),
    'key_file_password' => env('ESADAD_KEY_PASSWORD', 'password'),
    'key_file_alias' => env('ESADAD_KEY_ALIAS', 'server'),
    'key_Verifier_Alias' => env('ESADAD_VERIFIER_ALIAS', 'server2'),
    'key_encrypt_Alias' => env('ESADAD_ENCRYPT_ALIAS', 'merchant_mr000461'),

    'merchant_code' => env('ESADAD_MERCHANT_CODE', 'MR000461'),
    'merchant_password' => env('ESADAD_MERCHANT_PASSWORD', ''),
    
    'wsdl_url' => [
        'AUTHENTICATION' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_AUTHENTICATION-context-root/MERC_ONLINE_AUTHENTICATIONPort?WSDL',
        'PAYMENT_INITIATION' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_PAYMENT_INITIATION-context-root/MERC_ONLINE_PAYMENT_INITIATIONPort?WSDL',
        'PAYMENT_REQUEST' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_PAYMENT_REQUEST-context-root/MERC_ONLINE_PAYMENT_REQUESTPort?WSDL',
        'PAYMENT_CONFIRM' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_PAYMENT_CONFIRM-context-root/MERC_ONLINE_PAYMENT_CONFIRMPort?WSSL',
    ],
    
    'currency_code' => env('ESADAD_CURRENCY_CODE', '886'), // Yemeni Riyal
    
    'route' => [
        'prefix' => 'esadad',
        'middleware' => ['web'],
    ],
];
```

## Environment Variables

Add the following to your `.env` file:

```env
ESADAD_MERCHANT_CODE=your_merchant_code
ESADAD_MERCHANT_PASSWORD=your_merchant_password
ESADAD_KEY_PASSWORD=your_keystore_password
ESADAD_KEY_ALIAS=your_key_alias
ESADAD_VERIFIER_ALIAS=your_verifier_alias
ESADAD_ENCRYPT_ALIAS=your_encrypt_alias
ESADAD_CURRENCY_CODE=886
```

## Usage

### Basic Usage

1. Add the route to your `routes/web.php`:

```php
Route::esadad();
```

This will register the following routes:

- `GET /esadad/form` - Show payment form
- `POST /esadad/process` - Process payment
- `GET /esadad/otp` - Show OTP form
- `POST /esadad/verify-otp` - Verify OTP
- `GET /esadad/success` - Payment success page
- `GET /esadad/failure` - Payment failure page

### Customizing Routes

You can customize the routes by passing an options array to the `Route::esadad()` method:

```php
Route::esadad([
    'prefix' => 'payments/esadad',
    'middleware' => ['web', 'auth'],
]);
```

### Programmatic Usage

You can also use the package programmatically:

```php
use YourVendor\ESadad\Facades\ESadad;

// Process a payment
$result = ESadad::processPayment([
    'customer_id' => 'CUST123',
    'customer_password' => 'password123',
    'amount' => 100.00,
    'invoice_id' => 'INV-' . time(),
]);

// Verify OTP
$verification = ESadad::verifyOtp('123456');
```

### Events

The package dispatches the following events:

- `YourVendor\ESadad\Events\PaymentProcessed` - When a payment is successfully processed
- `YourVendor\ESadad\Events\PaymentFailed` - When a payment fails
- `YourVendor\ESadad\Events\OtpVerified` - When an OTP is successfully verified
- `YourVendor\ESadad\Events\OtpVerificationFailed` - When OTP verification fails

You can listen for these events in your application:

```php
// In your EventServiceProvider
protected $listen = [
    'YourVendor\\ESadad\\Events\\PaymentProcessed' => [
        'App\\Listeners\\LogSuccessfulPayment',
    ],
];
```

## Security

- All sensitive data is encrypted
- Secure SOAP communication with e-SADAD servers
- CSRF protection on all forms
- Input validation on all requests
- Secure session handling
- Comprehensive error logging

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Your Name](https://github.com/yourusername)
- [All Contributors](../../contributors)

## Support

For support, please email support@example.com or open an issue on our GitHub repository.
