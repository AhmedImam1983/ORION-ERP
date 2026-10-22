<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('رقم أمر الشراء');
            $table->foreignId('supplier_id')->constrained('suppliers')->comment('المورد');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('order_date')->comment('تاريخ الطلب');
            $table->date('expected_date')->nullable()->comment('تاريخ الاستلام المتوقع');
            $table->enum('status', ['draft','sent','confirmed','partial','received','cancelled'])
                ->default('draft')->comment('الحالة');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('received_amount', 15, 2)->default(0)->comment('المستلم');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name')->comment('اسم الصنف');
            $table->string('product_code')->nullable();
            $table->string('unit')->default('قطعة');
            $table->decimal('quantity', 15, 2)->default(0)->comment('الكمية المطلوبة');
            $table->decimal('received_quantity', 15, 2)->default(0)->comment('الكمية المستلمة');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};