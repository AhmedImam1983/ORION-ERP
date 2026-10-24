<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Services\ZatcaService;
use Tests\TestCase;

class ZatcaSubmissionSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.zatca.allow_production_sending' => false]);
        $this->app['auth']->forgetGuards();
    }

    public function test_invalid_environment_is_rejected_before_database_or_com_calls(): void
    {
        $result = app(ZatcaService::class)->submitInvoice($this->invoice('sale', 'confirmed'), 'staging');

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('بيئة زاتكا غير صحيحة', $result['error']);
    }

    public function test_purchase_invoice_cannot_be_submitted_to_zatca(): void
    {
        $result = app(ZatcaService::class)->submitInvoice($this->invoice('purchase', 'confirmed'), 'test');

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('فواتير البيع فقط', $result['error']);
    }

    public function test_draft_and_cancelled_sales_cannot_be_submitted_to_zatca(): void
    {
        foreach (['draft', 'cancelled'] as $status) {
            $result = app(ZatcaService::class)->submitInvoice($this->invoice('sale', $status), 'test');

            $this->assertFalse($result['success'], "Unexpected success for invoice status: {$status}");
            $this->assertSame('failed', $result['status']);
            $this->assertStringContainsString('لا يمكن إرسال فاتورة بالحالة', $result['error']);
        }
    }

    public function test_production_is_blocked_when_feature_flag_is_false(): void
    {
        config(['services.zatca.allow_production_sending' => false]);

        $result = app(ZatcaService::class)->submitInvoice($this->invoice('sale', 'confirmed'), 'production');

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('معطل افتراضيًا', $result['error']);
    }

    public function test_production_requires_an_authenticated_privileged_user_even_when_enabled(): void
    {
        config(['services.zatca.allow_production_sending' => true]);
        $this->app['auth']->forgetGuards();

        $result = app(ZatcaService::class)->submitInvoice($this->invoice('sale', 'confirmed'), 'production');

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('ليس لديك صلاحية الإرسال', $result['error']);
    }

    public function test_an_invoice_model_must_be_persisted_before_submission(): void
    {
        $result = app(ZatcaService::class)->submitInvoice($this->invoice('sale', 'confirmed'), 'test');

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsString('غير محفوظة في قاعدة البيانات', $result['error']);
    }

    private function invoice(string $type, string $status): Invoice
    {
        return new Invoice([
            'type' => $type,
            'status' => $status,
        ]);
    }
}
