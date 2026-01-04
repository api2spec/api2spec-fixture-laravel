<?php

declare(strict_types=1);

namespace App\Http\Requests\Tea;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PUT (full replacement) of a tea.
 *
 * @property string $name Tea name (required)
 * @property string $type Tea type (required)
 * @property string|null $origin Origin region (optional)
 * @property string $caffeineLevel Caffeine level (required)
 * @property int $steepTempCelsius Steeping temperature (required)
 * @property int $steepTimeSeconds Steeping time (required)
 * @property string|null $description Description (optional)
 */
class UpdateTeaRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::enum(TeaType::class)],
            'origin' => ['sometimes', 'nullable', 'string', 'max:100'],
            'caffeineLevel' => ['required', 'string', Rule::enum(CaffeineLevel::class)],
            'steepTempCelsius' => ['required', 'integer', 'min:60', 'max:100'],
            'steepTimeSeconds' => ['required', 'integer', 'min:1', 'max:600'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
