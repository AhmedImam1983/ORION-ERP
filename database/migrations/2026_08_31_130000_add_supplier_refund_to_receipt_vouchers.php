<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('receipt_vouchers')) {
            return;
        }

        if (!Schema::hasColumn('receipt_vouchers', 'purpose')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                $table->string('purpose', 40)
                    ->default('customer_receipt')
                    ->after('branch_id')
                    ->index();
            });
        }

        if (!Schema::hasColumn('receipt_vouchers', 'supplier_id')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                // Kept nullable and indexed. Service layer validates the supplier.
                $table->unsignedBigInteger('supplier_id')
                    ->nullable()
                    ->after('customer_id')
                    ->index();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('receipt_vouchers')) {
            return;
        }

        if (Schema::hasColumn('receipt_vouchers', 'supplier_id')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                $table->dropIndex(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        if (Schema::hasColumn('receipt_vouchers', 'purpose')) {
            Schema::table('receipt_vouchers', function (Blueprint $table) {
                $table->dropIndex(['purpose']);
                $table->dropColumn('purpose');
            });
        }
    }
};
