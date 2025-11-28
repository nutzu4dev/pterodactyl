<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models\Wipe;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $repeat = [];
        foreach (Wipe::all() as $wipe) {
            $amsterdamTime = Carbon::createFromFormat('H:i', '20:00', 'Europe/Amsterdam');
            $converted = $amsterdamTime->copy()->setTimezone($wipe->server->timezone);

            $repeat[] = !$wipe->repeat && !$wipe->force ? [] : ($wipe->repeat ? [
                '1 ' . Carbon::parse($wipe->time)->dayOfWeekIso . ' ' . Carbon::parse($wipe->time)->format('H:i'),
                '2 ' . Carbon::parse($wipe->time)->dayOfWeekIso . ' ' . Carbon::parse($wipe->time)->format('H:i'),
                '3 ' . Carbon::parse($wipe->time)->dayOfWeekIso . ' ' . Carbon::parse($wipe->time)->format('H:i'),
                '4 ' . Carbon::parse($wipe->time)->dayOfWeekIso . ' ' . Carbon::parse($wipe->time)->format('H:i'),
                '5 ' . Carbon::parse($wipe->time)->dayOfWeekIso . ' ' . Carbon::parse($wipe->time)->format('H:i'),
            ] : ['1 4 ' . $converted->format('H:i')]);
        }

        Schema::table('wipes', function (Blueprint $table) {
            $table->json('repeat')->change();
            $table->dropColumn('force');
        });

        foreach(Wipe::all() as $key => $wipe) {
            $wipe->update([
                'repeat' => $repeat[$key],
                'time' => count($repeat[$key]) ? null : $wipe->time,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wipes', function (Blueprint $table) {
            $table->boolean('repeat')->change();
            $table->boolean('force')->after('blueprints');
        });
    }
};