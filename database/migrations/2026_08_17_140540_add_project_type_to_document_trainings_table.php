<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->string('project_type', 20)->default('multiple')->after('detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->dropColumn('project_type');
        });
    }
};
