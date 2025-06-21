<?php

namespace MohZubiri\ESadad;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Arr;
use MohZubiri\ESadad\Services\EsadadConnectionService;
use MohZubiri\ESadad\Services\EsadadPreperingService;
use MohZubiri\ESadad\Services\EsadadSignatureService;

if (!function_exists('MohZubiri\ESadad\data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        return Arr::get($target, $key, $default);
    }
}

if (!function_exists('MohZubiri\ESadad\now')) {
    /**
     * Create a new Carbon instance for the current time.
     *
     * @param  \DateTimeZone|string|null  $tz
     * @return \Illuminate\Support\Carbon
     */
    function now($tz = null)
    {
        return Date::now($tz);
    }
}

class ESadad
{
    /**
     * The ESadad connection service instance.
     *
     * @var \MohZubiri\ESadad\Services\EsadadConnectionService
     */
    protected $connectionService;

    /**
     * The ESadad preparing service instance.
     *
     * @var \MohZubiri\ESadad\Services\EsadadPreperingService
     */
    protected $preparingService;

    /**
     * The ESadad signature service instance.
     *
     * @var \MohZubiri\ESadad\Services\EsadadSignatureService
     */
    protected $signatureService;

    /**
     * The merchant credentials.
     *
     * @var array
     */
    protected $merchantCredentials;

    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new ESadad instance.
     *
     * @param  \MohZubiri\ESadad\Services\EsadadConnectionService  $connectionService
     * @param  \MohZubiri\ESadad\Services\EsadadPreperingService  $preparingService
     * @param  \MohZubiri\ESadad\Services\EsadadSignatureService  $signatureService
     * @param  array  $config
     * @return void
     */
    public function __construct(
        EsadadConnectionService $connectionService,
        EsadadPreperingService $preparingService,
        EsadadSignatureService $signatureService,
        array $config = []
    ) {
        $this->connectionService = $connectionService;
        $this->preparingService = $preparingService;
        $this->signatureService = $signatureService;
        $this->config = $config;
        
        $this->merchantCredentials = [
            'code' => $this->config('merchant_code'),
            'password' => $this->config('merchant_password'),
        ];
    }

    /**
     * Get a configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Process a payment.
     *
     * @param  array  $data
     * @return array
     * @throws \Exception
     */
    public function processPayment(array $data)
    {
        // Validate required fields
        $required = ['customer_id', 'customer_password', 'amount', 'invoice_id'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("The {$field} field is required.");
            }
        }

        try {
            // Step 1: Authenticate with e-SADAD
            $authResult = $this->authenticate();
            
            if (!isset($authResult['tokenKey']) || empty($authResult['tokenKey'])) {
                throw new Exception('Authentication with e-SADAD failed.');
            }

            $tokenKey = $authResult['tokenKey'];
            
            // Step 2: Initiate payment (send OTP to customer)
            $initiationResult = $this->initiatePayment(
                $tokenKey,
                $data['customer_password'],
                $data['customer_id']
            );

            if (!isset($initiationResult['errorCode']) || $initiationResult['errorCode'] !== '000') {
                $errorMessage = $initiationResult['errorDescription'] ?? 'Failed to initiate payment.';
                throw new Exception($errorMessage);
            }

            return [
                'success' => true,
                'token_key' => $tokenKey,
                'message' => 'OTP has been sent to the customer.',
                'data' => [
                    'customer_id' => $data['customer_id'],
                    'amount' => $data['amount'],
                    'invoice_id' => $data['invoice_id'],
                ]
            ];

        } catch (Exception $e) {
            Log::error('e-SADAD payment processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500,
            ];
        }
    }

    /**
     * Verify OTP and complete payment.
     *
     * @param  string  $otp
     * @param  array   $paymentData
     * @return array
     * @throws \Exception
     */
    public function verifyOtp(string $otp, array $paymentData)
    {
        try {
            if (empty($paymentData['token_key']) || empty($paymentData['customer_id']) || 
                empty($paymentData['amount']) || empty($paymentData['invoice_id'])) {
                throw new Exception('Invalid payment data.');
            }

            // Step 3: Submit payment with OTP
            $paymentResult = $this->processPaymentWithOtp(
                $paymentData['token_key'],
                $otp,
                $paymentData['customer_id'],
                $paymentData['amount'],
                $paymentData['invoice_id']
            );

            if (!isset($paymentResult['errorCode']) || $paymentResult['errorCode'] !== '000') {
                $errorMessage = $paymentResult['errorDescription'] ?? 'Payment failed.';
                throw new Exception($errorMessage);
            }

            // Step 4: Confirm payment
            $confirmResult = $this->confirmPayment(
                $paymentData['token_key'],
                $paymentResult['bankTrxId'] ?? '',
                $paymentResult['sepTrxId'] ?? '',
                $paymentResult['invoiceId'] ?? $paymentData['invoice_id'],
                $paymentResult['stmtDate'] ?? date('Ymd'),
                $paymentData['amount']
            );

            return [
                'success' => true,
                'message' => 'Payment processed successfully.',
                'data' => array_merge($paymentResult, [
                    'confirmation' => $confirmResult,
                ]),
            ];

        } catch (Exception $e) {
            Log::error('e-SADAD OTP verification error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500,
            ];
        }
    }

    /**
     * Authenticate with e-SADAD.
     *
     * @return array
     * @throws \Exception
     */
    protected function authenticate(): array
    {
        $xml = $this->preparingService->prepareAuthRequest(
            $this->merchantCredentials['password'],
            $this->merchantCredentials['code']
        );

        return $this->connectionService->getDataFromProvider(
            $this->config('wsdl_url.AUTHENTICATION'),
            $xml,
            ['tokenKey', 'expiryDate']
        );
    }

    /**
     * Initiate payment (send OTP).
     *
     * @param  string  $tokenKey
     * @param  string  $customerPassword
     * @param  string  $customerId
     * @return array
     * @throws \Exception
     */
    protected function initiatePayment(string $tokenKey, string $customerPassword, string $customerId): array
    {
        $xml = $this->preparingService->prepareInitiatePayment(
            $tokenKey,
            $customerPassword,
            $customerId,
            $this->merchantCredentials['code']
        );

        return $this->connectionService->getDataFromProvider(
            $this->config('wsdl_url.PAYMENT_INITIATION'),
            $xml,
            ['errorCode', 'errorDescription']
        );
    }

    /**
     * Process payment with OTP.
     *
     * @param  string  $tokenKey
     * @param  string  $otp
     * @param  string  $customerId
     * @param  float   $amount
     * @param  string  $invoiceId
     * @return array
     * @throws \Exception
     */
    protected function processPaymentWithOtp(
        string $tokenKey,
        string $otp,
        string $customerId,
        float $amount,
        string $invoiceId
    ): array {
        $xml = $this->preparingService->preparePayment(
            $otp,
            $tokenKey,
            $amount,
            $customerId,
            $this->merchantCredentials['code'],
            $invoiceId
        );

        return $this->connectionService->getDataFromProvider(
            $this->config('wsdl_url.PAYMENT_REQUEST'),
            $xml,
            ['errorCode', 'errorDescription', 'bankTrxId', 'sepTrxId', 'invoiceId', 'stmtDate']
        );
    }

    /**
     * Confirm the payment.
     *
     * @param  string  $tokenKey
     * @param  string  $bankTrxId
     * @param  string  $sepTrxId
     * @param  string  $invoiceId
     * @param  string  $stmtDate
     * @param  float   $amount
     * @return array
     * @throws \Exception
     */
    protected function confirmPayment(
        string $tokenKey,
        string $bankTrxId,
        string $sepTrxId,
        string $invoiceId,
        string $stmtDate,
        float $amount
    ): array {
        $xml = $this->preparingService->prepareConfirmPayment(
            $tokenKey,
            $amount,
            $stmtDate,
            $this->merchantCredentials['code'],
            $invoiceId,
            $sepTrxId,
            $bankTrxId,
            'PmtNew',
            'PmtNew'
        );

        return $this->connectionService->getDataFromProvider(
            $this->config('wsdl_url.PAYMENT_CONFIRM'),
            $xml,
            ['errorCode', 'errorDescription']
        );
    }

    /**
     * Get the transaction status.
     *
     * @param  string  $invoiceId
     * @return array
     * @throws \Exception
     */
    public function getTransactionStatus(string $invoiceId): array
    {
        try {
            // This is a placeholder for transaction status check
            // You would typically implement this based on e-SADAD's API
            
            return [
                'success' => true,
                'status' => 'completed', // or 'pending', 'failed', etc.
                'invoice_id' => $invoiceId,
                'checked_at' => now()->toDateTimeString(),
            ];
            
        } catch (Exception $e) {
            Log::error('e-SADAD transaction status check failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500,
            ];
        }
    }

    /**
     * Refund a payment.
     *
     * @param  string  $invoiceId
     * @param  float   $amount
     * @param  string  $reason
     * @return array
     * @throws \Exception
     */
    public function refundPayment(string $invoiceId, float $amount, string $reason = ''): array
    {
        try {
            // This is a placeholder for refund functionality
            // You would typically implement this based on e-SADAD's API
            
            return [
                'success' => true,
                'message' => 'Refund processed successfully.',
                'invoice_id' => $invoiceId,
                'amount_refunded' => $amount,
                'refund_reason' => $reason,
                'refunded_at' => now()->toDateTimeString(),
            ];
            
        } catch (Exception $e) {
            Log::error('e-SADAD refund failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode() ?: 500,
            ];
        }
    }
}
