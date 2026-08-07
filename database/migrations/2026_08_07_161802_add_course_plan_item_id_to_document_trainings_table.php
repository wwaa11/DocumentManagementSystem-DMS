<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->foreignId('course_plan_item_id')
                ->nullable()
                ->after('id')
                ->constrained('course_plan_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_plan_item_id');
        });
    }
};
