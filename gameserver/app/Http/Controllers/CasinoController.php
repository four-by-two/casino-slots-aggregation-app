<?php

namespace App\Http\Controllers;
use Wainwright\CasinoDogOperatorApi\Traits\CasinoDogOperatorTrait;
use Illuminate\Http\Request;
use Datto\JsonRpc\Http\Client;
use Datto\JsonRpc\Http\Exceptions\HttpException;
use Datto\JsonRpc\Responses\ErrorResponse;
use ErrorException;
use App\Http\Controllers\CasinoClient;

class CasinoController extends Controller
{
  use CasinoDogOperatorTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
     public function __construct()
     {
         $this->base_url = 'https://1-games.4x2.games/api/createSession';
         $this->operator_key = 'c440ed23281a8a3877e8494199f1ce57';
         $this->operator_secret = 'LdC6jSdXwQ0I';
         $this->build_create_session_url = $this->create_session_builder($this->base_url);

         //$this->middleware('auth:api');
     }

     public function test(Request $request)
     {
       $uri = "https://1-games.4x2.games/api/testerpost";


        $client = new CasinoClient(array(
                'url' => $uri,
        ));
       try {
          return $client->listGames();
       } catch (ErrorException $exception) {
           echo $exception;
           exit(1);
       }
       //return $this->create_session('wainwright', '1234', 'USD', 'real');
     }


     public function create_session_builder()
     {

       $s = '?game=wainwright&player=1234&currency=USD&operator_key=&mode=real';

     }




}
