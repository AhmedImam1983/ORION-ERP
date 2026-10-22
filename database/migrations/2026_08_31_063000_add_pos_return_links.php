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
                    ->constrained('invoices')
                    ->restrictOnDelete();
            });
        }

        if (!Schema::hasColumn('invoice_items', 'original_invoice_item_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->foreignId('original_invoice_item_id')
                    ->nullable()
                    ->constrained('invoice_items')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        /*
         * Intentionally non-destructive.
         * These links become accounting/audit evidence once POS returns exist.
         * Do not silently remove them on rollback.
         */
    }
};
