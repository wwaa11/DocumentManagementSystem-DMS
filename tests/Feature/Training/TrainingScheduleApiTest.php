<?php

namespace Tests\Feature\Training;

use App\Http\Requests\Training\RemoveTrainingParticipantRequest;
use App\Http\Requests\Training\StoreTrainingDateRequest;
use App\Http\Requests\Training\StoreTrainingTimeRequest;
use App\Services\Training\TrainingApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TrainingScheduleApiTest extends TestCase
{
    private function baseUrl(): string
    {
        return rtrim((string) config('services.training.base_url'), '/');
    }

    public function test_project_detail_is_requested_with_the_project_id(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true, 'project' => ['project_id' => 5]]),
        ]);

        app(TrainingApiClient::class)->getProjectDetail(5);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), $this->baseUrl().'/project-detail')
            && str_contains($request->url(), 'project_id=5')
            && $request->method() === 'GET');
    }

    public function test_date_and_time_endpoints_post_the_documented_payload(): void
    {
        Http::fake(['*' => Http::response(['success' => true])]);

        $client = app(TrainingApiClient::class);

        $client->addDate([
            'project_id' => 1,
            'date_datetime' => '2026-08-27',
            'times' => [['time_start' => '09:00', 'time_end' => '12:00']],
        ]);
        $client->editTime(['time_id' => 20, 'time_start' => '13:00', 'time_end' => '17:00']);
        $client->removeDate(10);

        Http::assertSent(fn (Request $request): bool => $request->url() === $this->baseUrl().'/date/add'
            && $request['date_datetime'] === '2026-08-27'
            && $request['times'][0]['time_start'] === '09:00');

        Http::assertSent(fn (Request $request): bool => $request->url() === $this->baseUrl().'/time/edit'
            && $request['time_id'] === 20);

        Http::assertSent(fn (Request $request): bool => $request->url() === $this->baseUrl().'/date/remove'
            && $request['date_id'] === 10);
    }

    public function test_participants_are_sent_as_a_list_of_employee_ids(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'added' => []])]);

        app(TrainingApiClient::class)->addParticipants(20, ['3' => '650001', '7' => '650002']);

        Http::assertSent(fn (Request $request): bool => $request->url() === $this->baseUrl().'/participant/add'
            && $request['time_id'] === 20
            && $request['users'] === ['650001', '650002']);
    }

    public function test_approve_transactions_supports_single_and_bulk_payloads(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'approved' => [], 'skipped' => [], 'failed' => []])]);

        $client = app(TrainingApiClient::class);
        $client->approveTransaction(101);
        $client->approveTransactions([
            'transaction_ids' => [101, 102],
        ]);
        $client->approveTransactions([
            'project_id' => 1,
            'approve_all' => true,
        ]);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === $this->baseUrl().'/approve-transactions'
                && ($request->data()['transaction_id'] ?? null) === 101;
        });

        Http::assertSent(function (Request $request): bool {
            return $request->url() === $this->baseUrl().'/approve-transactions'
                && ($request->data()['transaction_ids'] ?? null) === [101, 102];
        });

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $request->url() === $this->baseUrl().'/approve-transactions'
                && ($data['project_id'] ?? null) === 1
                && ($data['approve_all'] ?? null) === true;
        });
    }

    public function test_store_document_training_defaults_project_type_to_multiple(): void
    {
        $rules = (new \App\Http\Requests\Training\StoreDocumentTrainingRequest)->rules();

        $this->assertSame('nullable|string|in:single,multiple,attendance', $rules['project_type']);
    }

    /**
     * The project_id rule hits the database, which is out of scope for these
     * payload-shape assertions.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function withoutProjectRule(array $rules): array
    {
        unset($rules['project_id']);

        return $rules;
    }

    public function test_store_date_request_requires_a_date_and_at_least_one_time(): void
    {
        $validator = Validator::make([], $this->withoutProjectRule((new StoreTrainingDateRequest)->rules()));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_datetime', $validator->errors()->toArray());
        $this->assertArrayHasKey('times', $validator->errors()->toArray());
    }

    public function test_store_time_request_rejects_an_end_time_before_the_start(): void
    {
        $validator = Validator::make([
            'date_id' => 10,
            'time_start' => '13:00',
            'time_end' => '09:00',
        ], $this->withoutProjectRule((new StoreTrainingTimeRequest)->rules()));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('time_end', $validator->errors()->toArray());
    }

    public function test_remove_participant_request_accepts_either_identifier_option(): void
    {
        $rules = $this->withoutProjectRule((new RemoveTrainingParticipantRequest)->rules());

        $byAttendId = Validator::make(['attend_id' => 201], $rules);
        $byTimeAndUser = Validator::make(['time_id' => 20, 'userid' => '650001'], $rules);
        $withoutAnything = Validator::make([], $rules);

        $this->assertFalse($byAttendId->fails());
        $this->assertFalse($byTimeAndUser->fails());
        $this->assertTrue($withoutAnything->fails());
    }
}
