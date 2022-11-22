<?php
namespace App\Http\Controllers\Casinodog\Game\PragmaticPlay;

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
class PragmaticPlayGame extends PragmaticPlayMain
{
    use GameKernelTrait;

    public function game_event(Request $request)
    {
        $internal_token = $request->internal_token;
        $action = $request->action;
        $parent_session = $this->get_internal_session($internal_token);

        if($action === 'reloadBalance.do') {
            return $this->reloadBalance($internal_token, $request);

        } elseif($action === 'doInit') {
            return $this->doInit($internal_token, $request);

        } elseif($action === 'doSpin' || $action === 'doCollect' || $action === 'doWin' || $action === 'doDeal') {

            return $this->doSpin($internal_token, $request, $action);
            
            
            $spin_init =  'def_s=13,11,9,11,7,13,12,11,5,7,12,12,8,7,5,4,12,12,8,9,5,4,12,12,8,9,12,9,6,7,6,5,11,10,13,8,12,9,13,10,13,13,13,3,13,13,13,13&fra=0.00&balance=0.04&nas=13&cfgs=4883&ver=2&frn=10&index=1&frt=N&balance_cash=0.04&reel_set_size=14&ev=FR0~0.01,20,10,0,1,1666232358,1,,&balance_bonus=0.00&na=s&scatters=1~100,20,5,0,0,0~0,0,0,0,0,0~1,1,1,1,1,1&gmb=0,0,0&rt=d&gameInfo={rtps:{ante:"94.49",purchase:"94.50",regular:"94.51"},props:{max_rnd_sim:"1",max_rnd_hr:"1054852",max_rnd_win:"5000",max_rnd_win_a:"4000"}}&wl_i=tbm~5000;tbm_a~4000&bl=0&stime=1666145970658&reel_set10=9,3,11,9,3,7,5,9,7,5,11,7,7,3,9,5,7,11,5,11,7,7,11,9,3,7,5,5,11,9,7,11,9,7,9,7,7,9,7,11~6,10,10,6,12,10,4,12,6,6,6,6,8,12,4,12,12,8,8,6,12,10,8,8,8,4,10,8,6,8,6,8,12,6,12,12,12,12,8,4,6,4,8,12,8,6,12,10,12~5,9,11,10,9,12,9,9,9,12,7,11,11,12,3,6,12,12,12,5,6,10,7,8,10,3,8,8,8,12,11,12,5,5,3,12,6,6,6,6,10,10,11,5,5,10,5,5,5,8,9,8,5,8,10,11,11,11,11,3,5,10,6,3,6,9,8~4,12,9,11,12,10,10,10,10,6,9,10,10,9,8,9,9,9,5,4,4,9,4,11,12,8,8,8,5,7,9,6,12,8,3,5,5,5,4,8,3,12,12,4,6,6,6,5,12,8,6,5,11,10,12,12,12,6,11,3,9,4,3,10,4,4,4,6,9,4,8,6,3,11,7~8,7,9,4,9,9,9,7,8,8,5,10,7,7,7,9,6,9,7,12,5,5,5,7,4,12,9,5,12,12,12,10,6,3,8,11,6,6,6,7,12,3,11,9,6~9,12,6,10,3,9,11,5,7,7,7,9,10,7,8,11,7,9,4,4,12,12,12,7,7,12,7,9,12,7,9,11,4,10,10,10,8,11,12,12,4,3,7,7,3,9,9,9,9,12,12,7,5,5,10,9,4,10,10&sc=0.01,0.02,0.03,0.04,0.05,0.10,0.20,0.30,0.40,0.50,0.75,1.00,2.00,2.50&defc=0.10&reel_set11=12,10,12,4,10,10,8,12,10,12,6,10,8,6,12,12,8,10,12,12,12,12,12,10,4,12,6,8,4,12,10,10,6,10,8,12,10,8,12,12,10,4,8,10&reel_set12=12,8,5,7,5,9,11,9,7,8,8,8,11,12,12,7,11,8,6,4,5,8,10,9,9,9,7,5,8,9,10,8,8,10,9,4,1,11,11,11,12,4,11,12,3,8,12,9,7,12,9,10,10,10,11,7,10,12,4,9,11,8,12,9,10,5,5,5,1,12,7,11,10,9,6,9,6,7,5,12,12,12,7,11,10,8,4,6,11,7,9,6,10,7,7,7,11,3,8,5,11,4,6,9,5,11,12,6,6,6,6,12,10,11,3,9,9,10,5,10,4,4,4,4,11,11,5,3,9,8,8,10,4,6,4,3,3,3,12,10,3,3,8,4,5,8,12,8,10,8~4,8,5,5,5,5,5,6,11,11,11,4,9,5,8,8,8,6,12,1,12,12,12,11,12,8,6,6,6,11,10,6,7,10,10,10,8,9,10,9,9,9,8,10,11,7,7,7,6,7,10,4,4,4,4,12,7,3,3,3,3,11,12,6,8~3,12,9,6,3,6,6,9,11,8,5,10,4,6,6,11,11,6,3,9,9,9,10,9,10,10,8,6,5,7,11,10,11,11,9,5,3,12,12,9,7,10,12,12,12,6,3,9,10,8,12,9,3,10,10,7,10,10,3,12,8,11,10,8,11,8,8,8,8,10,3,6,8,12,10,10,12,9,11,5,3,9,11,10,9,8,10,7,6,6,6,12,3,11,11,10,12,8,11,12,5,12,9,10,11,4,4,11,9,5,3,5,5,5,5,9,3,11,1,12,5,12,6,6,10,3,11,12,7,12,8,8,12,12,10,11,11,11,5,10,11,12,10,10,6,4,8,5,6,12,6,5,3,8,10,3,12,3,10,10,10,11,10,11,9,1,3,8,8,10,3,9,11,11,10,10,11,3,6,3,6,7,7,7,8,5,5,3,5,7,5,6,10,10,11,9,5,6,5,9,11,11,7,10,3,3,3,3,10,6,9,12,11,10,8,11,8,12,11,1,12,6,3,6,9,6,4,4,4,4,5,7,11,5,12,10,7,10,7,7,10,7,7,6,3,6,6,8,10,6,7,11~11,4,11,11,3,6,9,9,9,9,5,5,9,7,12,7,12,4,8,8,8,10,5,4,12,3,9,8,4,6,5,5,5,8,9,8,9,4,10,8,11,6,6,6,6,7,8,6,3,4,4,6,12,12,12,5,3,7,10,4,7,9,11,12,10,10,10,9,12,11,3,11,10,10,11,11,11,11,12,4,12,12,7,5,9,4,7,7,7,8,8,7,10,10,9,4,9,1,3,3,3,4,4,11,12,11,5,6,12,4,4,4,8,3,10,5,6,3,5,8,7,3~11,12,5,11,6,9,6,9,5,11,8,7,10,9,9,9,12,8,11,8,9,9,6,8,10,8,5,9,10,4,7,7,7,7,5,6,10,4,4,7,3,12,6,5,10,7,7,10,5,5,5,8,8,3,5,12,7,1,6,12,12,9,9,10,12,12,12,12,8,8,3,9,10,3,10,11,10,11,11,6,5,4,9,6,6,6,10,12,9,3,9,6,5,10,7,6,11,8,8,10,11,11,11,8,8,4,4,6,8,8,11,12,8,11,12,7,9,11,10,10,10,11,7,11,9,8,12,11,6,12,6,11,10,11,12,8,8,8,7,6,9,11,12,6,3,9,6,8,8,5,7,7,8,3,3,3,10,7,11,11,9,6,3,12,9,8,12,8,1,8,4,4,4,9,8,3,10,11,5,10,6,6,11,4,12,7,7,10,12~11,6,7,7,7,7,9,6,12,11,4,12,12,12,12,7,9,4,10,10,10,10,7,10,9,11,9,9,9,5,5,11,12,8,8,8,4,10,11,7,11,6,6,6,7,4,9,8,5,5,5,12,10,11,12,4,11,11,11,3,12,9,3,4,4,4,9,1,5,10,8,3,3,3,5,5,6,9,9,8&purInit_e=1&reel_set13=10,4,5,8,10,12,11,9,11,9,8,12,1,7,3,10,11,8,6,12,4,1,12,12,11,7,7,11,11,9,5,8,9,12,10,9&sh=8&wilds=2~0,0,0,0,0,0~1,1,1,1,1,1&bonuses=0&fsbonus=&st=rect&c=0.10&sw=6&sver=5&g={reg:{def_s:"12,11,5,7,12,12,8,7,5,4,12,12,8,9,5,4,12,12,8,9,12,9,6,7,6,5,11,10,13,8,12,9,13,10,13,13,13,3,13,13,13,13",def_sa:"10,9,10,6,12,3",def_sb:"12,3,3,11,10,10",prm:"2~2,3,5;2~2,3,5;2~2,3,5",reel_set:"0",s:"12,11,5,7,12,12,8,7,5,4,12,12,8,9,5,4,12,12,8,9,12,9,6,7,6,5,11,10,13,8,12,9,13,10,13,13,13,3,13,13,13,13",sa:"10,9,10,6,12,3",sb:"12,3,3,11,10,10",sh:"7",st:"rect",sw:"6"},top:{def_s:"7,11,9,11",def_sa:"3",def_sb:"7",prm:"2~2,3,5;2~2,3,5;2~2,3,5",reel_set:"1",s:"7,11,9,11",sa:"3",sb:"7",sh:"4",st:"rect",sw:"1"}}&bls=20,25&counter=2&paytable=0,0,0,0,0,0;0,0,0,0,0,0;0,0,0,0,0,0;400,200,80,40,20,0;125,50,25,15,0,0;75,25,15,10,0,0;30,15,10,6,0,0;30,15,10,6,0,0;20,10,6,4,0,0;20,10,6,4,0,0;15,8,4,2,0,0;12,8,4,2,0,0;12,8,4,2,0,0;0,0,0,0,0,0&l=20&rtp=94.51&total_bet_max=5,000.00&reel_set0=4,6,6,6,6,6,1,8,6,8,12,8,8,8,10,8,4,12,6,12,12,12,12,10,12,12,8,10~7,5,9,3,11,7,1,7,9,11~6,1,6,7,12,8,12,9,9,9,5,6,8,11,12,10,7,8,12,12,12,11,5,10,9,10,8,11,6,3,8,8,8,6,10,5,10,3,5,8,10,6,6,6,10,7,5,12,10,9,1,11,9,5,5,5,8,12,5,11,10,5,6,3,11,11,11,3,5,3,12,12,11,12,9,11,3~12,9,9,3,10,10,10,9,10,12,8,3,12,9,9,9,11,6,4,3,8,7,8,8,8,8,5,9,4,9,8,5,5,5,4,10,10,11,11,6,6,6,6,4,10,5,5,6,12,12,12,11,4,7,4,1,12,4,4,4,12,1,3,6,6,11,4~9,7,9,11,8,9,10,9,9,9,9,9,12,4,6,8,1,6,12,10,3,7,7,7,7,3,4,8,7,7,3,10,6,12,5,5,5,7,11,5,8,9,9,12,9,7,8,12,12,12,6,12,11,7,8,5,5,4,3,11,6,6,6,6,7,6,7,9,8,9,9,12,8,10~5,12,8,10,9,9,7,9,7,7,7,11,3,9,7,8,12,4,12,1,12,12,12,12,4,12,10,11,12,4,3,9,10,11,10,10,10,7,5,7,9,1,9,4,7,10,6,9,9,9,7,7,12,5,11,3,4,7,10,9,7&s=13,11,9,11,7,13,12,11,5,7,12,12,8,7,5,4,12,12,8,9,5,4,12,12,8,9,12,9,6,7,6,5,11,10,13,8,12,9,13,10,13,13,13,3,13,13,13,13&accInit=[{id:0,mask:"cp;mp"}]&reel_set2=7,9,7,5,7,9,11,11,7,11,7,7,11,3,7,7,11,1,9,5,3,9,7,3,5,9,11,9,7,3,5,11,5,7,3,7,3,9,5,1,11,7,7,9,9,7,9,9,5,7,11,11,9~6,8,10,12,10,8,4,10,10,4,12,6,8,10,6,6,6,1,6,6,12,6,12,8,4,6,8,12,6,4,8,6,10,8,8,8,6,8,8,12,8,8,6,6,10,6,6,12,8,8,12,12,12,12,8,12,12,4,6,12,4,12,8,1,12,10,4,12,10,12,12~10,10,8,5,10,11,6,9,9,9,6,12,7,8,10,5,12,3,12,12,12,8,3,8,10,8,12,10,6,12,8,8,8,9,6,5,11,7,1,6,10,6,6,6,8,5,3,11,12,9,7,10,11,5,5,5,5,9,5,11,9,10,5,12,11,11,11,3,5,3,12,6,1,3,11,11,12~11,6,10,10,10,7,11,3,9,9,9,1,8,5,8,8,8,4,12,12,3,5,5,5,10,4,9,6,6,6,9,11,8,12,12,12,4,10,9,4,4,4,4,5,6,6,12~8,3,3,9,6,9,9,9,12,10,8,9,7,9,7,7,7,7,4,3,1,7,9,6,12,5,5,5,11,9,10,8,6,8,12,12,12,4,7,6,8,7,10,5,6,6,6,11,9,9,5,11,12,7,12~8,7,10,12,3,5,5,9,7,7,7,3,4,7,12,10,10,9,1,9,12,12,12,4,7,1,9,12,8,3,9,6,10,10,10,4,10,7,9,12,11,9,11,7,9,9,9,7,12,10,12,4,11,4,7,7,5&t=243&reel_set1=11,5,11,11,9,9,11,11,5,7,11,9,11,11,7,3,11,11,11,9,7,9,3,1,5,9,7,7,9,3,9,1,3,9,11,11,9,7&reel_set4=10,6,6,6,12,4,6,8,8,8,10,1,12,12,12,12,8,12,8,6~7,1,7,7,3,5,3,7,9,5,9,11,7,11,7,3,11,9,9,5,7,1,11,9,11,7,7,9,5,11~6,11,12,9,9,9,5,9,10,12,10,12,12,12,11,6,8,9,3,8,8,8,12,8,5,9,3,6,6,6,3,10,11,8,10,5,5,5,7,10,3,5,6,11,11,11,12,11,1,5,6,1~6,5,12,10,10,10,10,3,6,3,9,9,9,3,8,5,4,8,8,8,9,12,12,4,5,5,5,11,6,8,1,6,6,6,11,10,1,7,12,12,12,5,9,10,11,4,4,4,4,9,12,9,11,11,11,11,8,4,4,6~10,12,9,9,9,9,8,10,8,7,8,7,7,7,9,9,3,11,6,5,5,5,5,12,9,12,12,12,12,6,11,8,9,7,6,6,6,7,3,4,6,7,1~7,3,9,10,7,7,7,3,4,7,8,7,12,12,12,9,4,11,12,11,10,10,10,7,10,12,9,5,9,9,9,9,4,6,12,1,10,5&purInit=[{type:"fsbl",bet:2000,bet_level:0}]&reel_set3=10,12,8,10,12,12,12,12,1,4,10,4,12,6,10,8&reel_set6=9,11,3,7,9,7,5,7,7,5,11,9,5,9,7,9,9,3,11,7,1,11,3,5,7,1,11,11,7,7~6,1,8,12,12,8,12,10,6,6,6,6,12,1,12,12,10,6,6,12,4,8,8,8,10,6,10,6,8,4,12,4,10,8,12,12,12,6,8,4,8,12,8,12,6,4,8,8~8,11,5,12,12,9,9,9,6,8,5,9,9,11,6,12,12,12,1,9,7,6,9,3,5,8,8,8,10,3,12,6,12,6,6,6,6,11,10,10,5,10,11,11,5,5,5,3,10,7,3,10,3,12,11,11,11,8,10,5,12,8,11,5,1~5,1,6,4,9,5,10,10,10,8,3,9,8,3,4,10,9,9,9,6,12,3,4,9,12,4,1,8,8,8,11,4,11,10,3,12,12,5,5,5,4,1,8,7,9,6,11,6,6,6,5,7,6,10,4,12,6,6,12,12,12,4,4,8,10,12,10,6,4,4,4,12,9,11,5,3,9,9,11,8~10,5,7,9,9,9,11,12,6,8,7,7,7,12,7,8,11,5,5,5,7,10,4,9,12,12,12,3,9,8,1,6,6,6,3,9,7,9,6,12~12,7,12,9,10,5,11,7,7,7,7,12,9,10,3,10,5,4,12,12,12,11,4,3,8,10,7,7,9,10,10,10,12,1,8,5,9,9,6,12,9,9,9,7,7,4,3,4,9,7,1,11&reel_set5=9,9,11,7,9,7,11,3,9,5,11,11,11,11,11,7,7,9,3,9,5,9,11,1,11,11,3&reel_set8=7,12,3,11,11,9,8,12,11,9,7,3,4,8,11,8,8,8,8,4,10,12,6,4,7,8,8,11,11,9,10,11,7,9,9,9,9,12,4,10,3,5,8,11,12,5,10,7,12,10,6,12,5,12,11,11,11,3,5,1,6,5,10,4,6,8,7,5,12,8,11,12,4,10,10,10,9,10,12,9,8,5,11,4,11,5,11,6,6,11,4,9,3,5,5,5,4,11,9,9,12,11,10,11,5,11,10,10,7,12,8,7,12,12,12,8,8,7,8,9,10,11,4,5,10,8,4,12,9,9,8,11,7,7,7,10,5,3,6,9,8,7,6,12,1,4,7,9,3,12,12,6,6,6,10,8,6,11,6,7,5,4,12,10,9,11,3,10,9,12,9,4,4,4,11,7,6,10,3,7,4,9,5,8,9,10,9,8,9,9,3,3,3,8,7,8,5,10,11,6,10,8,8,5,12,10,8,9,3,4,12~12,2,7,2,7,5,3,12,12,4,7,7,10,5,4,6,11,6,11,6,5,8,6,7,11,12,5,5,5,8,12,11,7,10,12,5,4,3,8,12,11,6,6,8,6,8,4,11,8,9,10,6,4,3,8,10,11,11,11,10,6,5,9,8,4,3,5,7,11,6,11,10,8,1,10,6,6,11,7,5,12,5,6,6,8,6,8,8,8,8,5,10,11,11,9,8,10,4,10,9,8,11,11,9,4,6,11,7,10,10,6,8,8,3,8,5,9,12,12,12,6,8,7,10,9,6,8,8,12,10,6,2,4,9,8,12,4,5,11,4,4,6,8,6,6,8,4,2,6,6,6,12,11,11,9,9,6,9,11,8,12,6,8,7,12,7,10,4,4,7,11,3,7,9,11,10,4,8,10,10,10,12,11,6,5,6,5,11,2,10,8,3,10,4,12,11,7,11,8,6,5,2,11,1,10,7,9,4,12,9,9,9,10,4,8,6,2,8,9,9,7,10,4,12,11,8,12,10,5,10,5,7,12,6,3,6,5,12,6,7,7,7,12,12,7,11,10,6,7,4,12,11,8,1,8,12,6,5,5,10,12,11,12,7,6,3,5,8,11,6,4,4,4,8,8,6,7,12,12,10,5,12,12,7,12,6,6,10,5,5,10,10,11,10,6,10,5,7,4,12,3,3,3,8,11,8,4,6,4,12,8,4,7,12,7,3,5,12,1,9,8,10,6,8,9,11,10,8,8,12,4,5~10,5,9,6,8,4,12,12,2,8,7,11,11,10,1,10,11,5,9,9,9,6,9,10,5,2,6,8,4,11,3,9,6,3,6,10,5,11,2,12,12,12,12,7,2,10,2,10,10,3,11,5,3,10,3,10,12,11,7,8,3,4,8,8,8,8,6,9,3,6,9,10,7,10,11,10,1,9,12,12,5,8,5,8,3,6,6,6,9,12,11,5,11,11,9,12,3,6,3,10,7,7,12,10,7,8,6,5,5,5,8,5,10,8,12,8,10,8,7,10,5,5,9,6,10,6,10,6,3,7,11,11,11,12,12,3,8,10,11,11,8,6,6,7,11,10,10,6,12,10,11,3,10,10,10,9,9,3,4,6,10,5,9,10,10,1,4,12,8,9,8,5,8,11,7,7,7,12,6,12,3,9,12,11,6,7,11,6,5,12,10,3,9,10,6,5,11,3,3,3,10,12,3,11,9,11,12,3,11,10,11,3,7,9,3,5,11,3,12,4,4,4,10,8,5,11,6,11,11,12,3,6,5,3,10,7,12,10,6,7,6,6,9~9,8,8,11,9,7,5,7,4,8,7,5,9,9,9,10,4,11,4,10,10,8,6,5,6,12,4,7,3,8,8,8,6,12,2,5,3,4,7,11,11,12,10,5,9,3,5,5,5,11,12,8,12,6,4,8,9,11,12,12,9,10,9,6,6,6,7,9,11,4,5,5,7,9,8,12,5,8,9,3,12,12,12,10,3,11,12,8,11,12,4,3,4,12,3,12,10,10,10,10,6,6,11,6,9,9,8,7,4,4,3,11,4,6,11,11,11,4,9,2,11,12,12,10,7,8,7,3,5,8,10,7,7,7,3,9,4,10,12,5,2,4,6,3,4,11,8,12,3,3,3,3,11,7,9,11,4,4,1,6,7,5,6,4,10,4,4,4,1,11,4,10,2,5,9,5,3,4,10,8,9,4,7~8,11,11,10,11,8,5,8,9,9,9,6,11,9,1,10,12,12,6,11,7,7,7,7,6,7,3,12,10,9,11,10,6,5,5,5,5,2,4,7,8,11,2,10,10,11,12,12,12,1,6,12,12,5,7,7,12,8,11,6,6,6,5,12,9,4,10,11,8,5,4,11,11,11,5,8,6,9,10,6,10,4,9,10,10,10,10,3,7,9,3,7,12,9,7,8,7,8,8,8,8,6,9,9,6,12,3,2,6,3,3,3,11,8,12,8,8,11,10,3,8,3,4,4,4,9,6,11,8,12,11,4,8,8,9,8~10,3,7,7,9,9,6,7,11,3,5,7,5,12,7,7,7,7,9,7,11,8,9,5,6,6,10,10,4,9,5,3,7,12,12,12,9,12,1,12,4,7,4,9,10,9,7,10,5,7,12,4,10,10,10,9,4,4,12,11,9,11,10,10,12,5,6,12,5,7,3,9,9,9,11,5,10,7,9,11,12,5,11,6,9,10,11,8,4,10,8,8,8,10,9,9,3,4,9,11,12,5,9,9,12,6,12,4,6,6,6,6,11,3,11,8,9,8,10,9,4,8,9,9,5,1,7,5,5,5,6,6,4,6,9,5,9,11,12,12,9,4,11,12,12,11,11,11,11,7,7,5,9,11,9,11,11,8,12,12,11,11,7,5,10,4,4,4,12,10,4,7,4,11,4,11,8,8,10,12,11,12,4,3,3,3,3,9,6,3,12,5,9,10,8,8,10,10,7,12,11,8,9,5&reel_set7=10,12,8,12,12,12,12,4,1,12,10,10,8,6&reel_set9=9,8,11,5,12,9,9,8,10,10,12,1,3,11,12,5,12,12,8,11,5,6,2,12,9,1,7,4,8,7,12,10,4,4,8,7,10,10,11,9,2,7,11,9,6,11,8,11,12,9,1&total_bet_min=0.20';
            
                        
            $spin1_1 = 'tw=0.20&fra=0.00&balance=0.04&frn=9&index=2&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&rs=mc&tmb_win=0.20&l0=0~0.10~6~19~8~21~10&l1=0~0.10~6~19~14~21~10&rs_p=0&bl=0&stime=1666146432451&rs_c=1&sh=8&rs_m=1&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"8,5,8,9,2,5,11,5,2,5,9,4,13,8,10,8,6,4,13,6,13,3,6,9,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"9,5,10,9,8,11",sb:"5,11,6,4,4,6",sh:"7",st:"rect",sw:"6",tmb:"0,8~2,8~4,2~8,2~13,8~15,8"},top:{reel_set:"9",s:"10,3,9,10",sa:"11",sb:"12",sh:"4",st:"rect",sw:"1"}}&counter=4&l=20&s=13,10,9,3,10,13,8,5,8,9,2,5,11,5,2,5,9,4,13,8,10,8,6,4,13,6,13,3,6,9,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.20';

            $spin1_2 = 'tw=0.20&tmb_res=0.20&fra=0.00&balance=0.04&frn=9&index=3&frt=N&balance_cash=0.04&balance_bonus=0.00&na=c&rs_t=1&tmb_win=0.20&bl=0&stime=1666146435001&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"9,5,3,9,8,5,11,5,10,9,9,4,13,5,10,5,6,4,13,6,13,3,6,9,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"7,5,3,9,3,11",sb:"5,11,6,4,4,6",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"10,3,9,10",sa:"11",sb:"12",sh:"4",st:"rect",sw:"1"}}&counter=6&l=20&s=13,10,9,3,10,13,9,5,3,9,8,5,11,5,10,9,9,4,13,5,10,5,6,4,13,6,13,3,6,9,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

            $spin1_doCollect = 'fra=0.20&balance=0.04&frn=9&index=4&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&stime=1666146435167&sver=5&counter=8';

            $spin2_1 = 'tw=0.00&fra=0.20&balance=0.04&frn=8&index=5&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&bl=0&stime=1666146574140&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"8,3,11,7,11,9,7,8,4,9,9,9,10,13,4,9,6,5,13,13,13,3,6,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"3,3,10,2,7,9",sb:"11,7,10,8,12,12",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"12,10,7,12",sa:"10",sb:"8",sh:"4",st:"rect",sw:"1"}}&counter=10&l=20&s=13,12,7,10,12,13,8,3,11,7,11,9,7,8,4,9,9,9,10,13,4,9,6,5,13,13,13,3,6,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

            $spin3_1 = 'tw=0.00&fra=0.20&balance=0.04&frn=7&index=6&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&bl=0&stime=1666146594775&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"2",s:"11,6,5,6,6,7,5,10,12,4,6,7,5,6,12,4,11,7,11,13,12,11,10,13,13,13,13,11,4,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"7,12,6,6,6,12",sb:"11,4,11,10,7,9",sh:"7",st:"rect",sw:"6"},top:{reel_set:"3",s:"10,1,12,10",sa:"12",sb:"6",sh:"4",st:"rect",sw:"1"}}&counter=12&l=20&s=13,10,12,1,10,13,11,6,5,6,6,7,5,10,12,4,6,7,5,6,12,4,11,7,11,13,12,11,10,13,13,13,13,11,4,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

            $spin4_1 = 'tw=1.68&l10=0~0.08~18~13~8~27~10&l12=0~0.08~18~13~32~21~10&l11=0~0.08~18~13~32~3~10&l14=0~0.08~18~25~8~3~10&l13=0~0.08~18~13~32~27~10&l16=0~0.08~18~25~8~27~10&l15=0~0.08~18~25~8~21~10&l18=0~0.08~18~25~32~21~10&l17=0~0.08~18~25~32~3~10&fra=0.20&balance=0.04&l19=0~0.08~18~25~32~27~10&frn=6&index=7&frt=N&balance_cash=0.04&l21=0~0.04~12~19~8~3&l20=0~0.04~12~13~8~3&balance_bonus=0.00&na=s&rs=mc&tmb_win=1.68&l0=0~0.08~6~13~8~3~4&l1=0~0.08~6~13~8~15~4&l2=0~0.08~6~13~14~3~4&rs_p=0&l3=0~0.08~6~13~14~15~4&l4=0~0.08~6~13~20~3~4&l5=0~0.08~6~13~20~15~4&bl=0&l6=0~0.08~6~13~26~3~4&stime=1666146610422&l7=0~0.08~6~13~26~15~4&l8=0~0.08~18~13~8~3~10&l9=0~0.08~18~13~8~21~10&rs_c=1&sh=8&rs_m=1&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"10,6,2,1,11,12,12,2,10,10,9,12,11,12,10,11,6,7,13,11,10,11,13,8,13,13,11,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"7,6,8,7,7,12",sb:"8,10,7,4,10,10",sh:"7",st:"rect",sw:"6",tmb:"0,10~2,2~4,11~6,12~7,2~8,10~9,10~12,11~13,12~14,10~15,11~19,11~20,10~21,11~26,11"},top:{reel_set:"9",s:"10,2,6,8",sa:"1",sb:"5",sh:"4",st:"rect",sw:"1",tmb:"0,10~1,2"}}&counter=14&l=20&s=13,8,6,2,10,13,10,6,2,1,11,12,12,2,10,10,9,12,11,12,10,11,6,7,13,11,10,11,13,8,13,13,11,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=1.68';

            $spin4_2 = 'tw=2.00&fra=0.20&balance=0.04&frn=6&index=8&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&rs=mc&tmb_win=2.00&l0=0~0.04~6~1~14&l1=0~0.04~6~1~20&l2=0~0.04~6~1~26&rs_p=1&l3=0~0.04~6~1~32&l4=0~0.04~12~1~14&l5=0~0.04~12~1~20&bl=0&l6=0~0.04~12~1~26&stime=1666146612975&l7=0~0.04~12~1~32&rs_c=1&sh=8&rs_m=1&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"8,1,10,6,7,12,8,6,8,7,9,12,7,6,8,7,6,7,13,6,8,1,13,8,13,13,8,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"8,5,3,6,12,12",sb:"8,10,7,4,10,10",sh:"7",st:"rect",sw:"6",tmb:"0,8~6,8~8,8~14,8~20,8~26,8"},top:{reel_set:"9",s:"12,1,6,8",sa:"5",sb:"5",sh:"4",st:"rect",sw:"1",tmb:"3,8"}}&counter=16&l=20&s=13,8,6,1,12,13,8,1,10,6,7,12,8,6,8,7,9,12,7,6,8,7,6,7,13,6,8,1,13,8,13,13,8,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.32';

            $spin4_3 = 'tw=2.00&tmb_res=2.00&fra=0.20&balance=0.04&frn=6&index=9&frt=N&balance_cash=0.04&balance_bonus=0.00&na=c&rs_t=2&tmb_win=2.00&bl=0&stime=1666146615852&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"8,1,3,6,7,12,8,6,3,7,9,12,7,6,3,7,6,7,13,6,3,1,13,8,13,13,10,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"4,5,12,6,12,12",sb:"8,10,7,4,10,10",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"5,12,1,6",sa:"8",sb:"5",sh:"4",st:"rect",sw:"1"}}&counter=18&l=20&s=13,6,1,12,5,13,8,1,3,6,7,12,8,6,3,7,9,12,7,6,3,7,6,7,13,6,3,1,13,8,13,13,10,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

            $spin4_4 = 'fra=2.20&balance=0.04&frn=6&index=10&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&stime=1666146616009&sver=5&counter=20';

            $spin5_1 = 'tw=0.00&fra=2.20&balance=0.04&frn=5&index=11&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&bl=0&stime=1666146658438&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"2",s:"5,12,11,6,12,7,5,6,11,6,6,7,9,10,10,6,11,10,1,13,13,13,8,9,7,13,13,13,9,13,13,13,13,13,10,13,13,13,13,13,13,13",sa:"11,6,11,8,12,7",sb:"11,12,10,12,7,9",sh:"7",st:"rect",sw:"6"},top:{reel_set:"3",s:"10,12,10,6",sa:"12",sb:"8",sh:"4",st:"rect",sw:"1"}}&counter=22&l=20&s=13,6,10,12,10,13,5,12,11,6,12,7,5,6,11,6,6,7,9,10,10,6,11,10,1,13,13,13,8,9,7,13,13,13,9,13,13,13,13,13,10,13,13,13,13,13,13,13&w=0.00';

            $spin6_1 = 'tw=0.00&fra=2.20&balance=0.04&frn=4&index=12&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&bl=0&stime=1666146669151&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"4,4,3,5,3,11,4,10,3,12,3,5,11,13,10,7,8,12,3,13,13,13,2,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"9,4,12,5,7,10",sb:"10,12,10,2,6,7",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"2,6,8,5",sa:"10",sb:"9",sh:"4",st:"rect",sw:"1"}}&counter=24&l=20&s=13,5,8,6,2,13,4,4,3,5,3,11,4,10,3,12,3,5,11,13,10,7,8,12,3,13,13,13,2,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';


            $spin7_1 = 'tw=0.00&fra=2.20&balance=0.04&frn=3&index=13&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&bl=0&stime=1666146685544&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"8,6,10,4,2,10,10,12,11,11,9,11,11,12,13,12,9,5,7,13,13,12,9,4,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"8,8,2,4,8,9",sb:"9,9,3,5,4,6",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"11,9,7,8",sa:"8",sb:"2",sh:"4",st:"rect",sw:"1"}}&counter=26&l=20&s=13,8,7,9,11,13,8,6,10,4,2,10,10,12,11,11,9,11,11,12,13,12,9,5,7,13,13,12,9,4,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

            $spin8_1 = 'tw=0.15&fra=2.20&balance=0.04&frn=2&index=14&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&rs=mc&tmb_win=0.15&l0=0~0.15~24~13~20~27&rs_p=0&bl=0&stime=1666146701108&rs_c=1&sh=8&rs_m=1&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"11,8,3,8,4,8,10,5,10,4,8,8,9,12,5,4,12,8,5,13,12,5,7,13,8,13,11,13,13,13,8,13,9,13,13,13,6,13,9,13,13,13",sa:"12,6,3,11,10,7",sb:"6,11,9,9,8,5",sh:"7",st:"rect",sw:"6",tmb:"7,5~14,5~18,5~21,5"},top:{reel_set:"9",s:"12,1,12,12",sa:"10",sb:"4",sh:"4",st:"rect",sw:"1"}}&counter=28&l=20&s=13,12,12,1,12,13,11,8,3,8,4,8,10,5,10,4,8,8,9,12,5,4,12,8,5,13,12,5,7,13,8,13,11,13,13,13,8,13,9,13,13,13,6,13,9,13,13,13&w=0.15';

            $spin8_2 = 'tw=0.23&fra=2.20&balance=0.04&frn=2&index=15&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&rs=mc&tmb_win=0.23&l0=0~0.02~6~1~2&l1=0~0.02~6~1~26&l2=0~0.02~6~19~2&rs_p=1&l3=0~0.02~6~19~26&bl=0&stime=1666146703679&rs_c=1&sh=8&rs_m=1&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"12,6,3,11,4,8,11,8,3,8,8,8,10,12,10,4,12,8,9,13,12,4,7,13,8,13,11,13,13,13,8,13,9,13,13,13,6,13,9,13,13,13",sa:"12,7,7,11,10,7",sb:"6,11,9,9,8,5",sh:"7",st:"rect",sw:"6",tmb:"0,12~13,12~20,12"},top:{reel_set:"9",s:"12,1,12,12",sa:"10",sb:"4",sh:"4",st:"rect",sw:"1",tmb:"2,12~3,12"}}&counter=30&l=20&s=13,12,12,1,12,13,12,6,3,11,4,8,11,8,3,8,8,8,10,12,10,4,12,8,9,13,12,4,7,13,8,13,11,13,13,13,8,13,9,13,13,13,6,13,9,13,13,13&w=0.08';

            $spin8_3 = 'tw=0.23&tmb_res=0.23&fra=2.20&balance=0.04&frn=2&index=16&frt=N&balance_cash=0.04&balance_bonus=0.00&na=c&rs_t=2&tmb_win=0.23&bl=0&stime=1666146706569&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"12,7,7,11,4,8,11,6,3,8,8,8,10,8,3,4,12,8,9,13,10,4,7,13,8,13,11,13,13,13,8,13,9,13,13,13,6,13,9,13,13,13",sa:"12,7,7,11,10,7",sb:"6,11,9,9,8,5",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"9,10,12,1",sa:"3",sb:"4",sh:"4",st:"rect",sw:"1"}}&counter=32&l=20&s=13,1,12,10,9,13,12,7,7,11,4,8,11,6,3,8,8,8,10,8,3,4,12,8,9,13,10,4,7,13,8,13,11,13,13,13,8,13,9,13,13,13,6,13,9,13,13,13&w=0.00';

            $spin8_collect = 'fra=2.43&balance=0.04&frn=2&index=17&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&stime=1666146706741&sver=5&counter=34';

            $spin9_1 = 'tw=0.00&fra=2.43&balance=0.04&frn=1&index=18&frt=N&balance_cash=0.04&balance_bonus=0.00&na=s&bl=0&stime=1666146737560&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"9,6,7,1,5,5,4,8,7,10,5,11,4,5,7,11,11,9,11,13,7,11,13,12,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"12,7,6,4,5,12",sb:"7,11,5,4,12,4",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"9,4,11,12",sa:"5",sb:"9",sh:"4",st:"rect",sw:"1"}}&counter=36&l=20&s=13,12,11,4,9,13,9,6,7,1,5,5,4,8,7,10,5,11,4,5,7,11,11,9,11,13,7,11,13,12,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

            $final_spin = 'tw=0.00&fra=2.43&balance=0.04&frn=0&index=19&frt=N&balance_cash=0.04&ev=FR1~0.00,20,2.43,,&balance_bonus=0.00&na=s&bl=0&stime=1666146750351&sh=8&st=rect&c=0.01&sw=6&sver=5&g={reg:{reel_set:"8",s:"5,4,2,3,11,5,9,4,6,6,11,11,9,10,10,13,8,3,9,13,11,13,12,12,8,13,9,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"11,4,5,3,11,5",sb:"11,12,3,4,9,8",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"9,10,12,1",sa:"3",sb:"12",sh:"4",st:"rect",sw:"1"}}&counter=38&l=20&s=13,1,12,10,9,13,5,4,2,3,11,5,9,4,6,6,11,11,9,10,10,13,8,3,9,13,11,13,12,12,8,13,9,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';


            'tw=0.00&balance=248&index=2&balance_cash=248&balance_bonus=0.00&na=s&bl=0&stime=1666146853799&sh=8&st=rect&c=0.10&sw=6&sver=5&g={reg:{reel_set:"8",s:"6,11,3,11,6,12,12,9,10,11,6,12,9,9,8,13,6,13,13,6,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13",sa:"6,7,3,1,9,12",sb:"8,3,11,5,12,11",sh:"7",st:"rect",sw:"6"},top:{reel_set:"9",s:"9,4,11,12",sa:"5",sb:"9",sh:"4",st:"rect",sw:"1"}}&counter=5&l=20&s=13,12,11,4,9,13,6,11,3,11,6,12,12,9,10,11,6,12,9,9,8,13,6,13,13,6,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13,13&w=0.00';

        } elseif($action === 'saveSettings.do') {
            return $this->curl_request($request);
        } else {
            return $this->real_money_token();
        }

    } 


    public function promo_event(Request $request)
    {
        $action = $request->action;

        if($action === 'unread') {
            return '{"error":0,"description":"OK","announcements":[]}';
        } elseif($action === 'active') {
            $symbol = $request->symbol;
            $mgckey = $this->real_money_token();
            $get = Http::get('https://aventonv-dk1.pragmaticplay.net/gs2c/promo/race/prizes?symbol='.$symbol.'&mgckey='.$mgckey);
            return $get;
        } else {
            return $this->real_money_token();
        }
    }

    public function real_money_token() {
        $cache_length = 120; // 120 seconds = 2 minutes

        if($cache_length === 0) {
            $real_url = $this->get_token_mrbit();
            $parse = $this->parse_query($real_url);
            $get_token = $parse['mgckey'];
            return $get_token;
        }
        $get_token = Cache::remember('pragmaticplay:realmoneytoken', $cache_length, function () {
            $real_url = $this->get_token_mrbit();
            $get_token = Cache::put('pragmaticplay:realurl', $real_url, 120);
            $parse = $this->parse_query($real_url);
            return $parse['mgckey'];
        });
        return $get_token;
    }

    public function reloadBalance($internal_token, Request $request) {
        $send_event = $this->curl_request($request);
        $query = $this->parse_query($send_event);
        $get_balance = $this->get_balance($internal_token) / 100;
        $query['balance'] = $get_balance;
        $query['balance_cash'] = $get_balance;
        $query = $this->build_response_query($query);
        return $query;
    }

    public function doInit($internal_token, Request $request) {
        $send_event = $this->curl_request($request);
        $query = $this->parse_query($send_event);
        $get_balance = $this->get_balance($internal_token) / 100;
        $query['balance'] = $get_balance;
        $query['balance_cash'] = $get_balance;
        $query['rtp'] = '1.00';
        //$query['gameInfo'] = '{props:{max_rnd_sim:"19230769",max_rnd_hr:"1",max_rnd_win:"200"}}';
        $query['cfgs'] = '2523';
        $query = $this->build_response_query($query);
        return $query;
    }



    public function create_new_bridge_session($internal_token, Request $request) {
        $bridge_session = new PragmaticPlaySessions();
        $session = $bridge_session->fresh_game_session($request->symbol, 'redirect');
        $update_session = $this->update_session($internal_token, 'token_original_bridge', $session['token_original']);
        Cache::put($internal_token.':index', 2, now()->addHours(6));
        Cache::put($internal_token.':counter', 3, now()->addHours(6));
        Cache::put($internal_token.':balance', 10000000);
        $session_data = $this->get_internal_session($internal_token, 'session');
        return $session_data['token_original_bridge'];
    }

    public function getAmount($money)
    {
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);
        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;
        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot) * 100;
        return (float) str_replace(',', '.', $removedThousandSeparator);
    }

    public function doSpin($internal_token, Request $request, $action = NULL) {
        $rand_internal_id = rand(0, 100000);
        $parent_session = $this->get_internal_session($internal_token, 'session');
        abort(400, json_encode($parent_session));
        $token_original_bridge = $parent_session['token_original'];
        $altered_win_request = $request->toArray();

        if(isset($altered_win_request['mgckey'])) {
            $altered_win_request['mgckey'] = $token_original_bridge;
        }
        if(isset($altered_win_request['index'])) {
            $altered_win_request['index'] = Cache::get($token_original_bridge.':index');
            $altered_win_request['counter'] = Cache::get($token_original_bridge.':counter');
        }

        $cloned_request = (clone $request)->replace($altered_win_request); // build a new request with existing original headers from player, we are only replacing body content
        $respin_send_event = $this->curl_request($cloned_request);
        $query = $this->parse_query($respin_send_event);

        $new_bridge_balance = $this->getAmount($query['balance']);
        $old_bridge_balance_cache = Cache::get($token_original_bridge.':balance');
        $old_bridge_index_cache = Cache::get($token_original_bridge.':index');
        $old_bridge_counter_cache = Cache::get($token_original_bridge.':counter');

        $difference = (int) $new_bridge_balance - $old_bridge_balance_cache;

        if($difference < 0) {
            $bet_amount = str_replace('-', '', $difference);
            $freespin_state = Cache::get($parent_session['data']['player_operator_id'].'::freespins_'.$parent_session['data']['game_id']);
            if(!$freespin_state) {
                $process_game = $this->process_game($internal_token, $bet_amount, 0, $query);
            } else {
                $process_game = $this->process_game($internal_token, 0, 0, $query);
            }
        } else {
            $win_amount = $difference;
            if($action === "doFreeSpin") {
                $freespin_state['data']['total_win'] = $win_amount;
            }
            $process_game = $this->process_game($internal_token, 0, $win_amount, $query);
        }

        //Log::debug('callback: '.(int) $process_game);

        $query['balance'] = $process_game / 100;
        $query['balance_cash'] = $process_game / 100;

        Cache::forget($token_original_bridge.':balance');
        Cache::put($token_original_bridge.':balance', $new_bridge_balance);
        Cache::forget($token_original_bridge.':index');
        Cache::put($token_original_bridge.':index', $old_bridge_index_cache + 1);
        Cache::forget($token_original_bridge.':counter');
        Cache::put($token_original_bridge.':counter', $old_bridge_counter_cache + 2);
        $freespin_state = Cache::get($parent_session['data']['player_operator_id'].'::freespins_'.$parent_session['data']['game_id']);
        if($freespin_state) {
            if($freespin_state['data']['total_spins'] < 1) {
                Cache::forget($parent_session['data']['player_operator_id'].'::freespins_'.$parent_session['data']['game_id']);
                $total_win = $freespin_state['data']['total_win'];
                $this->freespin_state_completed($freespin_state);
                return 'ev=FR1~0.00,20,'.$total_win.',,&fra='.$total_win.'&nas=13&cfgs=4883&ver=2&frn=0&frt=N&'.$this->build_response_query($query);
            }
            $freespin_state['data']['total_spins'] = ((int) $freespin_state['data']['total_spins'] - 1);
            Cache::set($parent_session['data']['player_operator_id'].'::freespins_added', $freespin_state);

            return 'fra='.$freespin_state['data']['total_win'].'&nas=13&cfgs=4883&ver=2&frn='.$freespin_state['data']['total_spins'].'&frt=N&'.$this->build_response_query($query);
        }
        return $this->build_response_query($query);
    }

    public function old_game_mechanic()
    {
        $balance_call_needed = true;
        $bonus_active = false;

        if(isset($query['fs_total'])) { //payout bonus game
            $bonus_active = true;
            $win_amount = $query['tw'];
            $process_game = $this->process_game($internal_token, 0, $win_amount, $query);
            $query['balance'] = $process_game;
            $query['balance_cash'] = $process_game;
            return $this->build_response_query($query);
        }

        if(isset($query['fs'])) {
            $bonus_active = true;
            $fs = $query['fs'];

        if(isset($query['fs_bought'])) {
                if($fs === 1) {
                    $bet_amount = $query['c'] * $query['l'] * 100; // credit * lines * 100 (convert to 100 coin value)
                    $process_game = $this->process_game($internal_token, ($bet_amount * 100), 0, $query);
                    if(is_numeric($process_game)) {
                        $balance = $process_game / 100;
                        $query['balance'] = $balance;
                        $query['balance_cash'] = $balance;
                        return $this->build_response_query($query);
                    } else
                    { //throw insufficient balance error
                        if($process_game === '-1') {
                            return '-1&balance=-1&balance_cash=-1';
                        } else {
                            Log::notice('Unknown bet processing error occured: '.$request);
                            return 'unlogged'; // returning this will log out the session
                        }
                    }
                }
            }
        }

        if(isset($query['c'])) { // check if it's bet call
            if($query['na'] === 's') {
                $bet_amount = $query['c'] * $query['l'] * 100; // credit * lines * 100 (convert to 100 coin value)
                if($bonus_active === true) {
                    $bet_amount = 0;
                }
                $process_game = $this->process_game($internal_token, $bet_amount, 0, $query);
                $balance_call_needed = false;
                if(is_numeric($process_game)) {
                    $balance = $process_game / 100;
                } else
                { //throw insufficient balance error
                    if($process_game === '-1') {
                        return '-1&balance=-1&balance_cash=-1';
                    } else {
                        Log::notice('Unknown bet processing error occured: '.$request);
                        return 'unlogged'; // returning this will log out the session
                    }
                }
            }
        }

        if(isset($query['w'])) {
            $selectWinArgument = $query['w'];
            $winRaw = floatval($selectWinArgument);
            if($winRaw !== '0.00') {
                $win_amount = $query['w'] * 100;
                if($bonus_active === true) {
                    $win_amount = 0;
                }
                $process_game = $this->process_game($internal_token, 0, $win_amount, $query);
                $balance = $process_game / 100;
                $balance_call_needed = false;
            }
        }

        if($balance_call_needed === true) {
            $balance = $this->get_balance($internal_token) / 100;
        }

        $query['balance'] = $balance;
        $query['balance_cash'] = $balance;
        $query = $this->build_response_query($query);

        return $query;
    }


    public function build_response_query($query)
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

    public static function proxy_event($internal_token, $request) {
        $resp = ProxyHelperFacade::CreateProxy($request)->toHost('https://demogamesfree.pragmaticplay.net', 'api/games/pragmaticplay/'.$internal_token);
        return $resp;
    }

    public function curl_cloned_request($internal_token, $data, $request)
    {
        $internal_token = $request->segment(4);
        $url_explode = explode($internal_token, $request->fullUrl());
        $url = 'https://demogamesfree.pragmaticplay.net'.$url_explode[1];

        $response = Http::retry(1, 1500, function ($exception, $request) {
            return $exception instanceof ConnectionException;
        })->withBody(
            $data, 'application/x-www-form-urlencoded'
        )->post($url);

        return $response;
    }

    public function curl_request(Request $request)
    {
        $internal_token = $request->segment(4);
        $url_explode = explode($internal_token, $request->fullUrl());
        $url = 'https://demogamesfree.pragmaticplay.net'.$url_explode[1];
        $data = $request->getContent();

        $response = Http::retry(1, 1500, function ($exception, $request) {
            return $exception instanceof ConnectionException;
        })->withBody(
            $data, 'application/x-www-form-urlencoded'
        )->post($url);

        return $response;
    }


    public function get_token_mrbit() {
        $url = "https://mrbit.bet/api/games/CW_PPP_JohnHunterAndTheBookOfTut/play_real";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
        "authority: mrbit.bet",
        "accept: application/json, text/plain, */*",
        "accept-language: en-ZA,en;q=0.9",
        "cookie: sub_accounts=Vld3aHVGU005ZWtoTGtLVy9TS2NicFV1RzRtNzRxUDZaRkVNT1dUR2xNZ3ROWnlPcXJ6YVJqbkZFbnd4dm1lc09zcTJuSWVLYk5nMDE5dzVMNVg3dldIQ2dZR3RWNkJOTXRnY1VNMHNrQXRKYmgzYUZLLzJYSkdkWjErbkVEejlPR3hDQmtTTExoZjRHOC9iME1ZSVpwZ0J0U1ZuSEFVbU9qdTN3ZS9xL2tqVThIOXZEWU1LdFB6QVRFMWpJWjlSR25TeUJKd1VBUWlMaHFGRldpb1BpeFI5STRxd1N5cGNyTFE3ZW5SRkJHQUM4QTM2WVg2dXVWRERBc1lNd2RRbFp3bTQvb2l4OTYvTFR1WnlwNWh2UEdROWJTcjF1a1J3TG56NEVZUFZNL1c5YmdkOVQ2YlJFZlJDZjJZVEdMRjVaVXlGZi9uN0QvNHVxRkRobVJhWnBCMlY1MnlhNElqTXVETmZVdWhKZm5jRFRkZDE1QSt1WndOcmNxMVNGeFVEcXJHaCtpN0wzL2FuNnl2bDA1R2FQcUNtWTc4RHNPbkIxOVRSWkVMWnAwYz0tLVZNRGd3MXJmWjZ0Ym1FR3lLcVg5amc9PQ%3D%3D--5f7a07e288e9e1f8acf70125ffa9e69ec7385238; refcode=mb293517; encrypted_refcode=69b89cea4c501153dea82a88d8444e35; visit_url=https%3A%2F%2Fmrbit.bet%2Fen%3Fautologin_data%3D8ec9d745c0239b4044a58be744905d2050064e4b07d8e3a4a8ab547627fb09f61c148ed51c008a3572eb58dc25ce04fd5efc7225b70db9799662769c8728069777bfc87c0cdef7bbb6a1cc078148099975ae76f455cadf07a92a494ffd1507dca688d097946f9c33411800bdd4c132194de58e1ed33e10e4706cb0675a7d9628af12dca793bc84ca76d5bb614c1ea5b0832301dfe5ba4e282892b2ae8fabcb2e63ad7ee3a79338062935bc32d3ea0fbd%26autologin_iv%3Da7534bcf093c5f7266f85600ea006be3%26autologin_signature%3D50832673672d9c16b27605ccfa94450a486db4cc%26locale%3Den%26sud%3D0d92b457-2c0b-4d3e-ae39-fbba69d3ee69; s2s=; language=en; skip_registration_hint=true; _uId_cookie=ab017e32ece7d76b74c954516f3049c4; user_is_registered=true; geo=NL; seen_user_before=true; traffic-rules=aDF1ZXRlQ3E4SHpkSnQvMDBhWTRhZjh0STZ1cWdaVHFxb3JuVEI4MU5DaDkvbWZ5WHdLZlJ4TGlIZXdYVlo2MzZwMUhGU0RobXhGeHlKekFVSTV2ZllGUFJ5aC9KU1hybUJ6NHJrK2ZEdzdhY1pla2JHNVVqQmpMR1VzbTh4MWo4QXIrR2lnWnBEOFZtR092WEl4T1ZmVmQwaW9Pd1M1RXVNNVA1cmt4ZVlZVFUydmlDYXBJamxkS0hlREZzK0l5bGIza25sWU1qUVU3b01YejM4WGplQT09LS1lUXdTdmhub0Q5eXl6MXNQS083eS9RPT0%3D--3b69f9a5eb5f011468d7ca503db9fa0b03d9859e; vwo_identity_id=tTIHw4Ef6-9-Pk_AE8qTZ4uJu_V8h4pPeulQyZNzg20; argos_hash=Rb7cj1i9PnFTA0ZIn4uzqxNQXbY%3D; visited_at=1663906529; session_uuid=0d92b457-2c0b-4d3e-ae39-fbba69d3ee69; device_id=UnhhOiOw3X6U5118n7VPPCzb2pKUw0q7SgT2VgU-14U; locale=en; _vwo_uuid_v2=D5930FB4138011740A0A245DFB692AB08|0211bf1c756d20d0bf6ad91cea015981; _vwo_ssm=1; _vis_opt_s=1%7C; _vis_opt_test_cookie=1; _vwo_uuid=D5930FB4138011740A0A245DFB692AB08; _ga=GA1.2.1759930141.1663906540; _gid=GA1.2.1061124868.1663906540; token_id=96dfbf7a-6ff3-4cb5-a871-fc1d19475616; user_token=BAhJIikyN2FlNGRhZi1mNGU1LTQwOTYtYmVmMS1hMjI0MjZjNjVhYTAGOgZFVA%3D%3D--12774947c1b7415e6267d01914e3f434ba035b46; auth_token=27ae4daf-f4e5-4096-bef1-a22426c65aa0; _vwo_ds=3%3Aa_0%2Ct_0%3A0%241663906530%3A78.16856224%3A%3A13_0%2C12_0%2C11_0%2C10_0%2C9_0%2C8_0%2C7_0%3A31_0%2C29_0%2C24_0%2C3_0%2C2_0%3A23; _vwo_sn=0%3A14; _mr_bit_session=UGlyaXJ4UFBPV0ZQV0xBVWxKalFFSzRWeUZrQmxQRnNFblQxV1FUZGU2QUJ2dUJzdE9BNTFxeWN0YjFuSHJyWFVDOUtoenhlUVlPcExUbVd3a3pVeVdJYkY5U0xpc0JMdWxrSTd4QW1CRnFYVWJxc0FnaVRVbGpOQnpDYUVmRXhjejFsK3NnT0lHWDdJUmJvaUdVYkUrMjllUGliencvdE5FM0xEWlJoVWZjM2U0L0hHMjBsdDU3eWJYTlh3Q0lFaFBNYmw1TlZzRTlpU1lla1J4M1V4NCtVWWFWVEJFOHpnalQvK0dJSEFhcHhLZnBBSWNYNm82UFdSdVVVVjdQQTBsNEZXNlBJMVNVN2kwS3JlS0p0WWErdmxCT1ZiQkFMYW8zcWZRT1BwRzg9LS0xaVdDWWt0YlBEOWlhWFNnWS9vMWFBPT0%3D--48c543ebd5a0aaad19515a32ab199515b5f222fe; __cf_bm=FlSDPbBl9ExsMCC80oMGOEPj8q38kr6LDvsIyLMR1xk-1663907370-0-AQ9EU8zts0nVYFqW+1LaSmjR4ePt0u2DkuK3NVuTbUJBA0hqGMOMvzIRGPs6abTWF4RlBt2cEhFIVDL+F/OxEbY=",
        "sec-ch-ua-mobile: ?1",
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-origin",
        "sentry-trace: 3f887eb8a5c7492da5e3d2656bd1d092-809671d341d34e68-0",
        "user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.102 Mobile Safari/537.36",
        "x-locale: en",
        "x-token: 27ae4daf-f4e5-4096-bef1-a22426c65aa0",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp, true);
        $token = $resp['payload']['runnerOptions']['token'];

        $url = "https://api.atlantgaming.com/api/v2/launcher";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "authority: api.atlantgaming.com",
            "accept: */*",
            "accept-language: en-ZA,en;q=0.9",
            "content-type: text/plain;charset=UTF-8",
            "origin: https://launch.atlantgaming.com",
            "sec-ch-ua-mobile: ?1",
            "sec-fetch-dest: empty",
            "sec-fetch-mode: cors",
            "sec-fetch-site: same-site",
            "user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.102 Mobile Safari/537.36",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $data = '{"jsonrpc":"2.0","params":{"token":"'.$token.'"},"method":"sessions/launch"}';
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp, true);
        $url = $resp['data']['url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        $headers = [];
        $output = rtrim($output);
        $data = explode("\n",$output);
        $headers['status'] = $data[0];
        array_shift($data);

        foreach($data as $part){
            $middle = explode(":",$part,2);
            if ( !isset($middle[1]) ) { $middle[1] = null; }
            $headers[trim($middle[0])] = trim($middle[1]);
        }
        return $headers['location'];
        }
    }