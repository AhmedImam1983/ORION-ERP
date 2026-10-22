<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('vat_type', 1)->default('S')->after('tax_amount')
                  ->comment('ZATCA VAT Type snapshot');
            $table->string('vat_exemption_reason_code')->nullable()->after('vat_type');
            $table->string('vat_exemption_reason')->nullable()->after('vat_exemption_reason_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['vat_type', 'vat_exemption_reason_code', 'vat_exemption_reason']);
        });
    }
};
