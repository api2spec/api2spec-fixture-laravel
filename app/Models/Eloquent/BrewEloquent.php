<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Enums\BrewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Brew session Eloquent model.
 *
 * @property string $id UUID
 * @property string $teapot_id Teapot UUID
 * @property string $tea_id Tea UUID
 * @property BrewStatus $status Brew status
 * @property int $water_temp_celsius Water temperature (60-100)
 * @property string|null $notes Brewing notes (max 500 chars)
 * @property \Illuminate\Support\Carbon $started_at Start timestamp
 * @property \Illuminate\Support\Carbon|null $completed_at Completion timestamp
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 */
class BrewEloquent extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'brews';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'teapot_id',
        'tea_id',
        'status',
        'water_temp_celsius',
        'notes',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => BrewStatus::class,
        'water_temp_celsius' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the teapot for this brew.
     *
     * @return BelongsTo<TeapotEloquent, $this>
     */
    public function teapot(): BelongsTo
    {
        return $this->belongsTo(TeapotEloquent::class, 'teapot_id');
    }

    /**
     * Get the tea for this brew.
     *
     * @return BelongsTo<TeaEloquent, $this>
     */
    public function tea(): BelongsTo
    {
        return $this->belongsTo(TeaEloquent::class, 'tea_id');
    }

    /**
     * Get the steeps for this brew.
     *
     * @return HasMany<SteepEloquent, $this>
     */
    public function steeps(): HasMany
    {
        return $this->hasMany(SteepEloquent::class, 'brew_id');
    }
}
