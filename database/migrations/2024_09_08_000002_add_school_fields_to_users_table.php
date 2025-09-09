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
        Schema::table('users', function (Blueprint $table) {
            // Add school relationship - nullable for Super Admin
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            
            // Role system: super_admin, admin, user
            $table->enum('role', ['super_admin', 'admin', 'user'])->default('user');
            
            // Extended user fields
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->boolean('active')->default(true);
            
            // Indexes for performance
            $table->index(['school_id', 'role']);
            $table->index(['active', 'role']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id', 'role']);
            $table->dropIndex(['active', 'role']);
            $table->dropIndex(['role']);
            
            $table->dropColumn([
                'school_id',
                'role',
                'first_name',
                'last_name',
                'phone',
                'date_of_birth',
                'profile_image_path',
                'active'
            ]);
        });
    }
};