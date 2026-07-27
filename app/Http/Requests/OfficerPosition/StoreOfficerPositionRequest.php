<?php

namespace App\Http\Requests\OfficerPosition;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficerPositionRequest extends FormRequest
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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
