<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الـ cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // الصلاحيات
        $permissions = [
            // لوحة التحكم
            'view_dashboard',

            // الأصناف
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',

            // العملاء والموردين
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',

            // الفواتير
            'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',

            // الحسابات
            'view_accounts', 'create_accounts', 'edit_accounts', 'delete_accounts',
            'view_journal_entries', 'create_journal_entries', 'edit_journal_entries', 'delete_journal_entries',

            // الموظفين
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
            'view_attendances', 'create_attendances', 'edit_attendances', 'delete_attendances',

            // الإعدادات
            'manage_users', 'manage_roles', 'manage_settings', 'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // الأدوار
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view_dashboard',
            'view_products', 'create_products', 'edit_products',
            'view_categories', 'create_categories', 'edit_categories',
            'view_customers', 'create_customers', 'edit_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers',
            'view_invoices', 'create_invoices', 'edit_invoices',
            'view_accounts', 'view_journal_entries', 'create_journal_entries',
            'view_employees', 'view_attendances', 'create_attendances',
            'view_reports',
        ]);

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'view_dashboard',
            'view_products',
            'view_customers', 'create_customers',
            'view_invoices', 'create_invoices',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'view_dashboard',
            'view_invoices',
            'view_accounts', 'create_accounts', 'edit_accounts',
            'view_journal_entries', 'create_journal_entries', 'edit_journal_entries',
            'view_reports',
        ]);

        $storekeeper = Role::firstOrCreate(['name' => 'storekeeper', 'guard_name' => 'web']);
        $storekeeper->syncPermissions([
            'view_dashboard',
            'view_products', 'create_products', 'edit_products',
            'view_categories',
            'view_suppliers',
        ]);

        // اديله الـ super_admin للـ user الأول
        $user = User::first();
        if ($user) {
            $user->assignRole('super_admin');
        }

        $this->command->info('✅ تم إنشاء الأدوار والصلاحيات بنجاح:');
        $this->command->info('   - مدير النظام (super_admin): كل الصلاحيات');
        $this->command->info('   - مدير (admin): إدارة كاملة بدون حذف');
        $this->command->info('   - كاشير (cashier): مبيعات وفواتير فقط');
        $this->command->info('   - محاسب (accountant): حسابات وتقارير');
        $this->command->info('   - أمين مخزن (storekeeper): مخازن وأصناف');
    }
}