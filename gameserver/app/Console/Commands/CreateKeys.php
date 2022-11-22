<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateKeys extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'casinodog:generate-salt
        {--s|show : Display the keys instead of modifying files.}
        {--always-no : Skip generating keys if it already exists.}
        {--f|force : Skip confirmation when overwriting an existing keys.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set secret salts and set a master token.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = Str::random(64);

        if ($this->option('show')) {
            $this->comment($key);

            return;
        }

        if (file_exists($path = $this->envPath()) === false) {
            return $this->displayKey($key);
        }

        if (Str::contains(file_get_contents($path), 'WAINWRIGHT_CASINODOG_SECURITY_SALT') === false) {
            // create new entry
            file_put_contents($path, PHP_EOL."WAINWRIGHT_CASINODOG_SECURITY_SALT=$key".PHP_EOL, FILE_APPEND);
        } else {
            if ($this->option('always-no')) {
                $this->comment('Secret key already exists. Skipping...');

                return;
            }

            if ($this->isConfirmed() === false) {
                $this->comment('Phew... No changes were made to your secret key.');

                return;
            }
            // update existing entry
            file_put_contents($path, str_replace(
                'WAINWRIGHT_CASINODOG_SECURITY_SALT='.$this->laravel['config']['casinodog.securitysalt'],
                'WAINWRIGHT_CASINODOG_SECURITY_SALT='.$key, file_get_contents($path)
            ));
        }

        $this->displayKey($key);
    }

    /**
     * Display the key.
     *
     * @param  string  $key
     * @return void
     */
    protected function displayKey($key)
    {
        $this->laravel['config']['casinodog.securitysalt'] = $key;

        $this->info("security hash [$key] set successfully. It is used for signing hashes.");
    }

    /**
     * Check if the modification is confirmed.
     *
     * @return bool
     */
    protected function isConfirmed()
    {
        return $this->option('force') ? true : $this->confirm(
            'This will invalidate all existing session entry tokens. Are you sure you want to override the secret key?'
        );
    }

    /**
     * Get the .env file path.
     *
     * @return string
     */
    protected function envPath()
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }

        // check if laravel version Less than 5.4.17
        if (version_compare($this->laravel->version(), '5.4.17', '<')) {
            return $this->laravel->basePath().DIRECTORY_SEPARATOR.'.env';
        }

        return $this->laravel->basePath('.env');
    }
}
