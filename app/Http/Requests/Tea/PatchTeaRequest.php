<?php

declare(strict_types=1);

namespace App\Http\Requests\Tea;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PATCH (partial update) of a tea.
 *
 * @property string|null $name Tea name (optional)
 * @property string|null $type Tea type (optional)
 * @property string|null $origin Origin region (optional)
 * @property string|null $caffeineLevel Caffeine level (optional)
 * @property int|null $steepTempCelsius Steeping temperature (optional)
 * @property int|null $steepTimeSeconds Steeping time (optional)
 * @property string|null $description Description (optional)
 */
class PatchTeaRequest extends FormRequest
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
            'type' => ['sometimes', 'string', Rule::enum(TeaType::class)],
            'origin' => ['sometimes', 'nullable', 'string', 'max:100'],
            'caffeineLevel' => ['sometimes', 'string', Rule::enum(CaffeineLevel::class)],
            'steepTempCelsius' => ['sometimes', 'integer', 'min:60', 'max:100'],
            'steepTimeSeconds' => ['sometimes', 'integer', 'min:1', 'max:600'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
