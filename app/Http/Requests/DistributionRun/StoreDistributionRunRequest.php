<?php

namespace App\Http\Requests\DistributionRun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDistributionRunRequest extends FormRequest
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
            'distribution_schedule_id' => [
                'required',
                'integer',
                Rule::exists('distribution_schedules', 'id')->where('status', 'scheduled'),
                Rule::unique('distribution_runs', 'distribution_schedule_id'),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
