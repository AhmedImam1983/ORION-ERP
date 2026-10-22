<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');           // اسم البنك
            $table->string('account_name');         // اسم صاحب الحساب
            $table->string('account_number')->unique();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('currency')->default('SAR');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('from_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->date('transaction_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};