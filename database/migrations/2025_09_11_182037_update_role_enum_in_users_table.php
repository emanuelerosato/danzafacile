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
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Update existing data first, column type is already flexible
            DB::statement("UPDATE users SET role = 'student' WHERE role = 'user'");
        } else {
            // MySQL: Change the column type to VARCHAR to avoid enum constraints
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'student'");

            // Update existing 'user' roles to 'student'
            DB::statement("UPDATE users SET role = 'student' WHERE role = 'user'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Just revert data changes
            DB::statement("UPDATE users SET role = 'user' WHERE role = 'student'");
        } else {
            // MySQL: Revert back to the original enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') NOT NULL DEFAULT 'user'");
        }
    }
};
