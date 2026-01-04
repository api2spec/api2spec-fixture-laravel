<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Support\Carbon;

/**
 * Tea entity.
 *
 * @property string $id UUID
 * @property string $name Tea name (1-100 chars)
 * @property TeaType $type Tea type
 * @property string|null $origin Origin region (max 100 chars)
 * @property CaffeineLevel $caffeine_level Caffeine level
 * @property int $steep_temp_celsius Steeping temperature (60-100)
 * @property int $steep_time_seconds Steeping time (1-600)
 * @property string|null $description Optional description (max 1000 chars)
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 */
class Tea
{
    public function __construct(
        public string $id,
        public string $name,
        public TeaType $type,
        public ?string $origin,
        public CaffeineLevel $caffeine_level,
        public int $steep_temp_celsius,
        public int $steep_time_seconds,
        public ?string $description,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'origin' => $this->origin,
            'caffeineLevel' => $this->caffeine_level->value,
            'steepTempCelsius' => $this->steep_temp_celsius,
            'steepTimeSeconds' => $this->steep_time_seconds,
            'description' => $this->description,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
