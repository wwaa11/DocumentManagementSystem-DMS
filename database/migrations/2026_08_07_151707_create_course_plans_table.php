<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('number');
            $table->string('name');
            $table->text('origin');
            $table->text('objective');
            $table->string('training_type');
            $table->json('schedule_months');
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->string('department');
            $table->string('created_by');
            $table->foreign('created_by')->references('userid')->on('users');
            $table->string('status')->default('wait_approval');
            $table->timestamps();

            $table->index(['year', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_plans');
    }
};
