<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Support\Carbon;

/**
 * Teapot entity.
 *
 * @property string $id UUID
 * @property string $name Teapot name (1-100 chars)
 * @property TeapotMaterial $material Teapot material
 * @property int $capacity_ml Capacity in milliliters (1-5000)
 * @property TeapotStyle $style Teapot style
 * @property string|null $description Optional description (max 500 chars)
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 */
class Teapot
{
    public function __construct(
        public string $id,
        public string $name,
        public TeapotMaterial $material,
        public int $capacity_ml,
        public TeapotStyle $style,
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
            'material' => $this->material->value,
            'capacityMl' => $this->capacity_ml,
            'style' => $this->style->value,
            'description' => $this->description,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
