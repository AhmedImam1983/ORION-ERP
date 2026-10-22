<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_session_id')->constrained('pos_sessions')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('transaction_number')->unique()->comment('رقم العملية');
            $table->enum('payment_method', ['cash', 'card', 'wallet', 'mixed'])->default('cash')->comment('طريقة الدفع');
            $table->decimal('amount', 15, 2)->default(0)->comment('المبلغ');
            $table->decimal('cash_amount', 15, 2)->default(0)->comment('المبلغ النقدي');
            $table->decimal('card_amount', 15, 2)->default(0)->comment('مبلغ الفيزا');
            $table->decimal('wallet_amount', 15, 2)->default(0)->comment('مبلغ المحفظة');
            $table->decimal('change_amount', 15, 2)->default(0)->comment('الباقي');
            $table->enum('type', ['sale', 'refund'])->default('sale')->comment('النوع');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};