<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_session_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_session_items', 'vat_type')) {
                $table->string('vat_type', 1)->default('S')->after('total');
            }
            if (!Schema::hasColumn('pos_session_items', 'tax_percent')) {
                $table->decimal('tax_percent', 5, 2)->default(15)->after('vat_type');
            }
            if (!Schema::hasColumn('pos_session_items', 'vat_exemption_reason_code')) {
                $table->string('vat_exemption_reason_code')->nullable()->after('tax_percent');
            }
            if (!Schema::hasColumn('pos_session_items', 'vat_exemption_reason')) {
                $table->string('vat_exemption_reason')->nullable()->after('vat_exemption_reason_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_session_items', function (Blueprint $table) {
            $table->dropColumn(['vat_type', 'tax_percent', 'vat_exemption_reason_code', 'vat_exemption_reason']);
        });
    }
};
