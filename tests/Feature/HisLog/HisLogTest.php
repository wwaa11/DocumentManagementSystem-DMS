<?php

namespace Tests\Feature\HisLog;

use App\Http\Requests\IT\StoreHisLogRequest;
use App\Models\HisLog;
use App\Models\User;
use App\Services\IT\HisLogExcelExporter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use ZipArchive;

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
            'problem_detail' => 'ทดสอบปัญหา',
            'receiver_userid' => '650001',
            'fixer' => null,
            'root_cause' => 'แก้แล้ว',
            'status' => 'Closed',
            'time' => '10:30',
        ], (new StoreHisLogRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_store_request_requires_module(): void
    {
        $validator = Validator::make([
            'reported_at' => '2026-07-14',
            'reporter' => 'Lab',
            'status' => 'Open',
            'time' => '09:00',
        ], (new StoreHisLogRequest)->rules(), (new StoreHisLogRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('module', $validator->errors()->toArray());
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
        $this->assertContains('admin.it.hislogs.index', $links);
        $this->assertContains('admin.it.hislogs.dashboard', $links);
        $this->assertTrue(collect($user->menu['lists'])->contains(
            fn (array $item): bool => ($item['title'] ?? null) === 'HIS Logs' && ($item['link'] ?? null) === null
        ));
        $this->assertTrue(collect($user->menu['lists'])->contains(
            fn (array $item): bool => ($item['title'] ?? null) === 'All Logs' && ($item['link'] ?? null) === 'admin.it.hislogs.index'
        ));
    }

    public function test_module_and_fixer_options_are_configured(): void
    {
        $this->assertContains('OPD', HisLog::moduleOptions());
        $this->assertContains('Patient Info', HisLog::moduleOptions());
        $this->assertSame(['it', 'it-approve', 'it-hardware', 'it-hardware-approve', 'admin'], HisLog::fixerRoles());
        $this->assertSame(['Open', 'In Progress', 'Closed'], HisLog::statusOptions());
    }

    public function test_edit_and_update_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.it.hislogs.index'));
        $this->assertTrue(Route::has('admin.it.hislogs.edit'));
        $this->assertTrue(Route::has('admin.it.hislogs.update'));
        $this->assertTrue(Route::has('admin.it.hislogs.export'));
        $this->assertFalse(Route::has('admin.it.hislogs.import'));
        $this->assertSame(
            url('/it/admin/his-logs'),
            route('admin.it.hislogs.index')
        );
        $this->assertSame(
            url('/it/admin/his-logs/1/edit'),
            route('admin.it.hislogs.edit', ['hisLog' => 1])
        );
        $this->assertSame(
            url('/it/admin/his-logs/export'),
            route('admin.it.hislogs.export')
        );
    }

    public function test_excel_exporter_builds_valid_workbook(): void
    {
        $exporter = new HisLogExcelExporter;
        $content = $exporter->build([
            [
                'No.',
                'วันที่แจ้ง',
                'ผู้แจ้ง/แผนก',
                'module',
                'รายละเอียดปัญหา',
                'ผู้รับเรื่อง',
                'ผู้แก้ไข',
                'วิธีแก้ไข/root cause',
                'สถานะ',
                'shif',
                'time',
            ],
            [
                1,
                '2026-07-01',
                'Lab',
                'Assessment',
                'ทดสอบปัญหา',
                'IT Support',
                'Fixer',
                'แก้แล้ว',
                'Closed',
                'เช้า',
                '08:02',
            ],
        ]);

        $this->assertNotEmpty($content);
        $this->assertSame('PK', substr($content, 0, 2));

        $tempPath = tempnam(sys_get_temp_dir(), 'hislog_export_test_');
        $this->assertNotFalse($tempPath);

        $path = $tempPath.'.xlsx';
        $this->assertTrue(rename($tempPath, $path));
        file_put_contents($path, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $this->assertNotFalse($zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertNotFalse($zip->getFromName('xl/sharedStrings.xml'));
        $zip->close();

        @unlink($path);
    }

    public function test_excel_exporter_builds_dashboard_and_log_sheets(): void
    {
        $exporter = new HisLogExcelExporter;
        $content = $exporter->buildSheets([
            'Dashboard' => [
                ['HIS Log Dashboard Summary'],
                ['ช่วงเวลา', '2026-07-01 → 2026-07-31'],
                ['Metric', 'Value'],
                ['Total Cases', 10],
                ['Closed', 8],
            ],
            'HIS_Log' => [
                ['No.', 'module'],
                [1, 'OPD'],
            ],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'hislog_dashboard_export_test_');
        $this->assertNotFalse($tempPath);

        $path = $tempPath.'.xlsx';
        $this->assertTrue(rename($tempPath, $path));
        file_put_contents($path, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $workbook = $zip->getFromName('xl/workbook.xml');
        $this->assertNotFalse($workbook);
        $this->assertStringContainsString('Dashboard', (string) $workbook);
        $this->assertStringContainsString('HIS_Log', (string) $workbook);
        $this->assertNotFalse($zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertNotFalse($zip->getFromName('xl/worksheets/sheet2.xml'));
        $zip->close();

        @unlink($path);
    }
}
