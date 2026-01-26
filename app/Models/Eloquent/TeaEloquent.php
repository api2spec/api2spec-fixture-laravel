<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tea Eloquent model.
 *
 * @property string $id UUID
 * @property string $name Tea name (1-100 chars)
 * @property TeaType $type Tea type
 * @property string|null $origin Origin region (max 100 chars)
 * @property CaffeineLevel $caffeine_level Caffeine level
 * @property int $steep_temp_celsius Steeping temperature (60-100)
 * @property int $steep_time_seconds Steeping time (1-600)
 * @property string|null $description Optional description (max 1000 chars)
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 */
class TeaEloquent extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'origin',
        'caffeine_level',
        'steep_temp_celsius',
        'steep_time_seconds',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => TeaType::class,
        'caffeine_level' => CaffeineLevel::class,
        'steep_temp_celsius' => 'integer',
        'steep_time_seconds' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the brews for this tea.
     *
     * @return HasMany<BrewEloquent, $this>
     */
    public function brews(): HasMany
    {
        return $this->hasMany(BrewEloquent::class, 'tea_id');
    }
}
