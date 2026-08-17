<?php

namespace App\Services\Training;

use App\Models\DocumentTraining;
use App\Models\DocumentTrainingParticipant;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Handles the "edit project detail" side of a training project: dates, time
 * slots and participants living in the HRD training system.
 */
class TrainingScheduleService
{
    public function __construct(private TrainingApiClient $trainingApi) {}

    /**
     * @return array{success: bool, message: string, project?: array<string, mixed>}
     */
    public function projectDetail(DocumentTraining $document): array
    {
        $response = $this->trainingApi->getProjectDetail($document->training_id);
        $payload = $this->payload($response);

        if (! $this->isSuccessful($response, $payload)) {
            return $this->failure($payload, 'ไม่สามารถดึงรายละเอียดโครงการฝึกอบรมได้');
        }

        return [
            'success' => true,
            'message' => 'ดึงรายละเอียดโครงการสำเร็จ',
            'project' => $payload['project'] ?? [],
        ];
    }

    /**
     * Refresh the local participant list from HRD and return one row per person,
     * dropping duplicate rows that earlier imports may have left behind.
     *
     * @return array{success: bool, message: string, participants: list<array<string, mixed>>}
     */
    public function assessmentParticipants(DocumentTraining $document): array
    {
        if ($document->training_id !== null) {
            $this->syncLocalSchedule($document);
        }

        [$kept, $duplicateIds] = $this->deduplicateParticipants($document->participants()->orderBy('id')->get());

        if ($duplicateIds !== []) {
            $document->participants()->whereIn('id', $duplicateIds)->delete();
        }

        return [
            'success' => true,
            'message' => 'โหลดรายชื่อผู้เข้าร่วม '.count($kept).' คน',
            'participants' => array_map(fn (DocumentTrainingParticipant $participant): array => $this->assessmentPayload($participant), $kept),
        ];
    }

    /**
     * Collapse the participant rows to one per employee, preferring the row that
     * already carries assessment results.
     *
     * @param  Collection<int, DocumentTrainingParticipant>  $participants
     * @return array{0: list<DocumentTrainingParticipant>, 1: list<int>}
     */
    public function deduplicateParticipants(Collection $participants): array
    {
        $kept = [];
        $duplicateIds = [];

        foreach ($participants as $participant) {
            $userid = (string) $participant->participant;
            $current = $kept[$userid] ?? null;

            if ($current === null) {
                $kept[$userid] = $participant;

                continue;
            }

            if ($this->hasAssessment($participant) && ! $this->hasAssessment($current)) {
                $duplicateIds[] = $current->id;
                $kept[$userid] = $participant;

                continue;
            }

            $duplicateIds[] = $participant->id;
        }

        return [array_values($kept), $duplicateIds];
    }

    /**
     * @return array{id: int|null, userid: string, name: string, position: ?string, department: ?string, assessment_date: ?string, assessment_types: list<string>, score: string}
     */
    public function assessmentPayload(DocumentTrainingParticipant $participant): array
    {
        return [
            'id' => $participant->id,
            'userid' => (string) $participant->participant,
            'name' => (string) $participant->participant_name,
            'position' => $participant->participant_position,
            'department' => $participant->participant_department,
            'assessment_date' => $participant->assetment_date?->format('Y-m-d'),
            'assessment_types' => $this->assessmentTypes($participant->assetment_type),
            'score' => $participant->score !== null ? (string) $participant->score : '',
        ];
    }

    private function hasAssessment(DocumentTrainingParticipant $participant): bool
    {
        return filled($participant->assetment_type) || filled($participant->score);
    }

    /**
     * @return list<string>
     */
    private function assessmentTypes(?string $value): array
    {
        $codes = preg_split('/[+,|\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(
            array_map(fn (string $code): string => strtoupper(trim($code)), $codes),
            fn (string $code): bool => in_array($code, ['P', 'O', 'I'], true),
        )));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, date?: array<string, mixed>}
     */
    public function addDate(DocumentTraining $document, array $data): array
    {
        $payload = [
            'project_id' => $document->training_id,
            'date_datetime' => $data['date_datetime'],
            'date_title' => $this->thaiDateTitle($data['date_datetime']),
            'date_detail' => $data['date_detail'] ?? null,
            'date_location' => $data['date_location'] ?? null,
            'times' => $this->timesPayload($data['times'] ?? []),
        ];

        return $this->handle(
            $document,
            $this->trainingApi->addDate($this->withoutNulls($payload)),
            'add_training_date',
            'เพิ่มวันอบรม '.$data['date_datetime'],
            'เพิ่มวันอบรมไม่สำเร็จ!',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, date?: array<string, mixed>}
     */
    public function updateDate(DocumentTraining $document, array $data): array
    {
        $payload = [
            'date_id' => $data['date_id'],
            'date_detail' => $data['date_detail'] ?? null,
            'date_location' => $data['date_location'] ?? null,
        ];

        if (! empty($data['date_datetime'])) {
            $payload['date_datetime'] = $data['date_datetime'];
            $payload['date_title'] = $this->thaiDateTitle($data['date_datetime']);
        }

        return $this->handle(
            $document,
            $this->trainingApi->editDate($payload),
            'edit_training_date',
            'แก้ไขวันอบรม #'.$data['date_id'],
            'แก้ไขวันอบรมไม่สำเร็จ!',
        );
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function removeDate(DocumentTraining $document, int|string $dateId): array
    {
        return $this->handle(
            $document,
            $this->trainingApi->removeDate($dateId),
            'remove_training_date',
            'ลบวันอบรม #'.$dateId,
            'ลบวันอบรมไม่สำเร็จ!',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, time?: array<string, mixed>}
     */
    public function addTime(DocumentTraining $document, array $data): array
    {
        $payload = $this->timePayload($data) + ['date_id' => $data['date_id']];

        return $this->handle(
            $document,
            $this->trainingApi->addTime($payload),
            'add_training_time',
            'เพิ่มช่วงเวลา '.$data['time_start'].' - '.$data['time_end'],
            'เพิ่มช่วงเวลาไม่สำเร็จ!',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, time?: array<string, mixed>}
     */
    public function updateTime(DocumentTraining $document, array $data): array
    {
        $payload = $this->timePayload($data) + ['time_id' => $data['time_id']];

        return $this->handle(
            $document,
            $this->trainingApi->editTime($payload),
            'edit_training_time',
            'แก้ไขช่วงเวลา #'.$data['time_id'],
            'แก้ไขช่วงเวลาไม่สำเร็จ!',
        );
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function removeTime(DocumentTraining $document, int|string $timeId): array
    {
        return $this->handle(
            $document,
            $this->trainingApi->removeTime($timeId),
            'remove_training_time',
            'ลบช่วงเวลา #'.$timeId,
            'ลบช่วงเวลาไม่สำเร็จ!',
        );
    }

    /**
     * @param  list<string>  $users
     * @return array{success: bool, message: string, added?: array<int, mixed>, skipped?: array<int, mixed>, failed?: array<int, mixed>}
     */
    public function addParticipants(DocumentTraining $document, int|string $timeId, array $users): array
    {
        $response = $this->trainingApi->addParticipants($timeId, $users);
        $payload = $this->payload($response);

        if (! $response->successful()) {
            return $this->failure($payload, 'เพิ่มผู้เข้าร่วมไม่สำเร็จ!');
        }

        $added = $payload['added'] ?? [];
        $this->syncLocalSchedule($document);

        if ($added !== []) {
            $this->log($document, 'add_training_participant', 'เพิ่มผู้เข้าร่วม '.count($added).' คน ในช่วงเวลา #'.$timeId);
        }

        return [
            'success' => ($payload['failed'] ?? []) === [],
            'message' => $this->participantSummary($payload),
            'added' => $added,
            'skipped' => $payload['skipped'] ?? [],
            'failed' => $payload['failed'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string}
     */
    public function removeParticipant(DocumentTraining $document, array $data): array
    {
        $payload = $this->withoutNulls([
            'attend_id' => $data['attend_id'] ?? null,
            'time_id' => $data['time_id'] ?? null,
            'userid' => $data['userid'] ?? null,
        ]);

        return $this->handle(
            $document,
            $this->trainingApi->removeParticipant($payload),
            'remove_training_participant',
            'ลบผู้เข้าร่วม '.($data['userid'] ?? '#'.($data['attend_id'] ?? '-')),
            'ลบผู้เข้าร่วมไม่สำเร็จ!',
        );
    }

    /**
     * @param  list<string>  $users
     * @return array{success: bool, message: string, added?: array<int, mixed>, skipped?: array<int, mixed>, failed?: array<int, mixed>}
     */
    public function addLecturers(DocumentTraining $document, int|string $dateId, array $users): array
    {
        $response = $this->trainingApi->addLecturers($dateId, $users);
        $payload = $this->payload($response);

        if (! $response->successful()) {
            return $this->failure($payload, 'เพิ่มวิทยากรไม่สำเร็จ!');
        }

        $added = $payload['added'] ?? [];
        $this->syncLocalSchedule($document);

        if ($added !== []) {
            $this->log($document, 'add_training_lecturer', 'เพิ่มวิทยากร '.count($added).' คน ในวันอบรม #'.$dateId);
        }

        return [
            'success' => ($payload['failed'] ?? []) === [],
            'message' => $this->lecturerSummary($payload),
            'added' => $added,
            'skipped' => $payload['skipped'] ?? [],
            'failed' => $payload['failed'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string}
     */
    public function removeLecturer(DocumentTraining $document, array $data): array
    {
        $payload = $this->withoutNulls([
            'lecture_id' => $data['lecture_id'] ?? null,
            'date_id' => $data['date_id'] ?? null,
            'userid' => $data['userid'] ?? null,
        ]);

        return $this->handle(
            $document,
            $this->trainingApi->removeLecturer($payload),
            'remove_training_lecturer',
            'ลบวิทยากร '.($data['userid'] ?? '#'.($data['lecture_id'] ?? '-')),
            'ลบวิทยากรไม่สำเร็จ!',
        );
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function handle(
        DocumentTraining $document,
        Response $response,
        string $action,
        string $logDetail,
        string $fallbackMessage,
    ): array {
        $payload = $this->payload($response);

        if (! $this->isSuccessful($response, $payload)) {
            return $this->failure($payload, $fallbackMessage);
        }

        $this->log($document, $action, $logDetail);
        $this->syncLocalSchedule($document);

        return array_merge($payload, [
            'success' => true,
            'message' => $payload['message'] ?? 'ดำเนินการสำเร็จ',
        ]);
    }

    /**
     * Mirror the HRD schedule back into the local document so the printed
     * report and the assessment table stay aligned with the training system.
     */
    private function syncLocalSchedule(DocumentTraining $document): void
    {
        $response = $this->trainingApi->getProjectDetail($document->training_id);

        if (! $response->successful()) {
            return;
        }

        $project = $response->json('project');

        if (! is_array($project)) {
            return;
        }

        [$dates, $participants, $lecturers, $totalMinutes] = $this->flattenProject($project);

        DB::transaction(function () use ($document, $dates, $participants, $lecturers, $totalMinutes): void {
            $document->dates()->delete();

            foreach ($dates as $date) {
                $document->dates()->create($date);
            }

            $this->syncLocalParticipants($document, $participants);
            $this->syncLocalMentors($document, $lecturers);

            $document->hours = intdiv($totalMinutes, 60);
            $document->minutes = $totalMinutes % 60;
            $document->save();
        });
    }

    /**
     * @param  array<string, mixed>  $project
     * @return array{0: list<array<string, string>>, 1: array<string, array<string, mixed>>, 2: array<string, array<string, mixed>>, 3: int}
     */
    private function flattenProject(array $project): array
    {
        $dates = [];
        $participants = [];
        $lecturers = [];
        $totalMinutes = 0;

        foreach ($project['dates'] ?? [] as $date) {
            if (($date['date_active'] ?? true) === false) {
                continue;
            }

            foreach ($date['lecturers'] ?? [] as $lecturer) {
                $lecturers[(string) $lecturer['userid']] = $lecturer;
            }

            foreach ($date['times'] ?? [] as $time) {
                if (($time['time_active'] ?? true) === false) {
                    continue;
                }

                $dates[] = [
                    'date' => $date['date_datetime'],
                    'start_time' => $time['time_start'],
                    'end_time' => $time['time_end'],
                ];

                $totalMinutes += $this->minutesBetween($time['time_start'], $time['time_end']);

                foreach ($time['participants'] ?? [] as $participant) {
                    $participants[(string) $participant['userid']] = $participant;
                }
            }
        }

        return [$dates, $participants, $lecturers, $totalMinutes];
    }

    /**
     * @param  array<string, array<string, mixed>>  $participants
     */
    private function syncLocalParticipants(DocumentTraining $document, array $participants): void
    {
        if ($participants === []) {
            return;
        }

        $document->participants()
            ->whereNotIn('participant', $this->userids($participants))
            ->delete();

        $this->removeDuplicateParticipants($document);

        foreach ($participants as $userid => $participant) {
            $userid = (string) $userid;

            $document->participants()->updateOrCreate(
                ['participant' => $userid],
                [
                    'participant_name' => $participant['name'] ?? $userid,
                    'participant_position' => $participant['position'] ?? null,
                    'participant_department' => $participant['department'] ?? null,
                ],
            );
        }
    }

    /**
     * Keep the printed mentor list aligned with the unique lecturers across all dates.
     *
     * @param  array<string, array<string, mixed>>  $lecturers
     */
    private function syncLocalMentors(DocumentTraining $document, array $lecturers): void
    {
        $document->mentors()
            ->whereNotIn('mentor', $this->userids($lecturers))
            ->delete();

        $this->removeDuplicateMentors($document);

        foreach ($lecturers as $userid => $lecturer) {
            $userid = (string) $userid;

            $document->mentors()->updateOrCreate(
                ['mentor' => $userid],
                [
                    'mentor_name' => $lecturer['name'] ?? $userid,
                    'mentor_position' => $lecturer['position'] ?? null,
                ],
            );
        }
    }

    /**
     * PHP casts numeric array keys to integers, so employee ids must be
     * normalised back to strings before they are compared with the database.
     *
     * @param  array<string, array<string, mixed>>  $people
     * @return list<string>
     */
    private function userids(array $people): array
    {
        return array_map(
            static fn (string|int $userid): string => (string) $userid,
            array_keys($people),
        );
    }

    /**
     * Clean up rows inserted twice by earlier syncs, keeping the assessed row.
     */
    private function removeDuplicateParticipants(DocumentTraining $document): void
    {
        [, $duplicateIds] = $this->deduplicateParticipants($document->participants()->orderBy('id')->get());

        if ($duplicateIds === []) {
            return;
        }

        $document->participants()->whereIn('id', $duplicateIds)->delete();
    }

    private function removeDuplicateMentors(DocumentTraining $document): void
    {
        $duplicateIds = $document->mentors()
            ->orderBy('id')
            ->get(['id', 'mentor'])
            ->groupBy('mentor')
            ->flatMap(fn (Collection $rows): Collection => $rows->skip(1)->pluck('id'))
            ->all();

        if ($duplicateIds === []) {
            return;
        }

        $document->mentors()->whereIn('id', $duplicateIds)->delete();
    }

    private function log(DocumentTraining $document, string $action, string $detail): void
    {
        $document->logs()->create([
            'userid' => auth()->user()->userid,
            'action' => $action,
            'details' => $detail,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $times
     * @return array<int, array<string, mixed>>
     */
    private function timesPayload(array $times): array
    {
        return array_values(array_map(
            fn (array $time): array => $this->timePayload($time),
            $times,
        ));
    }

    /**
     * @param  array<string, mixed>  $time
     * @return array<string, mixed>
     */
    private function timePayload(array $time): array
    {
        $payload = [
            'time_detail' => $time['time_detail'] ?? null,
        ];

        foreach (['time_start', 'time_end'] as $field) {
            if (! empty($time[$field])) {
                $payload[$field] = $time[$field];
            }
        }

        if (isset($payload['time_start'], $payload['time_end'])) {
            $payload['time_title'] = $payload['time_start'].' - '.$payload['time_end'];
        }

        if (array_key_exists('time_limit', $time)) {
            $payload['time_limit'] = (bool) $time['time_limit'];
            $payload['time_max'] = $payload['time_limit'] ? (int) ($time['time_max'] ?? 0) : 0;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withoutNulls(array $payload): array
    {
        return array_filter($payload, fn ($value): bool => $value !== null && $value !== []);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Response $response): array
    {
        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isSuccessful(Response $response, array $payload): bool
    {
        return $response->successful() && ($payload['success'] ?? false) === true;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{success: false, message: string}
     */
    private function failure(array $payload, string $fallback): array
    {
        return [
            'success' => false,
            'message' => $this->errorMessage($payload, $fallback),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function errorMessage(array $payload, string $fallback): string
    {
        $errors = $payload['errors'] ?? null;

        if (is_array($errors) && $errors !== []) {
            $first = reset($errors);

            return is_array($first) ? (string) reset($first) : (string) $first;
        }

        return (string) ($payload['message'] ?? $fallback);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function participantSummary(array $payload): string
    {
        $parts = ['เพิ่มสำเร็จ '.count($payload['added'] ?? []).' คน'];

        if (($payload['skipped'] ?? []) !== []) {
            $parts[] = 'ลงทะเบียนอยู่แล้ว '.count($payload['skipped']).' คน';
        }

        if (($payload['failed'] ?? []) !== []) {
            $failed = array_map(
                fn (array $item): string => ($item['userid'] ?? '-').' ('.($item['message'] ?? '-').')',
                $payload['failed'],
            );
            $parts[] = 'ไม่สำเร็จ: '.implode(', ', $failed);
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function lecturerSummary(array $payload): string
    {
        $parts = ['เพิ่มสำเร็จ '.count($payload['added'] ?? []).' คน'];

        if (($payload['skipped'] ?? []) !== []) {
            $parts[] = 'เป็นวิทยากรอยู่แล้ว '.count($payload['skipped']).' คน';
        }

        if (($payload['failed'] ?? []) !== []) {
            $failed = array_map(
                fn (array $item): string => ($item['userid'] ?? '-').' ('.($item['message'] ?? '-').')',
                $payload['failed'],
            );
            $parts[] = 'ไม่สำเร็จ: '.implode(', ', $failed);
        }

        return implode(' · ', $parts);
    }

    private function thaiDateTitle(string $date): string
    {
        $carbon = Carbon::parse($date);
        $months = [
            1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม',
        ];

        return $carbon->day.' '.$months[$carbon->month].' '.($carbon->year + 543);
    }

    private function minutesBetween(string $start, string $end): int
    {
        $minutes = Carbon::parse($start)->diffInMinutes(Carbon::parse($end), false);

        return $minutes > 0 ? (int) $minutes : 0;
    }
}
