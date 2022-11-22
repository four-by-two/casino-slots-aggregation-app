<?php
namespace App\Http\Controllers\Casinodog\Game\PragmaticPlay;

use App\Http\Controllers\Casinodog\Game\SessionsHandler;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Casinodog\Game\GameKernel;

class PragmaticPlaySessions extends PragmaticPlayMain
{
    use GameKernelTrait;

    # Disclaimer: this should be made into a job and/or contract on any type of high load
    public function pragmaticplay_gameid_transformer($game_id, $direction)
    {
        if($direction === 'explode') {
            try {
                $games_kernel = new GameKernel;
                $games = $games_kernel->get_gameslist();
                $select_game = $games->where('gid', $game_id)->first();
                if(!$select_game) {
                    Log::warning('Error '.$game_id.' not found in gameslist');
                    return false;
                }
                $demolink = $select_game['demolink'];

                $origin_game_id = CasinoDog::in_between('gameSymbol=', 'u0026', $demolink);
                $origin_game_id = CasinoDog::remove_back_slashes($origin_game_id);
                Log::debug('Game ID transformed to '.$origin_game_id);

                return $demolink;
            } catch (\Exception $exception) {
                Log::warning('Errored trying to transform & explode game_id on pragmaticplay_gameid_transformer() function in PragmaticPlayController.'.$exception);
                return false;
            }
        } elseif($direction === 'concat') {
            $concat = 'softswiss/'.$game_id;
            return $concat;
        }
        Log::warning('Transform direction not supported, use concat or explode on pragmaticplay_gameid_transformer().');
        return false;
    }

    public function get_game_symbol($gid)
    {
        // Add in extra fields that you need for whatever reason on games
        // Launch the metadata job
        $select_game = $this->get_game($gid);
        if(!$select_game) {
            return false;
        }
        $demo_link = $select_game['demolink'];
        if(!$demo_link) {
            Log::warning('On extra_game_metadata, processed demo link does not seem to be available, which is needed for pragmatic play game_id transformation. Game ID: '.$gid);
            return false;
        }
        $explode = explode('?', $demo_link);
        parse_str($explode[1], $q_arr);

        if(isset($q_arr['gameSymbol'])) {
            if($q_arr['gameSymbol'] !== NULL) {
            return $q_arr['gameSymbol'];
            }
        }
        return false;
    }

    public function fresh_game_session($game_id, $method) {

        if($method === 'redirect') {

        $url = "https://demogamesfree.pragmaticplay.net/gs2c/openGame.do?gameSymbol=".$game_id."&websiteUrl=&platform=WEB&jurisdiction=99&lobby_url=&lang=en&cur=USD&isBridge=true&max_rnd_win=100";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
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
        return array(
            'html_content' => $launcherTest->body(),
            'modified_content' => NULL,
            'query' => $query,
            'token_original' => $query['mgckey'],
        );

        }


        if($method === 'realmoney_session') {
            $url = 'https://rarenew-dk4.pragmaticplay.net/gs2c/playGame.do?key=token%3Dea30fcc7-c0d1-49ae-ba12-5116f646515a%26symbol%3D'.$game_id.'%26platform%3DWEB%26language%3Den%26currency%3DUSD%26cashierUrl%3Dhttps%3A%2F%2Fstake.com%2Fdeposit%26lobbyUrl%3Dhttps%3A%2F%2Fstake.com%2Fcasino%2Fhome&stylename=rare_stake';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            
            $headers = array(
               "authority: rarenew-dk4.pragmaticplay.net",
               "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
               "accept-language: en-ZA,en;q=0.9",
               "cache-control: no-cache",
               "pragma: no-cache",
               "upgrade-insecure-requests: 1",
               "user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.101 Mobile Safari/537.36",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $redirectURL = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);            
            $resp = curl_exec($curl);
            curl_close($curl);

            $realmoney_dom = Http::withOptions([
                'verify' => false,
            ])->get($redirectURL);
            $session = $this->fresh_game_session($game_id, 'redirect');
            $parts = parse_url($redirectURL);
            parse_str($parts['query'], $query);
            $realmoney_mgckey = $this->in_between('mgckey: "', '"', $realmoney_dom);
            $token_original = $session['token_original'];
            $query['mgckey'] = $token_original;
            $final_dom = str_replace($realmoney_mgckey, $token_original, $realmoney_dom);
            return array(
                'html_content' => $final_dom,
                'modified_content' => NULL,
                'query' => $session['query'],
                'token_original' => $token_original,
            );
        }

        if($method === 'token_only') {
            $url = "https://demogamesfree.pragmaticplay.net/gs2c/openGame.do?gameSymbol=".$game_id."&websiteUrl=&platform=WEB&jurisdiction=99&lobby_url=&lang=en&isBridge=true&cur=USD";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $html = curl_exec($ch);
            $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            $launcherTest = Http::withOptions([
                'verify' => false,
            ])->get($redirectURL);

            $token_only_parts = parse_url($redirectURL);
            parse_str($token_only_parts['query'], $token_only_query);
            return $token_only_query['mgckey'];
        }

        if($method === 'demo_method') {
            $url = "https://demogamesfree.pragmaticplay.net/gs2c/openGame.do?gameSymbol=".$game_id."&websiteUrl=&platform=WEB&jurisdiction=99&isBridge=true&lobby_url=&lang=en&cur=USD";
            Log::debug('Game url request: '.$url);
            $http_get = Http::retry(2, 3000)->get($url);
            return $http_get;
        }

        // Add in additional grey methods here, specify the method on the internal session creation when a session is requested, don't split this here
        return 'generateSessionToken() method not supported';
    }

    public function create_session(string $internal_token)
    {
        $select_session = $this->get_internal_session($internal_token);
        if($select_session['status'] !== "success") { //internal session not found
               return false;
        }
        $session = $select_session['data']['session'];
        $token_internal = $session['id'];
        $game_id = $session['game_id'];
        $user_agent = $session['user_agent'] ?? '[]';
        $check_active_session = $this->find_previous_active_session($internal_token);

        $game_symbol = $this->get_game_symbol($game_id);

        $game_content = $this->fresh_game_session($game_symbol, 'redirect'); // set realmoney_dom if wanting dom from real money session

        $origin_session_token = $game_content['token_original'];


        if($origin_session_token === false)
        {
            save_log('PragmaticPlaySessions.php', 'Not being able to select play_token, even though the status & original game data seems correct. Possibly game source/structure has changed itself - disable game before proceeding to investigate thoroughly. '.json_encode($origin_session_token));
            return false;
        }

        SessionsHandler::sessionUpdate($token_internal, 'token_original', $origin_session_token); //update session table with the real game session token
        $changed_content = $this->modify_game($token_internal, $game_content['html_content']);
        $game_content['modified_content'] = $changed_content;
        $response = [
            'game_content' => $game_content,
            'internal_token' => $internal_token,
            'session' => $session,
        ];
        return $response;
    }


}
