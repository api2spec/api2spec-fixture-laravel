<?php

declare(strict_types=1);

namespace App\Http\Requests\Tea;

use App\Enums\CaffeineLevel;
use App\Enums\TeaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for creating a tea.
 *
 * @property string $name Tea name (required, 1-100 chars)
 * @property string $type Tea type (required, enum)
 * @property string|null $origin Origin region (optional, max 100 chars)
 * @property string|null $caffeineLevel Caffeine level (optional, default: medium)
 * @property int $steepTempCelsius Steeping temperature (required, 60-100)
 * @property int $steepTimeSeconds Steeping time (required, 1-600)
 * @property string|null $description Description (optional, max 1000 chars)
 */
class StoreTeaRequest extends FormRequest
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
            'caffeineLevel' => ['sometimes', 'string', Rule::enum(CaffeineLevel::class)],
            'steepTempCelsius' => ['required', 'integer', 'min:60', 'max:100'],
            'steepTimeSeconds' => ['required', 'integer', 'min:1', 'max:600'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
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
        $data['caffeineLevel'] ??= CaffeineLevel::Medium->value;
        return $data;
    }
}
