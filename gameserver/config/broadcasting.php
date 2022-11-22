<?php
return [
'default' => env('BROADCAST_DRIVER', 'redis'),
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
    ],
    'log' => [
        'driver' => 'log',
    ],
    'null' => [
        'driver' => 'null',
    ],
],
];

