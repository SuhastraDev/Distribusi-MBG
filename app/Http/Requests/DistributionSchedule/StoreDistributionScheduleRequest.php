<?php

namespace App\Http\Requests\DistributionSchedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDistributionScheduleRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:distribution_schedules,code'],
            'scheduled_date' => ['required', 'date'],
            'officer_id' => [
                'required',
                Rule::exists('officers', 'id')->where('status', 'active'),
            ],
            'depot_location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('status', 'active')->where('type', 'depot'),
            ],
            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('recipients', 'id')->where('status', 'active'),
            ],
            'status' => ['required', 'in:draft,scheduled,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
