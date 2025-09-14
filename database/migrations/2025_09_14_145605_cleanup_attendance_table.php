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
        // Migrate data from polymorphic to direct relationships
        DB::statement("UPDATE attendance SET course_id = attendable_id WHERE attendable_type = 'App\\\\Models\\\\Course' AND course_id IS NULL");
        DB::statement("UPDATE attendance SET event_id = attendable_id WHERE attendable_type = 'App\\\\Models\\\\Event' AND event_id IS NULL");
        DB::statement("UPDATE attendance SET marked_by_user_id = marked_by WHERE marked_by IS NOT NULL AND marked_by_user_id IS NULL");

        Schema::table('attendance', function (Blueprint $table) {
            // Drop foreign key constraint first if it exists
            try {
                $table->dropForeign(['marked_by']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }

            // Drop old polymorphic columns if they exist
            if (Schema::hasColumn('attendance', 'attendable_type')) {
                $table->dropColumn('attendable_type');
            }
            if (Schema::hasColumn('attendance', 'attendable_id')) {
                $table->dropColumn('attendable_id');
            }
            if (Schema::hasColumn('attendance', 'marked_by')) {
                $table->dropColumn('marked_by');
            }

            // Add indexes for performance (only if they don't exist)
            if (!$this->indexExists('attendance', 'attendance_school_id_attendance_date_index')) {
                $table->index(['school_id', 'attendance_date']);
            }
            if (!$this->indexExists('attendance', 'attendance_user_id_attendance_date_index')) {
                $table->index(['user_id', 'attendance_date']);
            }
            if (!$this->indexExists('attendance', 'attendance_course_id_attendance_date_index')) {
                $table->index(['course_id', 'attendance_date']);
            }
            if (!$this->indexExists('attendance', 'attendance_event_id_attendance_date_index')) {
                $table->index(['event_id', 'attendance_date']);
            }

            // Add unique constraints (only if they don't exist)
            if (!$this->indexExists('attendance', 'unique_user_course_date')) {
                $table->unique(['user_id', 'course_id', 'attendance_date'], 'unique_user_course_date');
            }
            if (!$this->indexExists('attendance', 'unique_user_event_date')) {
                $table->unique(['user_id', 'event_id', 'attendance_date'], 'unique_user_event_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Add back polymorphic columns
            $table->string('attendable_type')->after('school_id');
            $table->unsignedBigInteger('attendable_id')->after('attendable_type');
            $table->unsignedBigInteger('marked_by')->nullable()->after('notes');

            // Drop indexes and constraints
            $table->dropIndex(['school_id', 'attendance_date']);
            $table->dropIndex(['user_id', 'attendance_date']);
            $table->dropIndex(['course_id', 'attendance_date']);
            $table->dropIndex(['event_id', 'attendance_date']);
            $table->dropUnique('unique_user_course_date');
            $table->dropUnique('unique_user_event_date');
        });

        // Migrate data back
        DB::statement("UPDATE attendance SET attendable_type = 'App\\\\Models\\\\Course', attendable_id = course_id WHERE course_id IS NOT NULL");
        DB::statement("UPDATE attendance SET attendable_type = 'App\\\\Models\\\\Event', attendable_id = event_id WHERE event_id IS NOT NULL");
        DB::statement("UPDATE attendance SET marked_by = marked_by_user_id WHERE marked_by_user_id IS NOT NULL");
    }

    private function indexExists(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $idx) {
            if ($idx->Key_name === $index) {
                return true;
            }
        }
        return false;
    }
};
