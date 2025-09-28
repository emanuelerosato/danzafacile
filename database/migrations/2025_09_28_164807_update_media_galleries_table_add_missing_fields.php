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
        Schema::table('media_galleries', function (Blueprint $table) {
            // Aggiungi la colonna created_by
            $table->unsignedBigInteger('created_by')->after('course_id');

            // Aggiungi le colonne mancanti
            $table->boolean('is_public')->default(false)->after('type');
            $table->boolean('is_featured')->default(false)->after('is_public');
            $table->unsignedBigInteger('cover_image_id')->nullable()->after('is_featured');
            $table->json('settings')->nullable()->after('cover_image_id');

            // Aggiorna l'enum type per includere tutti i tipi
            $table->dropColumn('type');
        });

        Schema::table('media_galleries', function (Blueprint $table) {
            $table->enum('type', ['foto', 'video', 'misto', 'spettacoli', 'lezioni', 'eventi'])->after('description');
        });

        Schema::table('media_galleries', function (Blueprint $table) {
            // Aggiungi foreign key per created_by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Aggiungi foreign key per cover_image_id
            $table->foreign('cover_image_id')->references('id')->on('media_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_galleries', function (Blueprint $table) {
            // Rimuovi foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['cover_image_id']);

            // Rimuovi colonne aggiunte
            $table->dropColumn(['created_by', 'is_public', 'is_featured', 'cover_image_id', 'settings']);

            // Ripristina l'enum type originale
            $table->dropColumn('type');
        });

        Schema::table('media_galleries', function (Blueprint $table) {
            $table->enum('type', ['photo', 'video'])->after('description');
        });
    }
};