<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->id();

            // User yang melaporkan bug
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('title', 150);
            $table->enum('category', ['ui', 'data', 'system_error', 'other']);
            $table->string('related_page', 100)->nullable();
            $table->text('description');
            $table->string('attachment_path')->nullable();

            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])
                  ->default('pending');

            $table->text('admin_note')->nullable();

            // Admin yang menangani aduan
            $table->foreignId('handled_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};