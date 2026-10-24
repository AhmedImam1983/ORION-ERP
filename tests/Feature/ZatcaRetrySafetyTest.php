<?php

namespace Tests\Feature;

use App\Models\ZatcaInvoiceSubmission;
use PHPUnit\Framework\TestCase;

class ZatcaRetrySafetyTest extends TestCase
{
    public function test_http_200_and_202_are_accepted_statuses(): void
    {
        $this->assertTrue(ZatcaInvoiceSubmission::isAcceptedApiStatus(200));
        $this->assertTrue(ZatcaInvoiceSubmission::isAcceptedApiStatus(202));
        $this->assertFalse(ZatcaInvoiceSubmission::isAcceptedApiStatus(201));

        $this->assertFalse(ZatcaInvoiceSubmission::isAmbiguousApiResponse(200, true));
        // 202 is documented as accepted with warnings, even if the COM success flag is false.
        $this->assertFalse(ZatcaInvoiceSubmission::isAmbiguousApiResponse(202, false));
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
        $this->assertTrue(ZatcaInvoiceSubmission::isAmbiguousApiResponse(201, true));
        $this->assertTrue(ZatcaInvoiceSubmission::isAmbiguousApiResponse(204, true));
    }

    public function test_legacy_failed_200_and_202_responses_are_quarantined_for_recovery(): void
    {
        $legacyResponses = [
            [200, '{"status_code":200,"is_success":true,"error":null}'],
            [202, '{"status_code":202,"is_success":false,"error":"accepted with warnings"}'],
        ];

        foreach ($legacyResponses as [$statusCode, $response]) {
            $submission = new ZatcaInvoiceSubmission([
                'status' => ZatcaInvoiceSubmission::STATUS_FAILED,
                'status_code' => $statusCode,
                'response' => $response,
                'error' => null,
            ]);

            $this->assertTrue($submission->hasAcceptedStoredResponse());
            $this->assertTrue($submission->hasAmbiguousOutcome());
        }
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
