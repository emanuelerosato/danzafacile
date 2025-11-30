<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabella pagamenti eventi pubblici (separata da payments esistente per corsi).
     * Traccia transazioni PayPal/Stripe/onsite per iscrizioni eventi guest.
     *
     * RELAZIONI:
     * - events: evento pagato (cascade delete)
     * - event_registrations: iscrizione collegata (null on delete, può esistere prima del pagamento)
     * - users: utente pagante (cascade delete)
     * - schools: scuola organizzatrice (cascade delete)
     *
     * STATI:
     * - pending: pagamento iniziato ma non confermato
     * - completed: pagamento confermato (webhook ricevuto)
     * - failed: pagamento fallito
     * - refunded: pagamento rimborsato
     */
    public function up(): void
    {
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Evento per cui si paga');

            $table->foreignId('event_registration_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->comment('Iscrizione collegata (nullable: può essere creata dopo pagamento)');

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Utente pagante (guest o studente)');

            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Scuola destinataria del pagamento');

            // Importo e valuta
            $table->decimal('amount', 10, 2)->comment('Importo pagato (EUR)');
            $table->string('currency', 3)->default('EUR');

            // Stato pagamento
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])
                ->default('pending')
                ->comment('Stato transazione: pending/completed/failed/refunded');

            $table->enum('payment_method', ['paypal', 'stripe', 'onsite', 'free'])
                ->default('paypal')
                ->comment('Gateway usato: paypal/stripe/onsite/free');

            // Dati transazione gateway
            $table->string('transaction_id')->nullable()->unique()
                ->comment('ID transazione da PayPal/Stripe (es. PAYID-XXX)');

            $table->text('payment_gateway_response')->nullable()
                ->comment('JSON response completa da gateway (debug/audit)');

            // Dati pagatore (ridondanti ma utili per audit)
            $table->string('payer_email')->nullable()->comment('Email pagatore da gateway');
            $table->string('payer_name')->nullable()->comment('Nome pagatore da gateway');

            // Timestamp
            $table->timestamp('paid_at')->nullable()->comment('Data conferma pagamento (webhook)');
            $table->timestamps(); // created_at, updated_at

            // Indexes ottimizzati per query frequenti
            $table->index(['event_id', 'status'], 'event_payments_event_status_index');
            $table->index(['user_id', 'status'], 'event_payments_user_status_index');
            $table->index('transaction_id', 'event_payments_transaction_id_index');
            $table->index('status', 'event_payments_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_payments');
    }
};
