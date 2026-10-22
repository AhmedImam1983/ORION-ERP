<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->change();
            $table->string('product_code')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('product_name')->nullable(false)->change();
            $table->string('product_code')->nullable(false)->change();
        });
    }
};