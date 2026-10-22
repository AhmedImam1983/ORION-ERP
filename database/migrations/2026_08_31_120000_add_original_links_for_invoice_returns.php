<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'original_invoice_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('original_invoice_id')
                    ->nullable()
                    ->after('warehouse_id')
                    ->constrained('invoices')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('invoice_items', 'original_invoice_item_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreignId('original_invoice_item_id')
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('invoice_items')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoice_items', 'original_invoice_item_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('original_invoice_item_id');
            });
        }

        if (Schema::hasColumn('invoices', 'original_invoice_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('original_invoice_id');
            });
        }
    }
};
