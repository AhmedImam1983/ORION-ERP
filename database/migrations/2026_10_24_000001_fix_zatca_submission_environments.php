<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('zatca_invoice_submissions')) {
            return;
        }

        if (!Schema::hasColumn('zatca_invoice_submissions', 'sending_mode')
            || !Schema::hasColumn('zatca_invoice_submissions', 'environment')) {
            return;
        }

        // Older migration accidentally changed simulation/compliance to unknown.
        // Recover only records whose original sending_mode identifies the environment.
        $rows = DB::table('zatca_invoice_submissions')
            ->where('environment', 'unknown')
            ->whereIn('sending_mode', ['simulation', 'compliance', 'production'])
            ->orderBy('id')
            ->get(['id', 'invoice_id', 'environment', 'status', 'attempts',
                'status_code', 'uuid', 'invoice_hash', 'error', 'response',
                'last_attempt_at', 'sent_at', 'sending_mode']);

        foreach ($rows as $candidate) {
            DB::transaction(function () use ($candidate) {
                $legacy = DB::table('zatca_invoice_submissions')
                    ->where('id', $candidate->id)
                    ->lockForUpdate()
                    ->first();

                if (!$legacy || $legacy->environment !== 'unknown') {
                    return;
                }

                $targetEnvironment = $legacy->sending_mode === 'production'
                    ? 'production'
                    : 'test';

                $current = DB::table('zatca_invoice_submissions')
                    ->where('invoice_id', $legacy->invoice_id)
                    ->where('environment', $targetEnvironment)
                    ->lockForUpdate()
                    ->first();

                // No duplicate for this invoice/environment: safely reclassify the row.
                if (!$current) {
                    DB::table('zatca_invoice_submissions')
                        ->where('id', $legacy->id)
                        ->update([
                            'environment' => $targetEnvironment,
                            'updated_at' => now(),
                        ]);

                    return;
                }

                // If either row records a successful submission, preserve that success
                // so this repair cannot accidentally make an already-sent invoice resendable.
                $legacyWasSent = $legacy->status === 'sent';
                $currentWasSent = $current->status === 'sent';
                $finalStatus = ($legacyWasSent || $currentWasSent)
                    ? 'sent'
                    : $current->status;

                // When only the legacy row is sent, its accepted ZATCA identifiers/hash
                // are authoritative over a later failed/pending attempt.
                $primary = ($legacyWasSent && !$currentWasSent) ? $legacy : $current;
                $secondary = $primary === $legacy ? $current : $legacy;
                $prefer = static fn ($field) => $primary->{$field} !== null
                    ? $primary->{$field}
                    : $secondary->{$field};

                DB::table('zatca_invoice_submissions')
                    ->where('id', $current->id)
                    ->update([
                        'status' => $finalStatus,
                        'attempts' => max((int) $current->attempts, (int) $legacy->attempts),
                        'status_code' => $prefer('status_code'),
                        'uuid' => $prefer('uuid'),
                        'invoice_hash' => $prefer('invoice_hash'),
                        'error' => $finalStatus === 'sent' ? null : $prefer('error'),
                        'response' => $prefer('response'),
                        'last_attempt_at' => $prefer('last_attempt_at'),
                        'sent_at' => $prefer('sent_at'),
                        'sending_mode' => $prefer('sending_mode'),
                        'updated_at' => now(),
                    ]);

                // Remove the duplicate unknown row only after merging its useful data.
                DB::table('zatca_invoice_submissions')
                    ->where('id', $legacy->id)
                    ->delete();
            });
        }
    }

    public function down(): void
    {
        // Intentionally no-op: once submissions are classified, moving them back to
        // "unknown" could corrupt genuine records created after this migration.
    }
};
