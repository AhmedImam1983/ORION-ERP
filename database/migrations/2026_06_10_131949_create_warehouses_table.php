<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود المخزن');
            $table->string('name')->comment('اسم المخزن');
            $table->string('name_en')->nullable()->comment('اسم المخزن بالإنجليزية');
            $table->string('location')->nullable()->comment('الموقع');
            $table->string('phone')->nullable()->comment('التليفون');
            $table->string('manager')->nullable()->comment('مدير المخزن');
            $table->boolean('is_active')->default(true)->comment('نشط');
            $table->boolean('is_default')->default(false)->comment('المخزن الافتراضي');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });

        // نضيف warehouse_id للأصناف
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete()->after('category_id');
        });

        // نضيف warehouse_id للفواتير
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete()->after('warehouse');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::dropIfExists('warehouses');
    }
};