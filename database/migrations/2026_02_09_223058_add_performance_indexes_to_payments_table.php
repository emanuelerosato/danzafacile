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
     * Aggiunge composite index per migliorare performance su payments:
     * - (school_id, status, payment_date): reports pagamenti, dashboard stats
     */
    public function up(): void
    {
        // Controlla indexes esistenti prima di crearli
        $existingIndexes = collect(DB::select('SHOW INDEX FROM payments'))
            ->pluck('Key_name')
            ->unique()
            ->toArray();

        Schema::table('payments', function (Blueprint $table) use ($existingIndexes) {
            // Composite index per reports e dashboard (scuola + stato + data)
            if (!in_array('payments_school_status_date_index', $existingIndexes)) {
                $table->index(
                    ['school_id', 'status', 'payment_date'],
                    'payments_school_status_date_index'
                );
                echo "✓ Creato index: payments_school_status_date_index\n";
            } else {
                echo "⚠ Index già esistente: payments_school_status_date_index\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_school_status_date_index');
        });
    }
};
