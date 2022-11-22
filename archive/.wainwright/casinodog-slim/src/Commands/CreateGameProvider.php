<?php

namespace Wainwright\CasinoDog\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;
use DB;

class CreateGameProvider extends Command
{

    protected $signature = 'casino-dog:create-game-provider {name?}';

    public $description = 'Scaffold new game provider views and controllers.';

    public function handle()
    {   
        if ($this->argument('name')) {
            $game_provider = $this->argument('name');
        } else {
            $this->newLine();
            $this->info(' IMPORTANT:');
            $this->line(' Provider name should allign with the provider used within gameslist.');
            $this->newLine();
            $game_provider = $this->ask('Enter the new game provider name');
        }

        if($game_provider === NULL) {
            $this->components->error('Game provider name cannot be empty.');
        }

        $this->writeGameControllers($game_provider);


        return self::SUCCESS;
    }

    public function writeGameControllers($game_provider)
    {

        $game_provider_lower = strtolower($game_provider);
        $game_provider_capitalstart = ucfirst($game_provider_lower);

        if (!is_dir($stubsPathBaseDir = __DIR__ . '../../../src/Controllers/Game/'.$game_provider_capitalstart)) {
            (new Filesystem)->makeDirectory($stubsPathBaseDir, 0755, true);
        }
        if (!is_dir($stubsPathBaseDirAssets = __DIR__ . '../../../src/Controllers/Game/'.$game_provider_capitalstart.'/AssetStorage')) {
            (new Filesystem)->makeDirectory($stubsPathBaseDirAssets, 0755, true);
        }
        $viewBladeDir = __DIR__ . '../../../resources/views';

        $files = [
            __DIR__ . '../../../stubs/create_game_provider/Game.stub' => $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Game.php',
            __DIR__ . '../../../stubs/create_game_provider/Main.stub' => $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php',
            __DIR__ . '../../../stubs/create_game_provider/Sessions.stub' => $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Sessions.php',
            __DIR__ . '../../../stubs/create_game_provider/BladeView.stub' => $viewBladeDir . '/launcher-content-'.$game_provider_lower.'.blade.php',
        ];

        $this->writeStubs($files);

        $replacement = $this->replaceInFile('[GAME_PROVIDER_TAG_CAPITALSTART]', $game_provider_capitalstart, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Game.php');
        $replacement = $this->replaceInFile('[GAME_PROVIDER_TAG_ALL-LOWER]', $game_provider_lower, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Game.php');
        $replacement = $this->replaceInFile('[GAME_PROVIDER_TAG_CAPITALSTART]', $game_provider_capitalstart, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php');
        $replacement = $this->replaceInFile('[GAME_PROVIDER_TAG_ALL-LOWER]', $game_provider_lower, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Main.php');
        $replacement = $this->replaceInFile('[GAME_PROVIDER_TAG_CAPITALSTART]', $game_provider_capitalstart, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Sessions.php');
        $replacement = $this->replaceInFile('[GAME_PROVIDER_TAG_ALL-LOWER]', $game_provider_lower, $stubsPathBaseDir . '/'.$game_provider_capitalstart.'Sessions.php');

        $this->info('Controllers created.');
        $this->info('Now you only need to add the game provider to config/casino-dog.php with tag: '.$game_provider_lower);
    }

    public function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
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
