<?php

namespace App\Services;

use App\Models\DocumentHc;
use App\Models\DocumentHeartstream;
use App\Models\DocumentPac;
use App\Models\DocumentRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DocumentTypeRegistry
{
    /**
     * @return array<string, array{model: class-string<Model>, task_user: string}>
     */
    private function mappings(): array
    {
        return [
            'pac' => [
                'model' => DocumentPac::class,
                'task_user' => 'Xray',
            ],
            'lab' => [
                'model' => DocumentHc::class,
                'task_user' => 'LAB',
            ],
            'heartstream' => [
                'model' => DocumentHeartstream::class,
                'task_user' => 'HeartSteam',
            ],
            'register' => [
                'model' => DocumentRegister::class,
                'task_user' => 'Registration',
            ],
        ];
    }

    public function modelClass(string $type): string
    {
        return $this->mapping($type)['model'];
    }

    public function query(string $type): Builder
    {
        return $this->modelClass($type)::query();
    }

    public function find(string $type, mixed $id): ?Model
    {
        return $this->modelClass($type)::find($id);
    }

    public function taskUser(string $type): string
    {
        return $this->mapping($type)['task_user'];
    }

    public function allTypes(): array
    {
        return array_keys($this->mappings());
    }

    /**
     * @return array{model: class-string<Model>, task_user: string}
     */
    private function mapping(string $type): array
    {
        $mappings = $this->mappings();

        if (! isset($mappings[$type])) {
            throw new InvalidArgumentException("Unsupported document type [{$type}].");
        }

        return $mappings[$type];
    }
}
