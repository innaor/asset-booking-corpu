<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Check-in
            $table->enum('checkin_condition', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->text('checkin_note')->nullable();
            $table->string('checkin_photo')->nullable();
            $table->timestamp('checkin_at')->nullable();
            $table->foreignId('checkin_by')->nullable()->constrained('users')->nullOnDelete();

            // Check-out
            $table->enum('checkout_condition', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->text('checkout_note')->nullable();
            $table->string('checkout_photo')->nullable();
            $table->timestamp('checkout_at')->nullable();
            $table->foreignId('checkout_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['checkin_by']);
            $table->dropForeign(['checkout_by']);
            $table->dropColumn([
                'checkin_condition', 'checkin_note', 'checkin_photo', 'checkin_at', 'checkin_by',
                'checkout_condition', 'checkout_note', 'checkout_photo', 'checkout_at', 'checkout_by',
            ]);
        });
    }
};