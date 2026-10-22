<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_vehicles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('make', 100);
            $table->string('model', 100)->nullable();

            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();

            $table->string('engine', 100)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['make', 'model']);
            $table->index(['product_id', 'make', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_vehicles');
    }
};