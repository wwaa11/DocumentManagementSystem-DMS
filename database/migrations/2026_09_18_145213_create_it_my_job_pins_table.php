<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_my_job_pins', function (Blueprint $table) {
            $table->id();
            $table->string('userid');
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->timestamps();

            $table->unique(['userid', 'document_type', 'document_id']);
            $table->foreign('userid')->references('userid')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_my_job_pins');
    }
};
