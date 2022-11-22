<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;
use DB;
use App\Models\Gameslist;
use Illuminate\Support\Arr;

class ImportGameslistToDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casinodog:restore-default-gameslist {gameprovider?} {after=unset : Action to do after retrieving games (nothing, upsert, update, truncate+insert)?} {--save_to_db} {--truncate_current_db}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore gameslist from game provider using local storage.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {   
        if ($this->argument('gameprovider')) {
            $gameprovider = $this->argument('gameprovider');
        } else {
            $gameprovider = $this->ask('Enter game provider tag you wish to retrieve default gameslist for');
        }

        $gamelist_data = $this->retrieve($gameprovider);
        $gamelist_collect = collect(json_decode($gamelist_data, true));
        $gamelist_count = $gamelist_collect->count();
        if($gamelist_count < 2) {
            $this->error($gamelist_count.' games retrieved. To automatically perform database actions, you need a minimum of 2 games retrieved from storage or more.');
            die();
        }

        if($this->argument('after') === "unset") {
            $this->info('>> Games retrieved: '.$gamelist_count);
            $current_games_database = Gameslist::where('provider', $gameprovider)->count();
            $this->info('>> Current games in database: '.$current_games_database);

            $this->print_follow_up_actions();

            $after = $this->choice(
                'What followup database action to execute?',
                ['nothing', 'upsert', 'update', 'truncate+insert'], 'nothing');
        } else {
            $after = $this->argument('after');
        }

        if($after === 'upsert' || $after === 'update' || $after === 'truncate+insert') {
            return $this->database_action($after, $gamelist_collect, $gameprovider);
        }

        if($this->argument('after') === 'nothing' || $after === 'nothing') {
            return self::SUCCESS;
        } else {
            $this->error('Follow up argument '.$this->argument('after').' not valid.');
            $this->print_follow_up_actions();
            die();
        }

    }
    public function print_follow_up_actions()
    {
        $this->newLine();
        $this->info('Valid database follow up actions:');
        $this->line('>> \'nothing\' -- not execute any database action'); 
        $this->line('>> \'upsert\' -- insert new games, but skip if game already exists in database (based on `gid`)'); 
        $this->line('>> \'update\' -- insert new games and overwrite existing games'); 
        $this->line('>> \'truncate+insert\'  -- first delete all existing games in database and then insert the retrieved games');
        $this->newLine();
    }

    public function database_action($after, $gamelist_collect, $gameprovider,)
    {
        if($after === 'truncate+insert') {
            $delete_count = Gameslist::where('provider', $gameprovider)->count();
            Gameslist::where('provider', $gameprovider)->delete();
            $this->info('Truncated all entries ('.$delete_count.') belonging to '.$gameprovider);
            $this->newLine();
        }
        $gamelist_data = json_decode($gamelist_collect, true);
        $gamelist_array = Arr::except($gamelist_data, ['id']);
        $new_records = 0;
        $updated_records = 0;
        $existing_count = 0;
        foreach($gamelist_array as $game) {
            unset($game['id']);
            if(isset($game['enabled'])) {
                $game['active'] = 1;
                unset($game['enabled']);
            }

            $find_existing = Gameslist::where('gid', $game['gid'])->first();
            if(!$find_existing && $after === 'upsert') {
                Gameslist::create($game);
                $new_records++;
            } elseif($after === 'update') {
                if($find_existing) {
                    $find_existing->update($game);
                    $updated_records++;
                } else {
                    Gameslist::create($game);
                    $new_records++;
                }
            } elseif($after === 'truncate+insert') {
                Gameslist::create($game);
                $new_records++;
            } else {
                $existing_count++;
            }
        }
        
        $this->info('New games inserted:');
        $this->line('>> '.$new_records); 
        $this->info('Updated existing games:');
        $this->line('>> '.$updated_records); 
        $this->info('Existing games skipped:');
        $this->line('>> '.$existing_count); 
        $this->newLine();

        return self::SUCCESS;
    }

    public function retrieve($gameprovider)
    {
        // select gameprovider's main controller, from config
        $store_payload = gameclass($gameprovider)->default_gamelist("retrieve");

        $this->line('');
        isset($store_payload['message']) ? $this->line($store_payload['message']) : $this->error($store_payload['error']);
        isset($store_payload['message']) ? '': die();
        $this->line('');

        return $store_payload['message'];
    }
}
