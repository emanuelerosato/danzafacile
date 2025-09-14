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
        Schema::table('payments', function (Blueprint $table) {
            // Enhanced payment features
            $table->foreignId('event_id')->nullable()->after('course_id')->constrained('events')->cascadeOnDelete();
            $table->enum('payment_type', ['course_enrollment', 'event_registration', 'membership_fee', 'material', 'other'])
                  ->default('course_enrollment')->after('status');
            $table->datetime('due_date')->nullable()->after('payment_date');
            $table->text('notes')->nullable()->after('due_date');

            // Receipt management
            $table->string('receipt_number')->nullable()->unique()->after('notes');
            $table->timestamp('receipt_sent_at')->nullable()->after('receipt_number');

            // Installment support
            $table->boolean('is_installment')->default(false)->after('receipt_sent_at');
            $table->foreignId('parent_payment_id')->nullable()->after('is_installment')->constrained('payments')->cascadeOnDelete();
            $table->integer('installment_number')->nullable()->after('parent_payment_id');
            $table->integer('total_installments')->nullable()->after('installment_number');
            $table->enum('installment_frequency', ['monthly', 'quarterly', 'biannual', 'annual', 'custom'])
                  ->nullable()->after('total_installments');

            // Financial details
            $table->decimal('tax_amount', 10, 2)->default(0)->after('installment_frequency');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('net_amount', 10, 2)->nullable()->after('discount_amount');
            $table->decimal('payment_gateway_fee', 10, 2)->default(0)->after('net_amount');

            // Additional tracking
            $table->string('reference_number')->nullable()->after('payment_gateway_fee');
            $table->string('refund_reason')->nullable()->after('reference_number');
            $table->foreignId('processed_by_user_id')->nullable()->after('refund_reason')->constrained('users')->nullOnDelete();
            $table->json('gateway_response')->nullable()->after('processed_by_user_id');

            // Update existing status enum to include new statuses
            $table->dropColumn('status');
        });

        // Re-add status column with enhanced enum values
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'cancelled', 'processing', 'partial'])
                  ->default('pending')->after('payment_type');
        });

        // Update existing payment_method column to include new methods
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cash', 'credit_card', 'debit_card', 'bank_transfer',
                'paypal', 'stripe', 'online', 'check'
            ])->nullable()->after('net_amount');
        });

        // Add new indexes for performance
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['event_id', 'status']);
            $table->index(['payment_type', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['is_installment', 'parent_payment_id']);
            $table->index(['receipt_number']);
            $table->index(['processed_by_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remove new columns
            $table->dropForeign(['event_id']);
            $table->dropColumn([
                'event_id',
                'payment_type',
                'due_date',
                'notes',
                'receipt_number',
                'receipt_sent_at',
                'is_installment',
                'parent_payment_id',
                'installment_number',
                'total_installments',
                'installment_frequency',
                'tax_amount',
                'discount_amount',
                'net_amount',
                'payment_gateway_fee',
                'reference_number',
                'refund_reason',
                'processed_by_user_id',
                'gateway_response'
            ]);

            // Remove new indexes
            $table->dropIndex(['event_id', 'status']);
            $table->dropIndex(['payment_type', 'status']);
            $table->dropIndex(['due_date', 'status']);
            $table->dropIndex(['is_installment', 'parent_payment_id']);
            $table->dropIndex(['receipt_number']);
            $table->dropIndex(['processed_by_user_id']);
        });

        // Restore original status enum
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])
                  ->default('pending')->after('transaction_id');
        });

        // Restore original payment_method enum
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('currency');
        });
    }
};