<?php

return [
    /*
    |--------------------------------------------------------------------------
    | e-SADAD Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the e-SADAD Gateway integration.
    |
    */


    'key_file_path' => storage_path('app/keys/education.jks'),
    'key_file_password' => env('ESADAD_KEY_PASSWORD', 'password'),
    'key_file_alias' => env('ESADAD_KEY_ALIAS', 'server'),
    'key_Verifier_Alias' => env('ESADAD_VERIFIER_ALIAS', 'server2'),
    'key_encrypt_Alias' => env('ESADAD_ENCRYPT_ALIAS', 'merchant_mr000461'),

    'merchant_code' => env('ESADAD_MERCHANT_CODE', 'MR000461'),
    'merchant_password' => env('ESADAD_MERCHANT_PASSWORD', 'P@$$word@123'),
    
    'wsdl_url' => [
        'AUTHENTICATION' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_AUTHENTICATION-context-root/MERC_ONLINE_AUTHENTICATIONPort?WSDL',
        'PAYMENT_INITIATION' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_PAYMENT_INITIATION-context-root/MERC_ONLINE_PAYMENT_INITIATIONPort?WSDL',
        'PAYMENT_REQUEST' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_PAYMENT_REQUEST-context-root/MERC_ONLINE_PAYMENT_REQUESTPort?WSDL',
        'PAYMENT_CONFIRM' => 'https://195.94.15.100:8002/EBPP_ONLINE-MERC_ONLINE_PAYMENT_CONFIRM-context-root/MERC_ONLINE_PAYMENT_CONFIRMPort?WSDL'
    ],
    
    // Currency code (ISO standard)
    'currency_code' => env('ESADAD_CURRENCY_CODE', '886'), // Yemeni Riyal
    
    // Route configurations
    'route' => [
        'prefix' => 'esadad',
        'middleware' => ['web'],
    ],
];
