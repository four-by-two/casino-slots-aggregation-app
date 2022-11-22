<?php
namespace App\Http\Controllers\Casinodog\Game\Relaxgaming;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Wainwright\CasinoDog\Models\Gameslist;

class RelaxgamingSessions extends RelaxgamingMain
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
            $query = $this->parse_query($url);
            $game_id = isset($query['gameid']) ? $query['gameid'] : $query['productId'];
            $game_id2 = str_replace('ways', 'pays',$game_id);
            $origin_url = 'https://d3nsdzdtjbr5ml.cloudfront.net/casino/games-mt/'.$game_id.'/index.html?lang=en_US&gameid='.$game_id.'&jurisdiction=MT&gameurl=&apex=1&channel=web&moneymode=fun&partnerid=1&fullscreen=false';
            $html = Http::get('https://wainwrighted.herokuapp.com/'.$origin_url);
            $base_href = 'https://wainwrighted.herokuapp.com/https://d3nsdzdtjbr5ml.cloudfront.net/casino/games-mt/'.$game_id.'/';
            $data = [
                'origin_url' => $origin_url,
                'base_href' => $base_href, 
                'origin_session' => NULL, //change this if you are catching the "real" game session token from html content and want to store it to parent session
                'html' => $html,
                'origin_gameid' => $game_id,
            ];
            return $data;
        }
        
        /* example continued play session *
            // Please check Mascot/MascotSessions.php for examples on continued play (re-connecting existing sessions).
        */


        // Add in additional grey methods here, specify the method on the internal session creation when a session is requested, don't split this here
        return 'generateSessionToken() method not supported';
    }

    public function get_game_demolink($gid) {
        $select = Gameslist::where('gid', $gid)->first();
        return $select->demolink;
    }

    public function create_session(string $internal_token)
    {
        $select_session = $this->get_internal_session($internal_token);
        if($select_session['status'] !== 200) { //internal session not found
               return false;
        }

        $internal_token = $select_session['data']['token_internal'];
        $game_id = $select_session['data']['game_id_original'];

        $game = $this->fresh_game_session($game_id, 'demo_method', $internal_token);

        /* example continued play (connect to existing game session)
            // Please check Mascot/MascotSessions.php for examples on continued play (re-connecting existing sessions).
        */

        $html_content_modify = $this->modify_game($internal_token, $game['html'], $game['origin_gameid']); //modify the HTML content by rules specified in the Main.php configuration

        $response = [
            'html' => $html_content_modify,
            'origin_url' => $game['origin_url'],
            'base_href' => $game['base_href'],
            'origin_session' => $game['origin_session'],
            'origin_gameid' => $game['origin_gameid'],
        ];
        
        return $response;
    }


}
