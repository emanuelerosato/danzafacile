<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabella GDPR per tracciare consensi utenti (OBBLIGATORIO per eventi pubblici).
     *
     * CONSENT TYPES:
     * - privacy: consenso trattamento dati personali (OBBLIGATORIO)
     * - marketing: consenso marketing/newsletter (OPZIONALE)
     * - terms: accettazione termini e condizioni (OBBLIGATORIO)
     * - cookies: consenso cookies (OBBLIGATORIO per web)
     *
     * COMPLIANCE:
     * - Traccia IP e User-Agent per prova consenso informato
     * - Policy version per audit modifiche privacy policy
     * - Timestamp consenso per calcolo scadenze (2 anni GDPR)
     *
     * IMPORTANTE: Utenti guest DEVONO dare consenso privacy + terms per iscriversi eventi pubblici.
     */
    public function up(): void
    {
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->id();

            // Foreign Key
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Utente che ha dato consenso (guest o studente)');

            // Tipo consenso
            $table->string('consent_type', 50)
                ->comment('Tipo: privacy, marketing, terms, cookies');

            $table->boolean('consented')->default(false)
                ->comment('True = consenso dato, False = revocato');

            // Dati tecnici per prova consenso informato (GDPR Art. 7)
            $table->string('ip_address', 45)->nullable()
                ->comment('IP utente al momento del consenso (IPv4/IPv6)');

            $table->text('user_agent')->nullable()
                ->comment('Browser/device info per audit');

            $table->timestamp('consented_at')->nullable()
                ->comment('Timestamp esatto consenso (null se revocato)');

            // Versioning privacy policy
            $table->string('policy_version', 20)->default('1.0')
                ->comment('Versione policy accettata (es. 1.0, 2.1)');

            $table->timestamps(); // created_at, updated_at

            // Indexes ottimizzati
            $table->index(['user_id', 'consent_type'], 'gdpr_consents_user_type_index');
            $table->index('consent_type', 'gdpr_consents_type_index');
            $table->index('consented_at', 'gdpr_consents_consented_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gdpr_consents');
    }
};
