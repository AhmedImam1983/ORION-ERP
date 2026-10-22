<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->comment('رقم الفاتورة');
            $table->enum('type', ['sale', 'purchase', 'sale_return', 'purchase_return'])->default('sale')->comment('نوع الفاتورة');
            $table->enum('payment_type', ['cash', 'credit', 'installment'])->default('cash')->comment('طريقة الدفع');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('invoice_date')->comment('تاريخ الفاتورة');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('المجموع قبل الخصم');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('قيمة الخصم');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('نسبة الخصم');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('قيمة الضريبة');
            $table->decimal('tax_percent', 5, 2)->default(0)->comment('نسبة الضريبة');
            $table->decimal('total', 15, 2)->default(0)->comment('الإجمالي');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('المبلغ المدفوع');
            $table->decimal('remaining_amount', 15, 2)->default(0)->comment('المبلغ المتبقي');
            $table->enum('status', ['draft', 'confirmed', 'paid', 'partial', 'cancelled'])->default('draft')->comment('الحالة');
            $table->string('warehouse')->nullable()->comment('المخزن');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};