<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained('course_plans')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('userid');
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('wait');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['course_plan_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_approvers');
    }
};
