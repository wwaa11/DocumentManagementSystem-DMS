<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Services\Admin\ApproverAdminService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ApproverAdminService $service */
        $service = app(ApproverAdminService::class);

        return $service->canManageRoles($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ApproverAdminService $service */
        $service = app(ApproverAdminService::class);
        $assignable = $service->assignableRoleKeys((string) $this->user()?->role);

        $roleRule = Rule::in(
            $assignable === null
                ? array_merge(['user'], array_keys($service->allRoleLabels()))
                : $assignable
        );

        return [
            'userid' => 'required|string|max:255',
            'role' => ['required', 'string', 'max:255', $roleRule],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var ApproverAdminService $service */
            $service = app(ApproverAdminService::class);
            $target = User::query()->where('userid', $this->input('userid'))->first();

            if (! $target) {
                $validator->errors()->add('userid', 'ไม่พบผู้ใช้งานนี้');

                return;
            }

            if (! $service->canAssignRole(
                (string) $this->user()?->role,
                (string) $target->role,
                (string) $this->input('role')
            )) {
                $validator->errors()->add('role', 'คุณไม่มีสิทธิ์กำหนดบทบาทนี้');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'คุณไม่มีสิทธิ์กำหนดบทบาทนี้',
        ];
    }
}
