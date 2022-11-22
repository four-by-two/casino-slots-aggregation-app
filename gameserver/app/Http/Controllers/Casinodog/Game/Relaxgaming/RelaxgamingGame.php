<?php
namespace App\Http\Controllers\Casinodog\Game\Relaxgaming;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Wainwright\CasinoDog\Facades\ProxyHelperFacade;
use App\Http\Controllers\Casinodog\Game\GameKernelTrait;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Controllers\Casinodog\Game\GameKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use App\Http\Controllers\Casinodog\Game\OperatorsController;

class RelaxgamingGame extends RelaxgamingMain
{
    use GameKernelTrait;

    public function game_event($request)
    {   
        $action = $request->action;
        $internal_token = $request->internal_token;
        //$real_gameserver_url = 'https://dev-casino-client.api.relaxg.net/game/'.$action;
        $real_gameserver_url = 'https://stag-casino-client.api.relaxg.net/game/'.$action;
        //$real_gameserver_url = 'https://iomeu-casino-client.api.relaxg.com/game/'.$action;
        $select_session = $this->get_internal_session($internal_token)['data'];

        $real_response = $this->curl_request($real_gameserver_url, $request);
        $data_origin = json_decode($real_response->getContent(), true);
        if($action === 'funmoneylogin') {
            $data_origin['stats']['currency'] = 'USD';
            $data_origin['stats']['b'] = (int) $this->get_balance($internal_token);
            $delayed_win_payout = $this->delayed_win($internal_token, $select_session['player_id']);
            if($delayed_win_payout === true) {
                $data_origin['stats']['b'] = $this->get_balance($internal_token);
            }
            if(isset($data_origin['config'])) {
                if(isset($data_origin['config']['buyFeatureCost'])) {
                    Cache::put($select_session['game_id_original'].'-buyFeatureCost-betCostMultiplier', $data_origin['config']['buyFeatureCost']);
                }
            }
        }

        if($action === 'gamefinished') {
            //$delayed_win_payout = $this->delayed_win($internal_token, $select_session['player_id']);
            //if($delayed_win_payout === true) {
            //    $data_origin['updated'] = true;
            //    $data_origin['delayed_win_credited'] = true;
            //    $data_origin['stats'] = [];
            //    $data_origin['stats']['b'] = $this->get_balance($internal_token);
            //}
        }

        if($action === 'play') {
            //$data_origin['sessionUuid'] = $internal_token;
            $balance_call_needed = 1;
            $data_origin['name'] = $internal_token;
            $data_origin['displaySessionTimer'] = true;
            $data_origin['displayNetPosition'] = true;
            $data_origin['dog'] = [];
            $data_origin['dog']['action'] = $action;
            $data_origin['stats']['currency'] = $select_session['currency'];
            if($request->ga === "spin") {
                $bet = (int) $request->ba;
                $request_to_array = $request->toArray();
                $betsize = $bet;
                $bonus_id = 0;
                if($bet > 0) {
                    $balance_call_needed = 0;
                    $process_game = $this->process_game($internal_token, $bet, 0, $data_origin);
                    $data_origin['stats']['b'] = $process_game;
                }
            }

            $bonus_feature_detected = 0;
            if($data_origin['subgameTriggered'] === true) {
                $bonus_feature_detected = 1;
                $win = (int) $data_origin['win']; //total win is stored in 'awa' and tally's up each event, hence we get this from the last event
                $data_origin['dog']['win'] = $win;
                $data_origin['dog']['bet'] = $bet;

                    if(isset($request_to_array['restoreState'])) {
                        $data_origin['dog']['gametype'] = $request_to_array['restoreState']['mode'];
                        $bonus_id = $request_to_array['restoreState']['mode'];
                    }

                    if($data_origin['buyFeature'] === true) {
                        if($win < $bet) { // if the outcome of buy feature is SMALLER then the buy amount, we are saving this game for re-use in respin template database
                                $data_origin['dog']['round_betsize'] = $data_origin['correspondingBa'];
                                $this->save_game_respins_template($select_session['game_id'], json_encode($data_origin), $data_origin['correspondingBa'].'_'.$bonus_id);
                                $data_origin['dog']['respin_saved'] = 1;
                        } else { // if outcome of buy feature is BIGGER then we are calling the respin database for a template to replace the game results
                                 // if no eligible template is found then the game proceeds as usual
                            $respin_data = $this->retrieve_game_respins_template($select_session['game_id'], $data_origin['correspondingBa'].'_'.$bonus_id);
                            if($respin_data !== NULL) {
                                $respin_decode = json_decode($respin_data, true);
                                $counter = 0;
                                $respin_betsize = $respin_decode['correspondingBa'];
                                $original_betsize = $data_origin['correspondingBa'];
                                $ratio = $original_betsize / $respin_betsize;
                                $respin_decode['dog']['respin'] = 1;
                                $respin_decode['dog']['original_betsize'] = $original_betsize;
                                $respin_decode['dog']['respin_betsize'] = $respin_betsize;
                                $respin_decode['dog']['ratio_betsize'] = $ratio;
                                $respin_decode['dog']['respin_old_win'] = $win;
                                $respin_decode['ba'] = $data_origin['ba'];
                                $respin_decode['correspondingBa'] = $original_betsize;

                                /* // under construction to support different bet sizing, but developers of relax-games seem to be retarded and use different mappings for all their bonus games
                                   // for now we're saving the 'respin templates' based on: gameid, bonusid (game type), betsizing
                                   // if you go to production you should deff make mappings but then you need to go over every single game to support varying bonus game mappings
                                   // as now you will need much bigger respin template database (though not impossible)
                                if($betsize !== $round_betsize) {
                                    if(isset($respins_decode['freespins'][1])) {
                                        $count_freespin = 0;
                                        foreach($respins_decode['freespins'] as $freespin) {
                                            if(isset($freespin['win'])) {
                                                if($freespin['win'] > 0) {
                                                    $respins_decode['freespins'][$count_freespin]['win'] = ($freespin['win'] * $ratio);
                                                }
                                            }
                                            $count_freespin++;
                                        }
                                    } else {
                                        if(isset($respins_decode['freespins'][0]['win'])) {
                                            if($respins_decode['freespins'][0]['win'] > 0) {
                                                $respins_decode['freespins'][0]['win'] = ($respins_decode['freespins'][0]['win'] * $ratio);
                                            }
                                        }
                                    }

                                    if(isset($respins_decode['freespins']['respins'])) {
                                        $count_respin = 0;
                                        foreach($respins_decode['respins'] as $respin) {
                                            if(isset($respin['win'])) {
                                                if($respin['win'] > 0) {
                                                    $respins_decode['freespins']['respins'][$count_respin]['win'] = ($respin['win'] * $ratio);
                                                }
                                            }
                                            $count_respin++;
                                        }
                                    }

                                    $respin_decode['win'] = $respin_decode['win'] * $ratio; //total win is stored in 'awa' and tally's up each event, hence we get this from the last event
                                }
                                */

                                $respin = true;
                                $respin_decode['roundId'] = $data_origin['roundId'];
                                $balance_call_needed = 1;
                                $data_origin = $respin_decode;
                                $win = (int) $data_origin['win'];
                                $data_origin['dog']['gametype'] = $bonus_id;
                                $data_origin['dog']['respin_bonusfeature'] = 1;
                                $data_origin['dog']['respin_new_win'] = $win;
                            }
                        }
                    }
            } else { // normal game
                $win = (int) $data_origin['win'];
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
                    if($win > 100 and $bonus_feature_detected === 0) { // replace "normal" game result by respin template
                        $data_origin = $this->normal_respin($data_origin, $select_session);
                        $win = (int) $data_origin['ba']; 
                        $process_game = $this->process_game($internal_token, 0, $win, $data_origin);
                        $balance_call_needed = 1;
                        $respin_decode['dog']['original_win'] = $win;
                        Cache::forget('delayed_win::'.$select_session['player_id']);
                    } else {
                        $balance_call_needed = 0;
                        $process_game = $this->process_game($internal_token, 0, $win, $data_origin);
                        $data_origin['stats']['b'] = $process_game;
                    }
                }
            } else {
                if($bonus_feature_detected === 0) { // save "normal" game result as respin template (because win = 0)
                    $this->save_game_respins_template($select_session['game_id'], json_encode($data_origin), 'normal');
                }
            }

            $data_origin['dog']['bonus_feature_detected'] = $bonus_feature_detected;

            if($balance_call_needed === 1) {
            $data_origin['stats']['b'] = $this->get_balance($internal_token);
            }

            return $data_origin;
        }
        return $data_origin;
    }
    
    public function normal_respin($data_origin, $select_session) { // normal "gametype" respin trigger
        $respin_data = $this->retrieve_game_respins_template($select_session['game_id'], 'normal');
        if($respin_data !== NULL) {
            $respin = true;
            $respin_decode = json_decode($respin_data, true);
            $data = json_encode($data_origin);
            $respin_decode['roundId'] = $data_origin['roundId'];
            $respin_decode['dog']['respin'] = 1;
            return $respin_decode;
        } else {
            $data_origin['dog']['respin_failed'] = 1;
            return $data_origin;
        }
    }

    public function delayed_win($internal_token, $player_id)
    {
        $delayed_win = Cache::get('delayed_win::'.$player_id);
        if($delayed_win) {
                $data = array('delayed_win' => true);
                $process_game = $this->process_game($internal_token, 0, (int) $delayed_win, $data);
                Cache::forget('delayed_win::'.$player_id);
                return true;
        } else {
            return false;
        }
    }

    public function curl_request($url, $request)
    {
        $response = ProxyHelperFacade::CreateProxy($request)->toUrl($url);
        return $response;
    }

    public function example()
    {
    }



}
