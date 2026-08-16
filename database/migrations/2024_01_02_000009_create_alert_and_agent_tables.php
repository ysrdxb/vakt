<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained()->nullOnDelete();
            $table->string('alert_type');
            $table->string('recipient_email');
            $table->string('subject');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'alert_type']);
        });

        Schema::create('agent_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->json('payload');
            $table->string('agent_ip')->nullable();
            $table->boolean('signature_valid')->default(true);
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['project_id', 'received_at']);
        });

        Schema::create('ip_threat_log', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->json('project_ids'); // all projects where this IP appeared
            $table->integer('hit_count')->default(1);
            $table->boolean('is_coordinated_attack')->default(false);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'last_seen_at']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('ip_threat_log');
        Schema::dropIfExists('agent_reports');
        Schema::dropIfExists('alert_logs');
    }
};
