<?php
namespace App\Http\Controllers\Casinodog\Game;
use Illuminate\Contracts\Support\Arrayable;
use App\Http\Controllers\Casinodog\Game\SessionsHandler;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Casinodog\Game\OperatorsController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Models\Gameslist;

class GameKernel
{


    public function normalized_array($data, int $code = null, string $message = null): array {
        $data ??= [];
        $code ??= 200;
        $status = 'success';
        if($code !== 200) {
            $status = 'error';
        }

        $return_data = [
                'code' => (int) $code,
                'status' => $status,
                'data' => $data,
            ];
        return $this->to_array($return_data);
    }

    public function random_uuid()
    {
        return Str::Uuid();
    }

    public function getIp($request) {
        $kernel_casinodog = new \Wainwright\CasinoDog\CasinoDog;
        $get_ip = $kernel_casinodog->getIp($request);
        return $get_ip;
    }

    public function normalized_json($data, int $status_code = null, string $message = null): JsonResponse
    {
        $data ??= [];
        $status_code ??= 200;
        $array = $this->normalized_array($data, $status_code, $message);

        return response()->json($array, $status_code);
    }

    public function update_session($token_internal, $key, $value) {
        $session = $this->get_internal_session($token_internal);
        if($session['status'] === 'success') {
            $session = SessionsHandler::sessionUpdate($token_internal, $key, $value); //update session table
            return $this->normalized_array($this->to_array($session), 200, 'success');
        } else {
            return $this->normalized_array($this->to_array($session ?? NULL), 404, 'Session not found');
        }
    }

    public function in_between($a, $b, $data)
    {
        preg_match('/'.$a.'(.*?)'.$b.'/s', $data, $match);
        if(!isset($match[1])) {
            return false;
        }
        return $match[1];
    }

    public function get_internal_session(string $id, string $select_data = NULL) {
        $select_session = SessionsHandler::sessionData($id);
        try {
        if($select_session['status'] === 'success') {
            if($select_data !== NULL) {
                $specify_data = $select_session['data'][$select_data];
                return $this->normalized_array($this->to_array($specify_data), 200, "n/a");
            }
            return $this->normalized_array($this->to_array($select_session['data']), 200, "n/a");
        } else {
            return $this->normalized_array($this->to_array($select_session['data']), 400, 'Session not found');
        }
        } catch(\Exception $e) {
            $message = "{$id} session ID - " . $e->getMessage();
            abort(400, $message);
        }
    }
    public function fail_internal_session(string $token) { // session fail, expire session
        $session = $this->get_internal_session($token);
        if($session['status'] === 200) {
            $session_fail = SessionsHandler::sessionFailed($token);
            return $this->normalized_array($this->to_array($session['data']), 200, json_encode($session_fail));
        } else {
            return $this->normalized_array($this->to_array($session['data'] ?? NULL), 404, 'Session not found');
        }
    }

    public function get_balance($internal_token, $type = NULL):int
    {
        $type = 'internal';
        $data = [
            'game_data' => 'balance_call',
        ];
        $balance = OperatorsController::operatorCallbacks($internal_token, 'balance', $data);
        return (int) $balance;
    }

    public function process_game($internal_token, $betAmount, $winAmount, $game_data, $type = NULL):int
    {
        $type = 'internal';
        $data = [
            'bet' => $betAmount,
            'win' => $winAmount,
            'game_data' => $game_data,
        ];
        $balance = OperatorsController::operatorCallbacks($internal_token, 'game', $data);
        return (int) $balance;
    }

    public function expire_internal_session(string $token) {
        $session = $this->get_internal_session($token);
        if($session['status'] === 200) {
            $session_expired = SessionsHandler::sessionExpired($token);
            return $this->normalized_array($this->to_array($session['data']), 200, json_encode($session_expired));
        } else {
            return $this->normalized_array($this->to_array($session ?? NULL), 404, 'Session not found');
        }
    }

    public function build_response_query($query)
    {
        $resp = http_build_query($query);
        $resp = urldecode($resp);
        return $resp;
    }

    public function build_query($query)
    {
        $resp = http_build_query($query);
        $resp = urldecode($resp);
        return $resp;
    }

    public function parse_query($query_string)
    {
        parse_str($query_string, $q_arr);
        return $q_arr;
    }

    public function find_previous_active_session(string $token) {
        $session = $this->get_internal_session($token);
        $session_data = $session['data']['session'];

        $select_session = SessionsHandler::sessionFindPreviousActive($session_data['player_id'], $session_data['id'], $session_data['game_id']);
        if(isset($select_session['data']['session'])) {
            return $this->normalized_array($this->to_array($select_session['data']), 200, "n/a");
        } else {
            return $this->normalized_array($this->to_array($select_session), 404, 'Session not found');
        }
    }

    public function to_array($data)
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }
        return $data;
    }

    public function get_gameslist()
    {
        return Gameslist::short_list();
    }

    public function proxy_json_softswiss(string $url)
    {
        $proxy = new \Wainwright\CasinoDog\Controllers\ProxyController();
        return $proxy->launch_job('json_softswiss', $url);
    }

    public function proxy_game_session_static(string $url)
    {
        $host = $this->get_host($url);
        $allowedhosts = $host.',www.'.$host;
        $proxy = new \Wainwright\CasinoDog\Controllers\ProxyController();
        return $proxy->launch_job('game_session_static', $url, $allowedhosts);
    }

    public function get_host($url)
    {
        $url = urldecode($url);
        $parse = parse_url($url);
        $host = preg_replace('/^www\./', '', $parse['host']);
        return $host;
    }

    public function encrypt_string($string)
    {
         $encrypted = Crypt::encryptString($string);
         return $encrypted;
    }

    public function decrypt_string($string)
    {
         $decrypt= Crypt::decryptString($string);
         return $decrypt;
    }

    public function check_freespin_state($internal_token) {
        $session = $this->get_internal_session($internal_token);
        if($session['status'] === 200) {
            $session = $this->to_array($session['data']);
            $player_id = $session['player_id'];
            $player_operator_id = $session['player_operator_id'];
            $game_id = $session['game_id'];
            $game_provider = $session['game_provider'];
            $operator_id = $session['operator_id'];

            $frb = new \Wainwright\CasinoDog\Models\FreeSpins;
            $find_frb = $frb->where('player_id', $player_id)->where('game_id', $game_id)->where('operator_key', $operator_id)->where('active', true)->first();
            if($find_frb) {
                return $this->normalized_array($find_frb, 200, 'Free spins found.');

            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    public function freespin_state_completed($frb) {
            $frb = new \Wainwright\CasinoDog\Models\FreeSpins;
            $find_frb = $frb->where('id', $frb['id'])->update([
                "active" => false
            ]);
            if($find_frb) {
                return $this->normalized_array($find_frb, 200, 'Free spins disabled.');
            } else {
                return NULL;
            };
    }
}
