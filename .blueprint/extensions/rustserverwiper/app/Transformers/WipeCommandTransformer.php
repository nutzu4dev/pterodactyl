<?php

namespace Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Transformers;

use Pterodactyl\Transformers\Api\Client\BaseClientTransformer;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\WipeCommand;

class WipeCommandTransformer extends BaseClientTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return WipeCommand::RESOURCE_NAME;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(WipeCommand $model)
    {
        return $model->toArray();
    }
}
