<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Aggiunge composite indexes per migliorare performance su course_enrollments:
     * - (course_id, status): conteggio iscritti per corso
     * - (user_id, course_id): prevenzione duplicati e lookup veloce
     */
    public function up(): void
    {
        // Controlla indexes esistenti prima di crearli
        $existingIndexes = collect(DB::select('SHOW INDEX FROM course_enrollments'))
            ->pluck('Key_name')
            ->unique()
            ->toArray();

        Schema::table('course_enrollments', function (Blueprint $table) use ($existingIndexes) {
            // Composite index per conteggio iscritti (corso + stato)
            if (!in_array('course_enrollments_course_status_index', $existingIndexes)) {
                $table->index(
                    ['course_id', 'status'],
                    'course_enrollments_course_status_index'
                );
                echo "✓ Creato index: course_enrollments_course_status_index\n";
            } else {
                echo "⚠ Index già esistente: course_enrollments_course_status_index\n";
            }

            // Composite index per duplicate checks e lookup (studente + corso)
            if (!in_array('course_enrollments_user_course_index', $existingIndexes)) {
                $table->index(
                    ['user_id', 'course_id'],
                    'course_enrollments_user_course_index'
                );
                echo "✓ Creato index: course_enrollments_user_course_index\n";
            } else {
                echo "⚠ Index già esistente: course_enrollments_user_course_index\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            // Rimuovi indexes in ordine inverso
            $table->dropIndex('course_enrollments_user_course_index');
            $table->dropIndex('course_enrollments_course_status_index');
        });
    }
};
