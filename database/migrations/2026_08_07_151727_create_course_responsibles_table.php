<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained('course_plans')->cascadeOnDelete();
            $table->string('userid');
            $table->string('name');
            $table->string('position');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_responsibles');
    }
};
