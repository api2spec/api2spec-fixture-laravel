<?php

declare(strict_types=1);

namespace App\Http\Requests\Teapot;

use App\Enums\TeapotMaterial;
use App\Enums\TeapotStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PATCH (partial update) of a teapot.
 *
 * @property string|null $name Teapot name (optional)
 * @property string|null $material Teapot material (optional)
 * @property int|null $capacityMl Capacity in milliliters (optional)
 * @property string|null $style Teapot style (optional)
 * @property string|null $description Description (optional)
 */
class PatchTeapotRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:1', 'max:100'],
            'material' => ['sometimes', 'string', Rule::enum(TeapotMaterial::class)],
            'capacityMl' => ['sometimes', 'integer', 'min:1', 'max:5000'],
            'style' => ['sometimes', 'string', Rule::enum(TeapotStyle::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
