<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('مفتاح الإعداد');
            $table->text('value')->nullable()->comment('قيمة الإعداد');
            $table->string('group')->default('general')->comment('المجموعة');
            $table->string('type')->default('text')->comment('نوع القيمة: text/boolean/integer/json/file');
            $table->string('label')->nullable()->comment('العنوان بالعربية');
            $table->timestamps();
        });

        // Seed default values
        $defaults = [
            // بيانات المؤسسة
            ['key' => 'company_name',          'value' => 'الشركة المتحدة',  'group' => 'company',  'type' => 'text',    'label' => 'اسم الشركة'],
            ['key' => 'company_name_en',        'value' => 'United Company',  'group' => 'company',  'type' => 'text',    'label' => 'اسم الشركة بالإنجليزية'],
            ['key' => 'company_tax_number',     'value' => '',                'group' => 'company',  'type' => 'text',    'label' => 'الرقم الضريبي'],
            ['key' => 'company_cr',             'value' => '',                'group' => 'company',  'type' => 'text',    'label' => 'السجل التجاري'],
            ['key' => 'company_phone',          'value' => '',                'group' => 'company',  'type' => 'text',    'label' => 'الهاتف'],
            ['key' => 'company_email',          'value' => '',                'group' => 'company',  'type' => 'text',    'label' => 'البريد الإلكتروني'],
            ['key' => 'company_website',        'value' => '',                'group' => 'company',  'type' => 'text',    'label' => 'الموقع الإلكتروني'],
            ['key' => 'company_logo',           'value' => '',                'group' => 'company',  'type' => 'file',    'label' => 'شعار الشركة'],
            // العنوان
            ['key' => 'company_country',        'value' => 'SA',              'group' => 'address',  'type' => 'text',    'label' => 'رمز الدولة'],
            ['key' => 'company_city',           'value' => 'الرياض',          'group' => 'address',  'type' => 'text',    'label' => 'المدينة'],
            ['key' => 'company_district',       'value' => '',                'group' => 'address',  'type' => 'text',    'label' => 'الحي'],
            ['key' => 'company_street',         'value' => '',                'group' => 'address',  'type' => 'text',    'label' => 'الشارع'],
            ['key' => 'company_building',       'value' => '',                'group' => 'address',  'type' => 'text',    'label' => 'رقم المبنى'],
            ['key' => 'company_postal',         'value' => '',                'group' => 'address',  'type' => 'text',    'label' => 'الرمز البريدي'],
            // ZATCA
            ['key' => 'zatca_sending_mode',     'value' => 'simulation',      'group' => 'zatca',    'type' => 'text',    'label' => 'بيئة الإرسال'],
            ['key' => 'zatca_cert_path',        'value' => '',                'group' => 'zatca',    'type' => 'text',    'label' => 'مسار الشهادة'],
            ['key' => 'zatca_key_path',         'value' => '',                'group' => 'zatca',    'type' => 'text',    'label' => 'مسار المفتاح الخاص'],
            ['key' => 'zatca_secret',           'value' => '',                'group' => 'zatca',    'type' => 'text',    'label' => 'الـ Secret'],
            // العملة
            ['key' => 'currency_code',          'value' => 'SAR',             'group' => 'general',  'type' => 'text',    'label' => 'رمز العملة'],
            ['key' => 'currency_symbol',        'value' => 'ر.س',             'group' => 'general',  'type' => 'text',    'label' => 'رمز العملة المعروض'],
        ];

        foreach ($defaults as $row) {
            \DB::table('settings')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
