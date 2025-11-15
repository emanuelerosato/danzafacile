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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('room_id')->nullable()->constrained('school_rooms')->onDelete('set null');
            $table->date('lesson_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['scheduled', 'cancelled', 'completed', 'rescheduled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes per performance
            $table->index('course_id');
            $table->index('lesson_date');
            $table->index('status');
            $table->index('instructor_id');
            $table->index('room_id');
            $table->index(['lesson_date', 'start_time'], 'idx_lesson_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
