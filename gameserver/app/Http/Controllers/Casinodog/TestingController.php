<?php
namespace App\Http\Controllers\Casinodog;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionMethod;
use Event;
use App\Events\ExampleEvent;

class TestingController
{
    public $_function;

    public function __construct() {
        if(env('APP_DEBUG') !== true) {
            abort(403, 'Only available in APP_DEBUG=true');
        }
        if(config('casinodog.testing') === false) {
            abort(403, 'Testing disabled in config.');
        }
        $this->_methods = $this->available_methods();
    }

    protected function available_methods() {
        $controller_class = new ReflectionClass(explode('@', request()->route()[1]['uses'])[0]);
        $controller_methods = $controller_class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach($controller_methods as $method) {
            $list[] = [
                "name" => $method->name
            ];
        }
        return collect($list);
    }


    public function available_gamekernelmethods() {
        $controller = "\App\Http\Controllers\Casinodog\Game";
        $controller_class = new ReflectionClass($controller);
        $controller_methods = $controller_class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach($controller_methods as $method) {
            $list[] = [
                "method" => $method->name
            ];
        }
        return collect($list);
    }

    protected function check_method_availabillity()
    {
        if($this->_function === "handle") {
            abort(400, "You should not call the handle function, as it routes you to the function. Try a different function.");
        }
        if(!$this->_methods->where('name', $this->_function)->first()) {
            abort(400, "This function does not exist.");
        }
    }

    public function handle($function_name, Request $request) {
       $this->_function = $function_name;
       $this->check_method_availabillity();
        try {
            return $this->$function_name($request);
        } catch(\Exception $e) {
            abort(500, 'TestingController Function errored ' . $e->getMessage().' - on line: ' . $e->getLine());
        }
    }

    public function run_it()
    {
      return $this->available_gamekernelmethods();

    }


public function scoobiedoo()
{




}


    public function fire_event()
    {
        event(new ExampleEvent('test'));
        //Event::fire(new ExampleEvent('test'));
    }


    public function crypto_test(Request $request)
    {
      $token_string = "plaintoken";
      $secret_key = "12345";

      echo "string input used:<br><b><u>{$token_string}</u></b><br><br>";

      echo "secret/password used:<br><b><u>{$secret_key}</u></b><br><br>";

      $token_signature = generate_sign($token_string, $secret_key);
      echo "<i>generate_sign({$token_string}, {$secret_key})</i>:<br>";
      echo "Result: <b><u>{$token_signature}</b></u> <br><br>";

      $token_verify = verify_sign($token_signature, $token_string, $secret_key);
      echo "<i>verify_sign({$token_signature}, {$token_string}, {$secret_key})</i>:<br>";
      echo "Result (boolean): <b><u>{$token_verify}</b></u> <br><br>";

      $encryption = encrypt_string($token_string, $secret_key);
      echo "<i>encrypt_string({$token_string}, {$secret_key})</i>:<br>";
      echo "Result: <b><u>{$encryption}</b></u> <br><br>";

      $decryption = decrypt_string($encryption, $secret_key);
      echo "<i>decrypt_string({$encryption}, {$secret_key})</i>:<br>";
      echo "Result: <b><u>{$decryption}</b></u> <br><br>";
    }

    public function proxy_request(Request $request)
    {
    }

    public function createSessionAndRedirectEndpoint(Request $request)
    {
        $data = [
            'game' => $request->game,
            'currency' => $request->currency,
            'player' => $request->player,
            'operator_key' => $request->operator_key,
            'mode' => $request->mode,
            'request_ip' => $request->ip(),
        ];
        return \App\Http\Controllers\Casinodog\Game\SessionsHandler::createSession($data);
    }
}