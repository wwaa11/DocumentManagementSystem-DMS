<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\ApproverAdminService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApproverUpdateTest extends TestCase
{
    private string $department;

    private string $userid;

    protected function setUp(): void
    {
        parent::setUp();

        $row = DB::connection('staff')
            ->table('departments')
            ->join('approvers', 'departments.id', '=', 'approvers.department_id')
            ->where('departments.department', '!=', 'Doctor')
            ->where('approvers.level', 1)
            ->whereNotNull('approvers.userid')
            ->where('approvers.userid', '!=', '-')
            ->select('departments.department', 'approvers.userid')
            ->orderBy('approvers.id')
            ->first();

        $this->assertNotNull($row, 'Staff DB must have at least one level-1 approver for this test.');

        $this->department = (string) $row->department;
        $this->userid = (string) $row->userid;

        DB::connection('staff')->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::connection('staff')->transactionLevel() > 0) {
            DB::connection('staff')->rollBack();
        }

        parent::tearDown();
    }

    private function makeAdmin(): User
    {
        $user = new User([
            'userid' => '999999',
            'name' => 'Admin Updater',
            'position' => 'Admin',
            'department' => 'IT',
            'division' => 'IT',
            'email' => 'admin@example.com',
        ]);
        $user->role = 'admin';

        return $user;
    }

    public function test_update_approver_updates_existing_email_row(): void
    {
        $this->actingAs($this->makeAdmin());

        DB::connection('staff')->table('emails')->updateOrInsert(
            ['userid' => $this->userid],
            [
                'email' => 'before-update@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $newEmail = 'after-update-'.uniqid().'@example.com';

        app(ApproverAdminService::class)->updateApprover([
            'department' => $this->department,
            'userid' => $this->userid,
            'name' => 'Approver Name',
            'position' => 'Manager',
            'email' => $newEmail,
        ]);

        $this->assertSame(
            $newEmail,
            DB::connection('staff')->table('emails')->where('userid', $this->userid)->value('email')
        );
        $this->assertSame(
            1,
            DB::connection('staff')->table('emails')->where('userid', $this->userid)->count()
        );
    }

    public function test_update_approver_creates_email_row_when_missing(): void
    {
        $this->actingAs($this->makeAdmin());

        DB::connection('staff')->table('emails')->where('userid', $this->userid)->delete();

        $this->assertFalse(
            DB::connection('staff')->table('emails')->where('userid', $this->userid)->exists()
        );

        $newEmail = 'created-'.uniqid().'@example.com';

        app(ApproverAdminService::class)->updateApprover([
            'department' => $this->department,
            'userid' => $this->userid,
            'name' => 'Approver Name',
            'position' => 'Manager',
            'email' => $newEmail,
        ]);

        $emailRow = DB::connection('staff')->table('emails')->where('userid', $this->userid)->first();

        $this->assertNotNull($emailRow);
        $this->assertSame($this->userid, $emailRow->userid);
        $this->assertSame($newEmail, $emailRow->email);
    }
}
