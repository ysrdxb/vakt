<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vulnerabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('package_name');
            $table->string('current_version')->nullable();
            $table->string('safe_version')->nullable();
            $table->string('cve_id')->nullable();
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->text('description')->nullable();
            $table->string('advisory_url')->nullable();
            $table->enum('status', ['open', 'patched', 'accepted_risk'])->default('open');
            $table->text('accepted_risk_reason')->nullable();
            $table->timestamp('found_at');
            $table->timestamp('patched_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vulnerabilities');
    }
};
