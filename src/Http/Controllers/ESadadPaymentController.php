<?php

namespace YourVendor\ESadad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YourVendor\ESadad\Services\EsadadConnectionService;
use YourVendor\ESadad\Services\EsadadPreperingService;

class ESadadPaymentController extends Controller
{
    /**
     * The connection service instance.
     *
     * @var \YourVendor\ESadad\Services\EsadadConnectionService
     */
    protected $connectionService;

    /**
     * The preparing service instance.
     *
     * @var \YourVendor\ESadad\Services\EsadadPreperingService
     */
    protected $preparingService;

    /**
     * The merchant credentials.
     *
     * @var array
     */
    protected $merchantCredentials;

    /**
     * Create a new controller instance.
     *
     * @param  \YourVendor\ESadad\Services\EsadadConnectionService  $connectionService
     * @param  \YourVendor\ESadad\Services\EsadadPreperingService  $preparingService
     * @return void
     */
    public function __construct(
        EsadadConnectionService $connectionService,
        EsadadPreperingService $preparingService
    ) {
        $this->connectionService = $connectionService;
        $this->preparingService = $preparingService;
        $this->merchantCredentials = [
            'code' => config('esadad.merchant_code'),
            'password' => config('esadad.merchant_password'),
        ];
    }

    /**
     * Show the payment form.
     *
     * @return \Illuminate\View\View
     */
    public function showPaymentForm()
    {
        return view('esadad::payments.form');
    }

    /**
     * Process the payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|string',
            'customer_password' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'invoice_id' => 'required|string|unique:esadad_transactions,invoice_id',
        ]);

        try {
            // Step 1: Authenticate with e-SADAD
            $authResult = $this->authenticate();
            
            if (!isset($authResult['tokenKey']) || empty($authResult['tokenKey'])) {
                return back()->with('error', 'Authentication with e-SADAD failed. Please try again.');
            }

            $tokenKey = $authResult['tokenKey'];
            
            // Step 2: Initiate payment (send OTP to customer)
            $initiationResult = $this->initiatePayment(
                $tokenKey,
                $validated['customer_password'],
                $validated['customer_id']
            );

            if (!isset($initiationResult['errorCode']) || $initiationResult['errorCode'] !== '000') {
                $errorMessage = $initiationResult['errorDescription'] ?? 'Failed to initiate payment. Please try again.';
                return back()->with('error', $errorMessage);
            }

            // Store transaction data in session for the next step
            session([
                'esadad_payment' => [
                    'token_key' => $tokenKey,
                    'customer_id' => $validated['customer_id'],
                    'amount' => $validated['amount'],
                    'invoice_id' => $validated['invoice_id'],
                ]
            ]);

            return redirect()->route('esadad.otp.form')
                ->with('success', 'An OTP has been sent to your registered mobile number.');

        } catch (\Exception $e) {
            \Log::error('e-SADAD payment processing error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing your request. Please try again.');
        }
    }

    /**
     * Show the OTP verification form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showOtpForm()
    {
        if (!session('esadad_payment')) {
            return redirect()->route('esadad.form')
                ->with('error', 'Your payment session has expired. Please start over.');
        }

        return view('esadad::payments.otp');
    }

    /**
     * Verify the OTP and complete the payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|string|digits:6',
        ]);

        $paymentData = session('esadad_payment');
        if (!$paymentData) {
            return redirect()->route('esadad.form')
                ->with('error', 'Your payment session has expired. Please start over.');
        }

        try {
            // Step 3: Submit payment with OTP
            $paymentResult = $this->processPaymentWithOtp(
                $paymentData['token_key'],
                $validated['otp'],
                $paymentData['customer_id'],
                $paymentData['amount'],
                $paymentData['invoice_id']
            );

            if (!isset($paymentResult['errorCode']) || $paymentResult['errorCode'] !== '000') {
                $errorMessage = $paymentResult['errorDescription'] ?? 'Payment failed. Please try again.';
                return back()->with('error', $errorMessage);
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

            // Clear session data
            session()->forget('esadad_payment');

            // Log the transaction
            $this->logTransaction($paymentData, $paymentResult, $confirmResult);

            // Redirect to success page
            return redirect()->route('esadad.success')
                ->with('success', 'Payment processed successfully!');

        } catch (\Exception $e) {
            \Log::error('e-SADAD OTP verification error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while verifying your OTP. Please try again.');
        }
    }

    /**
     * Show the payment success page.
     *
     * @return \Illuminate\View\View
     */
    public function paymentSuccess()
    {
        if (!session('success')) {
            return redirect()->route('esadad.form');
        }

        return view('esadad::payments.success');
    }

    /**
     * Show the payment failure page.
     *
     * @return \Illuminate\View\View
     */
    public function paymentFailure()
    {
        return view('esadad::payments.failure');
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

        $response = $this->connectionService->getDataFromProvider(
            config('esadad.wsdl_url.AUTHENTICATION'),
            $xml,
            ['tokenKey', 'expiryDate']
        );

        return $response;
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
            config('esadad.wsdl_url.PAYMENT_INITIATION'),
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
     * @param  float  $amount
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
            config('esadad.wsdl_url.PAYMENT_REQUEST'),
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
     * @param  float  $amount
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
            config('esadad.wsdl_url.PAYMENT_CONFIRM'),
            $xml,
            ['errorCode', 'errorDescription']
        );
    }

    /**
     * Log the transaction to the database.
     *
     * @param  array  $paymentData
     * @param  array  $paymentResult
     * @param  array  $confirmResult
     * @return void
     */
    protected function logTransaction(array $paymentData, array $paymentResult, array $confirmResult): void
    {
        try {
            \DB::table('esadad_transactions')->insert([
                'invoice_id' => $paymentData['invoice_id'],
                'customer_id' => $paymentData['customer_id'],
                'amount' => $paymentData['amount'],
                'currency' => config('esadad.currency_code', '886'),
                'bank_trx_id' => $paymentResult['bankTrxId'] ?? null,
                'sep_trx_id' => $paymentResult['sepTrxId'] ?? null,
                'status' => isset($confirmResult['errorCode']) && $confirmResult['errorCode'] === '000' ? 'completed' : 'failed',
                'response_data' => json_encode($paymentResult + $confirmResult),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log e-SADAD transaction: ' . $e->getMessage());
        }
    }
}
