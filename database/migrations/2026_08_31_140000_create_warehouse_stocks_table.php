<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->decimal('opening_quantity', 18, 3)->default(0);
            $table->decimal('quantity', 18, 3)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id'], 'warehouse_stocks_product_warehouse_unique');
            $table->index(['warehouse_id', 'quantity'], 'warehouse_stocks_warehouse_quantity_idx');
        });

        // Safe cutover: preserve every existing product exactly in its currently assigned warehouse.
        // Products without warehouse_id are intentionally NOT guessed/backfilled.
        DB::table('products')
            ->whereNotNull('warehouse_id')
            ->orderBy('id')
            ->chunkById(200, function ($products) {
                $now = now();

                $rows = $products->map(function ($product) use ($now) {
                    $quantity = round((float) ($product->quantity ?? 0), 3);

                    return [
                        'product_id'       => $product->id,
                        'warehouse_id'     => $product->warehouse_id,
                        'opening_quantity' => $quantity,
                        'quantity'         => $quantity,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                })->all();

                if (!empty($rows)) {
                    DB::table('warehouse_stocks')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
