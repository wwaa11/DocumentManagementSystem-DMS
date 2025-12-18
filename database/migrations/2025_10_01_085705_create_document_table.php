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
        // AppData for document
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('document_type');
            $table->unsignedBigInteger('number')->default(0);
            $table->date('date');

            $table->unique(['document_type', 'date']);
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

        // Document List
        Schema::create('document_users', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('title');
            $table->text('detail');
            $table->timestamps();
        });

        Schema::create('document_its', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('type'); // support
            $table->string('title');
            $table->text('detail');
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_itusers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_user_id');
            $table->foreign('document_user_id')->references('id')->on('document_users');
            $table->index(['document_user_id']);
            $table->string('document_number')->unique();
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_hcs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_user_id');
            $table->foreign('document_user_id')->references('id')->on('document_users');
            $table->index(['document_user_id']);
            $table->string('document_number')->unique();
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_pacs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_user_id');
            $table->foreign('document_user_id')->references('id')->on('document_users');
            $table->index(['document_user_id']);
            $table->string('document_number')->unique();
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_user_id');
            $table->foreign('document_user_id')->references('id')->on('document_users');
            $table->index(['document_user_id']);
            $table->string('document_number')->unique();
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_heartstreams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_user_id');
            $table->foreign('document_user_id')->references('id')->on('document_users');
            $table->index(['document_user_id']);
            $table->string('document_number')->unique();
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('document_borrows', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('title')->nullable();
            $table->text('detail');
            $table->date('estimate_return_date');
            $table->string('status')->default('wait_approval');
            $table->timestamps();
        });

        Schema::create('hardwares', function (Blueprint $table) {
            $table->id();
            $table->string('borrow_id');
            $table->string('serial_number');
            $table->date('borrow_date');
            $table->date('return_date')->nullable();
            $table->timestamps();
        });

        // Detail for document
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
            $table->string('task_name');
            $table->string('task_user');
            $table->string('task_position')->nullable();
            $table->dateTime('date')->nullable();
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
        Schema::dropIfExists('document_itusers');
        Schema::dropIfExists('document_users');
        Schema::dropIfExists('document_hcs');
        Schema::dropIfExists('document_pacs');
        Schema::dropIfExists('document_borrows');
        Schema::dropIfExists('document_registers');
        Schema::dropIfExists('document_heartstreams');
        Schema::dropIfExists('approvers');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('files');
        Schema::dropIfExists('logs');
    }
};
