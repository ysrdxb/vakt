<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_hash')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->timestamp('last_modified')->nullable();
            $table->enum('status', ['clean', 'changed', 'new', 'deleted', 'suspicious'])->default('clean');
            $table->json('flagged_patterns')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'file_path']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_snapshots');
    }
};
