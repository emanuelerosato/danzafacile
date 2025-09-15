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
        Schema::table('media_items', function (Blueprint $table) {
            $table->enum('type', ['file', 'external_link', 'youtube', 'vimeo', 'instagram'])->default('file')->after('user_id');
            $table->string('external_url')->nullable()->after('file_size');
            $table->string('external_id')->nullable()->after('external_url');
            $table->string('thumbnail_url')->nullable()->after('external_id');
            $table->boolean('is_featured')->default(false)->after('order');
            $table->json('metadata')->nullable()->after('is_featured');

            // Modifica file_path e file_type per essere nullable (per i link esterni)
            $table->string('file_path')->nullable()->change();
            $table->string('file_type')->nullable()->change();

            // Aggiorna indici
            $table->index(['gallery_id', 'type']);
            $table->index(['gallery_id', 'is_featured']);
            $table->index(['type']);
            $table->index(['external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'external_url', 'external_id', 'thumbnail_url', 'is_featured', 'metadata']);

            // Rimetti file_path e file_type come non nullable
            $table->string('file_path')->nullable(false)->change();
            $table->string('file_type')->nullable(false)->change();
        });
    }
};
