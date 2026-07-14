<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_medias', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('type');
            $table->string('title');
            $table->text('detail')->nullable();
            $table->date('required_date');
            $table->string('sign_location')->nullable();
            $table->json('brochure_sizes')->nullable();
            $table->string('brochure_print_type')->nullable();
            $table->json('photo_work_types')->nullable();
            $table->date('photo_date')->nullable();
            $table->string('photo_time')->nullable();
            $table->string('photo_location')->nullable();
            $table->string('other_text')->nullable();
            $table->string('status')->default('wait_approval');
            $table->string('assigned_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_medias');
    }
};
