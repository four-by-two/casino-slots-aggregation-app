<?php
    return [
    'server_ip' => env('WAINWRIGHT_CASINODOG_SERVER_IP', '127.0.0.1'),
    'data_caching' => env('WAINWRIGHT_CASINODOG_DATA_CACHING', false), //set to true to enable caching of various data such as gamelistings and so on, drastically improving performance
    'securitysalt' => env('WAINWRIGHT_CASINODOG_SECURITY_SALT', env('APP_KEY'), 'AA61BED99602F187DA5D033D74D1A556'), // salt used for general signing of entry sessions and so on
    'domain' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1'),
    'hostname' => env('WAINWRIGHT_CASINODOG_HOSTNAME', '777.dog'),
    'master_ip' => env('WAINWRIGHT_CASINODOG_MASTER_IP', '127.0.0.1'), // this IP should be your personal or whatever your testing on, this IP will surpass the Operator IP check
    'testing' => env('WAINWRIGHT_CASINODOG_TESTINGCONTROLLER', true), //set to false to hard override disable all tests through TestingController. When set to true and APP_DEBUG is set to true in .env, you can make use of TestingController
    'cors_anywhere' => env('WAINWRIGHT_CASINODOG_CORSPROXY', 'https://wainwrighted.herokuapp.com/'), //corsproxy, should end with slash, download cors proxy: https://gitlab.com/casinoman/static-assets/cors-proxy
    'wainwright_proxy' => [
      'get_demolink' => env('WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK', true), // set to 1 if wanting to use proxy through cors_anywhere url on game import jobs
      'get_gamelist' => env('WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST', true), // set to 1 if wanting to use proxy through cors_anywhere url on game import jobs
    ],

    'debug_callback' => [
      'start_balance' => 1000000,
      'callback_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/debug/callback',
      'api_key' => 'debug_key',
      'api_secret' => 'debug_secret',
      'controller' => \App\Http\Controllers\Casinodog\DebugCallbackController::class,
      'active' => 1,
    ],
    'games' => [
      'pragmaticplay' => [
        'name' => 'Pragmatic Play',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/pragmaticplay/',
        'controller' => \App\Http\Controllers\Casinodog\Game\PragmaticPlay\PragmaticPlayMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 1,
        'demolink_retrieval_method' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all routes access
      ],
      'mascot' => [
        'name' => 'Mascot Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/mascot/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Mascot\MascotMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 1,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all routes access
      ],
      'hacksaw' => [
        'name' => 'Hacksaw Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/hacksaw/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Hacksaw\HacksawMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'redirect', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'netent' => [
        'name' => 'NetEnt',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/netent/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Netent\NetentMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'redirect', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'bsg' => [
        'name' => 'Betsoft Games',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/betsoft/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Betsoft\BetsoftMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'betsoft' => [
        'name' => 'Betsoft Games',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/betsoft/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Betsoft\BetsoftMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'stakelogic' => [
        'name' => 'Stakelogic',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/stakelogic/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Stakelogic\StakelogicMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'relax' => [
        'name' => 'Relax Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/relaxgaming/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Relaxgaming\RelaxgamingMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'relaxgaming' => [
        'name' => 'Relax Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/relaxgaming/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Relaxgaming\RelaxgamingMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'habanero' => [
        'name' => 'Habanero',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/habanero/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Habanero\HabaneroMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'playson' => [
        'name' => 'Playson',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/playson/',
        'controller' => \App\Http\Controllers\Casinodog\Game\Playson\PlaysonMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'redtiger' => [
        'name' => 'Redtiger Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL'), 'https://127.0.0.1').'/api/games/redtiger/',
        'controller' => \App\Http\Controllers\Casinodog\Game\RedTiger\RedTigerMain::class,
        'fake_iframe_url' => 0,
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
    ],
];
