<?php

namespace App\Services\Training;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TrainingApiClient
{
    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.config('services.training.token'),
        ])->withoutVerifying();
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.training.base_url'), '/').'/'.ltrim($path, '/');
    }

    public function createProject(array $payload): Response
    {
        return $this->client()->post($this->url('create-project'), $payload);
    }

    public function getTransactions(string|int $trainingId): Response
    {
        return $this->client()->get($this->url('get-transaction'), [
            'project_id' => $trainingId,
        ]);
    }

    public function approveTransaction(string|int $transactionId): Response
    {
        return $this->approveTransactions([
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * @param  array{transaction_id?: int|string, transaction_ids?: list<int|string>, time_id?: int|string, project_id?: int|string, approve_all?: bool}  $payload
     */
    public function approveTransactions(array $payload): Response
    {
        return $this->client()->post($this->url('approve-transactions'), $payload);
    }

    public function cancelProject(string|int $trainingId): Response
    {
        return $this->client()->post($this->url('cancel-project'), [
            'project_id' => $trainingId,
        ]);
    }

    public function getProjectDetail(string|int $trainingId): Response
    {
        return $this->client()->get($this->url('project-detail'), [
            'project_id' => $trainingId,
        ]);
    }

    /**
     * @param  array{project_id: int|string, date_datetime: string, date_title?: ?string, date_detail?: ?string, date_location?: ?string, times?: array<int, array<string, mixed>>}  $payload
     */
    public function addDate(array $payload): Response
    {
        return $this->client()->post($this->url('date/add'), $payload);
    }

    /**
     * @param  array{date_id: int|string, date_title?: ?string, date_detail?: ?string, date_location?: ?string, date_datetime?: string, date_active?: bool}  $payload
     */
    public function editDate(array $payload): Response
    {
        return $this->client()->post($this->url('date/edit'), $payload);
    }

    public function removeDate(string|int $dateId): Response
    {
        return $this->client()->post($this->url('date/remove'), [
            'date_id' => $dateId,
        ]);
    }

    /**
     * @param  array{date_id: int|string, time_start: string, time_end: string, time_title?: ?string, time_detail?: ?string, time_limit?: bool, time_max?: int}  $payload
     */
    public function addTime(array $payload): Response
    {
        return $this->client()->post($this->url('time/add'), $payload);
    }

    /**
     * @param  array{time_id: int|string, time_title?: ?string, time_detail?: ?string, time_start?: string, time_end?: string, time_limit?: bool, time_max?: int, time_active?: bool}  $payload
     */
    public function editTime(array $payload): Response
    {
        return $this->client()->post($this->url('time/edit'), $payload);
    }

    public function removeTime(string|int $timeId): Response
    {
        return $this->client()->post($this->url('time/remove'), [
            'time_id' => $timeId,
        ]);
    }

    /**
     * @param  list<string>  $users
     */
    public function addParticipants(string|int $timeId, array $users): Response
    {
        return $this->client()->post($this->url('participant/add'), [
            'time_id' => $timeId,
            'users' => array_values($users),
        ]);
    }

    /**
     * @param  array{attend_id?: int|string, time_id?: int|string, userid?: string}  $payload
     */
    public function removeParticipant(array $payload): Response
    {
        return $this->client()->post($this->url('participant/remove'), $payload);
    }

    /**
     * @param  list<string>  $users
     */
    public function addLecturers(string|int $dateId, array $users): Response
    {
        return $this->client()->post($this->url('lecturer/add'), [
            'date_id' => $dateId,
            'users' => array_values($users),
        ]);
    }

    /**
     * @param  array{lecture_id?: int|string, date_id?: int|string, userid?: string}  $payload
     */
    public function removeLecturer(array $payload): Response
    {
        return $this->client()->post($this->url('lecturer/remove'), $payload);
    }
}
