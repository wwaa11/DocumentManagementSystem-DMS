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
        Schema::create('document_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('hours');
            $table->integer('minutes');
            $table->string('detail');
            $table->string('status')->default('wait_approval');
            $table->string('training_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_training_mentors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_training_id');
            $table->foreign('document_training_id')->references('id')->on('document_trainings');
            $table->string('mentor');
            $table->string('mentor_name')->nullable();
            $table->string('mentor_position')->nullable();
            $table->timestamps();
        });

        Schema::create('document_training_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_training_id');
            $table->foreign('document_training_id')->references('id')->on('document_trainings');
            $table->string('participant');
            $table->string('participant_name')->nullable();
            $table->string('participant_position')->nullable();
            $table->string('participant_department')->nullable();
            $table->date('assetment_date')->nullable();
            $table->string('assetment_type')->nullable();
            $table->string('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_trainings');
        Schema::dropIfExists('document_training_mentors');
        Schema::dropIfExists('document_training_participants');
    }
};
