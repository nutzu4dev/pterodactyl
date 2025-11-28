<?php

namespace Pterodactyl\Console\Commands\BlueprintFramework\Extensions\Rustserverwiper;

use Illuminate\Console\Command;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary as BlueprintExtensionLibrary;

class RustserverwiperSrujknnztaCommand extends Command
{
  protected $signature = 'rustserverwiper:p:server:wipe';
  protected $description = 'Checks for and excecutes due Rust server wipes.';

  public function __construct(
    private BlueprintExtensionLibrary $blueprint,
  ) { parent::__construct(); }

  public function handle()
  {
    $blueprint = $this->blueprint;
    require base_path().'/.blueprint/extensions/rustserverwiper/console/functions/RustWipeCommand.php';
  }
}
