<?php

namespace App\Http\Requests\Recipient;

use App\Models\Recipient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecipientRequest extends FormRequest
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
        /** @var Recipient $recipient */
        $recipient = $this->route('recipient');

        return [
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('status', 'active'),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('recipients', 'code')->ignore($recipient->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'portion_count' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
