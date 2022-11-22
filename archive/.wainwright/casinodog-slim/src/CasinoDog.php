<?php

namespace Wainwright\CasinoDog;
use Wainwright\CasinoDog\Controllers\Livewire\AlertBanner;
use Illuminate\Http\Request;
use Wainwright\CasinoDog\Controllers\DataController;
use Wainwright\CasinoDog\Controllers\SignatureFunctions;

class CasinoDog
{
    public function save_log($type, $message, $extra_data = NULL) {
        $data = [
            'message' => $message
        ];
        return \Wainwright\CasinoDog\Models\DataLogger::save_log($type, $data, $extra_data);
    }


    public static function generate_sign($token) {
        $signature = new SignatureFunctions;
        return $signature->generate_sign($token);
    }

    public static function verify_sign($token, $sign) {
        $signature = new SignatureFunctions;
        return $signature->verify_sign($token, $sign);
    }

    public static function static_getIp($request) {
        foreach (array('HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }

    public function getIp($request) {
        foreach (array('HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }

    public static function remove_back_slashes($string)
    {
        $string=implode("",explode("\\",$string));
        return stripslashes(trim($string));
    }

    public static function requestIP(Request $request)
    {
        $ip = $request->header('CF-Connecting-IP');
        if($ip === NULL || !$ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if($ip === NULL) {
              $ip = $request->ip();
            }
        }
        return $ip;
    }

    /*
       Usage Example: return \Wainwright\CasinoDog\CasinoDog:errorRouting(401, 'Failed to create player.');
    */
    public static function errorRouting($statuscode, $message = NULL, $errorType = NULL, $data = NULL)
    {
        //Array with meta
        if($message !== NULL) {
            $message = array(
                'status' => $statuscode,
                'message' => $message,
                'type' => $errorType,
                'data' => $data,
            );
        } else {
                $message = array(
                'status' => $statuscode,
                'message' => $message,
                'type' => $errorType,
                'data' => $data,
                );
        }
        #Operator Error Page
        // Operator level error page (casino)
        if($errorType === 'operator') {
            return view('wainwright::error-operator-template')->with('error', $message);
        }
        #Game Provider Error Page
        // Per game provider erroring
        if($errorType === 'gameprovider') {
            return view('wainwright::error-gameprovider-template')->with('error', $message);
        }
        #Fallback Error Page
        // Error page that is used if nothing is used
        return view('wainwright::error-default-template')->with('error', $message);
    }

    public function morph_array($data)
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }
        return $data;
    }

    public static function morph_array_static($data)
    {
        $morph = new CasinoDog();
        return $morph->morph_array($data);
    }
}