<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 40)->unique();
            $table->date('transfer_date');

            $table->string('from_type', 20);
            $table->foreignId('from_treasury_id')->nullable()->constrained('treasuries')->restrictOnDelete();
            $table->foreignId('from_bank_account_id')->nullable()->constrained('bank_accounts')->restrictOnDelete();

            $table->string('to_type', 20);
            $table->foreignId('to_treasury_id')->nullable()->constrained('treasuries')->restrictOnDelete();
            $table->foreignId('to_bank_account_id')->nullable()->constrained('bank_accounts')->restrictOnDelete();

            $table->decimal('amount', 18, 2);
            $table->string('external_reference')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('posted');

            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['transfer_date', 'status']);
            $table->index(['from_type', 'to_type']);
            $table->index('journal_entry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transfers');
    }
};
