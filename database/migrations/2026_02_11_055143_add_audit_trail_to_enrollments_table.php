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
        Schema::table('course_enrollments', function (Blueprint $table) {
            // Audit trail per modifiche status
            $table->unsignedBigInteger('status_changed_by')->nullable()->after('status');
            $table->timestamp('status_changed_at')->nullable()->after('status_changed_by');

            // Audit trail per modifiche payment_status (future-proof)
            $table->unsignedBigInteger('payment_status_changed_by')->nullable()->after('payment_status');
            $table->timestamp('payment_status_changed_at')->nullable()->after('payment_status_changed_by');

            // Foreign keys
            $table->foreign('status_changed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('payment_status_changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropForeign(['status_changed_by']);
            $table->dropForeign(['payment_status_changed_by']);
            $table->dropColumn([
                'status_changed_by',
                'status_changed_at',
                'payment_status_changed_by',
                'payment_status_changed_at'
            ]);
        });
    }
};
