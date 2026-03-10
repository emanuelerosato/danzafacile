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
        Schema::table('staff', function (Blueprint $table) {
            // Rimuove il vincolo unique globale su employee_id
            // Il vincolo composito (school_id, employee_id) rimane attivo per garantire unicità per scuola
            $table->dropUnique('staff_employee_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // Ripristina il vincolo unique globale (solo per rollback)
            $table->unique('employee_id', 'staff_employee_id_unique');
        });
    }
};
