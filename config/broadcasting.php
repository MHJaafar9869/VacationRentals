<?php

return [
    'default' => env('BROADCAST_DRIVER', 'pusher'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', 'cb8c1a50035260a0a962'),
            'secret' => env('PUSHER_APP_SECRET', '03c058b89951f246edd8'),
            'app_id' => env('PUSHER_APP_ID', '1880519'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'eu'),
                'useTLS' => true,
            ],
        ],
    ],
];
