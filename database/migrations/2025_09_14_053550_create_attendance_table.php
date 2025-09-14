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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->morphs('attendable'); // course_id or event_id
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users'); // Who marked attendance
            $table->timestamps();

            // Unique constraint to prevent duplicate attendance records
            $table->unique(['user_id', 'attendable_type', 'attendable_id', 'date'], 'attendance_unique');

            // Indexes for performance
            $table->index(['attendable_type', 'attendable_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['school_id', 'date']);
            $table->index(['status', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};