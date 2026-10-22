<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_reversals', function (Blueprint $table) {
            $table->id();

            $table->string('reversal_number', 40)->unique();
            $table->date('reversal_date');

            // receipt_voucher | payment_voucher | financial_transfer
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->string('source_number', 60);

            $table->foreignId('original_journal_entry_id')
                ->constrained('journal_entries')
                ->restrictOnDelete();

            $table->foreignId('reversal_journal_entry_id')
                ->nullable()
                ->constrained('journal_entries')
                ->restrictOnDelete();

            $table->text('reason');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['source_type', 'source_id'], 'accounting_reversals_source_unique');
            $table->index(['reversal_date', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_reversals');
    }
};
