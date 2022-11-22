<?php

namespace App\Http\Controllers\Casinodog\API\Gameslist;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\Gameslist;
use Illuminate\Support\Facades\Validator;

class ListGameslist extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->filter_key_allowed = [
                'active', 'provider', 'gid', 'slug', 'gid_extra', 'bonusbuy', 'jackpot'
        ];
    }

    /*
    *   Get list of all games
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
                'per_page' => 'max:1000|integer',
                'page' => 'integer',
                'filter' => 'min:2|max:255|array',
        ]);
        $count = Gameslist::count();
        if($count === 0) {
            abort(400, 'No games found at all. Create games using the create method or scaffold the default listings with scaffold method.');
        }

        $gameslist = collect(Gameslist::short_list('attach'));

        if($request->filter) {
            $filter = collect($request->filter);
            foreach($filter as $filter_key=>$filter_value) {
                $collect = collect($gameslist);
                $gameslist = $collect->where($filter_key, $filter_value)->all();
            }
        }

        $limit = 50;
        if($request->per_page) {
            if(is_numeric($request->per_page)) {
                if($request->per_page > 0) {
                    if($request->per_page > 2500) {
                        $limit = (int) 2500;
                    } else {
                        $limit = (int) $request->per_page;
                    }
                }
            }
        }
        $result = collect($gameslist)->paginate($limit);
        $result_count = $result->count();
        if($result_count === 0) {
            abort(400, 'Try a lower page number.');
        }
        $result = collect($result)->insertBefore('current_page', $result_count, 'page_item_count')->insertBefore('current_page', 'success', 'status')->insertBefore('status', 200, 'code');
        return response()->json($result, 200);
    }
}
