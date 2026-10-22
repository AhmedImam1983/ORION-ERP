<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود الحساب');
            $table->string('name')->comment('اسم الحساب');
            $table->string('name_en')->nullable()->comment('اسم الحساب بالإنجليزية');
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->comment('نوع الحساب');
            $table->enum('level', ['main', 'sub', 'detail'])->default('detail')->comment('مستوى الحساب');
            $table->decimal('opening_balance', 15, 2)->default(0)->comment('الرصيد الافتتاحي');
            $table->enum('balance_type', ['debit', 'credit'])->default('debit')->comment('طبيعة الحساب');
            $table->boolean('is_active')->default(true)->comment('نشط');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};