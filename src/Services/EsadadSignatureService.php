<?php

namespace MohZubiri\ESadad\Services;

class EsadadSignatureService extends BaseService
{
    /**
     * Generate a digital signature using a keystore file
     *
     * @param string $dataToSign The data to sign
     * @return string Base64 encoded signature
     * @throws \Exception
     */
    public function generateSignature(string $dataToSign): string 
    {
        $dataToSign = trim($dataToSign);
        
        $keystoreFile = $this->config('key_file_path');
        $keystorePassword = $this->config('key_file_password');
        $alias = $this->config('key_file_alias');
        $javaDir = __DIR__ . '/../../java';

        $command = escapeshellcmd("java -cp $javaDir Signature_Generator \"$keystoreFile\" \"$keystorePassword\" \"$alias\" \"$dataToSign\"");
        $signature = shell_exec($command . " 2>&1");

        if ($signature === null) {
            throw new \Exception("Java execution failed.");
        }

        return trim($signature);
    }

    /**
     * Generate a secondary signature
     * 
     * @param string $dataToSign
     * @return string
     * @throws \Exception
     */
    public function generate2Signature(string $dataToSign): string 
    {
        $dataToSign = trim($dataToSign);
        $keystoreFile = $this->config('key_file_path');
        $keystorePassword = $this->config('key_file_password');
        $alias = $this->config('key_file_alias');
        $javaDir = __DIR__ . '/../../java';
      
        $command = escapeshellcmd("java -cp $javaDir Signature2Generator \"$keystoreFile\" \"$keystorePassword\" \"$alias\" \"$dataToSign\"");
        $signature = shell_exec($command . " 2>&1");

        if ($signature === null) {
            throw new \Exception("Java execution failed.");
        }

        return trim($signature);
    }

    /**
     * Encrypt a message
     * 
     * @param string $message
     * @return string
     * @throws \Exception
     */
    public function encryptionMessage(string $message): string 
    {
        $keystoreFile = $this->config('key_file_path');
        $keystorePassword = $this->config('key_file_password');
        $keyAlias = $this->config('key_encrypt_Alias'); 
        $keyPassword = $this->config('key_file_password');
        $javaDir = __DIR__ . '/../../java';
        $message = (string)trim($message);

        $command = escapeshellcmd("java -cp $javaDir KeyStoreEncryption \"$message\" \"$keystoreFile\" \"$keystorePassword\" \"$keyAlias\" \"$keyPassword\"");
        $signature = shell_exec($command . " 2>&1");

        if ($signature === null) {
            throw new \Exception("Java execution failed.");
        }

        return trim($signature);
    }

    /**
     * Verify a signature
     * 
     * @param string $signature
     * @param string $originalMessage
     * @return string
     * @throws \Exception
     */
    public function verifySignature(string $signature, string $originalMessage): string 
    {
        $keystoreFile = $this->config('key_file_path');
        $keystorePassword = $this->config('key_file_password');
        $keyAlias = $this->config('key_Verifier_Alias');
        $javaDir = __DIR__ . '/../../java';
        
        $command = escapeshellcmd("java -cp $javaDir SignatureVerifier \"$keystoreFile\"  \"$keystorePassword\" \"$keyAlias\" \"$originalMessage\" \"$signature\"");
        $result = shell_exec($command . " 2>&1");

        if ($result === null) {
            throw new \Exception("Java execution failed.");
        }

        return trim($result);
    }
}
