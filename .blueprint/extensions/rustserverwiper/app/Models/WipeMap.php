<?php

namespace Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models;

use Pterodactyl\Models\Model;

class WipeMap extends Model
{
    /**
     * Fields that are mass assignable.
     */
    protected $fillable = [
        'server_id',
        'name',
        'map',
    ];

    public static array $validationRules = [
        'server_id' => 'bail|required|numeric|exists:servers,id',
        'name' => 'nullable|string',
        'map' => 'required|url',
    ];
}