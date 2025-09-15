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
        Schema::create('staff_course_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('assignment_type', ['primary_instructor', 'assistant_instructor', 'substitute', 'coordinator']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->decimal('rate_override', 8, 2)->nullable(); // Override hourly rate for this specific course
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['staff_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index(['staff_id', 'assignment_type']);
            $table->unique(['staff_id', 'course_id', 'assignment_type'], 'unique_staff_course_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_course_assignments');
    }
};
