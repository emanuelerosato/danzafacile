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
        DB::statement("ALTER TABLE courses MODIFY COLUMN level ENUM('beginner','intermediate','advanced','professional') NOT NULL DEFAULT 'beginner'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE courses MODIFY COLUMN level ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner'");
    }
};
