<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    */

    'service_accounts' => [
        '###WRITE_YOUR_OWN_NAME_HERE###' => [
            'type' => "###REPLACE_WITH_YOUR_TYPE###",
            'project_id' => "###REPLACE_WITH_YOUR_PROJECT_ID###",
            'private_key_id' => "###REPLACE_WITH_YOUR_PRIVATE_KEY_ID###",
            'private_key' => "###REPLACE_WITH_YOUR_PRIVATE_KEY###",
            'client_email' => "###REPLACE_WITH_YOUR_CLIENT_EMAIL###",
            'client_id' => "###REPLACE_WITH_YOUR_CLIENT_ID###",
            'auth_uri' => "https://accounts.google.com/o/oauth2/auth",
            'token_uri' => "https://oauth2.googleapis.com/token",
            'auth_provider_x509_cert_url' => "https://www.googleapis.com/oauth2/v1/certs",
            'client_x509_cert_url' => "###REPLACE_WITH_YOUR_CLIENT_X509_CERT_URL###",
            'universe_domain' => "googleapis.com"
        ],
    ]
];
