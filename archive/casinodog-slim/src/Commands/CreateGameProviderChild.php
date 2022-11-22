<?php

namespace Wainwright\CasinoDog\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;
use DB;

class CreateGameProviderChild extends Command
{

    protected $signature = 'casino-dog:create-game-provider-child';

    public $description = 'Scaffold game provider that forwards to another parent game provider (for example game providers using the same external API).';

    public function handle()
    {   
        $this->newLine();
        $this->info(' IMPORTANT:');
        $this->line(' Provider name should allign with the provider used within gameslist.');
        $this->newLine();
        $game_provider = $this->ask('Enter the new game provider name');

        if($game_provider === NULL) {
            $this->components->error('Game provider name cannot be empty.');
            die();
        }

        $this->newLine();
        $this->info(' IMPORTANT:');
        $this->line(' Parent provider needs to be existing within casino-dog config.');
        $this->newLine();
        $game_provider_parent = $this->ask('Enter the parent gameprovider');

        $parent_select = config('casino-dog.games.'.$game_provider_parent.'.controller');
        if($parent_select === NULL) {
            $this->components->error('Parent gameprovider does not exist.');
            die();
        }

        $this->write_gameprovider_child($game_provider, $game_provider_parent);

        return self::SUCCESS;
    }

    public function write_gameprovider_child($game_provider, $game_provider_parent)
    {

        $game_provider_lower = strtolower($game_provider);
        $game_provider_parent = strtolower($game_provider_parent);
        $game_provider_capitalstart = ucfirst($game_provider_lower);
        if(is_dir(__DIR__ . '../../../src/Controllers/Game/'.$game_provider_capitalstart)) {
                $this->danger('Directory already exists within controller.');
                die();
        }

        if (!is_dir($stubsPathBaseDir = __DIR__ . '../../../src/Controllers/Game/'.$game_provider_capitalstart)) {
            (new Filesystem)->makeDirectory($stubsPathBaseDir, 0755, true);
        }
        if (!is_dir($stubsPathBaseDirAssets = __DIR__ . '../../../src/Controllers/Game/'.$game_provider_capitalstart.'/AssetStorage')) {
            (new Filesystem)->makeDirectory($stubsPathBaseDirAssets, 0755, true);
        }
        $viewBladeDir = __DIR__ . '../../../resources/views';

        $files = [
            __DIR__ . '../../../stubs/create_game_provider_child/Main.stub' => $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php',
        ];

        $this->writeStubs($files);

        $replacement = replaceInFile('[GAME_PROVIDER_TAG_CAPITALSTART]', $game_provider_capitalstart, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php');
        $replacement = replaceInFile('[GAME_PROVIDER_TAG_ALL-LOWER]', $game_provider_lower, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php');
        $replacement = replaceInFile('[GAME_PROVIDER_TAG_PARENT_CLASS]', config('casino-dog.games.'.$game_provider_parent.'.controller'), $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php');


        $this->info('Controllers created.');
        $this->info('Now you only need to add the game provider to config/casino-dog.php with tag: '.$game_provider_lower);
    }

    public function writeStubs($files):void {
        foreach ($files as $from => $to) {
            if (!file_exists($to)) {
                file_put_contents($to, file_get_contents($from));
                $this->info('> '.$to.' saved.');
            } else {
                if($this->confirm($to.' exists already. Do you want to overwrite this file?')) {
                    file_put_contents($to, file_get_contents($from));
                    $this->info('> '.$to.' saved.');
                } else {
                    $this->error('Skipped '.$to);
                }
            }
        }
    }

}
