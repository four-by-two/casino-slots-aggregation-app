<?php

namespace Wainwright\CasinoDog\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;
use DB;
use Wainwright\CasinoDog\Models\Gameslist;
class StoreDefaultGameslist extends Command
{
    protected $signature = 'casino-dog:store-default-gameslist {gameprovider?}';
    public $description = 'Save gameslist from database to json storage per provider';

    public function handle()
    {   
        if ($this->argument('gameprovider')) {
            $gameprovider = $this->argument('gameprovider');
        } else {
            $gameprovider = $this->ask('Enter game provider tag you wish to save');
        }

        return $this->store($gameprovider);
    }


    public function store($gameprovider)
    {
        $count = DB::table('wainwright_gameslist')->where('provider', $gameprovider)->count();
        if($count < 1) {
            $this->error('No data records found in wainwright_gameslist table for '.$gameprovider);
            die();
        }
        $this->line('');
        $this->line('');

        $this->info('Amount of entries found: '.$count);

        // select gameprovider's main controller, from config
        $gameslist = Gameslist::all()->where('provider', $gameprovider);
        $game_controller = config('casino-dog.games.'.$gameprovider.'.controller');
        $game_controller_kernel = new $game_controller;
        
        $store_payload = $game_controller_kernel->default_gamelist("store", json_encode($gameslist, JSON_PRETTY_PRINT));

        $this->line('');
        isset($store_payload['message']) ? $this->info($store_payload['message']) : $this->error($store_payload['error']);
        $this->line('');

        return self::SUCCESS;
    }

}
