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
        // La tabella media_galleries ha già le colonne necessarie
        // Aggiungiamo solo gli indici mancanti (Laravel gestirà i duplicati)
        try {
            Schema::table('media_galleries', function (Blueprint $table) {
                $table->index(['school_id', 'is_public']);
            });
        } catch (\Exception $e) {
            // Indice già esistente, continua
        }

        try {
            Schema::table('media_galleries', function (Blueprint $table) {
                $table->index(['school_id', 'is_featured']);
            });
        } catch (\Exception $e) {
            // Indice già esistente, continua
        }

        try {
            Schema::table('media_galleries', function (Blueprint $table) {
                $table->index(['created_by']);
            });
        } catch (\Exception $e) {
            // Indice già esistente, continua
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_galleries', function (Blueprint $table) {
            // Rimuovi solo gli indici aggiunti da questa migrazione
            $table->dropIndex(['school_id', 'is_public']);
            $table->dropIndex(['school_id', 'is_featured']);
            $table->dropIndex(['created_by']);
        });
    }
};
