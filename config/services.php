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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'fingerspot' => [
        'endpoint' => env('FINGERSPOT_ENDPOINT'),
        'endpoint_delete_user' => env('FINGERSPOT_ENDPOINT_DELETE_USER'),
        'token'    => env('FINGERSPOT_TOKEN'),
        'cloud_id' => env('FINGERSPOT_CLOUD_ID'),
        'tz'       => env('FINGERSPOT_TZ', 'Asia/Jakarta'),
    ],

    'group_api' => [
        'base_url'  => env('GROUP_API_BASE_URL', 'http://127.0.0.1:3001'),
        'api_key'   => env('GROUP_API_KEY'),
        'group'     => env('GROUP_DEFAULT_GROUP', 'PENGURUS KELOMPOK'),
        'personal_path' => env('WA_PERSONAL_PATH','/send-personal'),
    ],

    'wa' => [
        'base_url'  => env('WA_BASE_URL'),
        'api_key'   => env('WA_API_KEY'),
        'personal_path' => env('WA_PERSONAL_PATH','/send-personal'),
    ],

];
