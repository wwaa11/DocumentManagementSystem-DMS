<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('his_logs', function (Blueprint $table) {
            $table->id();
            $table->date('reported_at');
            $table->string('reporter');
            $table->string('module');
            $table->json('issues')->nullable();
            $table->text('problem_detail')->nullable();
            $table->string('receiver');
            $table->string('receiver_userid')->nullable();
            $table->string('fixer')->nullable();
            $table->text('root_cause')->nullable();
            $table->string('status')->default('Open');
            $table->time('time')->nullable();
            $table->string('shift');
            $table->timestamps();

            $table->index('reported_at');
            $table->index('module');
            $table->index('status');
            $table->index('shift');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('his_logs');
    }
};
