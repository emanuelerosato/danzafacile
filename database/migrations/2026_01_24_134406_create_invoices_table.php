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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Student

            // Invoice details
            $table->string('invoice_number')->unique(); // Auto-generated: YYYY-NNN
            $table->decimal('amount', 10, 2);
            $table->date('invoice_date');
            $table->text('description')->nullable();

            // Billing information (snapshot at invoice creation time)
            $table->string('billing_name');
            $table->string('billing_fiscal_code', 16)->nullable();
            $table->string('billing_email');
            $table->text('billing_address')->nullable();

            // PDF storage
            $table->string('pdf_path')->nullable();

            // Status and metadata
            $table->enum('status', ['draft', 'issued', 'sent', 'paid', 'cancelled'])->default('issued');
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['school_id', 'invoice_date']);
            $table->index(['school_id', 'user_id']);
            $table->index('invoice_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
