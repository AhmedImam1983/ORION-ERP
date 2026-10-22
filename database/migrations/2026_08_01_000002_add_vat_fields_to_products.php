<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('vat_type', 1)->default('S')->after('tax_code')
                  ->comment('ZATCA VAT Type: S/Z/E/O');
            $table->decimal('vat_percent', 5, 2)->default(15)->after('vat_type')
                  ->comment('نسبة ضريبة القيمة المضافة');
            $table->string('vat_exemption_reason_code')->nullable()->after('vat_percent')
                  ->comment('كود سبب الإعفاء');
            $table->string('vat_exemption_reason')->nullable()->after('vat_exemption_reason_code')
                  ->comment('سبب الإعفاء من الضريبة');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['vat_type', 'vat_percent', 'vat_exemption_reason_code', 'vat_exemption_reason']);
        });
    }
};
