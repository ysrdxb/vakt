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
        Schema::table('log_entries', function (Blueprint $table) {
            $table->text('ai_explanation')->nullable()->after('is_reviewed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_entries', function (Blueprint $table) {
            $table->dropColumn('ai_explanation');
        });
    }
};
