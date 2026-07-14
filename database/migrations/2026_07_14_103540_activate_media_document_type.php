<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('documents')
            ->where('short_name', 'media')
            ->update(['active' => true]);
    }

    public function down(): void
    {
        DB::table('documents')
            ->where('short_name', 'media')
            ->update(['active' => false]);
    }
};
