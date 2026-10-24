
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zatca_invoice_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            // Environment: test, production, unknown.
            // Test includes both simulation and compliance modes.
            $table->string('environment', 30);

            // Status: pending, processing, sent, failed.
            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('attempts')->default(0);

            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('uuid', 100)->nullable();
            $table->longText('invoice_hash')->nullable();
            $table->longText('error')->nullable();
            $table->longText('response')->nullable();

            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            // Original sending mode: simulation, compliance, production.
            $table->string('sending_mode', 30)->nullable();

            $table->timestamps();

            // One current submission record per invoice and environment.
            $table->unique(
                ['invoice_id', 'environment'],
                'zatca_submissions_invoice_env_unique'
            );

            $table->index(
                ['environment', 'status'],
                'zatca_submissions_env_status_index'
            );

            $table->index(
                ['status', 'last_attempt_at'],
                'zatca_submissions_status_attempt_index'
            );
        });

        /*
         * Preserve existing ZATCA submission data.
         * Never assume an unknown legacy record belongs to production.
         */
        $legacyInvoices = DB::table('invoices')
            ->where(function ($query) {
                $query->where('zatca_sent', true)
                    ->orWhereNotNull('zatca_status_code')
                    ->orWhereNotNull('zatca_uuid')
                    ->orWhereNotNull('zatca_invoice_hash')
                    ->orWhereNotNull('zatca_error')
                    ->orWhereNotNull('zatca_sent_at')
                    ->orWhereNotNull('zatca_response');
            })
            ->orderBy('id')
            ->get([
                'id',
                'zatca_sent',
                'zatca_environment',
                'zatca_status_code',
                'zatca_uuid',
                'zatca_invoice_hash',
                'zatca_error',
                'zatca_sent_at',
                'zatca_response',
            ]);

        $now = now();

        foreach ($legacyInvoices as $invoice) {
            $legacyMode = strtolower(
                trim((string) ($invoice->zatca_environment ?? ''))
            );

            // Normalize the old sending mode into the new environment.
            $environment = match ($legacyMode) {
                'simulation', 'compliance', 'test' => 'test',
                'production' => 'production',
                default => 'unknown',
            };

            // Keep the actual sending mode separate from the environment.
            $sendingMode = in_array(
                $legacyMode,
                ['simulation', 'compliance', 'production'],
                true
            ) ? $legacyMode : null;

            $wasSent = (bool) $invoice->zatca_sent;

            DB::table('zatca_invoice_submissions')->insert([
                'invoice_id'     => $invoice->id,
                'environment'    => $environment,
                'status'          => $wasSent ? 'sent' : 'failed',
                'attempts'        => 1,
                'status_code'     => $invoice->zatca_status_code,
                'uuid'             => $invoice->zatca_uuid,
                'invoice_hash'     => $invoice->zatca_invoice_hash,
                'error'            => $invoice->zatca_error,
                'response'         => $invoice->zatca_response,
                'last_attempt_at'  => $invoice->zatca_sent_at,
                'sent_at'          => $wasSent
                    ? $invoice->zatca_sent_at
                    : null,
                'sending_mode'     => $sendingMode,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('zatca_invoice_submissions');
    }
};
