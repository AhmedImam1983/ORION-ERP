<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->enum('type', [
                'purchase',        // شراء
                'sale',            // بيع
                'sale_return',     // مرتجع بيع
                'purchase_return', // مرتجع شراء
                'transfer_in',     // تحويل وارد
                'transfer_out',    // تحويل صادر
                'adjustment_in',   // تسوية زيادة
                'adjustment_out',  // تسوية نقص
                'opening',         // رصيد افتتاحي
            ])->comment('نوع الحركة');
            $table->decimal('quantity', 15, 2)->default(0)->comment('الكمية');
            $table->decimal('unit_price', 15, 2)->default(0)->comment('سعر الوحدة');
            $table->decimal('total', 15, 2)->default(0)->comment('الإجمالي');
            $table->decimal('balance_before', 15, 2)->default(0)->comment('الرصيد قبل');
            $table->decimal('balance_after', 15, 2)->default(0)->comment('الرصيد بعد');
            $table->string('reference')->nullable()->comment('المرجع');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};