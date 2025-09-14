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
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('role_name'); // Istruttore, Assistente, Coordinator, etc.
            $table->text('description')->nullable();
            $table->json('specializations')->nullable(); // Dance styles, age groups, etc.
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->json('availability')->nullable(); // Weekly availability schedule
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('permissions')->nullable(); // What they can access/modify
            $table->boolean('can_mark_attendance')->default(false);
            $table->boolean('can_view_payments')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicate roles for same user in same school
            $table->unique(['user_id', 'school_id', 'role_name']);

            // Indexes for performance
            $table->index(['school_id', 'active']);
            $table->index(['user_id', 'active']);
            $table->index('role_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_roles');
    }
};