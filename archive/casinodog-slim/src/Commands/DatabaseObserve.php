<?php

namespace Wainwright\CasinoDog\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Wainwright\CasinoDog\Models\GameImporterJob;
use Illuminate\Support\Facades\Cache;
use Wainwright\CasinoDog\Models\RawGameslist;

class DatabaseObserve extends Command
{
    protected $signature = 'casino-dog:database-observe';

    public $description = 'Database observer to execute commands on database updates.';

    public function handle()
    {
        $this->gameimporterjob_check();
        $this->rawgames_import_check();
    }

    public function gameimporterjob_check() {
        $count = GameImporterJob::where('state', 'JOB_QUEUED')->count();
        if($count > 0) {
            $kernel = new GameImporterJob;
            if($count < 2) {
                $select_rows = GameImporterJob::where('state', 'JOB_QUEUED')->first();
                $check_dispatch_state = Cache::get('casino-dog:database-observe::GameImporterJob::dispatched::'.$select_rows->id);
                $this->info('1 game importer job dispatched.');
                if($check_dispatch_state) {
                    //skipping because we recently dispatched already, to prevent slow queue double processing. Cache takes 5 minutes, you can add extra logging in here or functions.
                    $this->info('Skipped batch_id '.$single_row->id.' as we recently dispatched this import job already.');
                } else {
                    $dispatch = $kernel->start_job($select_rows->id);        
                }
                Cache::put('casino-dog:database-observe::GameImporterJob::dispatched::'.$select_rows->id, 1, now()->addMinutes(10));
            } elseif($count > 1) {
                $select_rows = GameImporterJob::all()->where('state', 'JOB_QUEUED');
                foreach($select_rows as $single_row) {
                    $check_dispatch_state = Cache::get('casino-dog:database-observe::GameImporterJob::dispatched::'.$single_row->id);
                    if($check_dispatch_state) {
                        //skipping because we recently dispatched already, to prevent slow queue double processing. Cache takes 5 minutes, you can add extra logging in here or functions.
                        $this->info('Skipped batch_id '.$single_row->id.' as we recently dispatched this import job already.');

                    } else {
                        $dispatch = $kernel->start_job($single_row->id);
                    }
                    Cache::put('casino-dog:database-observe::GameImporterJob::dispatched::'.$single_row->id, 1, now()->addMinutes(10));
                }
                $this->info($count.' game importer jobs dispatched.');
            }
        } else {
            $this->info('No game importer jobs found with state `JOB_QUEUED`.');
        }
    }

    public function gameimporterjob_update_count($batch_id)
    {
        $importerjob = new \Wainwright\CasinoDog\Models\GameImporterJob;
        $select_importerjob = $importerjob->where('id', $batch_id)->first();
        if($select_importerjob) {
            $count = ($select_importerjob->imported_games ?? 0) + 1;
            $select_importerjob->update([
                'imported_games' => $count
            ]);
        }
    }

    public function rawgames_import_check() {
        $count = RawGameslist::where('state', 'NEW')->count();
        if($count > 0) {
            $kernel = new RawGameslist;
            if($count < 2) {
                $select_rows = RawGameslist::where('state', 'NEW')->first();
                $check_dispatch_state = Cache::get('casino-dog:database-observe::RawGameslist::dispatched::'.$select_rows->id);
                $this->info('1 game importer job dispatched.');
                $this->gameimporterjob_update_count($select_rows->batch);
                if($check_dispatch_state) {
                    //skipping because we recently dispatched already, to prevent slow queue double processing. Cache takes 5 minutes, you can add extra logging in here or functions.
                    $this->info('Skipped batch_id '.$single_row->id.' as we recently dispatched this import job already.');
                } else {
                    $dispatch = $kernel->start_job($select_rows->gid);        
                }
                Cache::put('casino-dog:database-observe::RawGameslist::dispatched::'.$select_rows->id, 1, now()->addMinutes(15));
            } elseif($count > 1) {
                $select_rows = RawGameslist::all()->where('state', 'NEW');
                foreach($select_rows as $single_row) {
                    $check_dispatch_state = Cache::get('casino-dog:database-observe::RawGameslist::dispatched::'.$single_row->id);
                    $dispatch_count = 0;
                    $skip_count = 0;
                    $this->gameimporterjob_update_count($single_row->batch);
                    if($check_dispatch_state) {
                        //skipping because we recently dispatched already, to prevent slow queue double processing. Cache takes 5 minutes, you can add extra logging in here or functions.
                        $this->info('Skipped game id '.$single_row->gid.' as we recently dispatched this import job already.');
                        $skip_count++;
                    } else {
                        $dispatch = $kernel->start_job($single_row->gid);
                        $dispatch_count++;
                        Cache::put('casino-dog:database-observe::RawGameslist::dispatched::'.$single_row->id, 1, now()->addMinutes(15));
                    }
                }
                $this->info($count.' rows found in RawGameslist table.');
                $this->info($skip_count.' games skipped because we recently dispatched this game.');
                $this->info($dispatch_count.' games dispatched job to transfer to main gamelist.');
            }
        } else {
            $this->info('No raw imported games found with state `NEW`.');
        }
    }

}
