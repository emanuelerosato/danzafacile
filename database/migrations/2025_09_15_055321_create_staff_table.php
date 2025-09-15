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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->nullable()->unique();
            $table->string('role'); // instructor, coordinator, admin_assistant, receptionist, cleaner, maintenance
            $table->string('department')->nullable(); // dance, administration, maintenance, front_desk
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'volunteer'])->default('part_time');
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');

            // Personal Information
            $table->string('title')->nullable(); // Mr, Ms, Dr, Prof
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('address')->nullable();

            // Professional Information
            $table->text('qualifications')->nullable();
            $table->text('certifications')->nullable();
            $table->text('specializations')->nullable(); // jazz, ballet, hip_hop, contemporary, etc.
            $table->integer('years_experience')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();

            // Financial Information
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, cash, check
            $table->string('bank_account')->nullable();
            $table->string('tax_id')->nullable();

            // Availability & Schedule
            $table->json('availability')->nullable(); // giorni e orari disponibili
            $table->integer('max_hours_per_week')->nullable();
            $table->boolean('can_substitute')->default(false);
            $table->text('notes')->nullable();

            // System fields
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'role']);
            $table->index(['school_id', 'employment_type']);
            $table->index(['user_id']);
            $table->unique(['school_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
