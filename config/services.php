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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    'qrz' => [
    'username' => env('QRZ_USERNAME'),
    'password' => env('QRZ_PASSWORD'),
    ],
   
    'aprs' => [
    'key' => env('APRS_FI_KEY', ''),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
'telegram' => [
    'bot_token'          => env('TELEGRAM_BOT_TOKEN'),
    'admin_ids'          => explode(',', env('TELEGRAM_ADMIN_IDS', '5257679106')),
    'controller_chat_id' => env('TELEGRAM_CONTROLLER_CHAT_ID'),
    'group_chat_id'      => env('TELEGRAM_GROUP_CHAT_ID'),
],
'raynet' => [
    'portal_url' => env('RAYNET_PORTAL_URL', 'https://raynet-liverpool.net'),
],
'imap' => [
    'host'               => env('MAIL_HOST', 'mail.raynet-liverpool.net'),
    'publicfile_password'=> env('IMAP_PUBLICFILE_PASSWORD'),
    'memberfile_password'=> env('IMAP_MEMBERFILE_PASSWORD'),
],

    'imap' => [
        'host'                  => env('MAIL_HOST', 'mail.raynet-liverpool.net'),
        'publicfile_password'   => env('IMAP_PUBLICFILE_PASSWORD'),
        'memberfile_password'   => env('IMAP_MEMBERFILE_PASSWORD'),
        'committeefile_password'=> env('IMAP_COMMITTEEFILE_PASSWORD'),
        'adminfile_password'    => env('IMAP_ADMINFILE_PASSWORD'),
    ],
];
