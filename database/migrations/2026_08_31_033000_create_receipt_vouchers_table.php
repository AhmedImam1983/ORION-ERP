<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('voucher_number', 40)->unique();
            $table->date('receipt_date');

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignId('installment_id')
                ->nullable()
                ->constrained('installments')
                ->nullOnDelete();

            $table->string('destination_type', 20); // treasury | bank

            $table->foreignId('treasury_id')
                ->nullable()
                ->constrained('treasuries')
                ->restrictOnDelete();

            $table->foreignId('bank_account_id')
                ->nullable()
                ->constrained('bank_accounts')
                ->restrictOnDelete();

            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();

            $table->string('status', 20)->default('posted');

            $table->foreignId('journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['receipt_date', 'status']);
            $table->index(['customer_id', 'invoice_id']);
            $table->index(['destination_type', 'treasury_id']);
            $table->index(['destination_type', 'bank_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_vouchers');
    }
};
