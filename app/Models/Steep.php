<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;

/**
 * Steep cycle entity.
 *
 * @property string $id UUID
 * @property string $brew_id Parent brew UUID
 * @property int $steep_number Steep number (1st, 2nd, etc.)
 * @property int $duration_seconds Steep duration (min 1)
 * @property int|null $rating Rating 1-5
 * @property string|null $notes Tasting notes (max 200 chars)
 * @property Carbon $created_at Creation timestamp
 */
class Steep
{
    public function __construct(
        public string $id,
        public string $brew_id,
        public int $steep_number,
        public int $duration_seconds,
        public ?int $rating,
        public ?string $notes,
        public Carbon $created_at,
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
            'brewId' => $this->brew_id,
            'steepNumber' => $this->steep_number,
            'durationSeconds' => $this->duration_seconds,
            'rating' => $this->rating,
            'notes' => $this->notes,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
