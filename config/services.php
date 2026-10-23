<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'gocab' => [
        'base_url' => env('GOCAB_BASE_URL'),
        'api_key' => env('GOCAB_API_KEY'),
    ],
    'geoapify' => [
        'api_key' => env('GEOAPIFY_API_KEY', '4c6cd85076094e929039d46d2ceb5949'),
        'base_url' => env('GEOAPIFY_BASE_URL', 'https://api.geoapify.com/v1/geocode/search'),
        'timeout' => (int) env('GEOAPIFY_TIMEOUT', 5),
    ],

];
