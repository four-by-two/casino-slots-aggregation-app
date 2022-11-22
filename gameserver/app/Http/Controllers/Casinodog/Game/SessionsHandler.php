<?php
namespace App\Http\Controllers\Casinodog\Game;
use App\Http\Controllers\Casinodog\Game\GameKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use App\Http\Controllers\Casinodog\Game\OperatorsController;
use App\Models\ParentSessions;
use App\Models\Gameslist;

class SessionsHandler extends GameKernel
{
    public function entryWildcardDomain(Request $request) {
        $token = current(explode('.', $request->getHost()));

        $select_session = SessionsHandler::sessionData($token);
        if($select_session === false) {
            $casino_dog->save_log('SessionsHandler()', 'Session ' . $request->token . ' not found.');
            abort(404, 'Session '.$token.' not found.');
        } else {
            $token = $token;
            $player_id = $select_session['data']['player_id'];
            $entry_securekey = generate_sign($token);
            return redirect('/g?token='.$token.'&entry='.$entry_securekey.'&player_id='.$player_id);
            return $this->session($token, $player_id, $entry_securekey, $request);
        }
    }

    public function entrySession(Request $request)
    {
            $validate = $this->enterSessionValidation($request);
            $player_id = $request->player_id;
            $token = $request->token;
            $entry_securekey = $request->entry;
            return $this->session($token, $player_id, $entry_securekey, $request);
    }

    public function session($token, $player_id, $entry_securekey, Request $request)
    {
        $agent = $request->header('user-agent');
        try {
        $select_session = SessionsHandler::sessionData($request->token);
        if($select_session === false) {
           save_log('SessionsHandler()', 'Session ' . $request->token . ' not found.');
           abort(404, 'Session not found.');
        }
        $verify_signature = verify_sign($entry_securekey, $token);
        if($verify_signature === false) {
           abort(403, 'Entry signature invalid, create new session.');
        }
        if($select_session['data']['session']['active'] === 0) {
           abort(400, 'Session expired, create new session.');
        }
        $session_state_update = SessionsHandler::sessionUpdate($request->token, 'state', 'SESSION_ENTRY');
        if($session_state_update === false) {
           abort(400, 'Bad request. Not able to change session_state.');
        }
        $ua = $request->header('User-Agent');
        $set_user_agent = self::sessionUpdate($token, 'user_agent', array($ua)); //set user_agent from player to session
        $final_session_data = $session_state_update;
        $select_extra_meta = $final_session_data['data']['session']['extra_meta'];
        $provider_id = $final_session_data['data']['provider_info']['pid'];
        $game_controller = gameclass($provider_id);

        if(!$game_controller) {
            self::sessionFailed($token);
           abort(400, 'Bad request. Failed to retrieve game controller, report to system admin.');
        }

        $game_launcher_behaviour = config('casinodog.games.'.$provider_id.'.launcher_behaviour');
        if(!$game_launcher_behaviour) {
            $casino_dog->save_log('SessionHandler()', 'No launcher behaviour specified for method. Either disable games or add launcher behaviour to config.php. Session: '.json_encode($final_session_data));
            self::sessionFailed($token);
           abort(400, 'Bad request. No launcher behaviour specified.');
        }
        $game_controller = new $game_controller;
        $request_game_session = $game_controller->load_game_session($final_session_data);
        header('Access-Control-Allow-Origin: *');
        //header('X-Frame-Options: SAMEORIGIN');
        if($request_game_session === false) {
            self::sessionFailed($token);
           abort(400, 'Error trying to retrieve origin game, please refresh.');
        }

        //self::invalidate_previous_sessions($final_session_data);

        if($game_launcher_behaviour === 'redirect') {
            self::sessionUpdate($token, 'state', 'SESSION_STARTED');
            return redirect($request_game_session);
        }
        elseif($game_launcher_behaviour === 'internal_game') {
            self::sessionUpdate($token, 'state', 'SESSION_STARTED');
            //$request_game_session = preg_replace('/[\x00-\x1f\*]/', '', trim($request_game_session));
            $request_game_session = strtr(utf8_decode($request_game_session), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            //var_dump($request_game_session);
            return $request_game_session;
        }
        else {
            self::sessionFailed($token);
            $casino_dog->save_log('SessionsHandler()', 'Unsupported launcher configuration, set to either internal_game or redirect within /config/casino-dog.php.', json_encode($final_session_data));
           abort(400, 'Bad request. Unsupported launcher behaviour specified.');
        }
    } catch (\Exception $exception) {
       save_log('SessionsHandler', $exception->getMessage().' at line '.$exception->getLine().' in file '.$exception->getFile());
       abort(400, $exception);
    }
    }

    public static function sessionFailed($token)
    {
        try {
            SessionsHandler::sessionUpdate($token, 'state', 'SESSION_FAILED');
            SessionsHandler::sessionUpdate($token, 'active', 0);
            Cache::forget($token);
        } catch (\Exception $exception) {
            save_log('SessionsHandler()', 'Error trying to invalidate session at sessionFailed(). Token:'.$token);
        }
    }

    public static function sessionExpired($token)
    {
        try {
            SessionsHandler::sessionUpdate($token, 'state', 'SESSION_EXPIRED');
            SessionsHandler::sessionUpdate($token, 'active', 0);
            Cache::forget($token);
        } catch (\Exception $exception) {
           save_log('Error trying to expire session at sessionExpired(). Token:'.$token, json_encode($exception));
        }
    }

    public static function createSession($data)
    {
        $operator_key = $data['operator_key'];
        $game = $data['game'];
        $request_ip = $data['request_ip'];
        $game_mode = $data['mode'];
        $operator_key = $data['operator_key']; // ^ to change to operator ID
        $currency = $data['currency'];
        $player_operator_id = $data['player'];

        $collection = collect(Gameslist::short_list());
        $select_game = $collection->where('slug', $game)->where('active', 1)->first();

        if(!$select_game) { // Game not found or enabled
            $search_disabled = $collection->where('slug', $game)->where('active', 0)->first();
            if($search_disabled) {
                abort(400, "Game found, however this game is disabled - request_ip {$request_ip}");
            } else {
                abort(400, "Game not found - request_ip {$request_ip}");
            }
        }
        $owned_by = $data['ownedBy'] ?? '1';

        $extra_meta = [
            'provider' => $select_game['provider'],
            'launcher_behaviour' => config('casinodog.games.'.$select_game['provider'].'.launcher_behaviour'),
            'mode' => $game_mode,
        ];

        $player_id = hash_hmac('md5', $currency.'*'.$player_operator_id, $operator_key);
        //$invalidate_previous_init = self::invalidatePrev($player_operator_id, $operator_key);
        //if($invalidate_previous_init === false) { // Return error, as for some reason we were unable to invalidate previous sessions
        //    abort(500, "Critical error, please contact your account manager ASAP. Try using different player_id. - request_ip {$request_ip}");
        //     save_log('SessionsHandler::createSession()', $data);
        //}

        $session_object = array(
            'game_id' => $select_game['gid'],
            'player_id' => $player_id,
            'player_operator_id' => $player_operator_id,
            'operator_id' => $operator_key,
            'game_provider' => $select_game['provider'],
            'extra_meta' => json_encode($extra_meta),
            'currency' => $currency,
            'token_original' => 0,
            'token_original_bridge' => 0,
            'active' => true,
            'state' => 'SESSION_INIT',
            'request_ip' => $request_ip,
            'created_at' => now(),
            'updated_at' => now(),
        );

        $insert = ParentSessions::create($session_object);
        $session_id = $insert->id;

        $entry_signature = generate_sign($session_id);
        $session_url = env('APP_URL').'/g?token='.$session_id.'&entry='.$entry_signature.'&player_id='.$player_operator_id;

        if(config('casinodog.games.'.$select_game['provider'].'.custom_entry_path') !== 0) {
            $session_url = $game_controller->custom_entry_path($select_game['gid']).'?token='.$session_id.'&entry='.$entry_signature.'&player_id='.$player_operator_id;
        }
        return self::sessionUpdate($session_id, 'session_url', $session_url); //set user_agent from player to session
    }


    public static function invalidate_previous_sessions($current_session)
    {
        try {
            $current_session = $current_session['data']['session'];
            $player = $current_session['player_id'];
            $session_id = $current_session['id'];
            $game_id = $current_session['game_id'];

            $count = ParentSessions::where('player_id', $player)
            ->where('active', 1)
            ->where('game_id', $game_id)
            ->where('id', '!=', $session_id)
            ->where('state', 'SESSION_INIT')
            ->count();
            if($count > 0) {
                $all = ParentSessions::where('player_id', $player)
                ->where('active', 1)
                ->where('game_id', $game_id)
                ->where('id', '!=', $session_id)
                ->where('state', 'SESSION_INIT')
                ->get();

                if($count > 1) {
                    foreach($all as $session) {
                        $token = $session['id'];
                        SessionsHandler::sessionUpdate($token, 'state', 'SESSION_EXPIRED');
                        SessionsHandler::sessionUpdate($token, 'active', 0);
                        Cache::forget($token);
                     }
                } else {
                        $single = $all->first();
                        $token = $single['id'];
                        SessionsHandler::sessionUpdate($token, 'state', 'SESSION_EXPIRED');
                        SessionsHandler::sessionUpdate($token, 'active', 0);
                        Cache::forget($token);
                }
            }
        } catch (\Exception $exception) {
            save_log('SessionsHandler', 'Error trying to invalidate older sessions, this should never error. Investigate:'.$exception);
            return false;
        }
         return true;
    }

    public static function invalidatePrev($player)
    {
        try {
            ParentSessions::where('player_id', $player)
            ->where('active', 1)
            ->where('state', 'SESSION_INIT')
            ->update([
               'state' => 'SESSION_OVERRULE_INVALIDATION',
               'active' => 0,
            ]);
        } catch (\Exception $exception) {
            save_log('SessionsHandler', 'Error trying to invalidate older sessions at invalidatePrev() function, this should never error. Investigate: '.$exception);
            return false;
        }
         return true;
    }

    public static function sessionFindPreviousActive($player_id, $session_id, $game_id_original)
    {
        $find = ParentSessions::where('player_id', $player_id)
            ->where('game_id', $game_id_original)
            ->where('active', 1)
            ->where('id', '!=', $session_id)
            ->first();
        if(!$find) {
            return false;
        } else {
            return $find;
        }
    }

    public static function sessionData($session_id)
    {
        if(!is_uuid($session_id)) {
            return self::session_api_error_response("Session ID ({$session_id}) is not in UUID format.");
        }

        $retrieve_session_from_cache = Cache::get($session_id);
        if ($retrieve_session_from_cache) {
            $response_data = $retrieve_session_from_cache;
        } elseif(!$retrieve_session_from_cache) {
            $retrieve_session_from_database = ParentSessions::where('id', $session_id)->first();
            if($retrieve_session_from_database) {
                $collection = collect(Gameslist::short_list());
                $select_game = $collection->where('gid', $retrieve_session_from_database->game_id)->first();
                $response_data = array(
                    'status' => 'success',
                    'data' => array(
                        'session' => $retrieve_session_from_database,
                        'game_info' => $select_game,
                        'provider_info' => collect(Gameslist::provider_list())->where('pid', $select_game['provider'])->first(),
                        'cached_at' => now_nice(),
                    ),
                );
                $store_in_cache = Cache::put($session_id, $response_data, now()->addMinutes(20));
            }
        } else {
            return self::session_api_error_response("Session not found");
        }
        return $response_data ?? self::session_api_error_response("Session not found");
    }

    public static function session_api_error_response($message = NULL)
    {
        if($message === NULL) {
            $message = "No error message specified";
        }
        $error_response = array(
                'code' => 400,
                'status' => 'error',
                'data' => array(
                    'message' => $message,
                ),
        );
        return $error_response;
    }

    public static function sessionUpdate($session_id, $key, $newValue)
    {
        if(!is_uuid($session_id)) {
            return self::session_api_error_response("Session ID ({$session_id}) is not in UUID format.");
        }

        $retrieve_session_from_database = ParentSessions::where('id', $session_id)->first();

        if(!$retrieve_session_from_database) {
            //Session not found
            return false;
        }
        try {
            $new = ParentSessions::where('id', $session_id)->update([
                $key => $newValue
            ]);
        } catch (\Exception $exception) {
            save_log('SessionsHandler', 'Database error, most likely you are trying to update a non existing key/field, or cache is mismatched (session_id: '.$session_id.') - clearing this key. Investigate asap. Error: '.json_encode($exception));
            Cache::pull($session_id);
            return false;
        }
        $data = $retrieve_session_from_database;
        $data[$key] = $newValue;
        Cache::pull($session_id);
        //$store_in_cache = Cache::put($session_id, $data, now()->addMinutes(120));
        return self::sessionData($session_id);
    }

    public function enterSessionValidation(Request $request) {
        $validator = Validator::make($request->all(),
            [
                'entry' => ['required', 'min:10', 'max:100'],
                'token' => ['required', 'min:10', 'max:100'],
                'player_id' => ['required', 'min:3', 'max:100'],
            ]
        );
        if ($validator->fails()) {
            abort(400, $validator);
        }
    }
}
