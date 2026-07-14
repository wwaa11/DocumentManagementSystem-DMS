<?php

namespace Tests\Feature\HisLog;

use App\Http\Requests\IT\ImportHisLogRequest;
use App\Http\Requests\IT\StoreHisLogRequest;
use App\Models\HisLog;
use App\Models\User;
use App\Services\IT\HisLogService;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class HisLogTest extends TestCase
{
    public function test_shift_is_resolved_from_time(): void
    {
        $this->assertSame('เช้า', HisLog::resolveShiftFromTime('07:00'));
        $this->assertSame('เช้า', HisLog::resolveShiftFromTime('12:00'));
        $this->assertSame('บ่าย', HisLog::resolveShiftFromTime('12:01'));
        $this->assertSame('บ่าย', HisLog::resolveShiftFromTime('16:59'));
        $this->assertSame('ดึก', HisLog::resolveShiftFromTime('17:00'));
        $this->assertSame('ดึก', HisLog::resolveShiftFromTime('00:35'));
        $this->assertSame('ดึก', HisLog::resolveShiftFromTime('06:59'));
    }

    public function test_store_request_passes_validation(): void
    {
        $validator = Validator::make([
            'reported_at' => '2026-07-14',
            'reporter' => 'ก้อย / Eye',
            'module' => 'Package',
            'issues' => ['Package', 'Other'],
            'problem_detail' => 'ทดสอบปัญหา',
            'fixer' => 'เปิ้ล',
            'root_cause' => 'แก้แล้ว',
            'status' => 'Closed',
            'time' => '10:30',
        ], (new StoreHisLogRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_store_request_requires_issues_and_module(): void
    {
        $validator = Validator::make([
            'reported_at' => '2026-07-14',
            'reporter' => 'Lab',
            'status' => 'Open',
            'time' => '09:00',
        ], (new StoreHisLogRequest)->rules(), (new StoreHisLogRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('module', $validator->errors()->toArray());
        $this->assertArrayHasKey('issues', $validator->errors()->toArray());
    }

    public function test_import_request_requires_excel_file(): void
    {
        $validator = Validator::make([], (new ImportHisLogRequest)->rules(), (new ImportHisLogRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('excel_file', $validator->errors()->toArray());
    }

    public function test_it_menu_contains_his_logs_pages(): void
    {
        $user = new User([
            'userid' => '650001',
            'name' => 'IT Tester',
            'position' => 'IT Support',
            'department' => 'IT',
            'division' => 'ฝ่ายเทคโนโลยีสารสนเทศ',
        ]);
        $user->role = 'it';

        $links = collect($user->menu['lists'])->pluck('link')->filter()->values()->all();

        $this->assertContains('admin.it.hislogs.create', $links);
        $this->assertContains('admin.it.hislogs.dashboard', $links);
        $this->assertTrue(collect($user->menu['lists'])->contains(
            fn (array $item): bool => ($item['title'] ?? null) === 'HIS Logs' && ($item['link'] ?? null) === null
        ));
    }

    public function test_excel_parser_reads_his_log_sheet(): void
    {
        $source = public_path('HIS_Log_Dashboard.xlsx');
        $this->assertFileExists($source);

        $service = app(HisLogService::class);
        $method = new ReflectionMethod(HisLogService::class, 'readHisLogSheet');
        $method->setAccessible(true);

        /** @var list<array<int, string|null>> $rows */
        $rows = $method->invoke($service, $source);

        $this->assertNotEmpty($rows);
        $this->assertSame('Assessment', $rows[0][3]);
        $this->assertSame('Closed', $rows[0][8]);

        $mapMethod = new ReflectionMethod(HisLogService::class, 'mapImportRow');
        $mapMethod->setAccessible(true);

        /** @var array<string, mixed>|null $payload */
        $payload = $mapMethod->invoke($service, $rows[0]);

        $this->assertNotNull($payload);
        $this->assertSame('Assessment', $payload['module']);
        $this->assertSame('เช้า', $payload['shift']);
        $this->assertSame('08:02', $payload['time']);
        $this->assertSame('2026-07-01', $payload['reported_at']);
    }

    public function test_module_and_issue_options_are_configured(): void
    {
        $this->assertContains('OPD', HisLog::moduleOptions());
        $this->assertContains('Patient Info', HisLog::moduleOptions());
        $this->assertContains('Log In', HisLog::issueOptions());
        $this->assertSame(['Open', 'In Progress', 'Closed'], HisLog::statusOptions());
    }
}
