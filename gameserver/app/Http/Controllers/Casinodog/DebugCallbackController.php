<?php
namespace App\Http\Controllers\Casinodog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use App\Models\DebugCallbackBalances;

class DebugCallbackController
{
    protected $operator_key;
    public function __construct()
    {
        $this->operator_key = config('casinodog.debug_callback.api_key');
        $this->operator_secret = config('casinodog.debug_callback.api_secret');
        $this->active = (boolean) (config('casinodog.debug_callback.active'));
        if(!$this->active) {
            abort(400, "Debug callback mode is inactive.");
        }
    }

    public function handle(Request $request)
    {
        if($request->action === 'ping') {
            return $this->pong($request);
        }

        if($request->action === 'balance') {
            return $this->balance($request);
        }

        if($request->action === 'game') {
            return $this->game($request);
        }

        abort(403, 'Empty action.');
    }

    public function verify_sign($sign, $salt, $request)
    {
        $create_signature = hash_hmac('md5', $this->operator_secret, $salt); //recreate the signature
        if($create_signature === $sign) {
            return true;
        } else {
            save_log('DebugCallbackController()', 'Wrong security signature on callback. '.json_encode($request->all()));
            die();
        }
    }

    public function pong(Request $request)
    {
        $pong_hash = hash_hmac('md5', $this->operator_secret, $request->salt_sign);
        $data = [
            'status' => 200,
            'data' => [
                'pong' => $pong_hash,
            ],
        ];
        return response()->json($data, 200);
    }

    public function balance(Request $request)
    {
        $player = new DebugCallbackBalances;
        $select_player = $player->select_player($request->player_operator_id, $request->currency);

        $data = [
            'status' => 200,
            'data' => [
                'balance' => (int) $select_player->balance,
            ],
        ];
        return response()->json($data, 200);
    }


    public function game(Request $request)
    {
        save_log('DebugCallbackController', 'Callback received: '.json_encode($request->all()));
        $player = new DebugCallbackBalances;
        $this->verify_sign($request->sign, $request->salt_sign, $request);
        $balance_after_game = $player->process_game($request->player_operator_id, $request->bet, $request->win, $request->currency, $request->game, $request->all());
        $select_player = $player->select_player($request->player_operator_id, $request->currency);

        $data = [
            'status' => 200,
            'data' => [
                'balance' =>  (int) $select_player->balance,
            ],
          ];
          return response()->json($data, 200);
    }


}
