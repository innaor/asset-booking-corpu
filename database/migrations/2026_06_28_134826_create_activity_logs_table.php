<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {

            $table->id();

            // Admin yang melakukan aktivitas
            $table->foreignId('admin_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // User yang menjadi target aktivitas
            $table->foreignId('target_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Jenis aktivitas
            $table->enum('action', [
                'change_password',
                'impersonate'
            ]);

            // Deskripsi aktivitas
            $table->text('description');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};