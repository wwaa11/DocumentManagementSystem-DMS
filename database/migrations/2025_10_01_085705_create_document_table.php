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
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('document_type')->unique();
            $table->unsignedBigInteger('number')->default(0);
            $table->date('date');
        });

        Schema::create('document_list_approvers', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->string('userid');
            $table->unsignedSmallInteger('step')->default(1);

            $table->unique(['document_type', 'userid', 'step']);
        });

        Schema::create('document_list_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->unsignedSmallInteger('step')->default(1);
            $table->string('task_name');
            $table->string('task_description')->nullable();
            $table->string('task_user')->nullable();
            $table->string('task_position')->nullable();

            $table->unique(['document_type', 'step']);
        });

        Schema::create('document_its', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('type');
            $table->string('title');
            $table->text('detail');
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_hcs', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('detail');
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_pacs', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('detail');
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('approvers', function (Blueprint $table) {
            $table->id();
            // Polymorphic columns: 'approvable_id' and 'approvable_type'
            $table->morphs('approvable');
            $table->string('userid');
            $table->string('status')->default('wait');
            $table->unsignedSmallInteger('step')->default(1);
            $table->timestamp('approved_at')->nullable();
            // Create a unique index to prevent the same user from being listed as an approver twice for the same document
            $table->unique(['approvable_id', 'approvable_type', 'userid', 'step']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            // Polymorphic columns: 'approvable_id' and 'approvable_type'
            $table->morphs('taskable');
            $table->unsignedSmallInteger('step');
            $table->string('status')->default('wait');
            $table->datetime('date')->nullable();
            $table->timestamps();
            // Create a unique index to prevent the same user from being listed as an approver twice for the same document
            $table->unique(['taskable_id', 'taskable_type', 'step']);
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();
            // Polymorphic Columns: This replaces 'document_type' and 'document_id'.
            $table->morphs('fileable');
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            // Index on the polymorphic columns for performance
            $table->index(['fileable_id', 'fileable_type']);
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('loggable');
            $table->string('userid')
                ->nullable() // Allow logs for system events (user_id is null)
                ->constrained('users')
                ->onDelete('set null');
            $table->string('action');
            // Polymorphic Columns: This replaces 'loggable_id' and 'loggable_type'.
            $table->string('details')->nullable();
            $table->timestamps();
            // Index for faster lookups (e.g., find all actions by a user or on a specific type of document)
            $table->index(['userid', 'action']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_numbers');
        Schema::dropIfExists('document_list_approvers');
        Schema::dropIfExists('document_list_tasks');
        Schema::dropIfExists('document_its');
        Schema::dropIfExists('document_hcs');
        Schema::dropIfExists('document_pacs');
        Schema::dropIfExists('approvers');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('files');
        Schema::dropIfExists('logs');
    }
};
