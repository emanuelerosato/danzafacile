<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Aggiunge campi per gestire utenti guest (iscrizioni pubbliche):
     * - is_guest: flag per identificare utenti temporanei
     * - guest_token: token univoco per magic link authentication
     * - guest_token_expires_at: scadenza token sicurezza
     * - guest_phone: telefono alternativo (users.phone esiste già per studenti regolari)
     * - is_archived/archived_at/archive_reason: archiviazione automatica guest dopo evento
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Controlli esistenza colonne per evitare errori
            if (!Schema::hasColumn('users', 'is_guest')) {
                $table->boolean('is_guest')->default(false)->after('active');
            }

            if (!Schema::hasColumn('users', 'guest_token')) {
                $table->string('guest_token', 64)->unique()->nullable()->after('is_guest');
            }

            if (!Schema::hasColumn('users', 'guest_token_expires_at')) {
                $table->timestamp('guest_token_expires_at')->nullable()->after('guest_token');
            }

            if (!Schema::hasColumn('users', 'guest_phone')) {
                $table->string('guest_phone', 20)->nullable()->after('guest_token_expires_at')
                    ->comment('Telefono per utenti guest (phone esistente per studenti regolari)');
            }

            if (!Schema::hasColumn('users', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('guest_phone');
            }

            if (!Schema::hasColumn('users', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }

            if (!Schema::hasColumn('users', 'archive_reason')) {
                $table->string('archive_reason')->nullable()->after('archived_at');
            }

            // Indexes ottimizzati per query frequenti
            $table->index('is_guest', 'users_is_guest_index');
            $table->index(['is_guest', 'is_archived'], 'users_guest_archived_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rimuove indexes
            $table->dropIndex('users_is_guest_index');
            $table->dropIndex('users_guest_archived_index');

            // Rimuove colonne (controllo esistenza)
            $columns = ['archive_reason', 'archived_at', 'is_archived', 'guest_phone',
                       'guest_token_expires_at', 'guest_token', 'is_guest'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
