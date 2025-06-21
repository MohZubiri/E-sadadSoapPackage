<?php

namespace MohZubiri\ESadad\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class EsadadConnectionService extends BaseService
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
     * Send request to e-SADAD provider.
     *
     * @param  string  $url
     * @param  string  $xml
     * @param  array  $fields
     * @return array
     * @throws \Exception
     */
    public function getDataFromProvider(string $url, string $xml, array $fields = []): array
    {
        Log::debug('Sending request to e-SADAD', ['url' => $url, 'xml' => $xml]);

        try {
            $headers = [
                "Content-Type: text/xml; charset=utf-8",
                "Content-Length: " . strlen($xml),
                "SOAPAction: \"mercOnlineAuthentication\""
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception("cURL error: " . $error);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception("HTTP request failed with status code: " . $httpCode);
            }

            $response = html_entity_decode($response);
            $data = $this->parseSoapXmlResponse($response, $fields);

            if (!$this->verifySignature($data)) {
                throw new Exception("Invalid signature in response");
            }

            return $data;
        } catch (Exception $e) {
            Log::error('e-SADAD request failed', [
                'error' => $e->getMessage(),
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Parse SOAP XML response.
     *
     * @param  string  $xmlString
     * @param  array  $fields
     * @return array
     */
    public function parseSoapXmlResponse(string $xmlString, array $fields = []): array
    {
        $replacingArray = [
            'xmlns:typ="http://service/MERC_ONLINE_AUTHENTICATION.wsdl/types/"',
            'xmlns:typ="http://service/MERC_ONLINE_PAYMENT_REQUEST.wsdl/types/"'
        ];

        $cleaned = html_entity_decode($xmlString);
        $result = [];

        // Extract paramOut block
        $paramOutXml = $this->extractBetween($cleaned, '<typ:paramOut', '</typ:paramOut>');

        if ($paramOutXml) {
            $result['paramOut'] = trim(str_replace(
                $replacingArray,
                '',
                $paramOutXml
            ));

            // Extract fields from inside paramOut
            foreach ($fields as $field) {
                $startTag = "<typ:{$field}>";
                $endTag = "</typ:{$field}>";
                $result[$field] = $this->extractBetween($paramOutXml, $startTag, $endTag);
            }
        }

        // Extract signature (outside paramOut)
        $signature = $this->extractBetween($cleaned, '<typ:signature', '</typ:signature>');
        if ($signature) {
            $result['signature'] = trim(str_replace(
                $replacingArray,
                '',
                $signature
            ));
        }

        return $result;
    }

    /**
     * Extract content between two strings.
     *
     * @param  string  $text
     * @param  string  $start
     * @param  string  $end
     * @return string|null
     */
    protected function extractBetween(string $text, string $start, string $end): ?string
    {
        $pattern = '/' . preg_quote($start, '/') . '(.*?)' . preg_quote($end, '/') . '/s';
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Verify the signature of the response.
     *
     * @param  array  $data
     * @return bool
     */
    protected function verifySignature(array $data): bool
    {
        if (!isset($data['signature']) || !isset($data['paramOut'])) {
            return false;
        }

        try {
            return (bool) $this->signatureService->verifySignature(
                $data['signature'],
                $data['paramOut']
            );
        } catch (Exception $e) {
            Log::error('Signature verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
