<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_media_sign_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_media_id')->constrained('document_medias')->cascadeOnDelete();
            $table->string('sign_type');
            $table->text('detail')->nullable();
            $table->string('image_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_media_sign_items');
    }
};
