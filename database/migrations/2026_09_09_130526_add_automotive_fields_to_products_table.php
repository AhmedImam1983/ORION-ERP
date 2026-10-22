<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')
                ->nullable()
                ->after('category_id')
                ->constrained('brands')
                ->nullOnDelete();

            $table->string('part_number', 100)
                ->nullable()
                ->after('brand_id');

            $table->string('part_number_normalized', 100)
                ->nullable()
                ->after('part_number');

            $table->index('part_number');
            $table->index('part_number_normalized');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropIndex(['part_number']);
            $table->dropIndex(['part_number_normalized']);

            $table->dropColumn([
                'brand_id',
                'part_number',
                'part_number_normalized',
            ]);
        });
    }
};