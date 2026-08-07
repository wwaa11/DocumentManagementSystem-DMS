<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained('course_plans')->cascadeOnDelete();
            $table->string('number');
            $table->string('name');
            $table->text('origin');
            $table->text('objective');
            $table->string('training_type');
            $table->json('schedule_months');
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasColumn('course_plans', 'name')) {
            $plans = DB::table('course_plans')->get();

            foreach ($plans as $plan) {
                DB::table('course_plan_items')->insert([
                    'course_plan_id' => $plan->id,
                    'number' => $plan->number,
                    'name' => $plan->name,
                    'origin' => $plan->origin,
                    'objective' => $plan->objective,
                    'training_type' => $plan->training_type,
                    'schedule_months' => $plan->schedule_months,
                    'estimated_cost' => $plan->estimated_cost,
                    'sort_order' => 0,
                    'created_at' => $plan->created_at,
                    'updated_at' => $plan->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('course_instructors');
        Schema::dropIfExists('course_target_positions');
        Schema::dropIfExists('course_responsibles');

        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_item_id')->constrained('course_plan_items')->cascadeOnDelete();
            $table->string('userid');
            $table->string('name');
            $table->string('position');
            $table->string('source_type')->default('internal');
            $table->timestamps();
        });

        Schema::create('course_target_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_item_id')->constrained('course_plan_items')->cascadeOnDelete();
            $table->string('position');
            $table->timestamps();
        });

        Schema::create('course_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_item_id')->constrained('course_plan_items')->cascadeOnDelete();
            $table->string('userid');
            $table->string('name');
            $table->string('position');
            $table->timestamps();
        });

        Schema::table('course_plans', function (Blueprint $table) {
            $table->dropColumn([
                'number',
                'name',
                'origin',
                'objective',
                'training_type',
                'schedule_months',
                'estimated_cost',
            ]);
        });

        Schema::table('course_plans', function (Blueprint $table) {
            $table->unique(['year', 'department']);
        });
    }

    public function down(): void
    {
        Schema::table('course_plans', function (Blueprint $table) {
            $table->dropUnique(['year', 'department']);
        });

        Schema::table('course_plans', function (Blueprint $table) {
            $table->string('number')->nullable();
            $table->string('name')->nullable();
            $table->text('origin')->nullable();
            $table->text('objective')->nullable();
            $table->string('training_type')->nullable();
            $table->json('schedule_months')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
        });

        Schema::dropIfExists('course_instructors');
        Schema::dropIfExists('course_target_positions');
        Schema::dropIfExists('course_responsibles');
        Schema::dropIfExists('course_plan_items');

        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained('course_plans')->cascadeOnDelete();
            $table->string('userid');
            $table->string('name');
            $table->string('position');
            $table->string('source_type')->default('internal');
            $table->timestamps();
        });

        Schema::create('course_target_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained('course_plans')->cascadeOnDelete();
            $table->string('position');
            $table->timestamps();
        });

        Schema::create('course_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained('course_plans')->cascadeOnDelete();
            $table->string('userid');
            $table->string('name');
            $table->string('position');
            $table->timestamps();
        });
    }
};
