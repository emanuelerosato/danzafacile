<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Aggiunge sistema QR Code Check-In per event_registrations.
     *
     * FLUSSO CHECK-IN:
     * 1. User si iscrive → genera qr_code_token univoco (64 char random)
     * 2. User riceve email con QR code embedded (generato da qr_code_token)
     * 3. All'evento, staff scansiona QR → valida token → setta checked_in_at + checked_in_by
     *
     * SICUREZZA:
     * - Token univoco per iscrizione (non riutilizzabile)
     * - Unique constraint su qr_code_token
     * - Foreign key su checked_in_by (staff che ha fatto check-in)
     *
     * NOTA: events.qr_checkin_enabled controlla se funzionalità è attiva per evento specifico
     */
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // QR Code token univoco
            if (!Schema::hasColumn('event_registrations', 'qr_code_token')) {
                $table->string('qr_code_token', 64)->unique()->nullable()->after('status')
                    ->comment('Token univoco per QR code check-in (generato al registration)');
            }

            // Timestamp check-in
            if (!Schema::hasColumn('event_registrations', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('confirmed_at')
                    ->comment('Timestamp check-in evento (scansione QR)');
            }

            // Staff che ha effettuato check-in
            if (!Schema::hasColumn('event_registrations', 'checked_in_by')) {
                $table->foreignId('checked_in_by')->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('checked_in_at')
                    ->comment('ID staff/admin che ha scansionato QR (null se self-checkin)');
            }

            // Index per query frequenti
            $table->index('checked_in_at', 'event_registrations_checked_in_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // Rimuove foreign key PRIMA di droppare colonna
            if (Schema::hasColumn('event_registrations', 'checked_in_by')) {
                $table->dropForeign('event_registrations_checked_in_by_foreign');
            }

            // Rimuove colonne (Laravel auto-rimuove indexes)
            $columns = ['checked_in_by', 'checked_in_at', 'qr_code_token'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('event_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
