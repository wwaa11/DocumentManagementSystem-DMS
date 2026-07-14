<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentTrainingRequest extends FormRequest
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
            'approver' => 'required|array',
            'mentors_userid' => 'nullable|array',
            'participants_userid' => 'required|array',
            'training_name' => 'required|string',
            'date_mode' => 'required|string|in:range,specific',
            'start_date' => 'required_if:date_mode,range|nullable|date',
            'end_date' => 'required_if:date_mode,range|nullable|date',
            'start_time' => 'required_if:date_mode,range|nullable',
            'end_time' => 'required_if:date_mode,range|nullable',
            'specific_date' => 'required_if:date_mode,specific|array',
            'specific_start_time' => 'required_if:date_mode,specific|array',
            'specific_end_time' => 'required_if:date_mode,specific|array',
            'duration_hours' => 'required|integer',
            'duration_minutes' => 'nullable|integer',
            'source_type' => 'required|string',
        ];
    }
}
