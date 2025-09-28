<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new ENUM values while keeping existing ones
        DB::statement("ALTER TABLE documents MODIFY COLUMN category ENUM('general','medical','contract','identification','other','photo','agreement')");

        // Step 2: Map existing categories to new ones
        // 'photo' -> 'identification' (photos are usually ID documents)
        DB::statement("UPDATE documents SET category = 'identification' WHERE category = 'photo'");

        // 'agreement' -> 'contract' (agreements are contracts)
        DB::statement("UPDATE documents SET category = 'contract' WHERE category = 'agreement'");

        // 'medical' stays 'medical' (already aligned)

        // Step 3: Remove old ENUM values, keep only new ones
        DB::statement("ALTER TABLE documents MODIFY COLUMN category ENUM('general','medical','contract','identification','other')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse mapping
        DB::statement("ALTER TABLE documents MODIFY COLUMN category ENUM('general','medical','contract','identification','other','photo','agreement')");

        // Map back to old categories
        DB::statement("UPDATE documents SET category = 'photo' WHERE category = 'identification'");
        DB::statement("UPDATE documents SET category = 'agreement' WHERE category = 'contract'");

        // Restore original ENUM
        DB::statement("ALTER TABLE documents MODIFY COLUMN category ENUM('medical','photo','agreement')");
    }
};
