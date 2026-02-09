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
     * Aggiunge indexes per migliorare performance queries su tabella users:
     * - codice_fiscale: validazioni unique e ricerche rapide
     * - (school_id, role, active): listings filtrati per scuola/ruolo
     */
    public function up(): void
    {
        // Controlla indexes esistenti prima di crearli
        $existingIndexes = collect(DB::select('SHOW INDEX FROM users'))
            ->pluck('Key_name')
            ->unique()
            ->toArray();

        Schema::table('users', function (Blueprint $table) use ($existingIndexes) {
            // Index su codice_fiscale per validazioni e ricerche
            if (!in_array('users_codice_fiscale_index', $existingIndexes)) {
                $table->index('codice_fiscale', 'users_codice_fiscale_index');
                echo "✓ Creato index: users_codice_fiscale_index\n";
            } else {
                echo "⚠ Index già esistente: users_codice_fiscale_index\n";
            }

            // Composite index per listings filtrati (school + role + active)
            if (!in_array('users_school_role_active_index', $existingIndexes)) {
                $table->index(
                    ['school_id', 'role', 'active'],
                    'users_school_role_active_index'
                );
                echo "✓ Creato index: users_school_role_active_index\n";
            } else {
                echo "⚠ Index già esistente: users_school_role_active_index\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rimuovi indexes in ordine inverso
            $table->dropIndex('users_school_role_active_index');
            $table->dropIndex('users_codice_fiscale_index');
        });
    }
};
