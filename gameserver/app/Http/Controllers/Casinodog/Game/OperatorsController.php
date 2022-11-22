<?php
namespace App\Http\Controllers\Casinodog\Game;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\OperatorAccess;
use Illuminate\Support\Str;
use App\Http\Controllers\Casinodog\Game\SessionsHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;

class OperatorsController
{
    use GameKernelTrait;



	public static function createOperatorKey($data) {
    		$operator = new OperatorAccess();
    		$operator->operator_key = Str::orderedUuid();
    		$operator->operator_secret = Str::random(12);
    		$operator->operator_access = $data['operator_access'];
    		$operator->callback_url = $data['callback_url'];
    		$operator->ownedBy = $data['ownedBy'];
    		$operator->active = 1;
        $operator->last_used_at = now();
    		$operator->timestamps = true;
    		$operator->save();
    return $operator;
	}

	public static function operatorByKey($key)
	{
		$operator_query = Cache::get('operatorByKey:'.$key);
        if (!$operator_query) {
			$operator_query = OperatorAccess::where('operator_key', $key)->first();
			if(!$operator_query) {
				return false;
			} else {
				Cache::put('operatorByKey:'.$key, $operator_query, now()->addMinutes(15));
			}
		}
		$response = array('status' => 'success', 'data' => $operator_query);
		return $response;
	}

	public static function verifyKey($key, $ip) {
		if($ip === config('casino-dog.server_ip')) {
			$find = OperatorAccess::where('operator_key')->first();
		} elseif($ip === config('casino-dog.master_ip')) {
		$find = OperatorAccess::where('operator_key', $key)->first();
		} else {
		$find = OperatorAccess::where('operator_key', $key)->where('operator_access', $ip)->first();
		}
		if(!$find) {
			return false;
		}
		$response = array('status' => 'success', 'data' => $find);
		return $response;
	}

	public static function operatorPing($key, $ip) {
		$find = OperatorAccess::where('operator_key', $key)->first();
		if(!$find) {
			return false;
		}
		try {
		if($find->operator_access !== 'internal') {
			$salt_sign = Str::random(12);
			$query = [
				'action' => 'ping',
				'salt_sign' => $salt_sign,
			];
			$http = Http::timeout(5)->get($find->callback_url, $query);
			$pong_hash = hash_hmac('md5', $find->operator_secret, $salt_sign);
			$pong_hash_return = $http['data']['pong'];
			if($pong_hash !== $pong_hash_return) {
				save_log('OperatorsController()', 'Error ping, secret hash has does not allign', json_encode(array('Ping' => $pong_hash, 'Pong return' => $pong_hash_return)));
				return false;
			}
		}
		} catch(\Exception $e) {
			save_log('OperatorsController()', 'Error ping: '.$e->getMessage().' URL:'.$find->callback_url.'?action=ping&salt_sign='.$salt_sign);
			return false;
		}
		$response = array('status' => 'success', 'data' => $find);
		return $response;
	}

	public static function operatorCallbacks($session_key, $action, $game_data = NULL)
	{
		$session = SessionsHandler::sessionData($session_key);
		if($session === false) {
			save_log('OperatorsController()', 'Session not found while being asked to perform operator callback: '.json_encode($game_data));
			return false;
		}
    $session = $session['data']['session'];

		$operator_details = self::operatorByKey($session['operator_id']);
		if($operator_details === false) {
			save_log('OperatorsController()', 'Operator not found while gameplay is active.', $session);
			return false;
		}
		$callback = $operator_details['data']['callback_url'];
		if($action === 'balance') {
			$salt_sign = Str::random(12);
			$query = [
				'player_operator_id' => $session['player_operator_id'],
				'currency' => $session['currency'],
				'action' => 'balance',
				'sign' => hash_hmac('md5', $operator_details['data']['operator_secret'], $salt_sign),
				'salt_sign' => $salt_sign,
			];
			$callback_build = $callback.'?'.http_build_query($query);
			$http = Http::timeout(5)->get($callback_build);
			if(!$http->getStatusCode() === 200) {
				save_log('OperatorsController()', 'Error callback to '.$callback_build, json_encode($http));
				return false;
			} else {
				$decode = json_decode($http->getBody(), true);
				save_log('OperatorsController()', 'Succesfull callback '.$callback_build, $http->getBody());
        try {
				return $decode['data']['balance'];
        } catch(\Exception $e) {
            save_log('OperatorsController()', 'No balance found: '.json_encode($decode));
            return $decode;
        }
			}
		} elseif($action === 'game') {
			$salt_sign = Str::random(12);
			$query = [
				'player_operator_id' => $session['player_operator_id'],
				'currency' => $session['currency'],
				'action' => 'game',
				'sign' => hash_hmac('md5', $operator_details['data']['operator_secret'], $salt_sign),
				'salt_sign' => $salt_sign,
				'game' => $session['game_id'],
				'bet' => $game_data['bet'],
				'win' => $game_data['win'],
				'currency' => $session['currency'],
			];
			$http = Http::timeout(5)->get($callback, $query);

			if(!$http->status() === 200) {
				save_log('OperatorsController()', 'Error callback to '.$callback.' with query'.json_encode($query));
				return false;
			} else {
        try {
				$decode = json_decode($http, true);
				return $decode['data']['balance'];
        } catch(\Exception $e) {
            save_log('OperatorsController()', 'Error ('.$e->getMessage().') callback to '.$callback.' with query'.json_encode($query).' response: '.json_encode($decode));
            abort(400, $e->getMessage());
        }
			}
		}
		return $callback;
	}


}
