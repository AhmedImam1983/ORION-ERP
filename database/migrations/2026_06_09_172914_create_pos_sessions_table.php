<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number')->unique()->comment('رقم الجلسة');
            $table->foreignId('user_id')->constrained('users')->comment('الكاشير');
            $table->string('register_name')->default('كاشير 1')->comment('اسم الكاشير');
            $table->decimal('opening_cash', 15, 2)->default(0)->comment('رصيد الافتتاح');
            $table->decimal('closing_cash', 15, 2)->nullable()->comment('رصيد الإغلاق');
            $table->decimal('total_sales', 15, 2)->default(0)->comment('إجمالي المبيعات');
            $table->decimal('total_cash', 15, 2)->default(0)->comment('إجمالي النقدي');
            $table->decimal('total_card', 15, 2)->default(0)->comment('إجمالي الفيزا');
            $table->decimal('total_wallet', 15, 2)->default(0)->comment('إجمالي المحفظة');
            $table->decimal('total_refunds', 15, 2)->default(0)->comment('إجمالي المرتجعات');
            $table->integer('transactions_count')->default(0)->comment('عدد العمليات');
            $table->enum('status', ['open', 'closed'])->default('open')->comment('الحالة');
            $table->timestamp('opened_at')->nullable()->comment('وقت الفتح');
            $table->timestamp('closed_at')->nullable()->comment('وقت الإغلاق');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};