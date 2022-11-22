<?php

namespace Wainwright\CasinoDog\Commands;

use Illuminate\Support\Facades\Http;
use Wainwright\CasinoDog\Models\Gameslist;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;
use App\Models\User;
use Wainwright\CasinoDog\Models\Settings;
use DB;
use Wainwright\CasinoDog\Controllers\InstallController;

class ControlCasinoDog extends Command
{
    protected $signature = 'casino-dog:control {auto-task?}';
    public $description = 'Send basic casino dog controls.';

    public function handle()
    {
        $install_kernel = new InstallController;

        if ($this->argument('auto-task')) {
            $task = $this->argument('auto-task');

            if($task === 'migrate-fresh') {
                \Artisan::call('migrate:fresh');
            }

            if($task === 'migrate') {
                \Artisan::call('migrate');
            }

            if($task === 'publish') {
                \Artisan::call('vendor:publish --tag="casino-dog-config');
            }
            
            if($task === 'create-admin') {
                $create_admin = $install_kernel->createAdmin();
                if(isset($create_admin['user'])) {
                    $this->line('Did you know you can set default admin password by setting WAINWRIGHT_CASINODOG_ADMIN_PASSWORD in your .env or in casino-dog config?');
                    $this->line('');
                    $this->info('Admin user: '.$create_admin['user']);
                    $this->info('Admin password: '.$create_admin['password']);
                } else {
                    $this->danger($create_admin['message']);
                }
            }

            if(str_contains($task, 'update-env:')) {
                //syntax is update-env:{Key=value}
                $between = $this->in_between('{', '}', $task);
                $key = explode('=', $between)[0];
                $value = explode('=', $between)[1];
                $env = $this->putPermanentEnv($key, $value);
                $this->info($key.' set to '.$env);
                return $env;
            }

            if($task === 'set-global-api-limit') {
                $this->replaceInBetweenInFile("perMinute\(", "\)", '500', base_path('app/Providers/RouteServiceProvider.php'));
                $this->replaceInFile('$request->ip()', '$request->DogGetIP()', base_path('app/Providers/RouteServiceProvider.php'));
            }


        } else {
        if($this->confirm('Do you want to run database migrations?')) {
            \Artisan::call('vendor:publish --tag="casino-dog-migrations"');
            $this->info('> Running..  "vendor:publish --tag="casino-dog-migrations"');
            \Artisan::call('migrate');
            $this->info('> Running..  "artisan migrate"');
        }  else {
            $this->info('.. Skipped database migrations');
        }

        /* Publish config file*/
        if($this->confirm('Do you want to publish config?')) {
            \Artisan::call('vendor:publish --tag="casino-dog-config"');
            $this->info('> Running..  "vendor:publish --tag="casino-dog-config"');
            $this->info('> Config published in config/casino-dog.php');
        }  else {
            $this->info('.. Skipped publishing config');
        }
	}
        return self::SUCCESS;
    }
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
    public function replaceInBetweenInFile($a, $b, $replace, $path)
    {
        $file_get_contents = file_get_contents($path);
        $in_between = $this->in_between($a, $b, $file_get_contents);
        if($in_between) {
            $search_string = stripcslashes($a.$in_between.$b);
            $replace_string = stripcslashes($a.$replace.$b);
            file_put_contents($path, str_replace($search_string, $replace_string, file_get_contents($path)));
            return self::SUCCESS;
        }
        return self::SUCCESS;
    }

    public function in_between($a, $b, $data)
    {
        preg_match('/'.$a.'(.*?)'.$b.'/s', $data, $match);
        if(!isset($match[1])) {
            return false;
        }
        return $match[1];
    }

    public function putPermanentEnv($key, $value)
    {
        $path = app()->environmentFilePath();

        if(env($key) === NULL) {
              $fp = fopen($path, "r");
              $content = fread($fp, filesize($path));
                file_put_contents($path, $content. "\n". $key .'=' . $value);

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


    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

}
