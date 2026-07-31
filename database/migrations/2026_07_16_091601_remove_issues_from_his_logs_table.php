<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('his_logs', 'issues')) {
            return;
        }

        Schema::table('his_logs', function (Blueprint $table) {
            $table->dropColumn('issues');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('his_logs', 'issues')) {
            return;
        }

        Schema::table('his_logs', function (Blueprint $table) {
            $table->json('issues')->nullable()->after('module');
        });
    }
};
