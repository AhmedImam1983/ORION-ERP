<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // إنشاء الأدوار
        $roles = ['super_admin', 'admin', 'cashier', 'accountant', 'storekeeper'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // إنشاء المستخدم الرئيسي
        $user = User::firstOrCreate(
            ['email' => 'admin@orion.com'],
            [
                'name'     => 'مدير النظام',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('super_admin');

        // شجرة الحسابات
        $this->call(AccountsSeeder::class);
    }
}
