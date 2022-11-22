<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class GameslistSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach(config('casinodog.games') as $provider_id => $provider_table) {
            try {
                Artisan::call('casinodog:restore-default-gameslist '.$provider_id.' upsert');
                echo "{$provider_id} gameslist restored.";
            } catch(\Exception $e) {
                save_log('Error trying to scaffold gameslist', $e->getMessage());
                echo "{$provider_id} gameslist missing or corrupt, run 'php artisan casino-dog:restore-default-gameslist {$provider_id} upsert' to try to import manually.";
            }
        }
    }
}
