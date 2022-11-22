<?php
namespace Wainwright\CasinoDog\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Wainwright\CasinoDog\CasinoDog;
use Wainwright\CasinoDog\Traits\ApiResponseHelper;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use App\Models\User;
use Wainwright\CasinoDog\Models\OperatorAccess;
use Wainwright\CasinoDog\Models\Gameslist;

class InstallController
{
   use ApiResponseHelper;

    public function show()
    {
        $this->check_install_state();
        $providers = config('casino-dog.games');
        return view('wainwright::installer.installer');
    }

    public function check_db_connection()
    {
        try {
            \DB::connection()->getPDO();
            \DB::connection()->getDatabaseName();
            } catch (\Exception $e) {
            abort(401, $e->getMessage());
        }
    }

    public function submit(Request $request)
    {
        if (!is_dir(base_path('resources/views/errors'))) {
            $this->errorStubs();
        }

        $this->check_db_connection();
	$this->check_install_state();
        $validate = $this->installValidation($request);

        if($validate->status() !== 200) {
            $message = json_decode($validate->getContent(), true)['data']['message'];
            abort(401, $message);
        }

        $domain = $request['WAINWRIGHT_CASINODOG_DOMAIN'];
        if($domain[strlen($domain)-1] === '/') {
            $domain = substr($domain, 0, -1);
        }
        $corsproxy = $request['WAINWRIGHT_CASINODOG_CORSPROXY'];
        if($corsproxy[strlen($corsproxy)-1] !== '/') {
            $corsproxy =  $corsproxy.'/';
        }

        if($request['WAINWRIGHT_CASINODOG_TESTINGCONTROLLER'] === "0") {
            $testing_controller = "false";
        } else {
            $testing_controller = "true";
        }

        $this->panelServiceProviderStub();
        \Artisan::call('nova:install');
        \Artisan::call('nova:publish');
        \Artisan::call('migrate');
        \Artisan::call('casino-dog:install panel');
        \Artisan::call('casino-dog:install migrate');
        $create_admin = $this->createAdmin();
        \Artisan::call('optimize:clear');
        \Artisan::call('vendor:publish --tag="casino-dog"');
        replaceInBetweenInFile("perMinute\(", "\)", '500', base_path('app/Providers/RouteServiceProvider.php'));
        replaceInFile('$request->ip()', '$request->DogGetIP()', base_path('app/Providers/RouteServiceProvider.php'));

        if (view()->exists("wainwright::playground.index")) {
            //\Artisan::call('casino-dog-operator migrate');
            \Artisan::call('vendor:publish --tag="casino-dog-operator-api-migrations"');
            $user = User::where('email', 'admin@casinoman.app')->first();
            $callback_url = $domain.'/api/casino-dog-operator-api/callback';
            $add_operator = $this->addOperatorAccess($callback_url, $user->id, $request['WAINWRIGHT_CASINODOG_SERVER_IP']);
            $operator_key = $add_operator['operator_key'];
            $operator_secret = $add_operator['operator_secret'];
            $operator_startingbalance = 10000;
            $gameserver_api_baseurl = $domain;
            $gameserver_api_createsession = $domain.'/api/createSession';
            $gameserver_api_gameslist = $domain.'/api/gameslist/all';
            $gameserver_api_accessping = $domain.'/api/accessPing';
            \Artisan::call('vendor:publish --tag="casino-dog-operator-api-config"');
        }

        echo "<b>Put following in your .env file:</b>"."<br>";
        echo "<blockquote>";
        echo "APP_URL=".$domain."<br>";
        echo "WAINWRIGHT_CASINODOG_DOMAIN=".$domain."<br>";
        echo "WAINWRIGHT_CASINODOG_SERVER_IP=".$request['WAINWRIGHT_CASINODOG_SERVER_IP']."<br>";
        echo "WAINWRIGHT_CASINODOG_SECURITY_SALT=".$request['WAINWRIGHT_CASINODOG_SECURITY_SALT']."<br>";
        echo "WAINWRIGHT_CASINODOG_HOSTNAME=".$request['WAINWRIGHT_CASINODOG_HOSTNAME']."<br>";
        echo "WAINWRIGHT_CASINODOG_WILDCARD=".$request['WAINWRIGHT_CASINODOG_WILDCARD']."<br>";
	echo "WAINWRIGHT_CASINODOG_CORSPROXY=".$cors_anywhere."<br>";
        echo "WAINWRIGHT_CASINODOG_TESTINGCONTROLLER=".$request['WAINWRIGHT_CASINODOG_TESTINGCONTROLLER']."<br>";
        echo "WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK=".$request['WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK']."<br>";
        echo "WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST=".$request['WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST']."<br>";
        echo "WAINWRIGHT_CASINODOG_INSTALLABLE=0"."<br>";
        echo "WAINWRIGHT_CASINODOG_MASTER_IP=".$request['WAINWRIGHT_CASINODOG_MASTER_IP']."<br>";
        echo "</blockquote>";
        echo "<b>.env was missing, to prevent errors you should add manually this to your environment variables:</b>";
        if (view()->exists("wainwright::playground.index")) {
            echo "<b>operator_api package was detected, so added an operator key to the following:</b>"."<br>";
            echo "<blockquote>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_KEY=".$operator_key."<br>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_SECRET=".$operator_secret."<br>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_STARTING_BALANCE=".$operator_startingbalance."<br>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_API_BASEURL=".$gameserver_api_baseurl."<br>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_API_CREATESESSION=".$gameserver_api_createsession."<br>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_API_GAMESLIST=".$gameserver_api_gameslist."<br>";
            echo "WAINWRIGHT_CASINODOG_OPERATOR_API_ACCESSPING=".$gameserver_api_accessping."<br>";
            echo "</blockquote>";
        }
        echo "Your login details: ".$create_admin['user']." your password: ".$create_admin['password'];
        echo " -- Admin panel: <a href='/allseeingdavid'>/allseeingdavid</a>";

        $password = md5(env('APP_KEY').config('casino-dog.securitysalt'));
        \Artisan::call('optimize:clear');

        $this->set_installed_state();
    }

    public function addOperatorAccess($callback_url, $ownedBy, $operator_ip)
    {
        $data = [
            'operator_key' => md5(rand(20, 200).now()),
            'operator_secret' => substr(md5(now().rand(10, 100)), 0, rand(9, 12)),
            'callback_url' => $callback_url,
            'ownedBy' => $ownedBy,
            'active' => 1,
            'operator_access' => $operator_ip,
        ];
        $operator_model = new OperatorAccess();
        $operator_model->insert($data);
        return $data;
    }  


    public function panelServiceProviderStub() {
        $path = base_path('app/Providers');
        if(file_exists(base_path('app/Providers/NovaServiceProvider.php'))) {

        } else {

        $files = [
            __DIR__ . '../../../stubs/PanelServiceProvider.stub' => $path . '/NovaServiceProvider.php',
        ];

        $this->writeStubs($files, 'silent');
        }
    }


    public function gamesScaffold()
    {
        $get_games_count = Gameslist::count();
        if($get_games_count < 10) {
            $games_providers = config('casino-dog.games');
            foreach($games_providers as $game_id=>$game_scaffold) {
                try {
                $command = 'casino-dog:retrieve-default-gameslist '.$game_id.' upsert';
                \Artisan::call($command);
                } catch(\Exception $e) {
                    $casino_dog = new \Wainwright\CasinoDog\CasinoDog();
                    $casino_dog->save_log('InstallController()', $e->getMessage());
                }
            }
        }
    }

    public function errorStubs() {
            $errorBlade = base_path('resources/views/errors');
            (new Filesystem)->makeDirectory($errorBlade, 0755, true);

            $files = [
                __DIR__ . '../../../stubs/errors/400.blade.php' => $errorBlade . '/400.blade.php',
                __DIR__ . '../../../stubs/errors/401.blade.php' => $errorBlade . '/401.blade.php',
                __DIR__ . '../../../stubs/errors/403.blade.php' => $errorBlade . '/403.blade.php',
                __DIR__ . '../../../stubs/errors/404.blade.php' => $errorBlade . '/404.blade.php',
                __DIR__ . '../../../stubs/errors/419.blade.php' => $errorBlade . '/419.blade.php',
                __DIR__ . '../../../stubs/errors/429.blade.php' => $errorBlade . '/429.blade.php',
                __DIR__ . '../../../stubs/errors/500.blade.php' => $errorBlade . '/500.blade.php',
                __DIR__ . '../../../stubs/errors/503.blade.php' => $errorBlade . '/503.blade.php',
                __DIR__ . '../../../stubs/errors/layout.blade.php' => $errorBlade . '/layout.blade.php',
                __DIR__ . '../../../stubs/errors/minimal.blade.php' => $errorBlade . '/minimal.blade.php'
            ];

            $this->writeStubs($files, 'silent');
    }
    public function installValidation(Request $request) {
        $validator = Validator::make($request->all(), [
            'WAINWRIGHT_CASINODOG_SERVER_IP' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_SECURITY_SALT' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_DOMAIN' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_HOSTNAME' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_WILDCARD' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_PANEL_ALLOWED_IP_LIST' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_MASTER_IP' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_CORSPROXY' => ['required', 'max:165', 'min:3', 'regex:/^[^(\|\]`!%^&=}><’)]*$/'],
            'WAINWRIGHT_CASINODOG_TESTINGCONTROLLER' => ['required', 'max:3', 'min:1'],
            'WAINWRIGHT_CASINODOG_PROXY_GETDEMOLINK' => ['required', 'max:3', 'min:1'],
            'WAINWRIGHT_CASINODOG_PROXY_GETGAMELIST' => ['required', 'max:3', 'min:1']

        ]);

        if(isset($request['WAINWRIGHT_CASINODOG_SERVER_IP'])) {
            $ip = $request['WAINWRIGHT_CASINODOG_SERVER_IP'];
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $errorReason = $validator->errors()->first();
                $prepareResponse = array('message' => "WAINWRIGHT_CASINODOG_SERVER_IP does not to seem to be a valid IP", 'request_ip' => $request->DogGetIP());
                return $this->respondError($prepareResponse);
            }
        }


        if(isset($request['WAINWRIGHT_CASINODOG_MASTER_IP'])) {
            $ip = $request['WAINWRIGHT_CASINODOG_MASTER_IP'];
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $errorReason = $validator->errors()->first();
                $prepareResponse = array('message' => "WAINWRIGHT_CASINODOG_MASTER_IP does not to seem to be a valid IP", 'request_ip' => $request->DogGetIP());
                return $this->respondError($prepareResponse);
            }
        }


        if ($validator->stopOnFirstFailure()->fails()) {
            $errorReason = $validator->errors()->first();
            $prepareResponse = array('message' => $errorReason, 'request_ip' => $request->DogGetIP());
            return $this->respondError($prepareResponse);
        }

        return $this->respondOk();
    }


    public function createAdmin()
    {
        try {
            if(config('casino-dog.install_options.admin_password') !== NULL) {
                $plain_password = config('casino-dog.install_options.admin_password');
            } else {
                $password_input = config('casino-dog.securitysalt').config('casino-dog.domain').'777';
                $plain_password = md5($password_input);
            }

            $select_current = User::where('email', 'admin@casinoman.app')->first();
            if($select_current) {
                DB::table('users')->where('email', 'admin@casinoman.app')->update([
                    'password' => bcrypt($plain_password),
                    'is_admin' => 1
                ]);

                $data = [
                    'message' => 'Updated existing',
                    'user' => 'admin@casinoman.app',
                    'password' => $plain_password
                ];
                return $data;
            } else {

            $userData = [
                'name' => 'admin',
                'email' => 'admin@casinoman.app',
                'password' => bcrypt($plain_password),
                'is_admin' => 1
            ];
            DB::table('users')->insert($userData);
                $data = [
                    'message' => 'Created New User',
                    'user' => 'admin@casinoman.app',
                    'password' => $plain_password
                ];
                return $data;
            }
        } catch(\Exception $e) {
            //save_log('InstallController():createAdmin()', $e->getMessage());
		return $e->getMessage();

        }

    }

    public function set_installed_state()
    {
        file_put_contents(storage_path('framework/installed'), 1);
    }

    public function clear_install_state()
    {
        if(file_exists(storage_path('framework/installed'))) {
            unlink(storage_path('framework/installed'));
        }
    }

    public function putPermanentEnv($key, $value, $path)
    {
        if(env($key) === NULL) {
              $fp = fopen($path, "r");
              $content = fread($fp, filesize($path));
                file_put_contents($path, $content. "<br>". $key .'=' . $value);

        } else {

            $escaped = preg_quote('='.env($key), '/');

            file_put_contents($path, preg_replace(
                "/^{$key}{$escaped}/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
        return env($key);
    }

    public function check_install_state()
    {
        if(config('casino-dog.install_options.installable') === "0") {
            abort(403, 'Set INSTALLABLE flag in .env or config/casino-dog.php if you wish to run install again.');
        }
    }

    public function writeStubs($files, $verbose):void {
        foreach ($files as $from => $to) {
            if (!file_exists($to)) {
                file_put_contents($to, file_get_contents($from));
            } else {
                if($verbose === 'silent') {
                    file_put_contents($to, file_get_contents($from));
                } else {
                    if($this->confirm($to.' exists already. Do you want to overwrite this file?')) {
                        file_put_contents($to, file_get_contents($from));
                    } else {
                    }
                }
            }
        }
    }
}



