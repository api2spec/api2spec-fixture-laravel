<?php

declare(strict_types=1);

namespace App\Http\Requests\Brew;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request body for creating a brew.
 *
 * @property string $teapotId Teapot UUID (required)
 * @property string $teaId Tea UUID (required)
 * @property int|null $waterTempCelsius Water temperature (optional, 60-100)
 * @property string|null $notes Brewing notes (optional, max 500 chars)
 */
class StoreBrewRequest extends FormRequest
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
            'teapotId' => ['required', 'string', 'uuid'],
            'teaId' => ['required', 'string', 'uuid'],
            'waterTempCelsius' => ['sometimes', 'integer', 'min:60', 'max:100'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
