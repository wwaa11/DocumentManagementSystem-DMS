<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->dateTime('hrapprove')->nullable()->after('status');
        });

        Schema::create('document_training_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_training_id')->constrained('document_trainings')->cascadeOnDelete();
            $table->string('type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_training_files');

        Schema::table('document_trainings', function (Blueprint $table) {
            $table->dropColumn('hrapprove');
        });
    }
};
