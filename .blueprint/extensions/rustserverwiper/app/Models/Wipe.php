<?php

namespace Pterodactyl\BlueprintFramework\Extensions\rustserverwiper\Models;

use DateTimeZone;
use Carbon\Carbon;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Model;

class Wipe extends Model
{
    public const RESOURCE_NAME = 'wipe';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wipes';

    /**
     * Default values when creating the model. We want to switch to disabling OOM killer
     * on server instances unless the user specifies otherwise in the request.
     *
     * @var array
     */
    protected $attributes = [
        'random_seed' => false,
        'random_level' => false,
        'blueprints' => false,
        'repeat' => '[]',
    ];

    protected $casts = [
        'repeat' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [self::CREATED_AT, self::UPDATED_AT];

    /**
     * Fields that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', self::CREATED_AT, self::UPDATED_AT];

    /**
     * @var array
     */
    public static array $validationRules = [
        'server_id' => 'bail|required|numeric|exists:servers,id',
        'name' => 'required|string|min:1|max:191',
        'description' => 'required|string',
        'size' => 'nullable|numeric',
        'seed' => 'nullable|numeric',
        'random_seed' => 'boolean',
        'random_level' => 'boolean',
        'level' => 'nullable|url',
        'files' => 'nullable|string',
        'blueprints' => 'boolean',
        'time' => 'nullable|date', // after_or_equal:now can't be used here as it will use the default application timezone instead of the timezone the server has. This will be an issue when submitting older dates using the API request, however, this won't matter as it will still run the wipe the next minute.
        'ran_at' => 'nullable|date', // after_or_equal:now can't be used here as it will use the default application timezone instead of the timezone the server has. This is not an issue, as ran_at can not be set by API.
        'repeat' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commands()
    {
        return $this->hasMany(WipeCommand::class);
    }

    public function closest()
    {
        $timezone = $this->server->timezone ?? DateTimeZone::listIdentifiers(DateTimeZone::ALL)[0];
        $now = Carbon::now(new DateTimeZone($timezone));
        $closest = $this->time ? Carbon::parse($this->time) : null;

        foreach ($this->repeat as $value) {
            [$week, $weekday, $time] = explode(' ', $value);
            [$hour, $minute] = explode(':', $time);

            $day = Carbon::create($now->year, $now->month, 1, $hour, $minute, 0, $timezone);

            while ($day->dayOfWeekIso != (int)$weekday) {
                $day->addDay();
            }

            $day->addWeeks((int)$week - 1);

            if ($day->month !== $now->month) {
                continue;
            }

            if ($day->lessThan($now->copy()->startOfMinute())) {
                $day->addMonthNoOverflow()->day(1);
                while ($day->dayOfWeekIso != (int)$weekday) {
                    $day->addDay();
                }
                $day->addWeeks((int)$week - 1);
            }

            if (!$closest || $day->lessThan($closest)) {
                $closest = $day;
            }
        }

        return $closest;
    }
}