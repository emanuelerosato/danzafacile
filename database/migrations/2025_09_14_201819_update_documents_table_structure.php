<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Rinomina user_id a uploaded_by
            $table->renameColumn('user_id', 'uploaded_by');

            // Rinomina name a title
            $table->renameColumn('name', 'title');

            // Aggiungi nuove colonne
            $table->text('description')->nullable()->after('title');
            $table->string('original_filename')->after('description');
            $table->string('stored_filename')->after('original_filename');
            $table->string('mime_type')->after('stored_filename');

            // Modifica categorie esistenti
            $table->dropColumn('file_type');

            // Aggiungi colonne per approvazione
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');

            // Aggiungi colonne per visibilità e metadata
            $table->boolean('is_public')->default(false)->after('rejection_reason');
            $table->boolean('requires_approval')->default(true)->after('is_public');
            $table->json('metadata')->nullable()->after('requires_approval');
            $table->timestamp('expires_at')->nullable()->after('metadata');

            // Rimuovi course_id (non più necessario)
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
        });

        // Migra le categorie esistenti prima di cambiare l'ENUM
        DB::statement("UPDATE documents SET category = 'medical' WHERE category = 'medical'");
        DB::statement("UPDATE documents SET category = 'identification' WHERE category = 'photo'");
        DB::statement("UPDATE documents SET category = 'contract' WHERE category = 'agreement'");

        // Aggiorna le categorie
        DB::statement("ALTER TABLE documents MODIFY category ENUM('general', 'medical', 'contract', 'identification', 'other') DEFAULT 'general'");

        // Migra i dati esistenti
        DB::statement("UPDATE documents SET original_filename = title WHERE original_filename IS NULL");
        DB::statement("UPDATE documents SET stored_filename = CONCAT(id, '_', REPLACE(title, ' ', '_')) WHERE stored_filename IS NULL");
        DB::statement("UPDATE documents SET mime_type = CASE
            WHEN file_path LIKE '%.pdf' THEN 'application/pdf'
            WHEN file_path LIKE '%.jpg' OR file_path LIKE '%.jpeg' THEN 'image/jpeg'
            WHEN file_path LIKE '%.png' THEN 'image/png'
            WHEN file_path LIKE '%.gif' THEN 'image/gif'
            WHEN file_path LIKE '%.doc' THEN 'application/msword'
            WHEN file_path LIKE '%.docx' THEN 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ELSE 'application/octet-stream'
        END WHERE mime_type IS NULL");

        // Aggiorna indexes
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['school_id', 'category']);
            $table->index(['school_id', 'status']);
            $table->index(['uploaded_by']);
            $table->index(['approved_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Rimuovi nuove colonne
            $table->dropColumn(['description', 'original_filename', 'stored_filename', 'mime_type']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'rejection_reason', 'is_public', 'requires_approval', 'metadata', 'expires_at']);

            // Rimetti course_id
            $table->foreignId('course_id')->nullable()->after('school_id')->constrained()->onDelete('cascade');

            // Rinomina le colonne originali
            $table->renameColumn('uploaded_by', 'user_id');
            $table->renameColumn('title', 'name');

            // Rimetti file_type
            $table->string('file_type', 10)->after('file_path');
        });

        // Rimetti le categorie originali
        DB::statement("ALTER TABLE documents MODIFY category ENUM('medical', 'photo', 'agreement') DEFAULT 'medical'");
    }
};
