<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Existing products are inventory-controlled unless explicitly created as services.
            $table->string('item_type', 20)->default('stock')->index()->after('code');
        });

        // Seed the conventional category safely. Do not delete it on rollback because
        // a user may already have assigned services to it.
        if (Schema::hasTable('categories')) {
            $serviceCategory = DB::table('categories')
                ->where(function ($query) {
                    $query->where('code', 'SERVICES')
                        ->orWhere('name', 'الخدمات');
                })
                ->orderBy('id')
                ->first();

            if (!$serviceCategory) {
                $now = now();
                DB::table('categories')->insert([
                    'name'        => 'الخدمات',
                    'name_en'     => 'Services',
                    'code'        => 'SERVICES',
                    'description' => 'خدمات الورشة والتركيب والصيانة والأعمال غير المخزنية',
                    'is_active'   => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } elseif (!empty($serviceCategory->deleted_at)) {
                DB::table('categories')->where('id', $serviceCategory->id)->update([
                    'deleted_at' => null,
                    'is_active'  => true,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['item_type']);
            $table->dropColumn('item_type');
        });
    }
};
