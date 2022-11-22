<?php

namespace App\Http\Controllers\Casinodog\API\Gameslist;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\Gameslist;
use Illuminate\Support\Facades\Validator;

class UpdateGameGameslist extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('disableOnProduction');
        $this->update_options = [
            "boolean" => ['active'],
            "integer" => [],
            "string" => ['name', 'gid', 'slug', 'provider']
        ];
    }

    /*
    *   Update a single game by 'gid' value, if nothing is found also is searching on 'slug' (still using the gid input)
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
                'gid' => 'required|string|min:2|max:255',
                'update_key' => 'required|min:2|max:255',
                'update_value' => 'required|min:1|max:555',
        ]);

        $select_game = $this->find_game($request->gid);

        if(in_array($request->update_key, $this->update_options['string'])) {
            return $this->string_update($request, $select_game);
        }

        if(in_array($request->update_key, $this->update_options['boolean'])) {
                return $this->boolean_update($request, $select_game);
        }

        if(in_array($request->update_key, $this->update_options['integer'])) {
            return $this->integer_update($request, $select_game);
        }
    }

    public function find_game($gid)
    {
        $collection = collect(Gameslist::short_list());
        $select_game = $collection->where('gid', $gid)->first();
        if(!$select_game) {
            $select_game = $collection->where('slug', $gid)->first();
            if(!$select_game) {
                abort(400, 'Game with specified gid not found.');
            }
        }
        return $select_game;
    }

    public function string_update(Request $request, $select_game)
    {
        if(is_string($request->update_value)) {
            $gid = $select_game['gid'];

            if($request->update_key === 'gid') {
                $exists_already = Gameslist::where('gid', $request->update_value)->first();
                if($exists_already) {
                    abort(400, 'Another game with this gid exist already. Gid and slug are unique identifiers used in various important game functions.');
                }
            }
            if($request->update_key === 'slug') {
                $exists_already = Gameslist::where('slug', $request->update_value)->first();
                if($exists_already) {
                    abort(400, 'Another game with this slug exist already. Fields \'gid\' and \'slug\' are unique identifiers used in various important game functions.');
                }
            }

            Gameslist::where('gid', $gid)->update([
                    $request->update_key => $request->update_value
            ]);
            if($request->update_key === 'gid') {
                $collection = collect(Gameslist::short_list());
                $select_game = $collection->where('gid', $request->update_value)->first();
            }
            return $this->success_response($select_game);
        } else {
            abort(400, 'Value should be of a string type.');
        }
    }

    public function boolean_update(Request $request, $select_game)
    {
        $update_value = $request->update_value;
        if($update_value !== "false") {
            if($update_value !== "true") {
                abort(400, "Value should be of a boolean type, within quotation marks either \"true\" or \"false\".");
            }
        }
        Gameslist::where('gid', $select_game['gid'])->update([
                $request->update_key => $request->update_value
        ]);
        return $this->success_response($select_game);
    }

    public function success_response($select_game) {
        $collection = collect(Gameslist::short_list());
        $select_game = $collection->where('gid', $select_game['gid'])->first();
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
