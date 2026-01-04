<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BrewStatus;
use Illuminate\Support\Carbon;

/**
 * Brew session entity.
 *
 * @property string $id UUID
 * @property string $teapot_id Teapot UUID
 * @property string $tea_id Tea UUID
 * @property BrewStatus $status Brew status
 * @property int $water_temp_celsius Water temperature (60-100)
 * @property string|null $notes Brewing notes (max 500 chars)
 * @property Carbon $started_at Start timestamp
 * @property Carbon|null $completed_at Completion timestamp
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 */
class Brew
{
    public function __construct(
        public string $id,
        public string $teapot_id,
        public string $tea_id,
        public BrewStatus $status,
        public int $water_temp_celsius,
        public ?string $notes,
        public Carbon $started_at,
        public ?Carbon $completed_at,
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
            'teapotId' => $this->teapot_id,
            'teaId' => $this->tea_id,
            'status' => $this->status->value,
            'waterTempCelsius' => $this->water_temp_celsius,
            'notes' => $this->notes,
            'startedAt' => $this->started_at->toIso8601String(),
            'completedAt' => $this->completed_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
