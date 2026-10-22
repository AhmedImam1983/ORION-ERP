<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('zatca_sent')->default(false)->after('eta_error');
            $table->string('zatca_environment')->nullable()->after('zatca_sent');
            $table->integer('zatca_status_code')->nullable()->after('zatca_environment');
            $table->string('zatca_uuid', 100)->nullable()->after('zatca_status_code');
            $table->text('zatca_invoice_hash')->nullable()->after('zatca_uuid');
            $table->text('zatca_error')->nullable()->after('zatca_invoice_hash');
            $table->datetime('zatca_sent_at')->nullable()->after('zatca_error');
            $table->longText('zatca_response')->nullable()->after('zatca_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'zatca_sent',
                'zatca_environment',
                'zatca_status_code',
                'zatca_uuid',
                'zatca_invoice_hash',
                'zatca_error',
                'zatca_sent_at',
                'zatca_response',
            ]);
        });
    }
};
