<?php

namespace Tests\Feature\Training;

use App\Models\DocumentTrainingParticipant;
use App\Services\Training\TrainingScheduleService;
use Tests\TestCase;

class TrainingAssessmentParticipantsTest extends TestCase
{
    private function participant(int $id, string $userid, ?string $type = null, ?string $score = null): DocumentTrainingParticipant
    {
        $participant = new DocumentTrainingParticipant([
            'participant' => $userid,
            'participant_name' => 'พนักงาน '.$userid,
            'participant_position' => 'Engineer',
            'assetment_type' => $type,
            'score' => $score,
        ]);

        $participant->id = $id;

        return $participant;
    }

    public function test_the_assessment_list_keeps_one_row_per_employee(): void
    {
        [$kept, $duplicateIds] = app(TrainingScheduleService::class)->deduplicateParticipants(collect([
            $this->participant(1, '650001'),
            $this->participant(2, '650002'),
            $this->participant(3, '650001'),
        ]));

        $this->assertSame([1, 2], array_map(fn (DocumentTrainingParticipant $row): int => $row->id, $kept));
        $this->assertSame([3], $duplicateIds);
    }

    public function test_duplicate_rows_keep_the_one_holding_assessment_results(): void
    {
        [$kept, $duplicateIds] = app(TrainingScheduleService::class)->deduplicateParticipants(collect([
            $this->participant(1, '650001'),
            $this->participant(2, '650001', 'P+I', '3'),
        ]));

        $this->assertCount(1, $kept);
        $this->assertSame(2, $kept[0]->id);
        $this->assertSame([1], $duplicateIds);
    }

    public function test_the_payload_splits_combined_assessment_methods(): void
    {
        $participant = $this->participant(1, '650001', 'P+I', '3');
        $participant->assetment_date = '2026-08-17';

        $payload = app(TrainingScheduleService::class)->assessmentPayload($participant);

        $this->assertSame('650001', $payload['userid']);
        $this->assertSame(['P', 'I'], $payload['assessment_types']);
        $this->assertSame('2026-08-17', $payload['assessment_date']);
        $this->assertSame('3', $payload['score']);
    }

    public function test_the_payload_ignores_unknown_assessment_codes(): void
    {
        $payload = app(TrainingScheduleService::class)->assessmentPayload(
            $this->participant(1, '650001', 'p, x | O'),
        );

        $this->assertSame(['P', 'O'], $payload['assessment_types']);
        $this->assertNull($payload['assessment_date']);
        $this->assertSame('', $payload['score']);
    }
}
