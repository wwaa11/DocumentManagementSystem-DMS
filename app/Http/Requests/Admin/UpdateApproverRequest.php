<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApproverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(\App\Services\Admin\ApproverAdminService::class)->canManageApprovers($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department' => 'required|string',
            'userid' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|string|max:255',
        ];
    }
}
