<?php
namespace Wainwright\CasinoDog\Controllers\Testing;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Wainwright\CasinoDog\Controllers\Game\GameKernel;
use Wainwright\CasinoDog\Controllers\Game\GameKernelTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use DB;
use Wainwright\CasinoDog\CasinoDog;
use Wainwright\CasinoDog\Facades\ProxyHelperFacade;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class TestingController
{
    public function __construct() {
        if(env('APP_DEBUG') !== true) {
            abort(403, 'Only available in APP_DEBUG=true');
        }
        if(config('casino-dog.testing') === false) {
            abort(403, 'Testing disabled in config.');
        }


    }
    public function handle($function, Request $request) {


        try {
            return $this->$function($request);
        } catch(\Exception $e) {
            return 'TestingController Error: ' . $e->getMessage().' - on line: ' . $e->getLine();
        }
    }
    use GameKernelTrait;

    public function html() {
        return view('wainwright::example.testing-html');
    }

    public function viewer()
    {
        return Http::patch('https://rarenew-dk4.pragmaticplay.net/ReplayService');
    }

}
