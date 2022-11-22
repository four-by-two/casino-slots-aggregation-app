<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Wainwright\CasinoDog\Controllers\Game\SessionsHandler;
use Wainwright\CasinoDog\Controllers\Game\Netent\NetentMain;


Route::domain('{parent_token}'.config('casino-dog.wildcard_session_domain.domain'))->group(function () {
    Route::middleware('api', 'throttle:5000,1')->group(function () {
    Route::get('/g', [SessionsHandler::class, 'entrySession']); // defaulted "entry" session location
    Route::get('/', [SessionsHandler::class, 'entryWildcardDomain']); // "entry" session location for your wildcard session domain setup, make sure to reverse proxy nginx towards /wildcard on your session domain
    });
});
Route::domain(config('casino-dog.hostname'))->group(function () {
    Route::middleware('api', 'throttle:5000,1')->group(function () {
        Route::get('/', [SessionsHandler::class, 'entrySession'])->name('g'); // defaulted "entry" session location
        Route::get('/g', [SessionsHandler::class, 'entrySession'])->name('g'); // defaulted "entry" session location
        Route::get('/redirect_netent', [NetentMain::class, 'redirect_catch_content']); //used to hijack legitimate netent session to use our api 
        Route::get('/prelauncher_netent', [NetentMain::class, 'prelauncher_view']); //used to hijack legitimate netent session to use our api
    });
});

Route::middleware('api', 'throttle:5000,1')->prefix('api/games')->group(function () {
Route::match(['get', 'post', 'head', 'patch', 'put', 'delete'] , '{provider}/{internal_token}/{slug}/{action}', function($provider, $internal_token, $slug, $action, Request $request) {
        $game_controller = config('casino-dog.games.'.$provider.'.controller');
        $game_controller_kernel = new $game_controller;
        return $game_controller_kernel->game_event($request);
    })->where('slug', '([A-Za-z0-9_.\-\/]+)');
});

Route::middleware('api', 'throttle:5000,1')->prefix('gs2c/')->group(function () { //pragmatic play promo game events, can be removed if not used
    Route::match(['get', 'post', 'head', 'patch', 'put', 'delete'] , 'announcements/{action}', function($action, Request $request) {
        $game_controller = config('casino-dog.games.pragmaticplay.controller');
        $game_controller_kernel = new $game_controller;
        return $game_controller_kernel->promo_event($request);
    })->where('slug', '([A-Za-z0-9_.\-\/]+)');

    Route::match(['get', 'post', 'head', 'patch', 'put', 'delete'] , 'promo/{action}', function($action, Request $request) {
        $game_controller = config('casino-dog.games.pragmaticplay.controller');
        $game_controller_kernel = new $game_controller;
        return $game_controller_kernel->promo_event($request);
    })->where('slug', '([A-Za-z0-9_.\-\/]+)');
});

Route::middleware('web', 'throttle:5000,1')->prefix('dynamic_asset/')->group(function ($provider) { 
    Route::match(['get', 'post', 'head', 'patch', 'put', 'delete'] , '{provider}/{asset_name}', function($provider, $asset_name, Request $request) {
        $game_controller = config('casino-dog.games.'.$provider.'.controller');
        $game_controller_kernel = new $game_controller;
        return $game_controller_kernel->dynamic_asset($asset_name, $request);
    });
    Route::match(['get', 'post', 'head', 'patch', 'put', 'delete'] , '{provider}/{asset_name}/{slug}', function($provider, $asset_name, Request $request) {
        $game_controller = config('casino-dog.games.'.$provider.'.controller');
        $game_controller_kernel = new $game_controller;
        return $game_controller_kernel->dynamic_asset($asset_name, $request);
    })->where('slug', '([A-Za-z0-9_.\-\/]+)');
});