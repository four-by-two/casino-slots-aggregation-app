<?php
return [
'default' => env('CACHE_DRIVER', 'file'),
'stores' => [
    'array' => [
        'driver' => 'array',
    ],

    'database' => [
        'driver' => 'database',
        'table'  => env('CACHE_DATABASE_TABLE', 'cache'),
        'connection' => env('CACHE_DATABASE_CONNECTION', null),
    ],

    'file' => [
        'driver' => 'file',
        'path'   => storage_path('framework/cache'),
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
],
'prefix' => env('CACHE_PREFIX', 'cd'),
];
