<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود الصنف');
            $table->string('name')->comment('اسم الصنف');
            $table->string('name_en')->nullable()->comment('اسم الصنف بالإنجليزية');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('unit')->default('قطعة')->comment('وحدة القياس');
            $table->decimal('cost_price', 15, 2)->default(0)->comment('سعر التكلفة');
            $table->decimal('sale_price', 15, 2)->default(0)->comment('سعر البيع');
            $table->decimal('sale_price_2', 15, 2)->default(0)->comment('سعر البيع 2');
            $table->decimal('sale_price_3', 15, 2)->default(0)->comment('سعر البيع 3');
            $table->integer('min_quantity')->default(0)->comment('الحد الأدنى للمخزون');
            $table->string('barcode')->nullable()->comment('الباركود');
            $table->string('tax_code')->nullable()->comment('الكود الضريبي ETA');
            $table->string('gpc_brick')->nullable()->comment('GPC Brick');
            $table->text('description')->nullable()->comment('الوصف');
            $table->string('image')->nullable()->comment('الصورة');
            $table->boolean('is_active')->default(true)->comment('نشط');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};