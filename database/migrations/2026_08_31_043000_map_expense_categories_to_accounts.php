<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fill only NULL mappings for the existing Orion expense categories.
     *
     * Historical journal entries are NOT modified.
     * Existing non-NULL mappings are NOT overwritten.
     */
    public function up(): void
    {
        $map = [
            'إيجارات'             => '5202',
            'رواتب وأجور'         => '5201',
            'كهرباء ومياه'        => '5203',
            'اتصالات وإنترنت'     => '5208',
            'صيانة وإصلاح'        => '5206',
            'مواصلات ووقود'       => '5204',
            'قرطاسية ومستلزمات'   => '5207',
            'تسويق وإعلان'        => '5205',
            'ضيافة واجتماعات'     => '5210',
            'مصروفات متنوعة'      => '5210',
        ];

        foreach ($map as $categoryName => $accountCode) {
            $accountId = DB::table('accounts')
                ->where('code', $accountCode)
                ->where('is_active', true)
                ->value('id');

            if (!$accountId) {
                continue;
            }

            DB::table('expense_categories')
                ->where('name', $categoryName)
                ->whereNull('account_id')
                ->update([
                    'account_id' => $accountId,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Conservative rollback:
     * only remove a mapping when the category still points to the same
     * account assigned by this migration.
     */
    public function down(): void
    {
        $map = [
            'إيجارات'             => '5202',
            'رواتب وأجور'         => '5201',
            'كهرباء ومياه'        => '5203',
            'اتصالات وإنترنت'     => '5208',
            'صيانة وإصلاح'        => '5206',
            'مواصلات ووقود'       => '5204',
            'قرطاسية ومستلزمات'   => '5207',
            'تسويق وإعلان'        => '5205',
            'ضيافة واجتماعات'     => '5210',
            'مصروفات متنوعة'      => '5210',
        ];

        foreach ($map as $categoryName => $accountCode) {
            $accountId = DB::table('accounts')
                ->where('code', $accountCode)
                ->value('id');

            if (!$accountId) {
                continue;
            }

            DB::table('expense_categories')
                ->where('name', $categoryName)
                ->where('account_id', $accountId)
                ->update([
                    'account_id' => null,
                    'updated_at' => now(),
                ]);
        }
    }
};
