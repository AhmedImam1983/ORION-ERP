<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('commercial_registration')->nullable()->after('tax_number')
                  ->comment('السجل التجاري');
            $table->string('zatca_scheme_id')->default('TIN')->after('commercial_registration')
                  ->comment('TIN أو CRN أو NAP');
            $table->string('street_name')->nullable()->after('zatca_scheme_id');
            $table->string('building_number')->nullable()->after('street_name');
            $table->string('city_name')->nullable()->after('building_number');
            $table->string('district')->nullable()->after('city_name');
            $table->string('postal_code')->nullable()->after('district');
            $table->string('country_code')->default('SA')->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['commercial_registration', 'zatca_scheme_id', 'street_name',
                'building_number', 'city_name', 'district', 'postal_code', 'country_code']);
        });
    }
};
