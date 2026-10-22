<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasuries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // خزينة رئيسية، خزينة فرع السويس
            $table->string('code')->unique(); // MAIN, BRANCH-01, POS-01
            $table->enum('type', ['main', 'branch', 'pos', 'other'])->default('branch');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('treasury_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treasury_id')->constrained('treasuries')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'transfer_in', 'transfer_out']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('reference')->nullable(); // رقم الفاتورة أو التحويل
            $table->string('description')->nullable();
            $table->foreignId('from_treasury_id')->nullable()->constrained('treasuries')->nullOnDelete();
            $table->foreignId('to_treasury_id')->nullable()->constrained('treasuries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('transaction_date');
            $table->timestamps();

            $table->index(['treasury_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_transactions');
        Schema::dropIfExists('treasuries');
    }
};