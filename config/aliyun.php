<?php

return [
    'access_key'    => env('ACCESS_KEY_ID'),
    'access_secret' => env('ACCESS_KEY_SECRET'),
    'oss'           => [
        'endpoint'      => env('OSS_ENDPOINT'),
        'bucket'        => env('OSS_BUCKET'),
        'domain'        => env('OSS_DOMAIN', 'http://static.xshiwu.com/'),
        'upload_domain' => env('OSS_UPLOAD_DOMAIN', 'http://static.xshiwu.com/uploads/'),
    ],
    'sms'           => [
        'sign_name'       => env('SMS_SIGN_NAME'),
        'tlp_verify_code' => env('SMS_TPL_VERIFY'),
    ],
];
