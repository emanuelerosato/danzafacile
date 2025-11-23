<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Messaging (FCM) and Firebase Services
    |
    */

    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS', 'storage/app/firebase/firebase-credentials.json'),
    ],

    'database_url' => env('FIREBASE_DATABASE_URL', 'https://danzafacile-default-rtdb.firebaseio.com'),

    /*
    |--------------------------------------------------------------------------
    | Push Notifications Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        // Number of days before considering a token inactive
        'token_expiry_days' => 30,

        // Maximum number of retries for failed notifications
        'max_retries' => 3,

        // Timeout for notification delivery (seconds)
        'timeout' => 60,
    ],
];
