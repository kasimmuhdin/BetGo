<?php

return [
    'name' => 'BetGo',
    'featured_plan' => 'pro',

    'contact' => [
        'email' => env('BETGO_CONTACT_EMAIL'),
        'phone' => env('BETGO_CONTACT_PHONE'),
        'address' => env('BETGO_CONTACT_ADDRESS', 'Addis Ababa, Ethiopia'),
    ],

    'social' => [
        'facebook' => env('BETGO_FACEBOOK_URL'),
        'instagram' => env('BETGO_INSTAGRAM_URL'),
        'x' => env('BETGO_X_URL'),
        'youtube' => env('BETGO_YOUTUBE_URL'),
        'telegram' => env('BETGO_TELEGRAM_URL'),
    ],
];
