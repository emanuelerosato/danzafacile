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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('quarterly_price', 10, 2)->nullable()->after('monthly_price');
            $table->decimal('yearly_price', 10, 2)->nullable()->after('quarterly_price');
            $table->decimal('package_10_price', 10, 2)->nullable()->after('single_lesson_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['quarterly_price', 'yearly_price', 'package_10_price']);
        });
    }
};
