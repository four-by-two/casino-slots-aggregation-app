<?php
if (!function_exists('save_log')) {
    function save_log($type, $message, $extra_data = NULL) {
        $data = [
            'message' => $message
        ];
        $kernel = new \App\Websocket\Chat;
        $kernel->sendMessage($data, "internal", "all");
        return \App\Models\DataLogger::save_log($type, $data, $extra_data);
    }
}
if (!function_exists('morph_array')) {
    function morph_array($data)
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }
        return $data;
    }
}
if (!function_exists('now')) {
function now($timezone = null)
    {
       return Carbon\Carbon::now();
    }
}
if (!function_exists('now_nice')) {
function now_nice()
    {
        return Carbon\Carbon::parse(now())->format('Y-m-d H:i:s');
    }
}

if (!function_exists('is_uuid')) {
function is_uuid($uuid)
    {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        } else {
            return true;
        }
    }
}
if (!function_exists('encrypt_string')) {
function encrypt_string($plaintext, $password = NULL)
    {
    if($password === NULL) {
        $password = config('casinodog.securitysalt');
    }
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return base64_encode($iv . $hash . $ciphertext);
    }
}
if (!function_exists('decrypt_string')) {
    function decrypt_string($string, $password = NULL)
    {
        $ivHashCiphertext = base64_decode($string);
        if($password === NULL) {
            $password = config('casinodog.securitysalt');
        }
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }
}

if (!function_exists('generate_sign')) {
    function generate_sign(string $token, string $pwd = NULL)
    {
        $timestamp = time();
        if($pwd === NULL) {
            $pwd = config('casino-dog.securitysalt');
        }
        $encryption_key = $pwd.'-'.$timestamp; //Consider timestamp the randomizing salt, can be replaced by any randomizing key/regex
        $generate_sign = hash_hmac('md5', $token, $encryption_key);
        $concat_sign_time = $generate_sign.'-'.$timestamp;
        return $concat_sign_time;
    }
}
if (!function_exists('verify_sign')) {
    function verify_sign(string $signature, string $token, string $pwd = NULL)
    {
        if($pwd === NULL) {
            $pwd = config('casino-dog.securitysalt');
        }
        try {
            $explode_signature = explode('-', $signature);
            $timestamp = $explode_signature[1];
            $encryption_key =  $pwd.'-'.$timestamp;
            $generate_sign = hash_hmac('md5', $token, $encryption_key);
            $concat_sign_time = $generate_sign.'-'.$timestamp;
            if($signature === $concat_sign_time) { // verify signature is same outcome
                return true;
            }
        } catch (\Exception $exception) {
            return false;
        }
        return false; //signature not matching, returning false
    }
}

if (!function_exists('replaceInFile')) {
function replaceInFile($search, $replace, $path)
{
    file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
}
}

if (!function_exists('replaceInBetweenInFile')) {
function replaceInBetweenInFile($a, $b, $replace, $path)
{
    $file_get_contents = file_get_contents($path);
    $in_between = in_between($a, $b, $file_get_contents);
    if($in_between) {
        $search_string = stripcslashes($a.$in_between.$b);
        $replace_string = stripcslashes($a.$replace.$b);
        file_put_contents($path, str_replace($search_string, $replace_string, file_get_contents($path)));
        return true;
    }
    return true;
}
}

if (!function_exists('in_between')) {
function in_between($a, $b, $data)
{
    preg_match('/'.$a.'(.*?)'.$b.'/s', $data, $match);
    if(!isset($match[1])) {
        return false;
    }
    return $match[1];
}
}

if (!function_exists('gameclass')) {
    function gameclass($provider)
    {
        try {
        $game_controller = config('casinodog.games.'.$provider.'.controller');
        if($game_controller === NULL) {
            save_log('gameclass error', "gamecontroller not found {$provider}");
            abort(400, "gamecontroller not found {$provider}");
        }
        $game_controller_kernel = new $game_controller;
        return $game_controller_kernel;
        } catch(\Exception $e) {
            save_log('gameclass error', $e->getMessage());
            abort(400, $e->getMessage());
        }

    }
}