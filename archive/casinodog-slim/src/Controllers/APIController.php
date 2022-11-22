<?php
namespace Wainwright\CasinoDog\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Wainwright\CasinoDog\CasinoDog;
use Wainwright\CasinoDog\Controllers\Game\SessionsHandler;
use Wainwright\CasinoDog\Controllers\Game\OperatorsController;
use Wainwright\CasinoDog\Traits\ApiResponseHelper;
use Wainwright\CasinoDog\Models\Gameslist;
use Illuminate\Support\Facades\Cache;
class APIController
{
   use ApiResponseHelper;

    public function providerslist_wainwright($providers, $count) {
        if($count < 2) {
            $games_count = Gameslist::where('provider', $providers[0]['slug'])->count();
            $providerslist = array(
                'id' => $providers[0]['slug'],
                'slug' => $providers[0]['slug'],
                'name' => ucfirst($providers[0]['name']),
                'parent' => NULL,
                'eligible_games' => $games_count,
                'icon' => 'ResponsiveIcon',
                'provider' => $providers[0]['provider'],
                'created_at' => now(),
                'updated_at' => now(),
            );
        } else {
        foreach($providers as $provider) {
            $games_count = Gameslist::where('provider', $provider['slug'])->count();
            $providerslist[] = array(
                'id' => $provider['slug'],
                'slug' => $provider['slug'],
                'name' => ucfirst($provider['slug']),
                'parent' => NULL,
                'eligible_games' => $games_count,
                'icon' => 'ResponsiveIcon',
                'provider' => $provider['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            );
        }
        }


        return $providerslist;
    }

    public function game_descriptions() {
        $cache_length = 300; // 300 seconds = 5 minutes

        if($cache_length === 0) {
            $game_desc = file_get_contents(__DIR__.'../../game_descriptions.json');
        }
        $game_desc = Cache::remember('gameDescriptions', 300, function () {
            return file_get_contents(__DIR__.'/../../game_descriptions.json');
        });
        $g2 = json_decode($game_desc, true);

        return $g2;
    }


    public function accessPingEndpoint(Request $request) {
        $validate = $this->operatorKeyValidation($request);
        if($validate->status() !== 200) {
            return $validate;
        }

        $prepareResponse = array('message' => 'Connection success.', 'request_ip' => $request->DogGetIP());
        return response()->json($prepareResponse, 200);
    }



   public function gamesListEndpoint(string $layout, Request $request)
   {
        //$validate = $this->operatorKeyValidation($request);
        //if($validate->status() !== 200) {
        //    return $validate;
        //}
        $games = collect(Gameslist::build_list());
        return $games;
        return $games;
   }

   public function meepEndpoint(Request $request)
   {
        if($request->action === 'toggle_respin') {
            if($request->operator_player_id) {
                $validate = $this->meepValidation($request);
                    if($validate->status() !== 200) {
                        return $validate;
                    }
                    $check_toggle = Cache::get($request->operator_player_id.'::respinToggled');
                    if($check_toggle) {
                        Cache::pull($request->operator_player_id.'::respinToggled');
                        $enabled = 0;
                    } else {
                        Cache::set($request->operator_player_id.'::respinToggled', '1');
                        $enabled = 1;
                    }
                    return $enabled;
            }
        }
   }

   public function promotionsEndpoint(Request $request)
   {
       $validate = $this->promotionsValidation($request);
       if($validate->status() !== 200) {
           return $validate;
       }

       $data = [
           'game' => $request->game,
           'currency' => $request->currency,
           'player' => $request->player,
           'operator_key' => $request->operator_key,
           'freespins_count' => $request->freespins_count,
           'freespins_betamount' => $request->freespins_count,
           'request_ip' => $request->DogGetIP(),
       ];
       $freebets = new \Wainwright\CasinoDog\Models\FreeSpins;
       return $freebets->add_freespins($request);
   }


   public function providersListEndpoint(Request $request)
   {
    $cache_length = 60;
    $limit = 25;
    if($request->limit) {
        if(is_numeric($request->limit)) {
            if($request->limit > 0) {
                if($request->limit > 100) {
                    $limit = (int) 100;
                } else {
                $limit = (int) $request->limit;
                }
            }
        }
    }

    $providers = collect(Gameslist::providers());
    return collect($this->providerslist_wainwright($providers, $limit))->paginate($limit);
   }


    public function createSessionIframed(Request $request)
    {
        $validate = $this->createSessionValidation($request);
        if($validate->status() !== 200) {
            return $validate;
        }

        $data = [
            'game' => $request->game,
            'currency' => $request->currency,
            'player' => $request->player,
            'operator_key' => $request->operator_key,
            'mode' => $request->mode,
            'request_ip' => $request->DogGetIP(),
        ];

        $session_create = SessionsHandler::createSession($data);
        if($session_create['status'] === 'success') {

            $data = [
                'session' => $session_create['message'],
                'ably' => [
                    'channel' => $session_create['message']['data']['token_internal'],
                    'key' => 'DnzkiQ.C6XmFg:IeY501QwXXAVDqIt6cOZCkjiXVbn0bD6ZJfi4Qsgzq8',
                ],
            ];

            return view('wainwright::iframed-view')->with('game_data', $data);
        } else {
            return $this->respondError($session_create);
        }
    }


    public function createSessionAndRedirectEndpoint(Request $request)
    {
        $validate = $this->createSessionValidation($request);
        if($validate->status() !== 200) {
            return $validate;
        }

        $data = [
            'game' => $request->game,
            'currency' => $request->currency,
            'player' => $request->player,
            'operator_key' => $request->operator_key,
            'mode' => $request->mode,
            'request_ip' => $request->DogGetIP(),
        ];

        $session_create = SessionsHandler::createSession($data);
        if($session_create['status'] === 'success') {
            return redirect($session_create['message']['session_url']);
        } else {
            return $this->respondError($session_create);
        }
    }

   public function createSessionEndpoint(Request $request)
    {
        $validate = $this->createSessionValidation($request);
        if($validate->status() !== 200) {
            return $validate;
        }
        $data = [
            'game' => $request->game,
            'currency' => $request->currency,
            'player' => $request->player,
            'operator_key' => $request->operator_key,
            'mode' => $request->mode,
            'request_ip' => $request->DogGetIP(),
        ];


        $session_create = SessionsHandler::createSession($data);
        if($session_create['status'] === 'success') {
            return response()->json($session_create, 200);
        } else {
            return $this->respondError($session_create);
        }
    }

    public function meepValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'operator_player_id' => ['required', 'min:3', 'max:100', 'regex:/^[^(\|\]`!%^&=};:?><’)]*$/'],
            'operator_key' => ['required', 'min:10', 'max:50'],
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            $errorReason = $validator->errors()->first();
            $prepareResponse = array('message' => $errorReason, 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        $operator_verify = OperatorsController::verifyKey($request->operator_key, $request->DogGetIP());
        if($operator_verify === false) {
                $prepareResponse = array('message' => 'Operator key did not pass validation.', 'request_ip' => $request->DogGetIP());
                return $this->respondError($prepareResponse);
        }

        return $this->respondOk();
    }

    public function operatorKeyValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'operator_key' => ['required', 'max:65', 'min:3'],
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            $errorReason = $validator->errors()->first();
            $prepareResponse = array('message' => $errorReason, 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        $operator_verify = OperatorsController::verifyKey($request->operator_key, $request->DogGetIP());
        if($operator_verify === false) {
                $prepareResponse = array('message' => 'Operator key did not pass validation. Make sure correct IP allowed is set.', 'request_ip' => $request->DogGetIP());
                return $this->respondError($prepareResponse);
        }

        return $this->respondOk();
    }

    public function promotionsValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'game' => ['required', 'max:65', 'min:3'],
            'player' => ['required', 'min:3', 'max:100', 'regex:/^[^(\|\]`!%^&=};:?><’)]*$/'],
            'currency' => ['required', 'min:2', 'max:7'],
            'operator_key' => ['required', 'min:10', 'max:50'],
            'freespins_count' => ['required', 'min:1', 'max:15'],
            'freespins_betamount' => ['required', 'min:1', 'max:15'],
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            $errorReason = $validator->errors()->first();
            $prepareResponse = array('message' => $errorReason, 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        $operator_verify = OperatorsController::verifyKey($request->operator_key, $request->DogGetIP());
        if($operator_verify === false) {
                $prepareResponse = array('message' => 'Operator key did not pass validation.', 'request_ip' => $request->DogGetIP());
                return $this->respondError($prepareResponse);
        }

        $operator_ping = OperatorsController::operatorPing($request->operator_key, $request->DogGetIP());
        if($operator_ping === false) {
            $prepareResponse = array('message' => 'Operator ping failed on callback.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        $freespins_count = (int) $request->freespins_count;
        if(!is_int($freespins_count)) {
            $prepareResponse = array('message' => 'freespins_count should be integer number amount of spins added.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }
        $freespins_betamount = (int) $request->freespins_betamount;
        if(!is_int($freespins_betamount)) {
            $prepareResponse = array('message' => 'freespins_betamount should be the bet in cents per spin, for example amount 20 ecquates to 0.20$.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        if($request->freespins_count < 5) {
            $prepareResponse = array('message' => 'freespins_count should be of a minimum of 5 and maximum 100.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        if($request->freespins_count > 100) {
            $prepareResponse = array('message' => 'freespins_count should be of a minimum of 5 and maximum 100.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        return $this->respondOk();
    }

    public function createSessionValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'game' => ['required', 'max:65', 'min:3'],
            'player' => ['required', 'min:3', 'max:100', 'regex:/^[^(\|\]`!%^&=};:?><’)]*$/'],
            'currency' => ['required', 'min:2', 'max:7'],
            'operator_key' => ['required', 'min:10', 'max:50'],
            'mode' => ['required', 'min:2', 'max:15'],
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            $errorReason = $validator->errors()->first();
            $prepareResponse = array('message' => $errorReason, 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        //$operator_verify = OperatorsController::verifyKey($request->operator_key, $request->DogGetIP());
        //if($operator_verify === false) {
        //        $prepareResponse = array('message' => 'Operator key did not pass validation.', 'request_ip' => $request->DogGetIP());
        //        return $this->respondError($prepareResponse);
        //}

        $operator_ping = OperatorsController::operatorPing($request->operator_key, $request->DogGetIP());
        if($operator_ping === false) {
            $prepareResponse = array('message' => 'Operator ping failed on callback.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        if($request->mode !== 'real') {
            $prepareResponse = array('message' => 'Mode can only be \'demo\' or \'real\'.', 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }
        return $this->respondOk();
    }
}
