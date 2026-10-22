<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name')->comment('اسم الصنف');
            $table->string('product_code')->nullable()->comment('كود الصنف');
            $table->string('unit')->default('قطعة')->comment('الوحدة');
            $table->decimal('quantity', 15, 3)->default(1)->comment('الكمية');
            $table->decimal('unit_price', 15, 2)->default(0)->comment('سعر الوحدة');
            $table->decimal('cost_price', 15, 2)->default(0)->comment('سعر التكلفة');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('نسبة الخصم');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('قيمة الخصم');
            $table->decimal('tax_percent', 5, 2)->default(0)->comment('نسبة الضريبة');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('قيمة الضريبة');
            $table->decimal('total', 15, 2)->default(0)->comment('الإجمالي');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};