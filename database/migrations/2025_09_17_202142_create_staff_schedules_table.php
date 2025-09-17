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
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->string('title'); // Nome del turno es. "Lezione Danza Classica"
            $table->enum('type', ['course', 'event', 'administrative', 'maintenance', 'other'])->default('course');
            $table->date('date'); // Data del turno
            $table->time('start_time'); // Ora inizio
            $table->time('end_time'); // Ora fine
            $table->string('location')->nullable(); // Sala/luogo
            $table->text('description')->nullable(); // Descrizione dettagliata
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->decimal('hourly_rate', 8, 2)->nullable(); // Tariffa oraria per questo turno
            $table->integer('max_hours')->nullable(); // Ore massime per questo turno
            $table->json('requirements')->nullable(); // Requisiti specifici
            $table->text('notes')->nullable(); // Note aggiuntive
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['school_id', 'date']);
            $table->index(['staff_id', 'date']);
            $table->index(['school_id', 'type', 'status']);
            $table->index(['date', 'start_time', 'end_time']);

            // Unique constraint per evitare sovrapposizioni dello stesso staff
            $table->unique(['staff_id', 'date', 'start_time'], 'staff_no_overlap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};
