<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('voucher_number', 40)->unique();
            $table->date('payment_date');

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            // supplier_payment | direct_expense
            $table->string('purpose', 30);

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignId('expense_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            // treasury | bank
            $table->string('source_type', 20);

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
            $table->string('beneficiary_name')->nullable();
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

            $table->index(['payment_date', 'status']);
            $table->index(['purpose', 'supplier_id']);
            $table->index(['invoice_id', 'status']);
            $table->index(['source_type', 'treasury_id']);
            $table->index(['source_type', 'bank_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
