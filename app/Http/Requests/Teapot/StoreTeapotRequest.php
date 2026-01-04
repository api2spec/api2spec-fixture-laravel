<?php

declare(strict_types=1);

namespace App\Http\Requests\Teapot;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for creating a teapot.
 *
 * @property string $name Teapot name (required, 1-100 chars)
 * @property string $material Teapot material (required, enum)
 * @property int $capacityMl Capacity in milliliters (required, 1-5000)
 * @property string|null $style Teapot style (optional, enum, default: english)
 * @property string|null $description Description (optional, max 500 chars)
 */
class StoreTeapotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'material' => ['required', 'string', Rule::enum(TeapotMaterial::class)],
            'capacityMl' => ['required', 'integer', 'min:1', 'max:5000'],
            'style' => ['sometimes', 'string', Rule::enum(TeapotStyle::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get validated data with defaults applied.
     *
     * @return array<string, mixed>
     */
    public function validatedWithDefaults(): array
    {
        $data = $this->validated();
        $data['style'] ??= TeapotStyle::English->value;
        return $data;
    }
}
