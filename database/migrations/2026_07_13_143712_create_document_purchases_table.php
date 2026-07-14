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
        Schema::create('document_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('requester');
            $table->foreign('requester')->references('userid')->on('users');
            $table->string('document_phone');
            $table->string('document_number')->unique();
            $table->string('type');
            $table->string('title');
            $table->text('detail')->nullable();
            $table->string('po_number')->nullable();
            $table->string('po_reason')->nullable();
            $table->string('status')->default('wait_approval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_purchases');
    }
};
