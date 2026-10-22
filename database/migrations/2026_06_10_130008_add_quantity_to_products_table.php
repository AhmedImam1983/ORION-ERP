<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->default(0)->after('min_quantity')->comment('الرصيد الحالي');
            $table->decimal('opening_quantity', 15, 2)->default(0)->after('quantity')->comment('رصيد أول المدة');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'opening_quantity']);
        });
    }
};