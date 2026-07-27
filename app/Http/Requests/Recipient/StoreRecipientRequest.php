<?php

namespace App\Http\Requests\Recipient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('status', 'active'),
            ],
            'code' => ['required', 'string', 'max:50', 'unique:recipients,code'],
            'name' => ['required', 'string', 'max:255'],
            'portion_count' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
