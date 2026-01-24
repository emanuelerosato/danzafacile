<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SENIOR FIX: Task #4 - Gestione Minori
     *
     * Aggiunge campi per la gestione dei genitori/tutori legali per studenti minorenni:
     * - guardian_first_name: Nome del genitore/tutore
     * - guardian_last_name: Cognome del genitore/tutore
     * - guardian_fiscal_code: Codice fiscale del genitore
     * - guardian_email: Email del genitore (per comunicazioni e fatture)
     * - guardian_phone: Telefono del genitore
     * - is_minor: Flag booleano per identificare rapidamente i minorenni
     *
     * Business Logic:
     * - Se is_minor = true, i campi guardian_* devono essere compilati
     * - Fatture per minorenni vanno intestate al genitore
     * - Comunicazioni inviate all'email del genitore
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Guardian personal data
            $table->string('guardian_first_name', 255)->nullable()->after('medical_notes');
            $table->string('guardian_last_name', 255)->nullable()->after('guardian_first_name');
            $table->string('guardian_fiscal_code', 16)->nullable()->after('guardian_last_name');

            // Guardian contact data
            $table->string('guardian_email', 255)->nullable()->after('guardian_fiscal_code');
            $table->string('guardian_phone', 20)->nullable()->after('guardian_email');

            // Minor flag (cached for performance, can be calculated from date_of_birth)
            $table->boolean('is_minor')->default(false)->after('guardian_phone');

            // Index for performance (query students by minor status)
            $table->index('is_minor', 'idx_users_is_minor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex('idx_users_is_minor');

            // Drop columns
            $table->dropColumn([
                'guardian_first_name',
                'guardian_last_name',
                'guardian_fiscal_code',
                'guardian_email',
                'guardian_phone',
                'is_minor'
            ]);
        });
    }
};
