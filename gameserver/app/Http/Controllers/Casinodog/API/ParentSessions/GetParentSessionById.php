<?php

namespace App\Http\Controllers\Casinodog\API\ParentSessions;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Support\Facades\Validator;

class GetParentSessionById extends BaseController
{
    use GameKernelTrait;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /*
    *   Get existing parent session details using GameKernelTrait
    */
    public function handle(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string|min:2|max:255',
        ]);
        if(!is_uuid($request->id)) {
            abort(400, "ID is not a valid UUID string.");
        }
        $session = $this->get_session($request->id);
        return response()->json($session, $session['code']);
    }
}
