<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('   📒 إضافة شجرة الحسابات...');

        $accounts = [
            // ===== الأصول =====
            ['code'=>'1000','name'=>'الأصول','type'=>'asset','parent'=>null,'is_group'=>true],

            // الأصول المتداولة
            ['code'=>'1100','name'=>'الأصول المتداولة','type'=>'asset','parent'=>'1000','is_group'=>true],
            ['code'=>'1101','name'=>'النقدية بالصندوق','type'=>'asset','parent'=>'1100'],
            ['code'=>'1102','name'=>'نقدية بالخزينة الرئيسية','type'=>'asset','parent'=>'1100'],
            ['code'=>'1103','name'=>'نقدية بخزينة الفرع','type'=>'asset','parent'=>'1100'],
            ['code'=>'1110','name'=>'حسابات بنكية','type'=>'asset','parent'=>'1100','is_group'=>true],
            ['code'=>'1111','name'=>'البنك الأهلي المصري','type'=>'asset','parent'=>'1110'],
            ['code'=>'1112','name'=>'بنك مصر','type'=>'asset','parent'=>'1110'],
            ['code'=>'1113','name'=>'بنك القاهرة','type'=>'asset','parent'=>'1110'],
            ['code'=>'1200','name'=>'الذمم المدينة','type'=>'asset','parent'=>'1100','is_group'=>true],
            ['code'=>'1201','name'=>'ذمم العملاء','type'=>'asset','parent'=>'1200'],
            ['code'=>'1202','name'=>'أوراق القبض','type'=>'asset','parent'=>'1200'],
            ['code'=>'1203','name'=>'مدينون متنوعون','type'=>'asset','parent'=>'1200'],
            ['code'=>'1300','name'=>'المخزون','type'=>'asset','parent'=>'1100','is_group'=>true],
            ['code'=>'1301','name'=>'مخزون البضاعة','type'=>'asset','parent'=>'1300'],
            ['code'=>'1302','name'=>'مواد خام','type'=>'asset','parent'=>'1300'],
            ['code'=>'1303','name'=>'فروقات جرد المخزون','type'=>'asset','parent'=>'1300'],
            ['code'=>'1400','name'=>'مصروفات مدفوعة مقدماً','type'=>'asset','parent'=>'1100'],
            ['code'=>'1401','name'=>'إيجارات مقدمة','type'=>'asset','parent'=>'1400'],
            ['code'=>'1402','name'=>'تأمينات مدفوعة','type'=>'asset','parent'=>'1400'],

            // الأصول الثابتة
            ['code'=>'1500','name'=>'الأصول الثابتة','type'=>'asset','parent'=>'1000','is_group'=>true],
            ['code'=>'1501','name'=>'أثاث ومفروشات','type'=>'asset','parent'=>'1500'],
            ['code'=>'1502','name'=>'أجهزة حاسوب','type'=>'asset','parent'=>'1500'],
            ['code'=>'1503','name'=>'سيارات ومعدات نقل','type'=>'asset','parent'=>'1500'],
            ['code'=>'1504','name'=>'مباني وعقارات','type'=>'asset','parent'=>'1500'],
            ['code'=>'1505','name'=>'معدات وآلات','type'=>'asset','parent'=>'1500'],
            ['code'=>'1510','name'=>'مجمع إهلاك الأصول الثابتة','type'=>'asset','parent'=>'1500'],
            ['code'=>'1511','name'=>'مجمع إهلاك الأثاث','type'=>'asset','parent'=>'1510'],
            ['code'=>'1512','name'=>'مجمع إهلاك الحاسوب','type'=>'asset','parent'=>'1510'],
            ['code'=>'1513','name'=>'مجمع إهلاك السيارات','type'=>'asset','parent'=>'1510'],

            // ===== الخصوم =====
            ['code'=>'2000','name'=>'الخصوم','type'=>'liability','parent'=>null,'is_group'=>true],

            // الخصوم المتداولة
            ['code'=>'2100','name'=>'الخصوم المتداولة','type'=>'liability','parent'=>'2000','is_group'=>true],
            ['code'=>'2101','name'=>'ذمم الموردين','type'=>'liability','parent'=>'2100'],
            ['code'=>'2102','name'=>'أوراق الدفع','type'=>'liability','parent'=>'2100'],
            ['code'=>'2103','name'=>'دائنون متنوعون','type'=>'liability','parent'=>'2100'],
            ['code'=>'2104','name'=>'ضريبة القيمة المضافة','type'=>'liability','parent'=>'2100'],
            ['code'=>'2105','name'=>'ضريبة الدخل المستحقة','type'=>'liability','parent'=>'2100'],
            ['code'=>'2106','name'=>'رواتب مستحقة الدفع','type'=>'liability','parent'=>'2100'],
            ['code'=>'2107','name'=>'إيرادات مقدمة','type'=>'liability','parent'=>'2100'],

            // الخصوم طويلة الأجل
            ['code'=>'2200','name'=>'الخصوم طويلة الأجل','type'=>'liability','parent'=>'2000','is_group'=>true],
            ['code'=>'2201','name'=>'قروض بنكية طويلة الأجل','type'=>'liability','parent'=>'2200'],
            ['code'=>'2202','name'=>'قروض مساهمين','type'=>'liability','parent'=>'2200'],

            // ===== حقوق الملكية =====
            ['code'=>'3000','name'=>'حقوق الملكية','type'=>'equity','parent'=>null,'is_group'=>true],
            ['code'=>'3001','name'=>'رأس المال','type'=>'equity','parent'=>'3000'],
            ['code'=>'3002','name'=>'الاحتياطيات','type'=>'equity','parent'=>'3000'],
            ['code'=>'3003','name'=>'الأرباح المحتجزة','type'=>'equity','parent'=>'3000'],
            ['code'=>'3004','name'=>'أرباح وخسائر العام الحالي','type'=>'equity','parent'=>'3000'],

            // ===== الإيرادات =====
            ['code'=>'4000','name'=>'الإيرادات','type'=>'revenue','parent'=>null,'is_group'=>true],
            ['code'=>'4100','name'=>'إيرادات المبيعات','type'=>'revenue','parent'=>'4000','is_group'=>true],
            ['code'=>'4101','name'=>'مبيعات بضاعة','type'=>'revenue','parent'=>'4100'],
            ['code'=>'4102','name'=>'مبيعات خدمات','type'=>'revenue','parent'=>'4100'],
            ['code'=>'4103','name'=>'مردودات المبيعات','type'=>'revenue','parent'=>'4100'],
            ['code'=>'4104','name'=>'خصومات المبيعات','type'=>'revenue','parent'=>'4100'],
            ['code'=>'4200','name'=>'إيرادات أخرى','type'=>'revenue','parent'=>'4000','is_group'=>true],
            ['code'=>'4201','name'=>'إيرادات فوائد بنكية','type'=>'revenue','parent'=>'4200'],
            ['code'=>'4202','name'=>'إيرادات إيجارات','type'=>'revenue','parent'=>'4200'],
            ['code'=>'4203','name'=>'إيرادات متنوعة','type'=>'revenue','parent'=>'4200'],

            // ===== المصروفات =====
            ['code'=>'5000','name'=>'المصروفات','type'=>'expense','parent'=>null,'is_group'=>true],

            // تكلفة البضاعة المباعة
            ['code'=>'5100','name'=>'تكلفة البضاعة المباعة','type'=>'expense','parent'=>'5000','is_group'=>true],
            ['code'=>'5101','name'=>'تكلفة المبيعات','type'=>'expense','parent'=>'5100'],
            ['code'=>'5102','name'=>'مشتريات بضاعة','type'=>'expense','parent'=>'5100'],
            ['code'=>'5103','name'=>'مردودات المشتريات','type'=>'expense','parent'=>'5100'],

            // المصروفات التشغيلية
            ['code'=>'5200','name'=>'المصروفات التشغيلية','type'=>'expense','parent'=>'5000','is_group'=>true],
            ['code'=>'5201','name'=>'رواتب وأجور','type'=>'expense','parent'=>'5200'],
            ['code'=>'5202','name'=>'إيجارات','type'=>'expense','parent'=>'5200'],
            ['code'=>'5203','name'=>'كهرباء وماء','type'=>'expense','parent'=>'5200'],
            ['code'=>'5204','name'=>'مواصلات ونقل','type'=>'expense','parent'=>'5200'],
            ['code'=>'5205','name'=>'مصروفات دعاية وإعلان','type'=>'expense','parent'=>'5200'],
            ['code'=>'5206','name'=>'مصروفات صيانة','type'=>'expense','parent'=>'5200'],
            ['code'=>'5207','name'=>'مصروفات قرطاسية ومطبوعات','type'=>'expense','parent'=>'5200'],
            ['code'=>'5208','name'=>'مصروفات هاتف وإنترنت','type'=>'expense','parent'=>'5200'],
            ['code'=>'5209','name'=>'مصروفات تأمين','type'=>'expense','parent'=>'5200'],
            ['code'=>'5210','name'=>'مصروفات متنوعة','type'=>'expense','parent'=>'5200'],

            // المصروفات المالية
            ['code'=>'5300','name'=>'المصروفات المالية','type'=>'expense','parent'=>'5000','is_group'=>true],
            ['code'=>'5301','name'=>'فوائد بنكية مدينة','type'=>'expense','parent'=>'5300'],
            ['code'=>'5302','name'=>'عمولات بنكية','type'=>'expense','parent'=>'5300'],
            ['code'=>'5303','name'=>'خسائر فروق عملة','type'=>'expense','parent'=>'5300'],

            // الإهلاك
            ['code'=>'5400','name'=>'مصروفات الإهلاك','type'=>'expense','parent'=>'5000','is_group'=>true],
            ['code'=>'5401','name'=>'إهلاك الأثاث والمفروشات','type'=>'expense','parent'=>'5400'],
            ['code'=>'5402','name'=>'إهلاك الأجهزة والحاسوب','type'=>'expense','parent'=>'5400'],
            ['code'=>'5403','name'=>'إهلاك السيارات','type'=>'expense','parent'=>'5400'],
        ];

        // نبني map للكودات → IDs
        $codeToId = [];

        foreach ($accounts as $acc) {
            $parentId = null;
            if ($acc['parent'] && isset($codeToId[$acc['parent']])) {
                $parentId = $codeToId[$acc['parent']];
            }

            $existing = Account::where('code', $acc['code'])->first();
            if (!$existing) {
                $existing = Account::create([
                    'code'       => $acc['code'],
                    'name'       => $acc['name'],
                    'type'       => $acc['type'],
                    'parent_id'  => $parentId,
                    'is_active'  => true,
                ]);
            }

            $codeToId[$acc['code']] = $existing->id;
        }

        $this->command->info('   ✓ شجرة الحسابات: ' . count($accounts) . ' حساب');
    }
}
