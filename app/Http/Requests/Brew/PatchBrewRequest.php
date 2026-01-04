<?php

declare(strict_types=1);

namespace App\Http\Requests\Brew;

use App\Enums\BrewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request body for PATCH of a brew.
 *
 * @property string|null $status Brew status (optional, enum)
 * @property string|null $notes Brewing notes (optional, max 500 chars)
 * @property string|null $completedAt Completion timestamp (optional, ISO 8601)
 */
class PatchBrewRequest extends FormRequest
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
            'status' => ['sometimes', 'string', Rule::enum(BrewStatus::class)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
            'completedAt' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
