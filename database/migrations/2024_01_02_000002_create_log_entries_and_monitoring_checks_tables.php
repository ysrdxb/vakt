<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['debug', 'info', 'warning', 'error', 'critical'])->default('info');
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('detected_patterns')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('occurred_at');
            $table->boolean('is_reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'level', 'occurred_at']);
            $table->index(['project_id', 'is_reviewed']);
        });

        Schema::create('monitoring_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_at');
            $table->enum('status', ['ok', 'warning', 'critical'])->default('ok');
            $table->integer('log_lines_scanned')->default(0);
            $table->integer('errors_found')->default(0);
            $table->integer('warnings_found')->default(0);
            $table->json('critical_patterns_found')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_checks');
        Schema::dropIfExists('log_entries');
    }
};
