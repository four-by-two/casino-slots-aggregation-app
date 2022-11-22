<?php

namespace App\Http\Controllers\Casinodog\API\Datalogger;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\DataLogger;
use Illuminate\Support\Facades\Validator;

class ListDatalogger extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /*
    *   Get list of all games
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
                'per_page' => 'max:1000|integer',
                'page' => 'integer',
        ]);
        $count = DataLogger::count();
        if($count === 0) {
            abort(400, 'No logs found');
        }

        $datalogs = collect(DataLogger::orderBy('created_at', 'DESC')->get());

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
        $result = collect($datalogs)->paginate($limit);
        $result_count = $result->count();
        if($result_count === 0) {
            abort(400, 'Try a lower page number.');
        }
        $result = collect($result)->insertBefore('current_page', $result_count, 'page_item_count')->insertBefore('current_page', 'success', 'status')->insertBefore('status', 200, 'code');
        return response()->json($result, 200);
    }
}
