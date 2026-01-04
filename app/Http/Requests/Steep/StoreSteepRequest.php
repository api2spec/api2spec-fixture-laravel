<?php

declare(strict_types=1);

namespace App\Http\Requests\Steep;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request body for creating a steep.
 *
 * @property int $durationSeconds Steep duration (required, min 1)
 * @property int|null $rating Rating 1-5 (optional)
 * @property string|null $notes Tasting notes (optional, max 200 chars)
 */
class StoreSteepRequest extends FormRequest
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
            'durationSeconds' => ['required', 'integer', 'min:1'],
            'rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:200'],
        ];
    }
}
