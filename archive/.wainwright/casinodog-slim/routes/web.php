<?php

use Illuminate\Support\Facades\Route;
use Wainwright\CasinoDog\Controllers\Testing\TestingController;
use Illuminate\Http\Request;

Route::middleware('web', 'throttle:2000,1')->group(function () {
Route::get('/testing/{function}', [TestingController::class, 'handle']);

Route::get('/default_game_import', function (Request $request) {
    if(auth()->user()) {
        if(!auth()->user()->is_admin) {
            abort(403, "You are not admin.");
        }
        if(!$request->pid) {
            abort(401, "pid not specified.");
        }
        $pid = $request->pid;
        
        if($pid === 'all') {
            foreach(config('casino-dog.games') as $provider_id=>$provider) {
                \Wainwright\CasinoDog\Jobs\DefaultGameslistRetrieve::dispatch($provider_id);
            }
                Cache::put('defaultgameimport::all', now()->addMinutes(2));
                return back()->with('success_state', 'all');
        } else {
            if(config('casino-dog.games.'.$pid.'.active') !== NULL) {
                \Wainwright\CasinoDog\Jobs\DefaultGameslistRetrieve::dispatch($pid);
                Cache::put('defaultgameimport::'.$pid, now()->addMinutes(2));
                return back()->with('success_state', $pid);
            }
        }
    } else {
        abort(403, "Not logged in.");
    }
    return $data;
});
Route::any('/casino/cdnconfig.json', function (Request $request) {
    $url = env('APP_URL').'/dynamic_asset/relax/getclientconfig';
    $data = array(
        'clientconfigurl' => $url,
    );
    return $data;
  });
});