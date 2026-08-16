<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->text('description')->nullable();
            $table->enum('server_type', ['same_server', 'external_agent', 'ftp'])->default('same_server');
            $table->string('server_path')->nullable(); // absolute path for same_server
            $table->string('agent_secret')->nullable(); // HMAC key for external
            $table->text('agent_ip_whitelist')->nullable(); // comma separated IPs
            $table->string('ftp_host')->nullable();
            $table->string('ftp_user')->nullable();
            $table->text('ftp_password')->nullable(); // encrypted
            $table->enum('stack', ['laravel', 'php', 'wordpress', 'other'])->default('laravel');
            $table->string('php_version')->nullable();
            $table->string('laravel_version')->nullable();
            $table->string('log_path')->nullable(); // relative to project root
            $table->string('php_error_log_path')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('monitoring_interval_minutes')->default(5);
            $table->string('alert_email')->nullable();
            $table->enum('status', ['healthy', 'warning', 'critical', 'unknown'])->default('unknown');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
