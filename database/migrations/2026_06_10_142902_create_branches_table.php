<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود الفرع');
            $table->string('name')->comment('اسم الفرع');
            $table->string('name_en')->nullable();
            $table->string('address')->nullable()->comment('العنوان');
            $table->string('phone')->nullable()->comment('التليفون');
            $table->string('manager')->nullable()->comment('مدير الفرع');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete()->comment('المخزن الافتراضي');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_main')->default(false)->comment('الفرع الرئيسي');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // نضيف branch_id للمستخدمين
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('id');
        });

        // نضيف branch_id للفواتير
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('warehouse_id');
        });

        // نضيف branch_id لجلسات الكاشير
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::dropIfExists('branches');
    }
};