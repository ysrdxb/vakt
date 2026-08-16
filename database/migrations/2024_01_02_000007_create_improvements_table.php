<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('improvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['security', 'performance', 'ux', 'feature', 'technical_debt', 'compliance'])->default('feature');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('effort', ['small', 'medium', 'large'])->default('medium');
            $table->enum('status', ['proposed', 'client_review', 'approved', 'in_progress', 'done', 'declined'])->default('proposed');
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('research_notes')->nullable();
            $table->text('implementation_notes')->nullable();
            $table->text('decline_reason')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('improvements');
    }
};
