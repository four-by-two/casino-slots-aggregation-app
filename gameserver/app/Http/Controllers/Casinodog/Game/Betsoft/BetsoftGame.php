<?php
namespace App\Http\Controllers\Casinodog\Game\Betsoft;

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

class BetsoftGame extends BetsoftMain
{
    use GameKernelTrait;


    public function game_event($request)
    {
        $resp = $this->curl_request($request->origin, $request);
        $internal_token = $request->internal_token;
        
        $query = $this->parse_query($resp->getContent());

        $origin_balance = Cache::get($internal_token.'::betsoftOriginBalance');
        if(!$origin_balance) {
            Cache::set($internal_token.'::betsoftOriginBalance', $query['BALANCE']);
            $origin_balance = Cache::get($internal_token.'::betsoftOriginBalance');
        }
        $new_balance = $query['BALANCE'];
        if($request->input('CMD') === 'ENTER') {
            $query['BALANCE'] = ($this->get_balance($internal_token) / 100);
        } elseif($request->input('CMD') === 'PLACEBET') {
            if($origin_balance !== $new_balance) {
                if($origin_balance > $new_balance) {
                    $winAmount = 0;
                    $betAmount = (($origin_balance - $new_balance)  * 100);
                } else {
                    $betAmount = 0;
                    $winAmount = (($origin_balance - $new_balance) * 100);
                }
                Cache::set($internal_token.'::betsoftOriginBalance', $query['BALANCE']);
                $process_and_get_balance = $this->process_game($internal_token, $betAmount, $winAmount, $resp);
                $query['BALANCE'] = floatval(($process_and_get_balance / 100));
            }
        } else {
            $query['BALANCE'] = ($this->get_balance($internal_token) / 100);
        }

        $result = $this->build_query($query);
        return 'CMD='.$request->input('CMD').'&TOKEN='.$internal_token.'&'.$result;
    }


    public function curl_request($url, $request)
    {
        $resp = ProxyHelperFacade::CreateProxy($request)->toUrl($url);
        return $resp;
    }
}
