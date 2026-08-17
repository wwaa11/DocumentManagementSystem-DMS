<?php

namespace Tests\Feature\Course;

use App\Http\Requests\Training\StoreDocumentTrainingRequest;
use App\Models\CoursePlanItem;
use App\Models\DocumentTraining;
use App\Models\User;
use App\Services\Training\DocumentTrainingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CourseTrainingLinkTest extends TestCase
{
    public function test_out_of_plan_source_only_requires_a_reason_specific_field(): void
    {
        $rules = (new StoreDocumentTrainingRequest)->rules();

        $this->assertSame(['nullable', 'string', 'max:255'], $rules['department']);
        $this->assertContains('required_if:source_type,out_of_plan', $rules['out_of_plan_reason']);
    }

    public function test_course_plan_item_has_trainings_relationship(): void
    {
        $item = new CoursePlanItem([
            'course_plan_id' => 1,
            'number' => '1',
            'name' => 'หลักสูตรทดสอบ',
            'origin' => 'แผน',
            'objective' => 'เรียนรู้',
            'training_type' => 'internal',
            'schedule_months' => [1],
        ]);

        $training = new DocumentTraining(['status' => 'wait_approval']);
        $training->title = 'อบรมครั้งที่ 1';

        $item->setRelation('trainings', collect([$training]));

        $this->assertCount(1, $item->trainings);
        $this->assertSame('อบรมครั้งที่ 1', $item->trainings->first()->title);
    }

    public function test_document_training_type_name_depends_on_course_link(): void
    {
        $linked = new DocumentTraining(['course_plan_item_id' => 10]);
        $standalone = new DocumentTraining(['course_plan_item_id' => null]);

        $this->assertSame('ฝึกอบรมตามแผนหลักสูตร', $linked->document_type_name);
        $this->assertSame('ฝึกอบรมนอกแผน', $standalone->document_type_name);
    }

    public function test_course_plan_item_reports_existing_training_document(): void
    {
        $empty = new CoursePlanItem;
        $empty->setRelation('trainings', collect());

        $withTraining = new CoursePlanItem;
        $withTraining->setRelation('trainings', collect([new DocumentTraining(['status' => 'wait_approval'])]));

        $this->assertFalse($empty->hasTrainingDocument());
        $this->assertTrue($withTraining->hasTrainingDocument());
    }

    public function test_course_plan_item_detects_out_of_plan_courses(): void
    {
        $planned = new CoursePlanItem([
            'origin' => 'แผนพัฒนา',
            'objective' => 'เพิ่มทักษะ',
        ]);

        $outOfPlan = new CoursePlanItem([
            'origin' => 'จัดนอกแผน: ความเร่งด่วน',
            'objective' => CoursePlanItem::OUT_OF_PLAN_OBJECTIVE,
        ]);

        $this->assertFalse($planned->isOutOfPlan());
        $this->assertTrue($outOfPlan->isOutOfPlan());
    }

    public function test_out_of_plan_training_creates_course_item_when_tables_exist(): void
    {
        try {
            if (! Schema::hasTable('course_plans') || ! Schema::hasTable('document_trainings')) {
                $this->markTestSkipped('Required tables are not available in this environment.');
            }
        } catch (\Throwable) {
            $this->markTestSkipped('Database driver is not available in this environment.');
        }

        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No users available for this test.');
        }

        $user->forceFill([
            'can_create_course' => true,
            'course_departments' => ['แผนกทดสอบฝึกอบรม'],
        ])->save();

        $this->actingAs($user);

        $service = app(DocumentTrainingService::class);
        $request = Request::create('/training/create', 'POST', [
            'training_name' => 'อบรมนอกแผนทดสอบ',
            'source_type' => 'out_of_plan',
            'out_of_plan_reason' => 'ความเร่งด่วน',
            'department' => 'แผนกทดสอบฝึกอบรม',
            'year' => (int) now()->year,
            'date_mode' => 'specific',
            'specific_date' => [now()->toDateString()],
            'specific_start_time' => ['09:00'],
            'specific_end_time' => ['12:00'],
            'duration_hours' => 3,
            'duration_minutes' => 0,
            'project_type' => 'multiple',
            'approver' => [
                'userid' => $user->userid,
                'name' => $user->name,
                'position' => $user->position,
                'email' => $user->email,
            ],
            'mentors_userid' => [$user->userid],
            'mentors_name' => [$user->name],
            'mentors_position' => [$user->position],
            'participants_userid' => [$user->userid],
            'participants_name' => [$user->name],
            'participants_position' => [$user->position],
            'participants_dept' => [$user->department],
        ]);
        $request->setUserResolver(fn () => $user);

        $document = $service->createDocument($request);

        $this->assertNotNull($document->course_plan_item_id);
        $this->assertDatabaseHas('course_plan_items', [
            'id' => $document->course_plan_item_id,
            'name' => 'อบรมนอกแผนทดสอบ',
        ]);
    }
}
