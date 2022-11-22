<?php

namespace App\Http\Controllers\Casinodog\API\Gameslist;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\Gameslist;

class TruncateGameslist extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('disableOnProduction');
    }

    /*
    *   Truncate Gameslist either by provider or all games using "all" on provider input
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
                'provider' => 'required|string|min:1|max:555',
        ]);

        $scaffold = NULL;
        if($request->provider === "all") {
            $count = Gameslist::count();
            if($count === 0) {
                abort(400, 'Gameslist is already empty.');
            }
            $delete = Gameslist::truncate();
        } else {

            $get = Gameslist::where('provider', $request->provider);
            $count = $get->count();

            if($count === 0) {
                abort(400, 'No games found for specified provider.');
            }
            $delete = $get->truncate();
        }
        Cache::pull('providerlist:shortlist');
        Cache::pull('gameslist:shortlist');

        $response_data = [
            'code' => 200,
            'status' => 'success',
            'data' => [
                'deleted_games' => $count
            ]
        ];
        return response()->json($response_data, 200);
    }
}
