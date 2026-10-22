<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود الموظف');
            $table->string('name')->comment('اسم الموظف');
            $table->string('name_en')->nullable()->comment('الاسم بالإنجليزية');
            $table->string('national_id')->nullable()->unique()->comment('الرقم القومي');
            $table->string('phone')->nullable()->comment('التليفون');
            $table->string('email')->nullable()->comment('البريد الإلكتروني');
            $table->text('address')->nullable()->comment('العنوان');
            $table->string('department')->nullable()->comment('القسم');
            $table->string('job_title')->nullable()->comment('المسمى الوظيفي');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time')->comment('نوع التوظيف');
            $table->date('hire_date')->nullable()->comment('تاريخ التعيين');
            $table->decimal('basic_salary', 15, 2)->default(0)->comment('الراتب الأساسي');
            $table->decimal('allowances', 15, 2)->default(0)->comment('البدلات');
            $table->integer('working_hours')->default(8)->comment('ساعات العمل اليومية');
            $table->string('fingerprint_id')->nullable()->comment('رقم البصمة');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->comment('الحالة');
            $table->string('image')->nullable()->comment('الصورة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};