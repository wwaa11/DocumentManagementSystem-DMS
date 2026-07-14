<?php

namespace App\Services\Training;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TrainingApiClient
{
    private function client()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
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
        return $this->client()->post($this->url('approve-transaction'), [
            'transaction_id' => $transactionId,
        ]);
    }

    public function cancelProject(string|int $trainingId): Response
    {
        return $this->client()->post($this->url('cancel-project'), [
            'project_id' => $trainingId,
        ]);
    }
}
