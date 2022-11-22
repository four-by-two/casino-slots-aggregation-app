<?php

namespace App\Http\Controllers\Casinodog\API\ParentSessions;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;

class CreateParentSession extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Request a new parent game session.
     */
    public function handle(Request $request)
    {
        $this->validate($request, [
            'game' => 'required|string|min:2|max:255',
            'currency' => 'required|string|min:2|max:10',
            'player' => 'required|string|min:2|max:155',
            'operator_key' => 'required|string|min:2|max:255',
            'request_ip' => 'required|string|min:2|max:255',
            'mode' => 'required|string|min:2|max:255',
        ]);
        
        $request_data = $request->only(['game', 'currency', 'operator_key', 'mode', 'request_ip', 'player']);
        return \App\Http\Controllers\Casinodog\Game\SessionsHandler::createSession($request_data);
    }
}
