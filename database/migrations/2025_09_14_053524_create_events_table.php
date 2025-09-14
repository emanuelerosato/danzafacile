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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['saggio', 'workshop', 'competizione', 'seminario', 'altro'])->default('altro');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('location')->nullable();
            $table->integer('max_participants')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('requires_registration')->default(true);
            $table->datetime('registration_deadline')->nullable();
            $table->json('requirements')->nullable(); // Age limits, skill level, etc.
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_public')->default(true); // Visible to students
            $table->timestamps();

            // Indexes for performance
            $table->index(['school_id', 'active']);
            $table->index(['type', 'active']);
            $table->index(['start_date', 'end_date']);
            $table->index(['is_public', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};