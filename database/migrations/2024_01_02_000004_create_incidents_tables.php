<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('severity', ['p1', 'p2', 'p3', 'p4'])->default('p3');
            $table->enum('status', ['open', 'investigating', 'contained', 'resolved', 'closed'])->default('open');
            $table->enum('source', ['auto_detected', 'manual'])->default('auto_detected');
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('affected_component')->nullable();
            $table->text('impact_description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->text('prevention_notes')->nullable();
            $table->decimal('estimated_cost_impact', 10, 2)->nullable();
            $table->json('related_log_entry_ids')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'severity', 'status']);
            $table->index(['status', 'detected_at']);
        });

        Schema::create('incident_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('performed_by')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['incident_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_timeline');
        Schema::dropIfExists('incidents');
    }
};
