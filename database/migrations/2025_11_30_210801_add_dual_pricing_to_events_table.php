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
     * DUAL PRICING STRATEGY per eventi pubblici:
     * 1. Rinomina 'price' → 'price_students' (mantiene dati esistenti)
     * 2. Aggiunge 'price_guests' per utenti esterni
     * 3. Aggiunge campi landing page pubblica (slug, description, CTA)
     * 4. Aggiunge payment gateway config (PayPal, Stripe, onsite, free)
     * 5. Aggiunge QR check-in toggle
     */
    public function up(): void
    {
        // STEP 1: Rinomina 'price' → 'price_students' usando RAW SQL (doctrine/dbal non disponibile in Laravel 12)
        if (Schema::hasColumn('events', 'price') && !Schema::hasColumn('events', 'price_students')) {
            DB::statement('ALTER TABLE events CHANGE COLUMN price price_students DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }

        Schema::table('events', function (Blueprint $table) {
            // STEP 2: Nuove colonne dual pricing
            if (!Schema::hasColumn('events', 'price_guests')) {
                $table->decimal('price_guests', 10, 2)->default(0.00)->after('price_students')
                    ->comment('Prezzo per utenti guest/pubblico (diverso da studenti iscritti)');
            }

            if (!Schema::hasColumn('events', 'requires_payment')) {
                $table->boolean('requires_payment')->default(false)->after('price_guests')
                    ->comment('Se true, iscrizione richiede pagamento online');
            }

            if (!Schema::hasColumn('events', 'payment_method')) {
                $table->enum('payment_method', ['paypal', 'stripe', 'onsite', 'free'])
                    ->default('free')->after('requires_payment')
                    ->comment('Metodo pagamento: paypal/stripe (online), onsite (in sede), free (gratuito)');
            }

            // STEP 3: Campi landing page pubblica
            if (!Schema::hasColumn('events', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name')
                    ->comment('URL-friendly per landing pubblica: /events/{slug}');
            }

            if (!Schema::hasColumn('events', 'landing_description')) {
                $table->text('landing_description')->nullable()->after('description')
                    ->comment('Descrizione estesa per landing page pubblica (separata da description interna)');
            }

            if (!Schema::hasColumn('events', 'landing_cta_text')) {
                $table->string('landing_cta_text', 100)->default('Iscriviti Ora')->after('landing_description')
                    ->comment('Testo CTA personalizzabile per bottone iscrizione');
            }

            // STEP 4: QR Check-in
            if (!Schema::hasColumn('events', 'qr_checkin_enabled')) {
                $table->boolean('qr_checkin_enabled')->default(true)->after('is_public')
                    ->comment('Abilita check-in via QR code all\'evento');
            }

            // Indexes per performance
            $table->index('slug', 'events_slug_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * ROLLBACK SICURO: ripristina 'price_students' → 'price' originale
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Rimuove index
            if (Schema::hasColumn('events', 'slug')) {
                $table->dropIndex('events_slug_index');
            }

            // Rimuove nuove colonne
            $columns = ['qr_checkin_enabled', 'landing_cta_text', 'landing_description',
                       'slug', 'payment_method', 'requires_payment', 'price_guests'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // STEP FINALE: Ripristina 'price_students' → 'price' usando RAW SQL
        if (Schema::hasColumn('events', 'price_students') && !Schema::hasColumn('events', 'price')) {
            DB::statement('ALTER TABLE events CHANGE COLUMN price_students price DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }
    }
};
