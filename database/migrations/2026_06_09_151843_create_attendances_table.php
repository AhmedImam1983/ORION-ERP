<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date')->comment('التاريخ');
            $table->time('check_in')->nullable()->comment('وقت الحضور');
            $table->time('check_out')->nullable()->comment('وقت الانصراف');
            $table->decimal('working_hours', 5, 2)->default(0)->comment('ساعات العمل');
            $table->decimal('overtime_hours', 5, 2)->default(0)->comment('ساعات إضافية');
            $table->decimal('late_minutes', 8, 2)->default(0)->comment('دقائق التأخير');
            $table->enum('status', ['present', 'absent', 'late', 'early_leave', 'vacation', 'mission'])->default('present')->comment('الحالة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};