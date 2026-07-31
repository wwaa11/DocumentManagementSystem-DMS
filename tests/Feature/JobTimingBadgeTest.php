<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class JobTimingBadgeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_marks_job_as_on_time_when_within_one_day(): void
    {
        Carbon::setTestNow('2026-07-31 15:00:00');

        $html = view('components.document.job-timing-badge', [
            'since' => now()->subHours(23),
        ])->render();

        $this->assertStringContainsString('On Time', $html);
        $this->assertStringNotContainsString('Overdue', $html);
    }

    public function test_marks_job_as_on_time_at_exactly_one_day(): void
    {
        Carbon::setTestNow('2026-07-31 15:00:00');

        $html = view('components.document.job-timing-badge', [
            'since' => now()->subSeconds(86400),
        ])->render();

        $this->assertStringContainsString('On Time', $html);
        $this->assertStringNotContainsString('Overdue', $html);
    }

    public function test_marks_job_as_overdue_when_older_than_one_day(): void
    {
        Carbon::setTestNow('2026-07-31 15:00:00');

        $html = view('components.document.job-timing-badge', [
            'since' => now()->subSeconds(86401),
        ])->render();

        $this->assertStringContainsString('Overdue', $html);
        $this->assertStringNotContainsString('On Time', $html);
    }
}
