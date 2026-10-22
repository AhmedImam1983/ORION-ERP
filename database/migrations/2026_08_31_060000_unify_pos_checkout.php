<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Persist the operational destinations selected when a POS session opens.
        if (Schema::hasTable('pos_sessions')) {
            $hasWarehouse = Schema::hasColumn('pos_sessions', 'warehouse_id');
            $hasTreasury  = Schema::hasColumn('pos_sessions', 'treasury_id');
            $hasBank      = Schema::hasColumn('pos_sessions', 'bank_account_id');

            Schema::table('pos_sessions', function (Blueprint $table) use ($hasWarehouse, $hasTreasury, $hasBank) {
                if (!$hasWarehouse) {
                    $table->foreignId('warehouse_id')
                        ->nullable()
                        ->after('branch_id')
                        ->constrained('warehouses')
                        ->nullOnDelete();
                }

                if (!$hasTreasury) {
                    $table->foreignId('treasury_id')
                        ->nullable()
                        ->after('warehouse_id')
                        ->constrained('treasuries')
                        ->nullOnDelete();
                }

                if (!$hasBank) {
                    $table->foreignId('bank_account_id')
                        ->nullable()
                        ->after('treasury_id')
                        ->constrained('bank_accounts')
                        ->nullOnDelete();
                }
            });
        }

        // Preserve VAT snapshot in the persisted POS cart across refresh/reconnect.
        if (Schema::hasTable('pos_session_items')) {
            $hasVatType = Schema::hasColumn('pos_session_items', 'vat_type');
            $hasTaxPercent = Schema::hasColumn('pos_session_items', 'tax_percent');
            $hasExemptionCode = Schema::hasColumn('pos_session_items', 'vat_exemption_reason_code');
            $hasExemptionReason = Schema::hasColumn('pos_session_items', 'vat_exemption_reason');

            Schema::table('pos_session_items', function (Blueprint $table) use (
                $hasVatType,
                $hasTaxPercent,
                $hasExemptionCode,
                $hasExemptionReason
            ) {
                if (!$hasVatType) {
                    $table->string('vat_type', 2)->default('S')->after('total');
                }

                if (!$hasTaxPercent) {
                    $table->decimal('tax_percent', 5, 2)->default(15)->after('vat_type');
                }

                if (!$hasExemptionCode) {
                    $table->string('vat_exemption_reason_code', 20)->nullable()->after('tax_percent');
                }

                if (!$hasExemptionReason) {
                    $table->text('vat_exemption_reason')->nullable()->after('vat_exemption_reason_code');
                }
            });
        }

        /*
         * Walk-in POS sales may have no customer master record.
         * ReceiptPostingService only permits this when allow_anonymous=true.
         * Existing normal receipt workflow remains customer-bound.
         */
        if (
            Schema::hasTable('receipt_vouchers')
            && Schema::hasColumn('receipt_vouchers', 'customer_id')
        ) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        /*
         * Do not force receipt_vouchers.customer_id back to NOT NULL automatically:
         * after POS use there may legitimately be anonymous receipt rows.
         */

        if (Schema::hasTable('pos_session_items')) {
            $columns = array_values(array_filter([
                Schema::hasColumn('pos_session_items', 'vat_exemption_reason') ? 'vat_exemption_reason' : null,
                Schema::hasColumn('pos_session_items', 'vat_exemption_reason_code') ? 'vat_exemption_reason_code' : null,
                Schema::hasColumn('pos_session_items', 'tax_percent') ? 'tax_percent' : null,
                Schema::hasColumn('pos_session_items', 'vat_type') ? 'vat_type' : null,
            ]));

            if ($columns) {
                Schema::table('pos_session_items', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        if (Schema::hasTable('pos_sessions')) {
            foreach (['bank_account_id', 'treasury_id', 'warehouse_id'] as $column) {
                if (Schema::hasColumn('pos_sessions', $column)) {
                    Schema::table('pos_sessions', function (Blueprint $table) use ($column) {
                        try {
                            $table->dropForeign([$column]);
                        } catch (\Throwable $e) {
                            // Keep rollback non-destructive if FK naming differs.
                        }

                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
