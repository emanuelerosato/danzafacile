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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->enum('status', ['registered', 'waitlist', 'confirmed', 'cancelled', 'attended'])->default('registered');
            $table->datetime('registration_date')->default(now());
            $table->datetime('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('additional_info')->nullable(); // Emergency contacts, dietary requirements, etc.
            $table->timestamps();

            // Unique constraint to prevent duplicate registrations
            $table->unique(['event_id', 'user_id']);

            // Indexes for performance
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['school_id', 'status']);
            $table->index('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};