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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome template (es: "Email 1 - Benvenuto")
            $table->string('slug')->unique(); // Slug univoco (es: "welcome")
            $table->integer('sequence_order'); // Ordine nel funnel (1, 2, 3, 4, 5)
            $table->integer('delay_days'); // Giorni di delay dall'iscrizione (0, 2, 5, 9, 14)
            $table->string('subject'); // Subject con placeholder {{Nome}}
            $table->text('body'); // Corpo email HTML
            $table->boolean('is_active')->default(true); // Se attiva nel funnel
            $table->text('notes')->nullable(); // Note interne
            $table->timestamps();

            $table->index('sequence_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
