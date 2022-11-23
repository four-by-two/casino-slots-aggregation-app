<?php
use Illuminate\Http\Request;

/** @var \Laravel\Lumen\Routing\Router $router */
$router->get('g', [
    'as' => 'casino.games.entry', 'uses' => '\App\Http\Controllers\Casinodog\Game\SessionsHandler@entrySession'
]);

// Available when env is set to local is enabled to test method functions fast. {$function_name} should be the name of function in TestingController

$router->post('testing/{function_name}', [
        'as' => 'casino.testingcontroller', 'uses' => '\App\Http\Controllers\Casinodog\TestingController@handle'
]);
$router->get('testing/{function_name}', [
    'as' => 'casino.testingcontroller', 'uses' => '\App\Http\Controllers\Casinodog\TestingController@handle'
]);
// Callback Debug Player Balance
$router->get('api/debug/callback', [
    'as' => 'casino.debug.callback', 'uses' => '\App\Http\Controllers\Casinodog\DebugCallbackController@handle'
]);

// Datalogger Control
$router->group(['prefix' => 'api/control/datalogger'], function () use ($router) {
    $router->get('list', [
            'as' => 'casino.datalogger.list', 'uses' => '\App\Http\Controllers\Casinodog\API\Datalogger\ListDatalogger@handle'
    ]);
});

// Parent Session Control
$router->group(['prefix' => 'api/control/parentsession'], function () use ($router) {
    $router->post('create', [
        'as' => 'casino.parentsession.create', 'uses' => '\App\Http\Controllers\Casinodog\API\ParentSessions\CreateParentSession@handle'
    ]);
    $router->get('get', [
        'as' => 'casino.parentsession.get.id', 'uses' => '\App\Http\Controllers\Casinodog\API\ParentSessions\GetParentSessionById@handle'
    ]);
    $router->get('list', [
            'as' => 'casino.parentsession.list', 'uses' => '\App\Http\Controllers\Casinodog\API\ParentSessions\ListParentSession@handle'
    ]);
});

// Gameslist Control
$router->group(['prefix' => 'api/control/gameslist'], function () use ($router) {
    $router->post('create', [
            'as' => 'casino.gameslist.create', 'uses' => '\App\Http\Controllers\Casinodog\API\ParentSessions\CreateParentSession@handle'
    ]);
    $router->put('scaffold', [
            'as' => 'casino.gameslist.scaffold', 'uses' => '\App\Http\Controllers\Casinodog\API\Gameslist\ScaffoldGameslist@handle'
    ]);
    $router->delete('truncate', [
            'as' => 'casino.gameslist.truncate', 'uses' => '\App\Http\Controllers\Casinodog\API\Gameslist\TruncateGameslist@handle'
    ]);
    $router->get('get', [
            'as' => 'casino.gameslist.get.id', 'uses' => '\App\Http\Controllers\Casinodog\API\Gameslist\GetGameslist@handle'
    ]);
    $router->get('list', [
            'as' => 'casino.gameslist.list', 'uses' => '\App\Http\Controllers\Casinodog\API\Gameslist\ListGameslist@handle'
    ]);
    $router->post('update', [
            'as' => 'casino.gameslist.update', 'uses' => '\App\Http\Controllers\Casinodog\API\Gameslist\UpdateGameGameslist@handle'
    ]);

});


// API games routes
$router->group(['prefix' => 'api/games'], function () use ($router) {
    $router->post('register_ws', ['as' => 'casino.websocket.register', 'uses' => '\App\Http\Controllers\Casinodog\s@register']);
    // Catch all game API routes and send to the right place
    $router->get('{provider}/{internal_token}/{slug}/{action:.*}', function ($provider, $internal_token, $slug, $action, Request $request) use ($router) {
        return gameclass($provider)->game_event($request);
    });
    // Catch all game API routes and send to the right place
    $router->get('{provider}/{internal_token}/{action:.*}', function ($provider, $internal_token, $action, Request $request) use ($router) {
        return gameclass($provider)->game_event($request);
    });
    // Catch all game API routes and send to the right place
    $router->post('{provider}/{internal_token}/{slug}/{action:.*}', function ($provider, $internal_token, $slug, $action, Request $request) use ($router) {
        return gameclass($provider)->game_event($request);
    });
    // Catch all game API routes and send to the right place
    $router->post('{provider}/{internal_token}/{action:.*}', function ($provider, $internal_token, $action, Request $request) use ($router) {
        return gameclass($provider)->game_event($request);
    });
});