<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * TASK #11 FASE 7: Audit log per modifiche storage quota da Super Admin
     */
    public function up(): void
    {
        Schema::create('storage_quota_audit_log', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('super_admin_id')->constrained('users')->onDelete('cascade');

            // Azione eseguita
            $table->enum('action', ['add_quota', 'set_unlimited', 'reset_to_base'])->index();

            // Valori prima della modifica
            $table->integer('old_quota_gb');
            $table->boolean('old_unlimited')->default(false);
            $table->timestamp('old_expires_at')->nullable();

            // Valori dopo la modifica
            $table->integer('new_quota_gb');
            $table->boolean('new_unlimited')->default(false);
            $table->timestamp('new_expires_at')->nullable();

            // Note admin
            $table->text('admin_note')->nullable();

            $table->timestamps();

            // Indici per query performance
            $table->index(['school_id', 'created_at']);
            $table->index('super_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_quota_audit_log');
    }
};
