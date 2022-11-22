<?php

namespace App\Http\Controllers\Casinodog\API\ParentSessions;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\ParentSessions;
use Illuminate\Support\Facades\Validator;

class ListParentSession extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /*
    *   Get list of parent sessions
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
           'per_page' => 'max:2500|integer',
           'page' => 'integer',
        ]);
        $count = ParentSessions::count();
        if($count === 0) {
            abort(400, 'No sessions created yet.');
        }
        $sessions = ParentSessions::all();

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
        $result = collect($sessions)->paginate($limit);
        $result_count = $result->count();
        if($result_count === 0) {
            abort(400, 'Try a lower page number.');
        }
        $result = collect($result)->insertBefore('current_page', $result_count, 'page_item_count')->insertBefore('current_page', 'success', 'status')->insertBefore('status', 200, 'code');
        return response()->json($result, 200);
    }
}
