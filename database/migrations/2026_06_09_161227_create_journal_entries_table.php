<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique()->comment('رقم القيد');
            $table->date('entry_date')->comment('تاريخ القيد');
            $table->string('reference')->nullable()->comment('المرجع');
            $table->text('description')->comment('البيان');
            $table->decimal('total_debit', 15, 2)->default(0)->comment('إجمالي المدين');
            $table->decimal('total_credit', 15, 2)->default(0)->comment('إجمالي الدائن');
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft')->comment('الحالة');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};