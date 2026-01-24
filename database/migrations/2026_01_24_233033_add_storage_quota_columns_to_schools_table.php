<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * TASK #11: Aggiunge colonne per gestione quota storage per scuola
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Quota storage in GB (default 5GB per tutte le scuole)
            $table->integer('storage_quota_gb')->default(5)->after('active');

            // Storage utilizzato in bytes (calcolato dalla somma dei file in media_galleries)
            $table->bigInteger('storage_used_bytes')->default(0)->after('storage_quota_gb');

            // Timestamp ultimo aggiornamento cache storage
            $table->timestamp('storage_cache_updated_at')->nullable()->after('storage_used_bytes');

            // Data scadenza quota aggiuntiva (NULL = permanente)
            $table->timestamp('storage_quota_expires_at')->nullable()->after('storage_cache_updated_at');

            // Flag storage illimitato (per piani enterprise)
            $table->boolean('storage_unlimited')->default(false)->after('storage_quota_expires_at');

            // Indici per query performance
            $table->index('storage_unlimited');
            $table->index('storage_quota_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex(['storage_unlimited']);
            $table->dropIndex(['storage_quota_expires_at']);
            $table->dropColumn([
                'storage_quota_gb',
                'storage_used_bytes',
                'storage_cache_updated_at',
                'storage_quota_expires_at',
                'storage_unlimited',
            ]);
        });
    }
};
