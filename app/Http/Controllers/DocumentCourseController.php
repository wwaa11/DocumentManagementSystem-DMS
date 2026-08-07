<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateCoursePermissionRequest;
use App\Http\Requests\Course\StoreCoursePlanRequest;
use App\Http\Requests\Course\UpdateCoursePlanRequest;
use App\Models\CoursePlan;
use App\Services\Course\CoursePlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DocumentCourseController extends Controller
{
    public function __construct(private CoursePlanService $coursePlanService) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $this->coursePlanService->canAccessCourseModule($user)) {
            return redirect()
                ->route('document.index')
                ->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหลักสูตรการฝึกอบรม ประจำปี');
        }

        $year = $request->integer('year') ?: (int) now()->year;
        $forms = $this->coursePlanService->listPlans($user, $year);
        $canCreate = $this->coursePlanService->availableDepartmentsForCreate($user, $year) !== [];

        return view('document.course.index', [
            'forms' => $forms,
            'year' => $year,
            'canCreate' => $canCreate,
            'departments' => $user->courseDepartments(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $year = $request->integer('year') ?: (int) now()->year;

        if (! $this->coursePlanService->canAccessCourseModule($user)) {
            return redirect()
                ->route('document.index')
                ->with('error', 'คุณไม่มีสิทธิ์สร้างหลักสูตรการฝึกอบรม');
        }

        $departments = $this->coursePlanService->availableDepartmentsForCreate($user, $year);

        if ($departments === []) {
            return redirect()
                ->route('document.course', ['year' => $year])
                ->with('error', 'แผนกที่คุณมีสิทธิ์สร้างมีแบบฟอร์มครบทุกแผนกแล้วในปี '.$year);
        }

        return view('document.course.form', $this->formViewData($year, $departments));
    }

    public function store(StoreCoursePlanRequest $request): RedirectResponse
    {
        try {
            $plan = $this->coursePlanService->createPlan($request->user(), $request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['department' => $exception->getMessage()]);
        }

        return redirect()
            ->route('document.course.show', $plan)
            ->with('success', 'สร้างแบบฟอร์มหลักสูตรสำเร็จ และส่งอนุมัติแล้ว');
    }

    public function show(CoursePlan $course): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $this->coursePlanService->canViewPlan($user, $course)) {
            return redirect()
                ->route('document.index')
                ->with('error', 'คุณไม่มีสิทธิ์ดูแบบฟอร์มนี้');
        }

        $course->load([
            'creator',
            'approvers',
            'items.instructors',
            'items.targetPositions',
            'items.responsibles',
            'items.trainings',
        ]);

        return view('document.course.show', [
            'plan' => $course,
            'canEdit' => $user->canCreateCourseForDepartment($course->department),
            'pendingLevels' => $course->pendingLevelsFor($user->userid),
        ]);
    }

    public function approve(Request $request, CoursePlan $course): RedirectResponse
    {
        $decision = $request->input('decision') === 'reject' ? 'reject' : 'approve';

        try {
            $this->coursePlanService->decide(
                $course,
                $request->user(),
                $decision,
                $request->input('reason')
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('document.course.show', $course)
            ->with('success', $decision === 'approve' ? 'อนุมัติเรียบร้อยแล้ว' : 'ไม่อนุมัติเรียบร้อยแล้ว');
    }

    public function edit(CoursePlan $course): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->canCreateCourseForDepartment($course->department)) {
            return redirect()
                ->route('document.course.show', $course)
                ->with('error', 'คุณไม่มีสิทธิ์แก้ไขแบบฟอร์มนี้');
        }

        $course->load([
            'approvers',
            'items.instructors',
            'items.targetPositions',
            'items.responsibles',
        ]);

        $departments = $this->coursePlanService->availableDepartmentsForCreate(
            $user,
            (int) $course->year,
            $course->id
        );

        if (! in_array($course->department, $departments, true)) {
            $departments[] = $course->department;
            sort($departments);
        }

        return view('document.course.form', array_merge(
            $this->formViewData((int) $course->year, $departments, $course),
            ['plan' => $course]
        ));
    }

    public function update(UpdateCoursePlanRequest $request, CoursePlan $course): RedirectResponse
    {
        try {
            $this->coursePlanService->updatePlan($request->user(), $course, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['department' => $exception->getMessage()]);
        }

        return redirect()
            ->route('document.course.show', $course)
            ->with('success', 'แก้ไขแบบฟอร์มสำเร็จ และเริ่มอนุมัติใหม่ทั้ง 3 ระดับ');
    }

    public function departmentApprovers(Request $request): JsonResponse
    {
        $department = (string) $request->input('department');

        if ($department === '') {
            return response()->json(['status' => false, 'message' => 'กรุณาเลือกแผนก'], 422);
        }

        if (! auth()->user()->canCreateCourseForDepartment($department)) {
            return response()->json(['status' => false, 'message' => 'ไม่มีสิทธิ์สำหรับแผนกนี้'], 403);
        }

        $approvers = $this->coursePlanService->staffApproversForDepartment($department);

        return response()->json([
            'status' => true,
            'level2' => $approvers['level2'],
            'level3' => $approvers['level3'],
        ]);
    }

    public function departmentPositions(Request $request): JsonResponse
    {
        $department = (string) $request->input('department');

        if ($department === '') {
            return response()->json(['status' => false, 'positions' => []], 422);
        }

        return response()->json([
            'status' => true,
            'positions' => $this->coursePlanService->positionsForDepartment($department),
        ]);
    }

    public function permissions(Request $request): View
    {
        return view(
            'admin.course-permissions',
            $this->coursePlanService->listPermissionUsers($request->input('search'))
        );
    }

    public function updatePermission(UpdateCoursePermissionRequest $request): RedirectResponse
    {
        try {
            $this->coursePlanService->updateUserCoursePermission($request->validated());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['course_departments' => $exception->getMessage()]);
        }

        return redirect()->back()->with('success', 'อัปเดตสิทธิ์สร้างหลักสูตรสำเร็จ');
    }

    /**
     * @param  list<string>  $departments
     * @return array<string, mixed>
     */
    private function formViewData(int $year, array $departments, ?CoursePlan $plan = null): array
    {
        $user = auth()->user();
        $level1 = null;

        if ($user->getapprover && ($user->getapprover->status ?? false)) {
            $level1 = $user->getapprover->approver;
        }

        return [
            'departments' => $departments,
            'year' => $year,
            'trainingTypes' => CoursePlan::trainingTypeLabels(),
            'months' => CoursePlan::monthLabels(),
            'level1' => $level1,
            'plan' => $plan,
            'isEdit' => $plan !== null,
        ];
    }
}
