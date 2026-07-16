<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('his_logs', function (Blueprint $table) {
            $table->dropColumn('issues');
        });
    }

    public function down(): void
    {
        Schema::table('his_logs', function (Blueprint $table) {
            $table->json('issues')->nullable()->after('module');
        });
    }
};
