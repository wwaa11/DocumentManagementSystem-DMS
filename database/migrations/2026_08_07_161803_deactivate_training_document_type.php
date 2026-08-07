<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('documents')
            ->where('short_name', 'training')
            ->update([
                'active' => false,
                'name' => 'ฝึกอบรมภาคอิสระ',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('documents')
            ->where('short_name', 'training')
            ->update([
                'active' => true,
                'updated_at' => now(),
            ]);
    }
};
