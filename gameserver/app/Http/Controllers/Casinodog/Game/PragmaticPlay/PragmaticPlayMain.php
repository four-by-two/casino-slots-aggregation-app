<?php
namespace App\Http\Controllers\Casinodog\Game\PragmaticPlay;
use App\Http\Controllers\Casinodog\Game\GameKernel;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Http\Request;

class PragmaticPlayMain extends GameKernel
{
    use GameKernelTrait;

    /*
    * load_game_session() is where we create/continue any game session and where we initiate the game content
    *
    * @param [type] $data
    * @return void
    */
    public function load_game_session($data) {
        $token = $data['data']['session']['id'];
        $session = new PragmaticPlaySessions();
        $game_content = $session->create_session($token);
        return $this->game_launch($game_content);
    }

    /*
    * game_launch() is where we send the finalized HTML content to the launcher blade view template
    *
    * @param [type] $game_content
    * @return void
    */
    public function game_launch($game_content) {
        return view('launcher')->with('game_content', $game_content);
    }


    /*
    * game_event() is where direct API requests from inside games are received
    *
    * @param Request $request
    * @return void
    */
    public function game_event(Request $request) {
        $event = new PragmaticPlayGame();
        $response = $event->game_event($request);
        return $event->game_event($request);
    }


    /*
    * promo_event() is where direct API requests from inside games are received
    *
    * @param Request $request
    * @return void
    */
    public function promo_event(Request $request) {
        $event = new PragmaticPlayGame();
        return $event->promo_event($request);
    }

    /*
    * error_handle() for handling errors, meant to make similar error pages as original game but can be used for any error handling you need
    *
    * @param [type] $type
    * @param [type] $message
    * @return void
    */
    public function error_handle($type, $message = NULL) {
        if($type === 'incorrect_game_event_request') {
            $message = ['status' => 400, 'error' => $type];
            return response()->json($message, 400);
        }
        abort(400, $message);
    }

    /*
    * dynamic_asset() used to load altered javascript from internal storage
    *
    * @param string $asset_name
    * @param Request $request
    * @return void
    */
    public function dynamic_asset(string $asset_name, Request $request) {
        if($asset_name === 'wurfl.js') {
            return $this->pretendResponseIsFile(__DIR__.'/AssetStorage/wurfl.js', 'application/javascript; charset=utf-8');
        }
        if($asset_name === 'pragmatic-pusher.js') {
            return $this->pretendResponseIsFile(__DIR__.'/AssetStorage/pragmatic-pusher.js', 'application/javascript; charset=utf-8');
        }
        if($asset_name === 'html5-script-external.js') {
            return $this->pretendResponseIsFile(__DIR__.'/AssetStorage/html5-script-external.js', 'application/javascript; charset=utf-8');
        }
        if($asset_name === 'logo_info.js') {
            return $this->pretendResponseIsFile(__DIR__.'/AssetStorage/logo_info.js', 'application/javascript; charset=utf-8');
        }

        if($asset_name === 'minilobby.json') {
            $lobbyGames = file_get_contents(__DIR__.'/AssetStorage/minilobby.json');

            $time = time();
            $mgckey = $_GET['mgckey'];
            $signature = hash_hmac('md5', $mgckey, $time.$mgckey);

            $gameStartURL = config('gameconfig.pragmaticplay.minilobby_url').'/'.$signature.'/'.$time;
            $data_origin = json_decode($lobbyGames);
            $data_origin->gameLaunchURL = $gameStartURL;
            $data_origin = json_encode($data_origin);

            return $data_origin;
        }
    }



    /*
    * fake_iframe_url() used to display as src in iframe, this is only visual. If you have access to game aggregation you should generate a working session with game provider.
    *
    * @param string $slug
    * @param [type] $currency
    * @return void
    */
    public function fake_iframe_url(string $slug, $currency) {
        $game_id_purification = explode(':', $slug);
        if($game_id_purification[1]) {
            $game_id = $game_id_purification[1];
        }
        if($currency === 'DEMO' || $currency === 'FUN') {
            $build_url = 'https://softswiss.pragmaticplay.net/gs2c/openGame.do?gameSymbol='.$game_id.'&websiteUrl=https%3A%2F%2Fblueoceangaming.com&platform=WEB&jurisdiction=99&lang=en&cur='.$currency;
        }
        $build_url = 'https://softswiss.pragmaticplay.net/gs2c/playGame.do?key=cashierUrl=&lobbyUrl=&symbol='.$game_id.'&websiteUrl=https%3A%2F%2Fblueoceangaming.com&platform=WEB&technology=H5&jurisdiction=99&lang=en&cur='.$currency.'&token='.$this->random_uuid().'&stylename=sfws_betssw';
        return $build_url;
    }


    /*
    * default_gamelist() used to import/export a default gamelist from file storage
    *
    * @param string $action
    * @param json $data
    * @return void
    */
    public function default_gamelist($action, $data = NULL)
    {
        $storage_location = __DIR__.'/AssetStorage/default_gamelist.json';
        if (!file_exists($storage_location)) {
            file_put_contents($storage_location, '[]');
        }

        if($action === "store") {
            if($data === NULL) {
                $message = ['status' => 400, 'error' => "You need to supply data to import."];
                return $message;
            }

            if(!$this->isJSON($data)) {
                $message = ['status' => 400, 'error' => "Data does not seem to be valid JSON scheme."];
                return $message;
            }
            $store = file_put_contents($storage_location, $data);
            $message = array('status' => 200, 'message' => "Data saved at ".$storage_location);
            return $message;
        
        } elseif($action === "retrieve") {
            try {
                $storage_location = __DIR__.'/AssetStorage/default_gamelist.json';


                $retrieve = file_get_contents($storage_location);
                if($this->isJSON($retrieve)) {
                    $message = ['status' => 200, 'message' => $retrieve];
                } else {
                    $message = ['status' => 400, 'error' => "Data retrieved at '.$storage_location.' not seem to be valid JSON scheme."];

                }
                } catch(\Exception $e) {
                    $message = ['status' => 400, 'error' => $e->getMessage()];
                }
            return $message;
        } elseif($action === "get_storage_location") {
            $message = ['status' => 200, 'message' => $storage_location];
            return $message;
        }

        $message = ['status' => 400, 'error' => $action." action not valid in default_gamelist() function."];
        return $message;
    }

    /*
    * modify_game() used for replacing HTML content
    *
    * @param [type] $token_internal
    * @param [type] $game_content
    * @return void
    */


    public function modify_game($token_internal, $game_content)
    {
        $select_session = $this->get_internal_session($token_internal, 'session');
        /* Build the new API URL to replace the game API */
        $new_api_endpoint = config('casinodog.games.pragmaticplay.new_api_endpoint').$token_internal.'/';
        /* Replacing HTML content of original game */
        $gc = $game_content;
	      $cors_proxy = config('casinodog.cors_anywhere');
        $gc = str_replace('"gameService":"https://demogames.pragmaticplay.net/', '"gameService":"'.$new_api_endpoint, $gc);
        $gc = str_replace('"gameService":"https://demogamesfree.pragmaticplay.net/', '"gameService":"'.$new_api_endpoint, $gc);
        $gc = str_replace('"gameService":"https://rarenew-dk4.pragmaticplay.net/', '"gameService":"'.$new_api_endpoint, $gc);

        $new_api_reload_balance = str_replace('/01-dev-api.777.dog/', '', $new_api_endpoint);
        $gc = str_replace('/gs2c/reloadBalance.do', $new_api_reload_balance.'gs2c/reloadBalance.do', $gc);
	$device_wurlf = config('casino-dog.hostname').'/dynamic_asset/pragmaticplay/wurfl.js';
        $gc = str_replace('device.pragmaticplay.net/wurfl.js', $device_wurlf, $gc);
        $gc = str_replace('cashierUrl: ""',  'cashierUrl: "/"', $gc);
        $gc = str_replace('lobbyUrl: ""',  'lobbyUrl: "/"', $gc);

        $gc = str_replace('extend_events: "1"',  'extend_events: "0"', $gc);
        $gc = str_replace('MainWindow',  'StandardMode InFrame logoOff logoOff', $gc);
        $gc = str_replace('https://demofreegames.pragmaticplay.net/gs2c/common/js/html5-script-external.js', '/dynamic_asset/pragmaticplay/html5-script-external.js?game='.$select_session['data']['game_id'], $gc);
        $gc = str_replace('https://demofreegames.pragmaticplay.net/gs2c/common/js/html5-script-external.js', '/dynamic_asset/pragmaticplay/html5-script-external.js?game='.$select_session['data']['game_id'], $gc);
        $gc = str_replace('https://rarenew-dk4.pragmaticplay.net/gs2c/common/js/html5-script-external.js', '/dynamic_asset/pragmaticplay/html5-script-external.js?game='.$select_session['data']['game_id'], $gc);

        $gc = str_replace('UA-83294317',  'UA-15294317', $gc);
        $gc = str_replace('//www.google-analytics.com/analytics.js', '/dynamic_asset/pragmaticplay/pragmatic-pusher.js', $gc);
        $gc = str_replace('"datapath":"https://demogames.pragmaticplay.net/gs2c/common/', '"replaySystemContextPath":"/ReplayService","openHistoryInWindow":false, "multiProductMiniLobby":false, "currencyOriginal": "USD", "instantFrbUpdateSeconds": 180, "lobbyLaunched":false, "instantFrbEnabled":true, "amountType": "COIN", "miniLobby": true, "region": "Other", "ingameLobbyApiURL":"/dynamic_asset/pragmaticplay/minilobby.json", "historyType":"internal", "miniLobby":true, "styleName":"avnt_aventonv", "integrationType":"HTTP","sessionTimeout":"9999","openHistoryInTab":true,"datapath":"https://s1.1-games.4x2.games/pp/gs2c/common/', $gc);
        $gc = str_replace('"datapath":"https://demogamesfree.pragmaticplay.net/gs2c/common/', '"replaySystemContextPath":"/ReplayService","openHistoryInWindow":false, "multiProductMiniLobby":false, "currencyOriginal": "USD", "instantFrbUpdateSeconds": 180, "lobbyLaunched":false, "instantFrbEnabled":true, "amountType": "COIN", "miniLobby": true, "region": "Other", "ingameLobbyApiURL":"/dynamic_asset/pragmaticplay/minilobby.json", "historyType":"internal", "miniLobby":true, "styleName":"avnt_aventonv", "integrationType":"HTTP","sessionTimeout":"9999","openHistoryInTab":true,"datapath":"https://s1.1-games.4x2.games/pp/gs2c/common/', $gc);

        $gc = str_replace('"datapath":"https://rarenew-dk4.pragmaticplay.net', '"datapath":"https://wainwrighted.herokuapp.com', $gc);
        $gc = str_replace('https://rarenew-dk4.pragmaticplay.net', 'https://wainwrighted.herokuapp.com/https://rarenew-dk4.pragmaticplay.net', $gc);

        $gc = str_replace('demoMode":"1"',  'demoMode":"0"', $gc);
        return $gc;
    }
}

