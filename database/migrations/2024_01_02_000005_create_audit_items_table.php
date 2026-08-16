<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->enum('status', ['pass', 'fail', 'partial', 'na', 'unchecked'])->default('unchecked');
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->text('notes')->nullable();
            $table->text('remediation_steps')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'category']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_items');
    }
};
