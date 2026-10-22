<?php

namespace Database\Seeders;

use App\Models\FixedAsset;
use Illuminate\Database\Seeder;

class FixedAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            [
                'code'             => 'FA-001',
                'name'             => 'سيارة تويوتا كورولا 2022',
                'category'         => 'سيارات',
                'purchase_date'    => '2022-01-15',
                'purchase_price'   => 280000,
                'salvage_value'    => 50000,
                'useful_life_years'=> 5,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 20,
                'location'         => 'موقف الشركة',
                'serial_number'    => 'TYT-2022-001',
                'supplier'         => 'وكالة تويوتا',
            ],
            [
                'code'             => 'FA-002',
                'name'             => 'لاب توب Dell Latitude 14',
                'category'         => 'أجهزة كمبيوتر',
                'purchase_date'    => '2023-03-10',
                'purchase_price'   => 25000,
                'salvage_value'    => 2000,
                'useful_life_years'=> 3,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 33.33,
                'location'         => 'مكتب المدير',
                'serial_number'    => 'DELL-2023-001',
                'supplier'         => 'شركة Dell مصر',
            ],
            [
                'code'             => 'FA-003',
                'name'             => 'طابعة HP LaserJet',
                'category'         => 'أجهزة كمبيوتر',
                'purchase_date'    => '2023-06-20',
                'purchase_price'   => 8500,
                'salvage_value'    => 500,
                'useful_life_years'=> 4,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 25,
                'location'         => 'مكتب السكرتارية',
                'serial_number'    => 'HP-2023-002',
                'supplier'         => 'HP Egypt',
            ],
            [
                'code'             => 'FA-004',
                'name'             => 'مكيف سبليت 2 حصان LG',
                'category'         => 'أجهزة كهربائية',
                'purchase_date'    => '2022-05-01',
                'purchase_price'   => 18000,
                'salvage_value'    => 2000,
                'useful_life_years'=> 7,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 14.29,
                'location'         => 'مكتب الإدارة',
                'serial_number'    => 'LG-2022-001',
                'supplier'         => 'LG Egypt',
            ],
            [
                'code'             => 'FA-005',
                'name'             => 'مكتب خشب كبير',
                'category'         => 'أثاث ومفروشات',
                'purchase_date'    => '2021-09-15',
                'purchase_price'   => 12000,
                'salvage_value'    => 1000,
                'useful_life_years'=> 10,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 10,
                'location'         => 'مكتب المدير العام',
                'serial_number'    => null,
                'supplier'         => 'معرض الأثاث الحديث',
            ],
            [
                'code'             => 'FA-006',
                'name'             => 'كراسي انتظار (6 كراسي)',
                'category'         => 'أثاث ومفروشات',
                'purchase_date'    => '2021-09-15',
                'purchase_price'   => 6000,
                'salvage_value'    => 500,
                'useful_life_years'=> 10,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 10,
                'location'         => 'قاعة الانتظار',
                'serial_number'    => null,
                'supplier'         => 'معرض الأثاث الحديث',
            ],
            [
                'code'             => 'FA-007',
                'name'             => 'ماكينة تصوير Xerox',
                'category'         => 'معدات وآلات',
                'purchase_date'    => '2022-11-01',
                'purchase_price'   => 35000,
                'salvage_value'    => 5000,
                'useful_life_years'=> 5,
                'depreciation_method' => 'declining_balance',
                'depreciation_rate'   => 20,
                'location'         => 'غرفة الطباعة',
                'serial_number'    => 'XRX-2022-007',
                'supplier'         => 'Xerox Egypt',
            ],
            [
                'code'             => 'FA-008',
                'name'             => 'سيرفر IBM x3650',
                'category'         => 'أجهزة كمبيوتر',
                'purchase_date'    => '2021-04-20',
                'purchase_price'   => 85000,
                'salvage_value'    => 10000,
                'useful_life_years'=> 5,
                'depreciation_method' => 'straight_line',
                'depreciation_rate'   => 20,
                'location'         => 'غرفة السيرفر',
                'serial_number'    => 'IBM-2021-001',
                'supplier'         => 'IBM Egypt',
            ],
        ];

        foreach ($assets as $data) {
            // حساب الاستهلاك المتراكم
            $purchaseDate   = \Carbon\Carbon::parse($data['purchase_date']);
            $monthsUsed     = $purchaseDate->diffInMonths(now());
            $monthlyDep     = ($data['purchase_price'] - $data['salvage_value']) / ($data['useful_life_years'] * 12);
            $accumulated    = min($monthsUsed * $monthlyDep, $data['purchase_price'] - $data['salvage_value']);
            $netValue       = $data['purchase_price'] - $accumulated;

            FixedAsset::firstOrCreate(['code' => $data['code']], [
                'name'                     => $data['name'],
                'category'                 => $data['category'],
                'purchase_date'            => $data['purchase_date'],
                'purchase_price'           => $data['purchase_price'],
                'salvage_value'            => $data['salvage_value'],
                'useful_life_years'        => $data['useful_life_years'],
                'depreciation_method'      => $data['depreciation_method'],
                'depreciation_rate'        => $data['depreciation_rate'],
                'accumulated_depreciation' => round($accumulated, 2),
                'net_book_value'           => round($netValue, 2),
                'last_depreciation_date'   => now()->subMonth(),
                'status'                   => 'active',
                'location'                 => $data['location'],
                'serial_number'            => $data['serial_number'],
                'supplier'                 => $data['supplier'],
                'created_by'               => 1,
            ]);
        }

        $this->command->info('✅ تم إضافة ' . count($assets) . ' أصل ثابت بنجاح');
    }
}