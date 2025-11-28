<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Middleware\Activity\ServerSubject;
use Pterodactyl\Http\Middleware\Api\Client\Server\ResourceBelongsToServer;
use Pterodactyl\Http\Middleware\Api\Client\Server\AuthenticateServerAccess;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Http\Controllers;

Route::group([
    'prefix' => '/servers/{server}',
    'middleware' => [
        ServerSubject::class,
        AuthenticateServerAccess::class,
        ResourceBelongsToServer::class,
    ],
], function () {
    Route::group(['prefix' => '/wipe'], function () {
        Route::get('/', [Controllers\WipeController::class, 'index']);

        Route::post('/timezone', [Controllers\WipeController::class, 'timezone']);
        Route::post('/map', [Controllers\WipeController::class, 'map']);
        Route::post('/{wipe:id?}', [Controllers\WipeController::class, 'store']);

        Route::delete('/map/{wipemap:id}', [Controllers\WipeController::class, 'deleteMap']);
        Route::delete('/{wipe:id}', [Controllers\WipeController::class, 'delete']);
    });
});
