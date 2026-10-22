<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود الأصل');
            $table->string('name')->comment('اسم الأصل');
            $table->string('category')->comment('الفئة');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('purchase_date')->comment('تاريخ الشراء');
            $table->decimal('purchase_price', 15, 2)->default(0)->comment('سعر الشراء');
            $table->decimal('salvage_value', 15, 2)->default(0)->comment('قيمة الخردة');
            $table->integer('useful_life_years')->default(5)->comment('العمر الافتراضي بالسنوات');
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])
                ->default('straight_line')->comment('طريقة الاستهلاك');
            $table->decimal('depreciation_rate', 5, 2)->default(20)->comment('نسبة الاستهلاك %');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0)->comment('مجمع الاستهلاك');
            $table->decimal('net_book_value', 15, 2)->default(0)->comment('صافي القيمة الدفترية');
            $table->date('last_depreciation_date')->nullable()->comment('آخر تاريخ استهلاك');
            $table->enum('status', ['active', 'disposed', 'sold'])->default('active');
            $table->string('location')->nullable()->comment('الموقع');
            $table->string('serial_number')->nullable()->comment('الرقم التسلسلي');
            $table->string('supplier')->nullable()->comment('المورد');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->cascadeOnDelete();
            $table->date('depreciation_date')->comment('تاريخ الاستهلاك');
            $table->integer('year')->comment('السنة');
            $table->integer('month')->comment('الشهر');
            $table->decimal('amount', 15, 2)->default(0)->comment('قيمة الاستهلاك');
            $table->decimal('accumulated', 15, 2)->default(0)->comment('المجمع');
            $table->decimal('net_book_value', 15, 2)->default(0)->comment('صافي القيمة');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
    }
};