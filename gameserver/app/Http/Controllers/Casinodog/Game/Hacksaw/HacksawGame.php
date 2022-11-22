<?php
namespace App\Http\Controllers\Casinodog\Game\Hacksaw;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Facades\ProxyHelperFacade;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Controllers\Casinodog\Game\GameKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use App\Http\Controllers\Casinodog\Game\OperatorsController;

class HacksawGame extends HacksawMain
{
    use GameKernelTrait;

    public function game_event($request)
    {
        $internal_token = $request->internal_token;
        $select_session = $this->get_internal_session($internal_token)['data'];

        $url = str_replace('games/hacksaw/'.$internal_token.'/'.$select_session['game_id_original'].'/', '', $request->fullUrl());
        $game_id_origin = $request->segment(4);
        $action = NULL;
        if(str_contains($url, 'authenticate')) {
            $action = "authenticate";
            $url = 'https://rgs-demo.hacksawgaming.com/api/play/authenticate';
        } elseif(str_contains($url, 'gameInfo')) {
            $action = "gameInfo";
            $url = 'https://rgs-demo.hacksawgaming.com/api/meta/gameInfo?gameId='.$game_id_origin.'&currency=EUR&rm=96&jurisdiction=curacao';
        } elseif(str_contains($url, 'gameLaunch')) {
            $action = "gameLaunch";
            $url = 'https://rgs-demo.hacksawgaming.com/api/play/gameLaunch';
        } elseif(str_contains($url, 'bet')) {
            $action = "bet";
            $url = 'https://rgs-demo.hacksawgaming.com/api/play/bet';
        } elseif(str_contains($url, 'keepAlive')) {
            $action = "keepAlive";
            $url = 'https://rgs-demo.hacksawgaming.com/api/play/keepAlive';
        }


        $send_request = $this->curl_request($url, $request);
        $data_origin = json_decode($send_request->getContent(), true);

        if($action === "authenticate") { // store game info when player connects
            
            /* Script to "replay" event (for bonus continued play when player disconnects and reconnects while in-play)
            
            $stored_events = Cache::get('stored_events::'.$select_session['player_id'].$select_session['game_id']);
            Cache::forget('stored_events::'.$select_session['player_id'].$select_session['game_id']);

            if($stored_events) {
                $bets_gamestate = Cache::get('stored_gamestate::'.$select_session['player_id'].'::'.$select_session['game_id']);

                $data_origin['gameState'] = [
                    $bets_gamestate,
                    "gameData" => NULL,
                ];
                $data_origin['roundId'] = $stored_events['round']['roundId'];
                $data_origin['roundStatus'] = "wfwpc";


                $data_origin['events'] = $stored_events['round']['events'];

            }
            */

            $delayed_win = Cache::get('delayed_win::'.$select_session['player_id']);
            if($delayed_win) {
                    $process_game = $this->process_game($internal_token, 0, (int) $delayed_win, $data_origin);
                    $data_origin['accountBalance']['balance'] = $process_game;
                    Cache::pull('delayed_win::'.$select_session['player_id']);
            }
            if(isset($data_origin['bonusGames'])) {
                if(isset($data_origin['bonusGames'][1])) {
                    foreach($data_origin['bonusGames'] as $bonus) {
                        Cache::set($select_session['game_id_original'].'-'.$bonus['bonusGameId'].'-betCostMultiplier', $bonus['betCostMultiplier']);
                    }
                } else {
                    Cache::set($select_session['game_id_original'].'-'.$data_origin['bonusGames'][0]['betCostMultiplier'].'-betCostMultiplier', $data_origin['bonusGames'][0]['betCostMultiplier']);
                }
            }
        }

        if ($request->isMethod('post')) {
            //$data_origin['sessionUuid'] = $internal_token;
            $balance_call_needed = 1;
            $data_origin['name'] = $internal_token;
            $data_origin['displaySessionTimer'] = true;
            $data_origin['displayNetPosition'] = true;
            $data_origin['dog'] = [];
            $data_origin['dog']['action'] = $action;
            $data_origin['accountBalance']['currencyCode'] = "USD";
            if($request->bets) {
                $bets = $request->bets;
                $bet = (int) $bets[0]['betAmount'];
                $request_to_array = $request->toArray();
                $betsize = $request_to_array['bets'][0]['betAmount'];
                $bonus_id = 0;
                if(isset($request_to_array['bets'][0]['buyBonus'])) { // check if normal spin or a "buy bonus feature"
                    $amount = $request_to_array['bets'][0]['betAmount'];
                    $bonus_id = $request_to_array['bets'][0]['buyBonus'];
                    $cost_multiplier = Cache::get($select_session['game_id_original'].'-'.$bonus_id.'-betCostMultiplier'); //the cost multiplier is specified in the "authenticate" call when player connects, this should be stored above ^
                    $bet = (int) ($cost_multiplier * $amount);
                }
                if($bet > 0) {
                    $balance_call_needed = 0;
                    $process_game = $this->process_game($internal_token, $bet, 0, $data_origin);
                    $data_origin['accountBalance']['balance'] = $process_game;
                }
            }
            if($request->continueInstructions) {
                Cache::pull('stored_events::'.$select_session['player_id'].$select_session['game_id']);
                $delayed_win = Cache::pull('delayed_win::'.$select_session['player_id']);
                if($delayed_win) {
                    $process_game = $this->process_game($internal_token, 0, (int) $delayed_win, $data_origin);
                    $data_origin['accountBalance']['balance'] = $process_game;
                }
            }

            $bonus_feature_detected = 0;
            if(isset($data_origin['round'])) {
                if(isset($data_origin['round']['events'])) {
                    if(isset($data_origin['round']['events'][0])) {
                        if(isset($data_origin['round']['events'][1])) { // if more then 1 "event" (basically reel results) it indicates bonus feature is triggered
                            $bonus_feature_detected = 1;
                            $bonus_select_win = $data_origin['round']['events'][array_key_last($data_origin['round']['events'])]; //select the last "event"
                            $win = (int) $bonus_select_win['awa']; //total win is stored in 'awa' and tally's up each event, hence we get this from the last event
                            
                            
                            // $this->replace_bonus_events($data_origin); 
                            // $data_origin['replaced_bonus_old_win'] = $win; // let's store the old total amount won event, just for debugging purposes to see within result "our gain" by replacing eligible spins
                            // $bonus_select_win = $data_origin['round']['events'][array_key_last($data_origin['round']['events'])]; // re-iterate the awa amount to process
                            // $win = $bonus_select_win['awa']; //total bonus win
                            
                            if($bonus_id !== 0) { //buy-bonus detected
                                if($win < $bet) {
                                    if($win < ($bet * 0.75)) {
                                        $data_origin['dog']['gametype'] = $bonus_id;
                                        $data_origin['dog']['round_betsize'] = $request_to_array['bets'][0]['betAmount'];
                                        $this->save_game_respins_template($select_session['game_id'], json_encode($data_origin), $bonus_id);
                                        $data_origin['dog']['respin_saved'] = 1;
                                    }
                                } else {
                                    $respin_data = $this->retrieve_game_respins_template($select_session['game_id'], $bonus_id);
                                    if($respin_data !== NULL) {
                                        $respin_decode = json_decode($respin_data, true);
                                        $counter = 0;
                                        $round_betsize = $respin_decode['dog']['round_betsize'];
                                        $ratio = $betsize / $round_betsize;
                                        $respin_decode['dog']['respin'] = 1;
                                        $respin_decode['dog']['original_betsize'] = $betsize;
                                        $respin_decode['dog']['respin_betsize'] = $round_betsize;
                                        $respin_decode['dog']['ratio_betsize'] = $ratio;
                                        
                                        if($betsize !== $round_betsize) {
                                            foreach($respin_decode['round']['events'] as $event) {
                                                $respin_decode['round']['events'][$counter]['awa'] = (int) $event['awa'] * $ratio;
                                                $respin_decode['round']['events'][$counter]['wa'] = (int) $event['wa'] * $ratio;
                                                if(isset($event['c']['actions'])) {
                                                    if(isset($event['c']['actions'][1])) {
                                                        $counter_actions = 0;
                                                        foreach($event['c']['actions'] as $actions) {
                                                            if(isset($actions['data'])) {
                                                                if(isset($actions['data']['winAmount'])) {
                                                                    $respin_decode['round']['events'][$counter]['c']['actions'][$counter_actions]['data']['winAmount'] =  $actions['data']['winAmount'] * $ratio;
                                                                    $counter_actions++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                $counter++;
                                            }
                                        }

                                        $bonus_select_win = $data_origin['round']['events'][array_key_last($data_origin['round']['events'])]; //select the last "event"
                                        $win = $bonus_select_win['awa']; //total win is stored in 'awa' and tally's up each event, hence we get this from the last event
                                        $respin = true;
                                        $respin_decode['round']['roundId'] = $data_origin['round']['roundId'];
                                        $balance_call_needed = 1;
                                        $data_origin = $respin_decode;
                                        $data_origin['dog']['gametype'] = $bonus_id;
                                        $data_origin['dog']['respin_old_win'] = $win;
                                        $data_origin['dog']['respin_bonusfeature'] = 1;
                                        $bonus_select_win = $data_origin['round']['events'][array_key_last($data_origin['round']['events'])]; 
                                        $win = (int) $bonus_select_win['awa']; 
                                        $data_origin['dog']['respin_new_win'] = $win;
                                    }
                                }
                                    /* Store event for continued play when player reconnects, regardless the balance is actually added 
                                    Cache::set('stored_events::'.$select_session['player_id'].$select_session['game_id'], $data_origin);
                                    $game_state = [
                                        "bet" => [
                                                    "betAmount" => $betsize,
                                                    "betCoins" => "0",
                                                    "customBetData" => NULL,
                                                    "maxExposure" => "30000000",                                            
                                                    "betCoinValue" => "0",
                                                    "betLines" => "0",
                                                    "buyBonus" => $bonus_id,
                                                    "cheatCode" => "",
                                                    "buyBonusMultiplier" => $cost_multiplier,
                                        ],
                                    ];
                                    Cache::set('stored_gamestate::'.$select_session['player_id'].'::'.$select_session['game_id'], $game_state);
                                    */

                            } else {

                                
                            }
                        } else { // normal game
                            $win = (int) $data_origin['round']['events'][0]['awa'];
                        }
                        if($win > 0) {
                            if($bonus_feature_detected === 1) { // store the game result on "bonus" games, player will send respin event
                                $find_previous_wins = Cache::get('delayed_win::'.$select_session['player_id']);
                                if($find_previous_wins) {
                                    $old_win = (int) $find_previous_wins;
                                    Cache::set('delayed_win::'.$select_session['player_id'], (int) $win + $old_win);
                                } else {
                                    Cache::set('delayed_win::'.$select_session['player_id'], (int) $win);
                                }
                            } else {
                                if($win > 1 and $bonus_feature_detected === 0) { // replace "normal" game result by respin template
                                    $data_origin = $this->normal_respin($data_origin, $select_session);
                                    $win = (int) $data_origin['round']['events'][0]['awa'];
                                    $process_game = $this->process_game($internal_token, 0, $win, $data_origin);
                                    $balance_call_needed = 1;
                                    Cache::pull('delayed_win::'.$select_session['player_id']);
                                } else {
                                    $balance_call_needed = 0;
                                    $process_game = $this->process_game($internal_token, 0, $win, $data_origin);
                                    $data_origin['accountBalance']['balance'] = $process_game;
                                }
                            }
                        } else {
                            if($bonus_feature_detected === 0) { // save "normal" game result as respin template (because win = 0)
                                $this->save_game_respins_template($select_session['game_id'], json_encode($data_origin), 'normal');
                            }
                        }
                    }
                }
            }
            if($balance_call_needed === 1) {
            $data_origin['accountBalance']['balance'] = $this->get_balance($internal_token);
            }

            return $data_origin;
        }
        return $send_request;
    }

    public function normal_respin($data_origin, $select_session) { // normal "gametype" respin trigger
        $respin_data = $this->retrieve_game_respins_template($select_session['game_id'], 'normal');
        if($respin_data !== NULL) {
            $respin = true;
            $respin_decode = json_decode($respin_data, true);
            $data = json_encode($data_origin);
            $respin_decode['round']['roundId'] = $data_origin['round']['roundId'];
            $respin_decode['dog']['respin'] = 1;
            return $respin_decode;
        } else {
            $data_origin['dog']['respin_failed'] = 1;
            return $data_origin;
        }
    }

    public function replace_bonus_events($data_origin)
    {
        $total_events = 0;
        $spins_wins = 0;
        foreach($data_origin['round']['events'] as $bonus_spin) { // let's first loop through and save all bonus spins with 0 win outcome, these we can replace the winning bonus spins by in a loop after
            if($bonus_spin['wa'] === "0") {
                if(isset($bonus_spin['c']['bonus']['life'])) {
                    if($bonus_spin['c']['bonus']['life'] > "1") {
                        Cache::set('bonus_loss_'.$data_origin['round']['roundId'].$bonus_spin['etn'], $bonus_spin['c'], now()->addMinutes(3));
                    }
                } else {
                    Cache::set('bonus_loss_'.$data_origin['round']['roundId'].$bonus_spin['etn'], $bonus_spin['c'], now()->addMinutes(3));
                }
            } else {
                $spins_wins++;
            }
            $total_events++;
        }
        $counter = 0;
        $deduct_amount = 0;
        if($spins_wins > 1) { // if there are more then 1 spin wins we can trigger the 'respinning' feature (replacing wins by losses), reason is that hacksaw expects always atleast some win amount
            $data_origin['round']['new_events'] = [];
            $stop_new_array = 0;
                foreach($data_origin['round']['events'] as $bonus_spin) { // let's loop through events, now to replace wins
                    if($bonus_spin['wa'] > "100") {
                        if($counter !== $total_events) { //let's never replace the final spin for proper transition to score screen
                            $replacement_grid = Cache::get('bonus_loss_'.$data_origin['round']['roundId'].$bonus_spin['etn']);
                            if($replacement_grid) { // yay, we've "farmed" a loss eligible to replace win in the last loop
                                if(isset($bonus_spin['c']['bonus']['life'])) { // "bonus life" is feature common in bonus games on hacksaw
                                    if($bonus_spin['c']['bonus']['life'] > "1") { // let's not replace the "final" life spin as we shouldn't have farmed a proper replacement loss anyway for this
                                        $replacement_grid['bonus']['life'] = $bonus_spin['c']['bonus']['life']; // let's replace the life counter in the replacement game by the real game result life counter
                                        $deduct_amount = (int) $deduct_amount + $bonus_spin['wa'];
                                        $data_origin['round']['events'][$counter]['c'] = $replacement_grid;
                                        $data_origin['round']['events'][$counter]['wa'] = "0";
                                        $data_origin['round']['events'][$counter]['replaced'] = "1";
                                        $data_origin['round']['events'][$counter]['replacement_amount'] = $bonus_spin['wa'];
                                    }
                                } else { // bonus life feature not detected so just replacing with replacement grid
                                    $deduct_amount = (int) $deduct_amount + $bonus_spin['wa'];
                                    $data_origin['round']['events'][$counter]['c'] = $replacement_grid;
                                    $data_origin['round']['events'][$counter]['wa'] = "0";
                                    $data_origin['round']['events'][$counter]['replaced'] = "1";
                                    $data_origin['round']['events'][$counter]['replacement_amount'] = $bonus_spin['wa'];
                                }
                            }
                        }
                    }
                    $data_origin['round']['events'][$counter]['awa'] = $bonus_spin['awa'] - $deduct_amount; // we still are replacing the "awa" (which is the TOTAL win count up till that point) even when not replacing the game result in event
                    $counter++;
                }
        }
        return $data_origin;
    }

    public function curl_request($url, $request)
    {
        $resp = ProxyHelperFacade::CreateProxy($request)->toUrl($url);

        return $resp;
    }
}
