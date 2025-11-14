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
        Schema::create('lead_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_template_id')->constrained()->onDelete('cascade');
            $table->string('subject'); // Subject dell'email inviata
            $table->text('body'); // Corpo email inviato (snapshot)
            $table->enum('status', ['scheduled', 'sent', 'failed', 'opened', 'clicked'])->default('scheduled');
            $table->timestamp('scheduled_at'); // Quando deve essere inviata
            $table->timestamp('sent_at')->nullable(); // Quando è stata inviata
            $table->timestamp('opened_at')->nullable(); // Quando è stata aperta (se tracking)
            $table->timestamp('clicked_at')->nullable(); // Quando ha cliccato (se tracking)
            $table->text('error_message')->nullable(); // Messaggio errore se failed
            $table->timestamps();

            $table->index('lead_id');
            $table->index('email_template_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_email_logs');
    }
};
