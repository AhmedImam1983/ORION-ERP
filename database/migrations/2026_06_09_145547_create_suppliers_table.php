<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود المورد');
            $table->string('name')->comment('اسم المورد');
            $table->string('phone')->nullable()->comment('التليفون');
            $table->string('phone2')->nullable()->comment('تليفون 2');
            $table->string('email')->nullable()->comment('البريد الإلكتروني');
            $table->text('address')->nullable()->comment('العنوان');
            $table->string('tax_number')->nullable()->comment('الرقم الضريبي');
            $table->decimal('opening_balance', 15, 2)->default(0)->comment('رصيد افتتاحي');
            $table->enum('balance_type', ['debit', 'credit'])->default('credit')->comment('نوع الرصيد');
            $table->string('bank_name')->nullable()->comment('اسم البنك');
            $table->string('bank_account')->nullable()->comment('رقم الحساب');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->boolean('is_active')->default(true)->comment('نشط');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};