<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('eta_uuid')->nullable()->comment('UUID من منظومة الضرائب');
            $table->string('eta_long_id')->nullable()->comment('Long ID من منظومة الضرائب');
            $table->string('eta_hash_key')->nullable()->comment('Hash Key');
            $table->enum('eta_status', ['not_submitted', 'submitted', 'valid', 'invalid', 'cancelled'])->default('not_submitted')->comment('حالة الفاتورة الإلكترونية');
            $table->timestamp('eta_submitted_at')->nullable()->comment('وقت الإرسال');
            $table->text('eta_error')->nullable()->comment('رسالة الخطأ');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['eta_uuid', 'eta_long_id', 'eta_hash_key', 'eta_status', 'eta_submitted_at', 'eta_error']);
        });
    }
};