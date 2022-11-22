<?php
namespace Wainwright\CasinoDog\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Wainwright\CasinoDog\Controllers\DataController;
class DefaultGameslistRetrieve implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $provider;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function handle()
    {
        $provider = $this->provider;

    	$command = 'casino-dog:retrieve-default-gameslist '.$provider.' upsert';
         \Artisan::call($command);
        return true;
    }

}
