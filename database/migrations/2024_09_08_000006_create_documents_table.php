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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type', 10); // pdf, jpg, png, etc.
            $table->bigInteger('file_size'); // in bytes
            $table->enum('category', ['medical', 'photo', 'agreement'])->default('medical');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'category']);
            $table->index(['school_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index(['category', 'status']);
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};