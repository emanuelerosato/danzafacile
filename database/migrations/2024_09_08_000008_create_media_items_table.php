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
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('media_galleries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Who uploaded
            $table->string('file_path');
            $table->string('file_type', 10); // jpg, png, mp4, etc.
            $table->bigInteger('file_size'); // in bytes
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // For sorting within gallery
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['gallery_id', 'order']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};