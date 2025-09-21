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
        Schema::create('school_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('capacity')->nullable();
            $table->json('equipment')->nullable(); // Equipment/facilities available
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']); // Prevent duplicate room names per school
            $table->index(['school_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_rooms');
    }
};
