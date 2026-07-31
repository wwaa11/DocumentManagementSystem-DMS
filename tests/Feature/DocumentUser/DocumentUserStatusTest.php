<?php

namespace Tests\Feature\DocumentUser;

use App\Models\DocumentUser;
use Tests\TestCase;

class DocumentUserStatusTest extends TestCase
{
    public function test_status_follows_incomplete_section_when_another_section_is_complete(): void
    {
        $documentUser = $this->documentUserWithSections([
            (object) ['status' => 'wait_approval'],
            (object) ['status' => 'complete'],
        ]);

        $this->assertSame('wait_approval', $documentUser->status);
    }

    public function test_status_is_process_when_one_section_is_still_processing(): void
    {
        $documentUser = $this->documentUserWithSections([
            (object) ['status' => 'process'],
            (object) ['status' => 'complete'],
        ]);

        $this->assertSame('process', $documentUser->status);
    }

    public function test_status_is_pending_when_sections_are_mixed_pending_and_done(): void
    {
        $documentUser = $this->documentUserWithSections([
            (object) ['status' => 'pending'],
            (object) ['status' => 'done'],
        ]);

        $this->assertSame('pending', $documentUser->status);
    }

    public function test_status_is_complete_when_all_sections_are_complete(): void
    {
        $documentUser = $this->documentUserWithSections([
            (object) ['status' => 'complete'],
            (object) ['status' => 'complete'],
        ]);

        $this->assertSame('complete', $documentUser->status);
    }

    public function test_status_prefers_failure_over_incomplete_section(): void
    {
        $documentUser = $this->documentUserWithSections([
            (object) ['status' => 'pending'],
            (object) ['status' => 'reject'],
        ]);

        $this->assertSame('reject', $documentUser->status);
    }

    public function test_index_row_renders_status_for_mixed_sections(): void
    {
        $html = view('document.partials.index-row', [
            'document' => [
                'flag' => 'my',
                'id' => 1,
                'document_tag' => [
                    'document_tag' => 'USER',
                    'colour' => 'warning',
                ],
                'document_number' => 'USER-001',
                'document_type_name' => 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม',
                'title' => 'ทดสอบ',
                'detail' => 'รายละเอียด',
                'status' => 'wait_approval',
                'created_at' => now(),
            ],
        ])->render();

        $this->assertStringContainsString('รออนุมัติจากหัวหน้าแผนก', $html);
    }

    public function test_index_row_renders_complete_partial_status(): void
    {
        $html = view('document.partials.index-row', [
            'document' => [
                'flag' => 'my',
                'id' => 1,
                'document_tag' => [
                    'document_tag' => 'USER',
                    'colour' => 'warning',
                ],
                'document_number' => null,
                'document_type_name' => 'ขอรหัสผู้ใช้งานคอมพิวเตอร์/ขอสิทธิใช้งานโปรแกรม',
                'title' => 'ทดสอบ',
                'detail' => 'รายละเอียด',
                'status' => 'complete-partial',
                'created_at' => now(),
            ],
        ])->render();

        $this->assertStringContainsString('เสร็จบางส่วน', $html);
    }

    /**
     * @param  array<int, object{status: string}>  $sections
     */
    private function documentUserWithSections(array $sections): DocumentUser
    {
        $documentUser = new class extends DocumentUser
        {
            /** @var array<int, object{status: string}> */
            public array $stubSections = [];

            public function getAllDocuments(): array
            {
                return $this->stubSections;
            }
        };

        $documentUser->stubSections = $sections;

        return $documentUser;
    }
}
