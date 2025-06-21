<?php

namespace MohZubiri\ESadad\Services;

class EsadadPreperingService extends BaseService
{
    /**
     * The signature service instance.
     *
     * @var \MohZubiri\ESadad\Services\EsadadSignatureService
     */
    protected $signatureService;

    /**
     * Create a new service instance.
     *
     * @param  array  $config
     * @param  \MohZubiri\ESadad\Services\EsadadSignatureService  $signatureService
     * @return void
     */
    public function __construct(array $config, EsadadSignatureService $signatureService)
    {
        parent::__construct($config);
        $this->signatureService = $signatureService;
    }

    /**
     * Prepare the payment confirmation XML.
     *
     * @param  string  $tokenKey
     * @param  float  $amount
     * @param  string  $stmtDate
     * @param  string  $merchantCode
     * @param  string  $invoiceId
     * @param  string  $sepTrxId
     * @param  string  $bankTrxId
     * @param  string  $sepOnlineNo
     * @param  string  $pmtStatus
     * @return string
     */
    public function prepareConfirmPayment(
        string $tokenKey,
        float $amount,
        string $stmtDate,
        string $merchantCode,
        string $invoiceId,
        string $sepTrxId,
        string $bankTrxId,
        string $sepOnlineNo,
        string $pmtStatus
    ): string {
        $processDate = date('YmdHis');
        $currency = $this->config('currency_code', '886');

        $data = "<typ:tokenKey>$tokenKey</typ:tokenKey><typ:transRec><typ:trxAmount>$amount</typ:trxAmount><typ:invoiceId>$invoiceId</typ:invoiceId><typ:stmtDate>$stmtDate</typ:stmtDate><typ:sepTrxId>$sepTrxId</typ:sepTrxId><typ:bankTrxId>$bankTrxId</typ:bankTrxId><typ:pmtStatus>$pmtStatus</typ:pmtStatus><typ:processDate>$processDate</typ:processDate><typ:currency>$currency</typ:currency><typ:sepOnlineNo>$sepOnlineNo</typ:sepOnlineNo></typ:transRec><typ:merchantCode>$merchantCode</typ:merchantCode>";

        $signature = $this->signatureService->generateSignature(trim($data));

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:merc="http://service/MERC_ONLINE_PAYMENT_CONFIRM.wsdl" xmlns:typ="http://service/MERC_ONLINE_PAYMENT_CONFIRM.wsdl/types/">
   <soapenv:Header/>
   <soapenv:Body>
      <merc:mercOnlinePaymentConfirm>
         <McSoTransPayConfRequestUser_1>
            <typ:signature>{$signature}</typ:signature>
            <typ:paramIn>
               <typ:tokenKey>{$tokenKey}</typ:tokenKey>
               <typ:transRec>
                  <typ:trxAmount>{$amount}</typ:trxAmount>
                  <typ:invoiceId>{$invoiceId}</typ:invoiceId>
                  <typ:stmtDate>{$stmtDate}</typ:stmtDate>
                  <typ:sepTrxId>{$sepTrxId}</typ:sepTrxId>
                  <typ:bankTrxId>{$bankTrxId}</typ:bankTrxId>
                  <typ:pmtStatus>{$pmtStatus}</typ:pmtStatus>
                  <typ:processDate>{$processDate}</typ:processDate>
                  <typ:currency>{$currency}</typ:currency>
                  <typ:sepOnlineNo>{$sepOnlineNo}</typ:sepOnlineNo>
               </typ:transRec>
               <typ:merchantCode>{$merchantCode}</typ:merchantCode>
            </typ:paramIn>
         </McSoTransPayConfRequestUser_1>
      </merc:mercOnlinePaymentConfirm>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Prepare the payment request XML.
     *
     * @param  string  $otp
     * @param  string  $tokenKey
     * @param  float  $amount
     * @param  string  $customerId
     * @param  string  $merchantCode
     * @param  string  $invoiceId
     * @return string
     */
    public function preparePayment(
        string $otp,
        string $tokenKey,
        float $amount,
        string $customerId,
        string $merchantCode,
        string $invoiceId
    ): string {
        $processDate = date('YmdHis');
        $currency = $this->config('currency_code', '886');
        $encryptedOtp = $this->signatureService->encryptionMessage($otp);
        
        $data = "<typ:tokenKey>$tokenKey</typ:tokenKey><typ:transRec><typ:trxAmount>$amount</typ:trxAmount><typ:invoiceId>$invoiceId</typ:invoiceId><typ:otp>$encryptedOtp</typ:otp><typ:processDate>$processDate</typ:processDate><typ:currency>$currency</typ:currency><typ:sepOnlineNo>$customerId</typ:sepOnlineNo></typ:transRec><typ:merchantCode>$merchantCode</typ:merchantCode>";
        $signature = $this->signatureService->generateSignature(trim($data));

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                  xmlns:merc="http://service/MERC_ONLINE_PAYMENT_REQUEST.wsdl"
                  xmlns:typ="http://service/MERC_ONLINE_PAYMENT_REQUEST.wsdl/types/">
   <soapenv:Header/>
   <soapenv:Body>
      <merc:mercOnlinePaymentRequest>
         <McSoTransPayReqRequestUser_1>
            <typ:signature>{$signature}</typ:signature>
            <typ:paramIn>
               <typ:tokenKey>{$tokenKey}</typ:tokenKey>
               <typ:transRec>
                  <typ:trxAmount>{$amount}</typ:trxAmount>
                  <typ:invoiceId>{$invoiceId}</typ:invoiceId>
                  <typ:otp>{$encryptedOtp}</typ:otp>
                  <typ:processDate>{$processDate}</typ:processDate>
                  <typ:currency>{$currency}</typ:currency>
                  <typ:sepOnlineNo>{$customerId}</typ:sepOnlineNo>
               </typ:transRec>
               <typ:merchantCode>{$merchantCode}</typ:merchantCode>
            </typ:paramIn>
         </McSoTransPayReqRequestUser_1>
      </merc:mercOnlinePaymentRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Prepare the authentication request XML.
     *
     * @param  string  $password
     * @param  string  $merchantCode
     * @return string
     */
    public function prepareAuthRequest(string $password, string $merchantCode): string
    {
        $data = '<typ:password>' . $password . '</typ:password><typ:merchantCode>' . $merchantCode . '</typ:merchantCode>';
        $signature = $this->signatureService->generateSignature($data);

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:merc="http://service/MERC_ONLINE_AUTHENTICATION.wsdl" xmlns:typ="http://service/MERC_ONLINE_AUTHENTICATION.wsdl/types/">
    <soapenv:Header/>
    <soapenv:Body>
        <merc:mercOnlineAuthentication>
            <McSoCustAuthRequestUser_1>
                <typ:signature>$signature</typ:signature>
                <typ:paramIn>
                    <typ:password>$password</typ:password>
                    <typ:merchantCode>$merchantCode</typ:merchantCode>
                </typ:paramIn>
            </McSoCustAuthRequestUser_1>
        </merc:mercOnlineAuthentication>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * Prepare the payment initiation XML.
     *
     * @param  string  $tokenKey
     * @param  string  $customerPassword
     * @param  string  $customerId
     * @param  string  $merchantCode
     * @return string
     */
    public function prepareInitiatePayment(
        string $tokenKey,
        string $customerPassword,
        string $customerId,
        string $merchantCode
    ): string {
        $encryptedPassword = $this->signatureService->encryptionMessage($customerPassword);
        $data = '<typ:tokenKey>' . $tokenKey . '</typ:tokenKey><typ:transRec><typ:password>' . $encryptedPassword . '</typ:password><typ:sepOnlineNo>' . $customerId . '</typ:sepOnlineNo></typ:transRec><typ:merchantCode>' . $merchantCode . '</typ:merchantCode>';
        $signature = $this->signatureService->generateSignature(trim($data));

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:merc="http://service/MERC_ONLINE_PAYMENT_INITIATION.wsdl"
        xmlns:typ="http://service/MERC_ONLINE_PAYMENT_INITIATION.wsdl/types/">
<soapenv:Header/>
<soapenv:Body>
<merc:mercOnlinePaymentInitiation>
<McSoTransPayInitRequestUser_1>
    <typ:signature>$signature</typ:signature>
    <typ:paramIn>
        <typ:tokenKey>{$tokenKey}</typ:tokenKey>
        <typ:transRec>
            <typ:password>{$encryptedPassword}</typ:password>
            <typ:sepOnlineNo>{$customerId}</typ:sepOnlineNo>
        </typ:transRec>
        <typ:merchantCode>{$merchantCode}</typ:merchantCode>
    </typ:paramIn>
</McSoTransPayInitRequestUser_1>
</merc:mercOnlinePaymentInitiation>
</soapenv:Body>
</soapenv:Envelope>
XML;
    }
}
