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
            // Add missing fields for API testing
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'emergency_contact')) {
                $table->text('emergency_contact')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'medical_notes')) {
                $table->text('medical_notes')->nullable()->after('emergency_contact');
            }

            // Make first_name and last_name nullable for existing records
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'emergency_contact', 'medical_notes']);

            // Revert first_name and last_name to non-nullable
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });
    }
};