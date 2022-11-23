<?php
namespace App\Http\Controllers\Casinodog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use App\Models\ParentSessions;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;

class WebsocketAdapter
{
    use GameKernelTrait;
    protected $ws_id;
    protected $parentsession_id;
    public function build_response($response, $code)
    {
        if($code === 200) {
            $status = "success";
        } else {
            $status = "error";
        }

        return array(
                "status" => $status,
                "code" => $code,
                "ws_id" => $this->ws_id,
                "parentsession_id" => $this->parentsession_id,
                "response" => $response,
        );
    }

    public function register(string $ws_id, $internal_token)
    {
        $this->ws_id = $ws_id;
        $this->parentsession_id = $internal_token;

        if(!is_uuid($internal_token)) {
            return $this->build_response("Session token is not UUID format.", 400);
        }
        $select_session = $this->get_internal_session($internal_token, 'session');
        if($select_session['status'] !== "success") { //internal session not found
            return $this->build_response("Session not found.", 400);
        }

        $this->update_session($internal_token, 'ws_id', $ws_id);
        $select_session = $this->get_internal_session($internal_token, 'session');

        return $this->build_response($select_session, 200);
    }


}
