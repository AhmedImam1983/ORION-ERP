<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stock_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_stock_id')
                ->constrained('warehouse_stocks')
                ->cascadeOnDelete();

            $table->string('location_code', 100);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['warehouse_stock_id', 'location_code'], 'wsl_stock_loc_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_locations');
    }
};