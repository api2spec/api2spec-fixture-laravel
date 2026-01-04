<?php

declare(strict_types=1);

namespace App\Http\Requests\Teapot;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PUT (full replacement) of a teapot.
 *
 * @property string $name Teapot name (required)
 * @property string $material Teapot material (required)
 * @property int $capacityMl Capacity in milliliters (required)
 * @property string $style Teapot style (required)
 * @property string|null $description Description (optional)
 */
class UpdateTeapotRequest extends FormRequest
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
            'style' => ['required', 'string', Rule::enum(TeapotStyle::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
