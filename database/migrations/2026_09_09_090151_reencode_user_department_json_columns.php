<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $columns = [
        'course_departments',
        'view_departments',
    ];

    public function up(): void
    {
        DB::table('users')
            ->select(['id', ...$this->columns])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $updates = [];

                    foreach ($this->columns as $column) {
                        $value = $user->{$column};

                        if ($value === null) {
                            continue;
                        }

                        $decoded = json_decode((string) $value, true);

                        if (! is_array($decoded)) {
                            continue;
                        }

                        $updates[$column] = json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE);
                    }

                    if ($updates !== []) {
                        DB::table('users')->where('id', $user->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->select(['id', ...$this->columns])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $updates = [];

                    foreach ($this->columns as $column) {
                        $value = $user->{$column};

                        if ($value === null) {
                            continue;
                        }

                        $decoded = json_decode((string) $value, true);

                        if (! is_array($decoded)) {
                            continue;
                        }

                        $updates[$column] = json_encode(array_values($decoded));
                    }

                    if ($updates !== []) {
                        DB::table('users')->where('id', $user->id)->update($updates);
                    }
                }
            });
    }
};
