<?php
namespace App\Http\Controllers\Casinodog\Game\Playson;

use Illuminate\Support\Facades\Http;
use Wainwright\CasinoDog\Facades\ProxyHelperFacade;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class PlaysonGame extends PlaysonMain
{
    use GameKernelTrait;

    public function replace_game_event($gid, $type)
    {
    }
    
    public function game_event(Request $request)
    {
        $internal_token = $request->internal_token;
        $data = $request->getContent();
        $select_session = $this->get_internal_session($internal_token)['data'];


        $response = $this->curl_request($request);
        
        $command = $this->in_between('command="', '"', $response);
        $origin_session = $this->in_between('session="', '"', $response);
        $balance_origin_old = Cache::get($internal_token.'::playsonOriginalBalance::'.$origin_session);
        if(!$balance_origin_old) {
            $start_balance = 1010000;
            Cache::set($internal_token.'::playsonOriginalBalance::'.$origin_session, (int) $start_balance);
            $balance_origin_old = (int) Cache::get($internal_token.'::playsonOriginalBalance::'.$origin_session);
        }



        if($command === 'bet' || $command === 'bonus') {
            $balance_origin_new = $this->in_between('user_new cash="', '"', $response);
            $balance_origin_old = (int) Cache::get($internal_token.'::playsonOriginalBalance::'.$origin_session);
            
            $winAmount = $this->in_between('cash-win="', '"', $response);
            $betAmount = $this->in_between('cash-bet="', '"', $response);
    
                //$winAmount = 100;
                $check_toggle = Cache::get($select_session['player_operator_id'].'::respinToggled');
                Cache::set($internal_token.'::playsonOriginalBalance::'.$origin_session, (int) $balance_origin_new);               


                if($check_toggle) {
                if($winAmount > 5550) {
                    if($command === 'bet') {
                        $respin_data = $this->retrieve_game_respins_template($select_session['game_id'], 'normal');
                        if($respin_data !== NULL) {
                        $change_data = $this->replaceInBetweenDataset('session="', '"', $data, $respin_data);
                        $change_data = $this->replaceInBetweenDataset('prnd="', '"', $data, $change_data);
                        $change_data = $this->replaceInBetweenDataset('rnd="', '"', $data, $change_data);
                        $change_data = $this->replaceInBetweenDataset('cash-bet="', '"', $data, $change_data);
                        $change_data = $this->replaceInBetweenDataset('cash-bet-game="', '"', $data, $change_data);
                        $balance = $this->get_balance($internal_token);
                        $bet_amount = $this->in_between('bet_cash="', '"', $request->getContent());
                        $process_and_get_balance = $this->process_game($internal_token, ($bet_amount ?? 0), 0, $change_data);
                        $change_data = $this->replaceInBetweenValue('user_new cash="', '"', $change_data, $process_and_get_balance);
                        return $change_data;
                        }
                    }
                }
                }

                $process_and_get_balance = $this->process_game($internal_token, ($betAmount ?? 0), ($winAmount ?? 0), $response);
                $win_amount = $this->in_between('cash-win="', '"', $response);

                if($win_amount === "0") {
                    if($command === 'bonus') {
                        //add here to save bonus games to the 'respin templates' model like below if you want to replace bonus individual spins
                    }
                    if($command === 'bet') {
                        if($this->in_between('current_state="', '"', $response) !== 'bonus') {
                            $this->save_game_respins_template($select_session['game_id'], $response, 'normal');
                        }
                    }                   
                }
            $final = str_replace($balance_origin_new, $process_and_get_balance, $response);

            return $final;
        }
        $balance_origin_new = $this->in_between('user_new cash="', '"', $response);

            $final = str_replace($balance_origin_new, $this->get_balance($internal_token), $response);
            return $final;
    }

    public function curl_request(Request $request)
    {
        $url = $_SERVER['REQUEST_URI'];
        $exploded_url = explode('?', $url);
        $final_url = 'https://playsonsite-prod.ps-gamespace.com/gameserver/cgi/server.cgi?'.$exploded_url[1];

        $data = $request->getContent();

        $response = Http::retry(1, 1500, function ($exception, $request) {
            return $exception instanceof ConnectionException;
        })->withBody(
            $data, 'application/x-www-form-urlencoded'
        )->post($final_url);

        return $response;
    }


    public function replaceInBetweenDataset($a, $b, $replace_from_data, $replace_in_data)
    {
        $value_from = $this->in_between($a, $b, $replace_from_data);
        $value_in = $this->in_between($a, $b, $replace_in_data);
        return str_replace($value_in, $value_from, $replace_in_data);
    }

    public function replaceInBetweenValue($a, $b, $data, $value)
    {
        $value_from = $this->in_between($a, $b, $data);
        return str_replace($value_from, $value, $data);
    }
    


}

