<?php
use Illuminate\Support\Facades\Route;
use Wainwright\CasinoDog\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Wainwright\CasinoDog\Controllers\Testing\TestingController;
use Wainwright\CasinoDog\Controllers\InstallController;
use Illuminate\Http\Response;

Route::middleware('api', 'throttle:400,1')->prefix('api')->group(function () {
    Route::get('/createSession', [APIController::class, 'createSessionEndpoint']);
    Route::get('/createSessionAndRedirect', [APIController::class, 'createSessionAndRedirectEndpoint']);
    Route::get('/createSessionIframed', [APIController::class, 'createSessionIframed']);
    Route::get('/control/toggle_respin', [APIController::class, 'meepEndpoint']);
    Route::get('/control/add_freespins', [APIController::class, 'promotionsEndpoint']);
    Route::get('/accessPing', [APIController::class, 'accessPingEndpoint']);
    Route::get('/gameslist/{layout}', [APIController::class, 'gamesListEndpoint']);
});


Route::middleware('api', 'throttle:25,1')->group(function () {
    Route::get('/install', [InstallController::class, 'show']);
    Route::name('install-submit')->post('/install/submit', [InstallController::class, 'submit']);
});



Route::middleware('api', 'throttle:500,1')->prefix('api')->group(function () {
    Route::post('/testing/{function}', [TestingController::class, 'handle']);
});
