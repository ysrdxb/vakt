<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at');
            $table->enum('status', ['ok', 'warning', 'critical'])->default('ok');
            $table->text('summary')->nullable();
            $table->json('findings')->nullable();
            $table->text('actions_taken')->nullable();
            $table->text('next_steps')->nullable();
            $table->boolean('auto_generated')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'checked_at']);
        });

        Schema::create('sqa_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('period_month'); // e.g. "2024-01"
            $table->text('executive_summary')->nullable();
            $table->integer('security_score')->nullable();
            $table->integer('prev_security_score')->nullable();
            $table->json('incidents_summary')->nullable();
            $table->json('monitoring_summary')->nullable();
            $table->json('vulnerabilities_summary')->nullable();
            $table->json('improvements_summary')->nullable();
            $table->json('audit_summary')->nullable();
            $table->text('recommended_actions')->nullable();
            $table->enum('status', ['draft', 'ready', 'sent'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sqa_reports');
        Schema::dropIfExists('daily_logs');
    }
};
