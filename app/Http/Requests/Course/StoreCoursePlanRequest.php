<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCoursePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->can_create_course) {
            return false;
        }

        $department = $this->input('department');

        return $department
            ? $user->canCreateCourseForDepartment((string) $department)
            : $user->canCreateCourseForDepartment();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'department' => ['required', 'string'],
            'courses' => ['required', 'array', 'min:1'],
            'courses.*.number' => ['required', 'string', 'max:50'],
            'courses.*.name' => ['required', 'string', 'max:255'],
            'courses.*.origin' => ['required', 'string'],
            'courses.*.objective' => ['required', 'string'],
            'courses.*.training_type' => ['required', Rule::in(['internal', 'external', 'elearning'])],
            'courses.*.schedule_months' => ['required', 'array', 'min:1'],
            'courses.*.schedule_months.*' => ['integer', 'between:1,12'],
            'courses.*.estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'courses.*.instructors' => ['required', 'array', 'min:1'],
            'courses.*.instructors.*.userid' => ['required', 'string'],
            'courses.*.instructors.*.name' => ['required', 'string'],
            'courses.*.instructors.*.position' => ['required', 'string'],
            'courses.*.instructors.*.source_type' => ['required', Rule::in(['internal', 'external'])],
            'courses.*.target_positions' => ['required', 'array', 'min:1'],
            'courses.*.target_positions.*' => ['required', 'string'],
            'courses.*.responsibles' => ['required', 'array', 'min:1'],
            'courses.*.responsibles.*.userid' => ['required', 'string'],
            'courses.*.responsibles.*.name' => ['required', 'string'],
            'courses.*.responsibles.*.position' => ['required', 'string'],
            'approver_level1' => ['required', 'array'],
            'approver_level1.userid' => ['required', 'string'],
            'approver_level1.name' => ['nullable', 'string'],
            'approver_level1.position' => ['nullable', 'string'],
            'approver_level1.email' => ['nullable', 'email'],
            'approver_level2' => ['required', 'array'],
            'approver_level2.userid' => ['required', 'string'],
            'approver_level2.name' => ['nullable', 'string'],
            'approver_level2.position' => ['nullable', 'string'],
            'approver_level2.email' => ['nullable', 'email'],
            'approver_level3' => ['required', 'array'],
            'approver_level3.userid' => ['required', 'string'],
            'approver_level3.name' => ['nullable', 'string'],
            'approver_level3.position' => ['nullable', 'string'],
            'approver_level3.email' => ['nullable', 'email'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.required' => 'กรุณาระบุปี',
            'department.required' => 'กรุณาเลือกแผนก',
            'courses.required' => 'กรุณาเพิ่มหลักสูตรอย่างน้อย 1 หลักสูตร',
            'courses.*.number.required' => 'กรุณาระบุลำดับที่ของหลักสูตร',
            'courses.*.name.required' => 'กรุณาระบุชื่อหลักสูตร',
            'courses.*.origin.required' => 'กรุณาระบุที่มาหลักสูตร',
            'courses.*.objective.required' => 'กรุณาระบุวัตถุประสงค์ของหลักสูตร',
            'courses.*.training_type.required' => 'กรุณาเลือกประเภทการฝึกอบรม',
            'courses.*.schedule_months.required' => 'กรุณาเลือกกำหนดการอย่างน้อย 1 เดือน',
            'courses.*.instructors.required' => 'กรุณาเพิ่มวิทยากรอย่างน้อย 1 คน',
            'courses.*.target_positions.required' => 'กรุณาเลือกกลุ่มเป้าหมายอย่างน้อย 1 ตำแหน่ง',
            'courses.*.responsibles.required' => 'กรุณาเพิ่มผู้รับผิดชอบหลักสูตรอย่างน้อย 1 คน',
            'approver_level1.userid.required' => 'กรุณาเลือกผู้อนุมัติระดับ 1',
            'approver_level2.userid.required' => 'กรุณาเลือกผู้อนุมัติระดับ 2',
            'approver_level3.userid.required' => 'กรุณาเลือกผู้อนุมัติระดับ 3',
        ];
    }
}
