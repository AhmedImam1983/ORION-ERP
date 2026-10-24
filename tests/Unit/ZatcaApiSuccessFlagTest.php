<?php

namespace Tests\Unit;

use App\Models\ZatcaInvoiceSubmission;
use PHPUnit\Framework\TestCase;

class ZatcaApiSuccessFlagTest extends TestCase
{
    public function test_native_boolean_values_are_preserved(): void
    {
        self::assertTrue(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(true));
        self::assertFalse(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(false));
    }

    public function test_string_false_is_not_treated_as_truthy(): void
    {
        self::assertFalse(ZatcaInvoiceSubmission::normalizeApiSuccessFlag('false'));
        self::assertFalse(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(' FALSE '));
    }

    public function test_com_style_true_and_false_numeric_values_are_supported(): void
    {
        self::assertTrue(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(-1));
        self::assertTrue(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(1));
        self::assertFalse(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(0));
    }

    public function test_unknown_success_flag_is_not_assumed_to_be_true_or_false(): void
    {
        self::assertNull(ZatcaInvoiceSubmission::normalizeApiSuccessFlag('unknown'));
        self::assertNull(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(2));
        self::assertNull(ZatcaInvoiceSubmission::normalizeApiSuccessFlag(null));
    }
}
