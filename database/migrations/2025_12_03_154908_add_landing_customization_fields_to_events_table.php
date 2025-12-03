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
        Schema::table('events', function (Blueprint $table) {
            // Campo per descrizione breve (hero subtitle)
            $table->text('short_description')->nullable()->after('description');

            // Campo JSON per customizzazioni landing avanzate
            $table->json('additional_info')->nullable()->after('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'additional_info']);
        });
    }
};
