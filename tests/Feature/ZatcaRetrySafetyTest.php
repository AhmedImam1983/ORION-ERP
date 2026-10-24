<?php

namespace Tests\Feature;

use App\Models\ZatcaInvoiceSubmission;
use PHPUnit\Framework\TestCase;

class ZatcaRetrySafetyTest extends TestCase
{
    public function test_accepted_202_response_is_not_ambiguous(): void
    {
        $this->assertFalse(ZatcaInvoiceSubmission::isAmbiguousApiResponse(202, true));
    }

    public function test_explicit_client_rejection_is_not_ambiguous(): void
    {
        $this->assertFalse(ZatcaInvoiceSubmission::isAmbiguousApiResponse(400, false));
        $this->assertFalse(ZatcaInvoiceSubmission::isAmbiguousApiResponse(401, false));
    }

    public function test_timeout_conflict_rate_limit_and_server_errors_are_ambiguous(): void
    {
        foreach ([0, 408, 409, 425, 429, 500, 503] as $statusCode) {
            $this->assertTrue(
                ZatcaInvoiceSubmission::isAmbiguousApiResponse($statusCode, false),
                "Expected status {$statusCode} to require reconciliation."
            );
        }
    }

    public function test_unexpected_success_status_is_ambiguous(): void
    {
        $this->assertTrue(ZatcaInvoiceSubmission::isAmbiguousApiResponse(200, true));
        $this->assertTrue(ZatcaInvoiceSubmission::isAmbiguousApiResponse(201, true));
    }

    public function test_legacy_failed_request_with_no_response_at_reporting_step_is_ambiguous(): void
    {
        $submission = new ZatcaInvoiceSubmission([
            'status' => ZatcaInvoiceSubmission::STATUS_FAILED,
            'status_code' => null,
            'response' => null,
            'error' => '[9d-call-reporting-api] Connection timed out',
        ]);

        $this->assertTrue($submission->hasAmbiguousOutcome());
    }

    public function test_legacy_failed_request_with_a_definitive_401_response_is_not_ambiguous(): void
    {
        $submission = new ZatcaInvoiceSubmission([
            'status' => ZatcaInvoiceSubmission::STATUS_FAILED,
            'status_code' => 401,
            'response' => '{"status_code":401,"is_success":false,"error":"Unauthorized"}',
            'error' => 'Unauthorized',
        ]);

        $this->assertFalse($submission->hasAmbiguousOutcome());
    }

    public function test_uncertain_records_always_require_reconciliation(): void
    {
        $submission = new ZatcaInvoiceSubmission([
            'status' => ZatcaInvoiceSubmission::STATUS_UNCERTAIN,
            'status_code' => 202,
            'response' => '{"status_code":202,"is_success":false}',
        ]);

        $this->assertTrue($submission->hasAmbiguousOutcome());
    }
}
