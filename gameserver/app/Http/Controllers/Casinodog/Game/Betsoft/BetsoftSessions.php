<?php
namespace App\Http\Controllers\Casinodog\Game\Betsoft;

use App\Http\Controllers\Casinodog\Game\SessionsHandler;
use Wainwright\CasinoDog\CasinoDog;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Casinodog\Game\GameKernel;
use Wainwright\CasinoDog\Models\Gameslist;

class BetsoftSessions extends BetsoftMain
{
    use GameKernelTrait;

    public function extra_game_metadata($gid)
    {
        return false;
    }


    public function fresh_game_session($game_id, $method, $token_internal = NULL)
    {
        if($method === 'demo_method') {
            $url = $this->get_game_demolink($game_id);
            $url = str_replace("&homeUrl=https://www.n1casino.com/exit_iframe&lang=en", "&lang=en", $url);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $html = curl_exec($ch);
            $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            $launcherTest = Http::withOptions([
                'verify' => false,
            ])->get($redirectURL);

            $parts = parse_url($redirectURL);
            parse_str($parts['query'], $query);
            $game_api_origin = $this->in_between('URL_MAIN_SERVER=', '&', $launcherTest->body());
            $servlet_origin = $this->in_between('SERVLET_URL=', '&', $launcherTest->body());
            $app_host = str_replace('https://', '', env('APP_URL'));
            $changed_content = str_replace('SERVLET_URL='.$servlet_origin, "SERVLET_URL=/api/games/betsoft/".$token_internal."/".$game_id.'/play?origin='.$servlet_origin, $launcherTest->body());
            //$changed_content = str_replace('URL_MAIN_SERVER='.$game_api_origin, "URL_MAIN_SERVER=win.radio.fm", $changed_content);


            $append_query = explode('?', $redirectURL);

            $data = [
                'origin_session' => $query['SID'],
                'html' => $changed_content,
                'query' => $append_query[1],
            ];
            return $data;
        }

        if($method === 'continued_session') {
            $select_session = $this->get_internal_session($token_internal);
            $sid = $select_session['data']['token_original'];
            
            $game = $this->fresh_game_session($game_id, 'demo_method', $token_internal);
            
            $query = $this->parse_query($game['query']);
            $query['SID'] = $sid;
            $changed_query = $this->build_query($query);

            $data = [
                'origin_session' => $query['SID'],
                'html' => $game['html'],
                'query' => $changed_query,
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
        $select_session = $this->get_internal_session($internal_token);
        if($select_session['status'] !== 200) { //internal session not found
               return false;
        }

        $token_internal = $select_session['data']['token_internal'];
        $game_id = $select_session['data']['game_id_original'];
        
        if($select_session['data']['token_original'] === 0) {
            $game = $this->fresh_game_session($game_id, 'demo_method', $internal_token);
            $this->update_session($internal_token, 'token_original', $game['origin_session']); //update session table with the real game session
        } else {
            $game = $this->fresh_game_session($game_id, 'continued_session', $internal_token);
        }

        $response = [
            'html' => $game['html'],
            'query' => $game['query'],
        ];
        return $response;
    }


}
