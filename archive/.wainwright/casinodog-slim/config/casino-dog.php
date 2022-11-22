    <?php
    // config for Wainwright/CasinoDog
    return [

    'server_ip' => env('WAINWRIGHT_CASINODOG_SERVER_IP', '127.0.0.1'),
    'securitysalt' => env('WAINWRIGHT_CASINODOG_SECURITY_SALT', 'AA61BED99602F187DA5D033D74D1A556'), // salt used for general signing of entry sessions and so on
    'domain' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')),
    'hostname' => env('WAINWRIGHT_CASINODOG_HOSTNAME', '777.dog'),
    'master_ip' => env('WAINWRIGHT_CASINODOG_MASTER_IP', '127.0.0.1'), // this IP should be your personal or whatever your testing on, this IP will surpass the Operator IP check
    'testing' => env('WAINWRIGHT_CASINODOG_TESTINGCONTROLLER', true), //set to false to hard override disable all tests through TestingController. When set to true and APP_DEBUG is set to true in .env, you can make use of TestingController
    'cors_anywhere' => env('WAINWRIGHT_CASINODOG_CORSPROXY', 'https://wainwrighted.herokuapp.com/'), //corsproxy, should end with slash, download cors proxy: https://gitlab.com/casinoman/static-assets/cors-proxy
    'install_options' => [
        'installable' => env('WAINWRIGHT_CASINODOG_INSTALLABLE', "0"),
        'install_scaffold_operatorkey' => '0',
        'admin_password' => env('WAINWRIGHT_CASINODOG_ADMIN_PASSWORD', NULL),
    ],
    'wainwright_proxy' => [
      'get_demolink' => env('WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK', true), // set to 1 if wanting to use proxy through cors_anywhere url on game import jobs
      'get_gamelist' => env('WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST', true), // set to 1 if wanting to use proxy through cors_anywhere url on game import jobs
    ],
    'panel_ip_restrict' => env('WAINWRIGHT_CASINODOG_PANEL_IP_RESTRICT', true), //restrict panel access based on ip, you can add allowed ip's in panel_allowed_ips
    'panel_allowed_ips' => explode(',', env('WAINWRIGHT_CASINODOG_PANEL_ALLOWED_IP_LIST', '127.0.0.1')),
    'wildcard_session_domain' => [
      'domain' => env('WAINWRIGHT_CASINODOG_WILDCARD', '.777.dog'), // use .DOMAIN.TLD syntax, for example: .777.dog when generating session will become https://d485649e-b239-4dad-ac2e-8ec5a756b504.777.dog
    ],
    'urlscan_apikey' => '98a289d6-c886-446d-898f-9f99e352b850', // apikey is free for 5K reques ts per day at urlscan.io

    /* Used retrieving and then storing game thumbnails on S3*/
    's3_image_store' => [
      'disk' => 'minio', // this "disk" should be available within config/filesystems.php & should be using the "s3" driver within filesystem
      'image_source_url' => 'https://cdn.softswiss.net/i/s3/', // image url prefix
      'fallback_image_source' => 'https://cdn.softswiss.net/i/s3/', // used when image url not set direct linking
    ],
    'games' => [
      'pragmaticplay' => [
        'name' => 'Pragmatic Play',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/pragmaticplay/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\PragmaticPlay\PragmaticPlayMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 1,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all routes access
      ],
      'mascot' => [
        'name' => 'Mascot Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/mascot/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Mascot\MascotMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 1,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 0, //set to 0 to immediate cease all routes access
      ],
      'platipus' => [
        'name' => 'Platipus',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/platipus/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Platipus\PlatipusMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 1,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'hacksaw' => [
        'name' => 'Hacksaw Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/hacksaw/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Hacksaw\HacksawMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'redirect', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'netent' => [
        'name' => 'NetEnt',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/netent/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Netent\NetentMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 1, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'redirect', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'bsg' => [
        'name' => 'Betsoft Games',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/betsoft/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Betsoft\BetsoftMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'betsoft' => [
        'name' => 'Betsoft Games',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/betsoft/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Betsoft\BetsoftMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      '3oaks' => [
        'name' => '3Oaks',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/3oaks/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Oaks\OaksMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'stakelogic' => [
        'name' => 'Stakelogic',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/stakelogic/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Stakelogic\StakelogicMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'relax' => [
        'name' => 'Relax Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/relaxgaming/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Relaxgaming\RelaxgamingMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'relaxgaming' => [
        'name' => 'Relax Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/relaxgaming/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Relaxgaming\RelaxgamingMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'habanero' => [
        'name' => 'Habanero',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/habanero/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Habanero\HabaneroMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'playson' => [
        'name' => 'Playson',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/playson/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\Playson\PlaysonMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
      'redtiger' => [
        'name' => 'Redtiger Gaming',
        'new_api_endpoint' => env('WAINWRIGHT_CASINODOG_DOMAIN', env('APP_URL')).'/api/games/redtiger/',
        'controller' => \Wainwright\CasinoDog\Controllers\Game\RedTiger\RedTigerMain::class,
        'extra_game_metadata' => 0,
        'fake_iframe_url' => 0,
        'demolink_retrieval_method' => 0, // customize the demo link retrieval used on datacontroller, if set to 1 you will need'demolink_retrieval_method () in your Main class
        'custom_entry_path' => 0,
        'launcher_behaviour' => 'internal_game', // 'internal_game' or 'redirect' - expecting url on 'redirect' on SessionsHandler::requestSession()
        'active' => 1, //set to 0 to immediate cease all access on routes
      ],
    ],


    ];
