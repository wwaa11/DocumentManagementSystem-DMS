<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_borrows', function (Blueprint $table) {
            $table->date('borrow_date')->nullable()->after('detail');
        });
    }

    public function down(): void
    {
        Schema::table('document_borrows', function (Blueprint $table) {
            $table->dropColumn('borrow_date');
        });
    }
};
