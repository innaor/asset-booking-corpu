<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE activity_logs MODIFY action ENUM('change_password', 'impersonate', 'handle_bug_report') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE activity_logs MODIFY action ENUM('change_password', 'impersonate') NOT NULL");
    }
};