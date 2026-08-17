<?php

namespace Tests\Feature\Training;

use App\Http\Requests\Training\RemoveTrainingLecturerRequest;
use App\Http\Requests\Training\StoreTrainingLecturerRequest;
use App\Services\Training\TrainingApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TrainingLecturerApiTest extends TestCase
{
    private function baseUrl(): string
    {
        return rtrim((string) config('services.training.base_url'), '/');
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function withoutProjectRule(array $rules): array
    {
        unset($rules['project_id']);

        return $rules;
    }

    public function test_lecturers_are_sent_as_a_list_of_employee_ids(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'added' => []])]);

        app(TrainingApiClient::class)->addLecturers(10, ['3' => '640100', '7' => '640101']);

        Http::assertSent(fn (Request $request): bool => $request->url() === $this->baseUrl().'/lecturer/add'
            && $request['date_id'] === 10
            && $request['users'] === ['640100', '640101']);
    }

    public function test_remove_lecturer_accepts_lecture_id_or_date_with_userid(): void
    {
        Http::fake(['*' => Http::response(['success' => true])]);

        $client = app(TrainingApiClient::class);
        $client->removeLecturer(['lecture_id' => 5]);
        $client->removeLecturer(['date_id' => 10, 'userid' => '640100']);

        Http::assertSent(fn (Request $request): bool => $request->url() === $this->baseUrl().'/lecturer/remove'
            && ($request->data()['lecture_id'] ?? null) === 5);

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $request->url() === $this->baseUrl().'/lecturer/remove'
                && ($data['date_id'] ?? null) === 10
                && ($data['userid'] ?? null) === '640100';
        });
    }

    public function test_store_lecturer_request_requires_a_date_and_users(): void
    {
        $validator = Validator::make([], $this->withoutProjectRule((new StoreTrainingLecturerRequest)->rules()));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('users', $validator->errors()->toArray());
    }

    public function test_remove_lecturer_request_accepts_either_identifier_option(): void
    {
        $rules = $this->withoutProjectRule((new RemoveTrainingLecturerRequest)->rules());

        $byLectureId = Validator::make(['lecture_id' => 5], $rules);
        $byDateAndUser = Validator::make(['date_id' => 10, 'userid' => '640100'], $rules);
        $withoutAnything = Validator::make([], $rules);

        $this->assertFalse($byLectureId->fails());
        $this->assertFalse($byDateAndUser->fails());
        $this->assertTrue($withoutAnything->fails());
    }
}
