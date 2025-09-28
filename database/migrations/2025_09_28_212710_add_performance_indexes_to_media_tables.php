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
            // Indice composito per query filtrate per scuola, pubbliche e featured
            $table->index(['school_id', 'is_public', 'is_featured'], 'media_galleries_school_public_featured_idx');

            // Indice composito per query ordinate per data con filtro scuola
            $table->index(['school_id', 'created_at'], 'media_galleries_school_created_idx');

            // Indice per query con cover image
            $table->index(['cover_image_id', 'is_public'], 'media_galleries_cover_public_idx');
        });

        Schema::table('media_items', function (Blueprint $table) {
            // Indice per file_path per ottimizzare controlli esistenza file
            $table->index('file_path', 'media_items_file_path_idx');

            // Indice composito per query di thumbnails
            $table->index(['type', 'thumbnail_url'], 'media_items_type_thumbnail_idx');

            // Indice per migliorare performance metodo moveToPosition
            $table->index(['gallery_id', 'order', 'id'], 'media_items_gallery_order_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_galleries', function (Blueprint $table) {
            $table->dropIndex('media_galleries_school_public_featured_idx');
            $table->dropIndex('media_galleries_school_created_idx');
            $table->dropIndex('media_galleries_cover_public_idx');
        });

        Schema::table('media_items', function (Blueprint $table) {
            $table->dropIndex('media_items_file_path_idx');
            $table->dropIndex('media_items_type_thumbnail_idx');
            $table->dropIndex('media_items_gallery_order_id_idx');
        });
    }
};