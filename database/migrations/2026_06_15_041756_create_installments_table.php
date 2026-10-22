<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->integer('installment_number'); // رقم القسط
            $table->decimal('amount', 15, 2); // قيمة القسط
            $table->decimal('paid_amount', 15, 2)->default(0); // المدفوع
            $table->date('due_date'); // تاريخ الاستحقاق
            $table->date('paid_date')->nullable(); // تاريخ الدفع الفعلي
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial'])->default('pending');
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['invoice_id', 'status']);
            $table->index('due_date');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};