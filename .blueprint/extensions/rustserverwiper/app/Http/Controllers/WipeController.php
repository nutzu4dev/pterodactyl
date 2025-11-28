<?php

namespace Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Controllers;

use DateTimeZone;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Pterodactyl\Models\Server;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\Wipe;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\WipeMap;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\WipeCommand;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Jobs\WipeServerJob;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Transformers\WipeTransformer;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Requests\WipeRequest;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Requests\AddMapRequest;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Requests\TimezoneRequest;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Requests\DeleteWipeRequest;

class WipeController extends ClientApiController
{
    /**
     * ServersController constructor.
     */
    public function __construct(protected DaemonPowerRepository $powerRepository)
    {
        parent::__construct();
    }

    public function index(Server $server): array
    {
        $timezone = $server->timezone ?? DateTimeZone::listIdentifiers(DateTimeZone::ALL)[0];

        return [
            'name' => $server->variables->where('env_variable', 'HOSTNAME')->first()->server_value,
            'description' => $server->variables->where('env_variable', 'DESCRIPTION')->first()->server_value,
            'timezones' => DateTimeZone::listIdentifiers(DateTimeZone::ALL),
            'maps' => $server->wipemaps,
            'wipes' => $this->fractal->collection(Wipe::where('server_id', $server->id)
                ->where(function ($query) use ($timezone) {
                    $query->where('time', '>', new Carbon(Carbon::now(), new DateTimeZone($timezone)))
                        ->orWhereNull('time');
                })
                ->get())
                ->transformWith($this->getTransformer(WipeTransformer::class))
                ->toArray(),
        ];
    }

    public function store(Server $server, WipeRequest $request, $wipe = null): JsonResponse
    {
        $data = $request->all();
        if (count($data['repeat'])) $data['time'] = null;

        if (!$wipe) {
            $wipe = array_merge(['uuid' => Uuid::uuid4()->toString(), 'server_id' => $server->id], $data);

            if ($data['schedule']) {
                $wipe = Wipe::create($wipe);
                foreach ($request->input('commands') as $key => $value) {
                    if ($value) {
                        WipeCommand::create([
                            'wipe_id' => $wipe->id,
                            'command' => $value,
                            'time' => $request->input('command_times')[$key],
                        ]);
                    }
                }
            } else {
                $this->powerRepository->setServer($server)->send('stop');
                dispatch(new WipeServerJob($server, $request->all()))->delay(Carbon::now()->addMinute());
            }
        } else {
            $wipe = Wipe::where('id', $wipe)->first();

            foreach ($wipe->commands as $command) {
                $command->delete();
            }

            foreach ($request->input('commands') as $key => $value) {
                if ($value) {
                    WipeCommand::create([
                        'wipe_id' => $wipe->id,
                        'command' => $value,
                        'time' => $request->input('command_times')[$key],
                    ]);
                }
            }

            $wipe->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'size' => $data['size'],
                'seed' => $data['seed'],
                'random_seed' => $data['random_seed'],
                'random_level' => $data['random_level'],
                'level' => $data['level'],
                'files' => $data['files'],
                'blueprints' => $data['blueprints'],
                'time' => $data['time'],
                'repeat' => $data['repeat'],
            ]);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function timezone(Server $server, TimezoneRequest $request): JsonResponse
    {
        $server->update([
            'timezone' => $request->input('timezone'),
        ]);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function delete(DeleteWipeRequest $request, Server $server, Wipe $wipe): JsonResponse
    {
        foreach ($wipe->commands as $command) {
            $command->delete();
        }

        $wipe->delete();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function map(AddMapRequest $request, Server $server): JsonResponse
    {
        WipeMap::create([
            'server_id' => $server->id,
            'name' => $request->input('name'),
            'map' => $request->input('map'),
        ]);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function deleteMap(DeleteWipeRequest $request, Server $server, WipeMap $wipemap)
    {
        $wipemap->delete();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
