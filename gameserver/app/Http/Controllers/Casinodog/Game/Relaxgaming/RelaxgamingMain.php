<?php
namespace App\Http\Controllers\Casinodog\Game\Relaxgaming;

use App\Http\Controllers\Casinodog\Game\GameKernel;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Wainwright\CasinoDog\Facades\ProxyHelperFacade;

class RelaxgamingMain extends GameKernel
{
    use GameKernelTrait;

    /*
    * load_game_session() is where we create/continue any game session and where we initiate the game content
    *
    * @param [type] $data
    * @return void
    */
    public function load_game_session($data) {
        $token = $data['token_internal'];
        $session = new RelaxgamingSessions();
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
        return view('wainwright::launcher-content-relaxgaming')->with('game_content', $game_content);
    }

    /*
    * game_event() is where direct API requests from inside games are received
    *
    * @param Request $request
    * @return void
    */
    public function game_event(Request $request) {
        $event = new RelaxgamingGame();
        return $event->game_event($request);
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
    * dynamic_asset() used to load altered javascript from internal storage, simply point the assets you need loaded through here in the modify_content() to point to /dynamic_asset/[ASSET_NAME]
    *
    * @param string $asset_name
    * @param Request $request
    * @return void
    */
    public function dynamic_asset(string $asset_name, Request $request) 
    {
        if($asset_name === "config.js") {
            $http = 'https://cors-4.herokuapp.com/https://d2drhksbtcqozo.cloudfront.net/casino/games-mt/'.$request->gid.'/config.js';
            $resp = ProxyHelperFacade::CreateProxy($request)->toUrl($http);
            $new_api_endpoint = config('casino-dog.games.relax.new_api_endpoint').$request->internal_token.'/'.$request->gid.'/play';  // building up the api endpoint we want to receive game events upon
            $content = str_replace('https://dev-casino-client.api.relaxg.net/game', $new_api_endpoint, $resp->getContent());
            $content = str_replace('https://stag-casino-client.api.relaxg.net/game', $new_api_endpoint, $resp->getContent());
            $content = str_replace('spinDelay: true', 'spinDelay: false', $content);
            $content = str_replace('en_GB', 'en_US', $content);
            $content = str_replace('https://d3nsdzdtjbr5ml.cloudfront.net', 'https://casinoman.app', $content);

            //$content = str_replace('spinDelay: false', 'google.com', $content);
            //$content = str_replace('https://d3nsdzdtjbr5ml.cloudfront.net', 'https://02-gameserver.777.dog/', $content);           
	    return response($content)->header('Content-Type', 'application/javascript');

        }

        if(str_contains($asset_name, 'getclientconfig_')) {
            $asset_url = 'https://iomeu-casino-client.api.relaxg.com/capi/1.0/casino/games/getclientconfig';
            $resp = ProxyHelperFacade::CreateProxy($request)->toUrl($asset_url);
            $resp = json_decode($resp->getContent(), true);
            $exploded_asset = explode('_', $asset_name);
            $token = $exploded_asset[1];
            $game = $exploded_asset[2];
            $new_api_endpoint = config('casino-dog.games.relax.new_api_endpoint').$token.'/'.$game.'/play';  // building up the api endpoint we want to receive game events upon
            $resp['disableRgApi'] = true;
            $resp['loadRgApiLibUrl'] = false;
            $resp['gameServerApi'] = $new_api_endpoint;
            return response($resp)->header('Content-Type', 'application/json');
        }

        if($asset_name === "getclientconfig") {
            $asset_url = 'https://iomeu-casino-client.api.relaxg.com/capi/1.0/casino/games/getclientconfig?';
	       try {
            $referer = explode('?', request()->headers->get('referer'))[1];
            $query = $this->parse_query($referer);
            $new_api_endpoint = config('casino-dog.games.relax.new_api_endpoint').$query['token'].'/'.$query['gameid'].'/play';  // building up the api endpoint we want to receive game events upon
            $request_url = $_SERVER['REQUEST_URI'];
            $path = explode('?', $request_url)[1];
            $resp = ProxyHelperFacade::CreateProxy($request)->toUrl($asset_url.$path);
            $resp = json_decode($resp->getContent(), true);
            $resp['loadRgApiLibUrl'] = false;
            $resp['gameServerApi'] = $new_api_endpoint;
            } catch(\Exception $e) {
                $resp = ProxyHelperFacade::CreateProxy($request)->toUrl('https://iomeu-casino-client.api.relaxg.com/capi/1.0/casino/games/getclientconfig');
                $resp = json_decode($resp->getContent(), true);

	    $referer = explode('?', request()->headers->get('referer'))[1];
            $query = $this->parse_query($referer);
            $new_api_endpoint = config('casino-dog.games.relax.new_api_endpoint').$query['token'].'/'.$query['gameid'].'/play';  // building up the api endpoint we want to receive game events upon

                $resp['disableRgApi'] = true;
                $resp['loadRgApiLibUrl'] = false;
                $resp['gameServerApi'] = $new_api_endpoint;
                $resp = array('error' => $e->getMessage());
            }
            return response($resp)->header('Content-Type', 'application/json');
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
        /* example *
        
            $game_id_purification = explode(':', $slug);
            if($game_id_purification[1]) {
                $game_id = $game_id_purification[1];
            }
            if($currency === 'DEMO' || $currency === 'FUN') {
                $build_url = 'https://bog.relaxgaming.net/gs2c/openGame.do?gameSymbol='.$game_id.'&websiteUrl=https%3A%2F%2Fblueoceangaming.com&platform=WEB&jurisdiction=99&lang=en&cur='.$currency;
            }
            $build_url = 'https://bog.relaxgaming.net/gs2c/html5Game.do?gameSymbol='.$game_id.'&websiteUrl=https%3A%2F%2Fblueoceangaming.com&platform=WEB&jurisdiction=99&lang=en&cur='.$currency;
            return $build_url;
            
        */
    }

    /*
    * custom_entry_path() used for structuring the path the launcher is displayed on. You need to enable this in config ++ then copy the "/g" route in routes/games.php to reflect the custom entry path used below.
    *
    * @param [type] $gid
    * @return void
    */
    public function custom_entry_path($gid)
    {
        /* example *
            $url = env('APP_URL')."/casino/ContainerLauncher";
            return $url;
        */
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
    public function modify_game($token_internal, $game_content, $origin_game_id)
    {
        $select_session = $this->get_internal_session($token_internal)['data'];
        $new_api_endpoint = config('casino-dog.games.relaxgaming.new_api_endpoint').$token_internal.'/'.$select_session['game_id_original'].'/play';  // building up the api endpoint we want to receive game events upon
        $asset_url = env('APP_URL').'/dynamic_asset/relax/';
        $gc = $game_content;
        $gc = str_replace('config.js', $asset_url.'config.js?gid='.$origin_game_id.'&internal_token='.$token_internal, $gc);
        //$gc = str_replace('https://www.google-analytics.com', '', $gc);
        //$gc = str_replace('google', '', $gc);
        $gc = str_replace('UA-10', 'DAVIDWAINWRIGHT-15', $gc);
        $gc = str_replace('window.ga', '//window.ga', $gc);
        //$gc = str_replace('class=""', 'class="en_US"', $gc);
        $gc = str_replace('ga(', '//ga(', $gc);
        $gc = str_replace('sentry', '', $gc);


        /* example *
            $gc = str_replace('window.serverUrl="', 'window.serverUrl="'.$new_api_endpoint.'?origin_url=', $gc);
            $gc = str_replace('window.currency="NAN"', 'window.currency="USD"', $gc);
        */
        
       return $gc;
    }
}
