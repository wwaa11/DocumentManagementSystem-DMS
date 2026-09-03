<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_messages', function (Blueprint $table) {
            $table->id();
            $table->morphs('messagable');
            $table->string('userid');
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['messagable_id', 'messagable_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_messages');
    }
};
