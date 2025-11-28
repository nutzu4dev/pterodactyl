<?php

namespace Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Requests;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class DeleteWipeRequest extends ClientApiRequest
{
    /**
     * Determine if the API user has permission to perform this action.
     */
    public function permission(): string
    {
        return Permission::ACTION_WIPE_MANAGE;
    }
}
