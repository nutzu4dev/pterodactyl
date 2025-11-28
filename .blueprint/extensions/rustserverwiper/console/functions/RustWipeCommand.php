<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\Wipe;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Jobs\WipeServerJob;

$commandRepository = App::make(DaemonCommandRepository::class);
$powerRepository = App::make(DaemonPowerRepository::class);

$wipes = Wipe::all();
foreach ($wipes->filter(function ($wipe) {
    return !$wipe->ran_at || !$wipe->time;
}) as $wipe) {
    if ($wipe->server) {
        if ($wipe->server->status !== 'suspended') {
            try {
                $wipe_time = $wipe->closest();

                $now = new Carbon(Carbon::now(), new DateTimeZone($wipe->server->timezone ?? DateTimeZone::listIdentifiers(DateTimeZone::ALL)[0]));
                foreach ($wipe->commands as $command) {
                    if ($now->copy()->addMinutes($command->time)->startOfMinute()->format('Y-m-d H:i') === Carbon::parse($wipe_time)->startOfMinute()->format('Y-m-d H:i')) {
                        $commandRepository->setServer($wipe->server)->send($command->command);
                    }
                }

                if ($wipe_time <= $now) {
                    $powerRepository->setServer($wipe->server)->send('stop');
                    dispatch(new WipeServerJob($wipe->server, $wipe->toArray()))->delay(Carbon::now()->addMinute());
                    $wipe->update([
                        'ran_at' => Carbon::now(),
                    ]);
                }
            } catch (\Exception) {
            }
        }
    } else {
        $wipe->delete();
    }
}