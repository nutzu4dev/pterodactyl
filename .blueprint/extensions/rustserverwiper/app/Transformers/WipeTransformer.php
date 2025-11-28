<?php

namespace Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Transformers;

use League\Fractal\Resource\Collection;
use Pterodactyl\Transformers\Api\Client\BaseClientTransformer;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\Wipe;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\WipeCommand;

class WipeTransformer extends BaseClientTransformer
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'commands'
    ];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return Wipe::RESOURCE_NAME;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Wipe $model)
    {
        return array_merge($model->toArray(), ['closest' => $model->closest()->format('Y-m-d H:i:s')]);
    }

    /**
     * Returns the server commands associated with this wipe.
     *
     * @throws \Pterodactyl\Exceptions\Transformer\InvalidTransformerLevelException
     */
    public function includeCommands(Wipe $model): Collection
    {
        return $this->collection(
            $model->commands,
            $this->makeTransformer(WipeCommandTransformer::class),
            WipeCommand::RESOURCE_NAME
        );
    }
}
