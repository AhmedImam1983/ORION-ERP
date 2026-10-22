<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pos_transactions MODIFY COLUMN type ENUM('sale','return','refund','void') NOT NULL DEFAULT 'sale'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pos_transactions MODIFY COLUMN type ENUM('sale','refund','void') NOT NULL DEFAULT 'sale'");
    }
};