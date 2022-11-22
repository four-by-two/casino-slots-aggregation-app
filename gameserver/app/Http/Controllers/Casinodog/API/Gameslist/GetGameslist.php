<?php

namespace App\Http\Controllers\Casinodog\API\Gameslist;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\Gameslist;
use Illuminate\Support\Facades\Validator;

class GetGameslist extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /*
    *   Get a single game by 'gid' value, if nothing is found also is searching on 'slug' (still using the gid input)
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
                'gid' => 'required|string|min:2|max:255',
        ]);

        $collection = collect(Gameslist::short_list());
        $select_game = $collection->where('gid', $request->gid)->first();

        if(!$select_game) {
            $select_game = $collection->where('slug', $request->gid)->first();
            if(!$select_game) {
                abort(400, 'Game with that gid not found.');
            }
        }
        $provider_info = collect(Gameslist::provider_list())->where('pid', $select_game['provider'])->first();
        $game_data = array(
                'code' => 200,
                'status' => 'success',
                'data' => [
                        'game_info' => $select_game,
                        'provider_info' => $provider_info
                    ]
                );

        return response()->json($game_data, 200);
    }
}
