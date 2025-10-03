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
        Schema::create('document_approvers', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->string('userid');
            $table->unsignedSmallInteger('step')->default(1);
            $table->timestamps();
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
            $table->foreign('userid')->references('userid')->on('users');
            $table->string('status')->default('wait');
            $table->unsignedSmallInteger('step')->default(1);
            $table->timestamp('approved_at')->nullable();
            // Create a unique index to prevent the same user from being listed as an approver twice for the same document
            $table->unique(['approvable_id', 'approvable_type', 'userid']);
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

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable() // Allow logs for system events (user_id is null)
                ->constrained()
                ->onDelete('set null');
            $table->string('action');
            // Polymorphic Columns: This replaces 'loggable_id' and 'loggable_type'.
            $table->morphs('loggable');
            $table->json('details')->nullable();
            $table->timestamps();
            // Index for faster lookups (e.g., find all actions by a user or on a specific type of document)
            $table->index(['user_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approvers');
        Schema::dropIfExists('document_its');
        Schema::dropIfExists('document_hcs');
        Schema::dropIfExists('document_pacs');
        Schema::dropIfExists('approvers');
        Schema::dropIfExists('files');
        Schema::dropIfExists('activity_logs');
    }
};
