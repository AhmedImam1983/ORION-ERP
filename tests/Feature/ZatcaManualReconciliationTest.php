<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\ZatcaInvoiceSubmission;
use App\Services\ZatcaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ZatcaManualReconciliationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMinimalSchema();
        auth()->logout();
    }

    public function test_retry_claim_allows_a_reviewed_rejection(): void
    {
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_REJECTED);
        $submission->attempts = 2;
        $submission->save();

        $result = $this->invokeClaimSubmissionAttempt();

        $submission->refresh();
        $this->assertSame('claimed', $result['state']);
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_PROCESSING, $submission->status);
        $this->assertSame(3, (int) $submission->attempts);
        $this->assertNull($submission->status_code);
        $this->assertNull($submission->response);
    }

    public function test_retry_claim_blocks_an_already_sent_submission(): void
    {
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_SENT);
        $submission->attempts = 3;
        $submission->status_code = 202;
        $submission->response = '{"status_code":202}';
        $submission->save();

        $result = $this->invokeClaimSubmissionAttempt();

        $submission->refresh();
        $this->assertSame('sent', $result['state']);
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_SENT, $submission->status);
        $this->assertSame(3, (int) $submission->attempts);
    }

    public function test_retry_claim_blocks_an_uncertain_submission(): void
    {
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);
        $submission->attempts = 3;
        $submission->save();

        $result = $this->invokeClaimSubmissionAttempt();

        $submission->refresh();
        $this->assertSame('uncertain', $result['state']);
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_UNCERTAIN, $submission->status);
        $this->assertSame(3, (int) $submission->attempts);
    }

    public function test_retry_claim_blocks_a_recent_processing_submission(): void
    {
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_PROCESSING);
        $submission->attempts = 4;
        $submission->last_attempt_at = now()->subMinutes(5);
        $submission->save();

        $result = $this->invokeClaimSubmissionAttempt();

        $submission->refresh();
        $this->assertSame('processing', $result['state']);
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_PROCESSING, $submission->status);
        $this->assertSame(4, (int) $submission->attempts);
    }

    private function invokeClaimSubmissionAttempt(): array
    {
        $invoice = new Invoice;
        $invoice->forceFill(['id' => 1, 'type' => 'sale', 'status' => 'confirmed']);
        $invoice->exists = true;

        $service = new class extends ZatcaService
        {
            public function claimForTest(Invoice $invoice): array
            {
                return $this->claimSubmissionAttempt($invoice, 'test', 'simulation');
            }
        };

        return $service->claimForTest($invoice);
    }

    public function test_admin_can_record_an_accepted_outcome_with_evidence(): void
    {
        $this->actingAsAdmin();
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);
        $submission->status_code = 503;
        $submission->response = '{"status_code":503,"error":"upstream timeout"}';
        $submission->save();

        $result = app(ZatcaService::class)->recordManualReconciliation(
            $submission->id,
            'accepted',
            'zatca_portal',
            'ZATCA-PORTAL-REF-001',
            'Verified accepted invoice in the official ZATCA portal.'
        );

        $submission->refresh();
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_SENT, $submission->status);
        $this->assertNull($submission->status_code, 'A manual decision must not masquerade as an HTTP response code.');
        $this->assertSame('sent', $result['status']);
        $this->assertSame(1, DB::table('zatca_submission_reconciliations')->count());
        $audit = DB::table('zatca_submission_reconciliations')->first();
        $this->assertSame(503, (int) $audit->previous_status_code);
        $this->assertSame('accepted', $audit->outcome);
        $this->assertSame('ZATCA-PORTAL-REF-001', $audit->evidence_reference);
    }

    public function test_admin_can_record_a_definitive_rejection_and_enable_a_reviewed_retry(): void
    {
        $this->actingAsAdmin();
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);

        app(ZatcaService::class)->recordManualReconciliation(
            $submission->id,
            'rejected',
            'official_response',
            'ZATCA-RESP-401-REF',
            'Official response and portal record confirm this invoice was rejected.'
        );

        $submission->refresh();
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_REJECTED, $submission->status);
        $this->assertFalse($submission->hasAmbiguousOutcome());
        $this->assertSame(1, DB::table('zatca_submission_reconciliations')->count());
    }

    public function test_unresolved_review_remains_blocked_and_is_audited(): void
    {
        $this->actingAsAdmin();
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);

        $result = app(ZatcaService::class)->recordManualReconciliation(
            $submission->id,
            'unresolved',
            'other',
            'CASE-UNRESOLVED-01',
            'Checked available evidence but the final ZATCA outcome remains unknown.'
        );

        $submission->refresh();
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_UNCERTAIN, $submission->status);
        $this->assertTrue($submission->hasAmbiguousOutcome());
        $this->assertSame('uncertain', $result['status']);
        $this->assertSame(1, DB::table('zatca_submission_reconciliations')->count());
    }

    public function test_unknown_environment_can_be_reclassified_only_with_audited_decision(): void
    {
        $this->actingAsAdmin();
        $submission = $this->submission(
            ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN,
            ZatcaInvoiceSubmission::STATUS_SENT
        );

        app(ZatcaService::class)->recordManualReconciliation(
            $submission->id,
            'accepted',
            'zatca_portal',
            'PORTAL-UUID-ABC',
            'Official portal confirms the invoice is accepted in the Test environment.',
            'test'
        );

        $submission->refresh();
        $this->assertSame('test', $submission->environment);
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_SENT, $submission->status);
        $audit = DB::table('zatca_submission_reconciliations')->first();
        $this->assertSame('unknown', $audit->previous_environment);
        $this->assertSame('test', $audit->new_environment);
        $this->assertSame('test', $audit->target_environment);
    }

    public function test_unknown_environment_with_existing_target_record_is_archived_and_both_records_are_audited(): void
    {
        $this->actingAsAdmin();
        $legacy = $this->submission(ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN, ZatcaInvoiceSubmission::STATUS_FAILED);
        $target = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);

        app(ZatcaService::class)->recordManualReconciliation(
            $legacy->id,
            'accepted',
            'zatca_portal',
            'PORTAL-UUID-XYZ',
            'Official portal confirms acceptance for this invoice in the Test environment.',
            'test'
        );

        $legacy->refresh();
        $target->refresh();
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_RECONCILED, $legacy->status);
        $this->assertSame('unknown', $legacy->environment);
        $this->assertSame(ZatcaInvoiceSubmission::STATUS_SENT, $target->status);
        $this->assertSame(2, DB::table('zatca_submission_reconciliations')->count());
    }

    public function test_definitive_outcomes_require_an_official_evidence_source(): void
    {
        $this->actingAsAdmin();
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);
        $this->expectException(\InvalidArgumentException::class);

        app(ZatcaService::class)->recordManualReconciliation(
            $submission->id,
            'accepted',
            'other',
            'SOME-REFERENCE',
            'This note is long enough but the source is not official.'
        );
    }

    public function test_non_admin_cannot_record_a_reconciliation(): void
    {
        $submission = $this->submission('test', ZatcaInvoiceSubmission::STATUS_UNCERTAIN);
        $this->expectException(\RuntimeException::class);

        app(ZatcaService::class)->recordManualReconciliation(
            $submission->id,
            'accepted',
            'zatca_portal',
            'PORTAL-REF-SECURITY',
            'Official portal evidence was provided for this review.'
        );
    }

    public function test_unknown_environment_cannot_be_resolved_to_an_existing_sent_target_as_rejected(): void
    {
        $this->actingAsAdmin();
        $legacy = $this->submission(ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN, ZatcaInvoiceSubmission::STATUS_UNCERTAIN);
        $this->submission('test', ZatcaInvoiceSubmission::STATUS_SENT);
        $this->expectException(\RuntimeException::class);

        app(ZatcaService::class)->recordManualReconciliation(
            $legacy->id,
            'rejected',
            'zatca_portal',
            'PORTAL-REF-CONFLICT',
            'Portal evidence appears contradictory and must be reviewed carefully.',
            'test'
        );
    }

    private function actingAsAdmin(): void
    {
        $user = new class extends User
        {
            public function hasAnyRole(...$roles): bool
            {
                return true;
            }
        };
        $user->setAttribute('id', 99);
        $user->setAttribute('name', 'Test Admin');
        auth()->guard()->setUser($user);
    }

    private function submission(string $environment, string $status): ZatcaInvoiceSubmission
    {
        if (! DB::table('invoices')->where('id', 1)->exists()) {
            DB::table('invoices')->insert(['id' => 1, 'type' => 'sale', 'status' => 'confirmed']);
        }

        $submission = new ZatcaInvoiceSubmission;
        $submission->forceFill([
            'invoice_id' => 1,
            'environment' => $environment,
            'status' => $status,
            'attempts' => 1,
            'status_code' => null,
            'uuid' => null,
            'invoice_hash' => null,
            'error' => 'Previous outcome requires review.',
            'response' => null,
            'last_attempt_at' => now()->subMinutes(45),
            'sent_at' => null,
            'sending_mode' => 'simulation',
        ]);
        $submission->save();

        return $submission;
    }

    private function createMinimalSchema(): void
    {
        foreach ([
            'zatca_submission_reconciliations',
            'zatca_invoice_submissions',
            'invoices',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('status');
        });
        Schema::create('zatca_invoice_submissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('environment', 30);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('uuid', 100)->nullable();
            $table->longText('invoice_hash')->nullable();
            $table->longText('error')->nullable();
            $table->longText('response')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('sending_mode', 30)->nullable();
            $table->timestamps();
            $table->unique(['invoice_id', 'environment']);
        });
        Schema::create('zatca_submission_reconciliations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->string('outcome', 20);
            $table->string('previous_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->string('previous_environment', 30)->nullable();
            $table->string('new_environment', 30)->nullable();
            $table->string('target_environment', 30)->nullable();
            $table->unsignedSmallInteger('previous_status_code')->nullable();
            $table->longText('previous_response')->nullable();
            $table->string('evidence_type', 30);
            $table->string('evidence_reference', 255);
            $table->text('notes');
            $table->unsignedBigInteger('reconciled_by')->nullable();
            $table->timestamp('reconciled_at');
            $table->timestamps();
        });
    }
}
