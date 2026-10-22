<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->integer('month')->comment('الشهر 1-12');
            $table->integer('year')->comment('السنة');
            $table->decimal('basic_salary', 15, 2)->default(0)->comment('المرتب الأساسي');
            $table->decimal('allowances', 15, 2)->default(0)->comment('البدلات');
            $table->decimal('overtime_hours', 8, 2)->default(0)->comment('ساعات إضافية');
            $table->decimal('overtime_amount', 15, 2)->default(0)->comment('قيمة الإضافي');
            $table->decimal('bonus', 15, 2)->default(0)->comment('مكافأة');
            $table->decimal('deductions', 15, 2)->default(0)->comment('خصومات');
            $table->decimal('absence_deduction', 15, 2)->default(0)->comment('خصم الغياب');
            $table->decimal('late_deduction', 15, 2)->default(0)->comment('خصم التأخير');
            $table->decimal('insurance_deduction', 15, 2)->default(0)->comment('خصم التأمينات');
            $table->decimal('tax_deduction', 15, 2)->default(0)->comment('خصم الضريبة');
            $table->decimal('net_salary', 15, 2)->default(0)->comment('صافي المرتب');
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('payment_date')->nullable()->comment('تاريخ الصرف');
            $table->string('payment_method')->default('cash')->comment('طريقة الصرف');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};