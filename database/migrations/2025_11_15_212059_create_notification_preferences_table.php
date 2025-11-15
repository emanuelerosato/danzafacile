<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('enabled')->default(true);
            $table->boolean('lesson_reminders')->default(true);
            $table->integer('reminder_minutes_before')->default(60);
            $table->boolean('event_reminders')->default(true);
            $table->boolean('payment_reminders')->default(true);
            $table->boolean('system_notifications')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
