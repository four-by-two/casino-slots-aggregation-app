<?php
namespace App\Http\Controllers\Casinodog\Game\RedTiger;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use App\Models\Gameslist;

class RedTigerSessions extends RedTigerMain
{
    use GameKernelTrait;

    public function extra_game_metadata($gid)
    {
        return false;
    }

    public function fresh_game_session($game_id, $method, $token_internal = NULL)
    {
        if($method === 'demo_method') {
            $demo_link = $this->get_game_demolink($game_id);
            $html = Http::get(config('casino-dog.cors_anywhere').$demo_link);
            $token = $this->in_between('token": "', '"', $html);

            $data = [
                'token' => $token,
                'html' => $html,
                'link' => $demo_link,
            ];
            return $data;
        }
        // Add in additional grey methods here, specify the method on the internal session creation when a session is requested, don't split this here
        return 'generateSessionToken() method not supported';
    }

    public function get_game_demolink($gid) {
        $select = Gameslist::where('gid', $gid)->first();
        return $select->demolink;
    }


    public function get_game_identifier($gid) {
        $select = Gameslist::where('gid', $gid)->first();
        return $select->gid_extra;
    }

    public function create_session(string $internal_token)
    {
        $select_session = $this->get_internal_session($internal_token, 'session');
        if($select_session['status'] !== "success") { //internal session not found
             return false;
        }

        $token_internal = $select_session['data']['id'];
        $game_id = $select_session['data']['game_id'];


        $game = $this->fresh_game_session($game_id, 'demo_method', $token_internal);
        $update_session = $this->update_session($internal_token, 'token_original', $game['token']);
        $html_content_modify = $this->modify_game($token_internal, $game['html']);


        $response = [
            'game_content' => [
                    'original_content' => $game['html'],
                    'modified_content' => $html_content_modify,
            ],
            'origin_session' => $game['token'],
            'origin_game_id' => $game_id,
            'token' => $internal_token,
            'link' => $game['link'],
        ];
        return $response;
    }


}
