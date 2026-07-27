<?php

namespace App\Http\Requests\DistributionRun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRunDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role?->name, ['admin', 'petugas'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['arrived', 'delivered', 'skipped'])],
            'delivered_portion_count' => [
                'nullable',
                'integer',
                'min:0',
                'required_if:status,delivered',
            ],
            'proof_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
