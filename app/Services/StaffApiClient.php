<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class StaffApiClient
{
    private function client()
    {
        return Http::withHeaders([
            'token' => config('services.staff.token'),
        ])->timeout((int) config('services.staff.timeout', 30));
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.staff.base_url'), '/').'/'.ltrim($path, '/');
    }

    public function getUser(string $userid): Response
    {
        return $this->client()->post($this->url('getuser'), [
            'userid' => $userid,
        ]);
    }

    public function authenticate(string $userid, string $password): Response
    {
        return $this->client()->post($this->url('auth'), [
            'userid' => $userid,
            'password' => $password,
        ]);
    }

    public function getDepartments(): Response
    {
        return $this->client()->post($this->url('get/departments'));
    }

    public function getDepartmentPositions(string $department): Response
    {
        return $this->client()->post($this->url('get/departments/positions'), [
            'department' => $department,
        ]);
    }

    public function getDepartmentUsers(string $department): Response
    {
        return $this->client()->post($this->url('get/departments/users'), [
            'department' => $department,
        ]);
    }

    public function getDepartmentUsersByPosition(string $department, string $position): Response
    {
        return $this->client()->post($this->url('get/departments/users/position'), [
            'department' => $department,
            'position' => $position,
        ]);
    }
}
