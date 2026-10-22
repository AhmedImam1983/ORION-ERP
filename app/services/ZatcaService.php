<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class ZatcaService
{
    public function submitInvoice(Invoice $invoice): array
    {
        $uuid        = null;
        $invoiceHash = null;
        $environment = Setting::get('zatca_sending_mode', 'simulation');
        // ⚠️ القيمة الفعلية المخزنة في TBL_COM_INFO.ZatcaSendingMode بالنظام الشغّال = 0 لبيئة Simulation
        // (تم التأكد من قاعدة بيانات الأكسس الحقيقية وليس افتراضاً)
        $sendingMode = match ($environment) {
            'compliance' => 0,
            'simulation' => 0, // ← القيمة الحقيقية المستخدمة فعلياً
            'production' => 2,
            default      => 0,
        };

        $rootPath = Setting::get('zatca_root_path', 'D:\ZatcaIntegration');
        if (!is_dir($rootPath)) {
            @mkdir($rootPath, 0777, true);
        }

        $step = 'init';

        try {
            if (!extension_loaded('com_dotnet')) {
                throw new \RuntimeException('PHP COM extension غير مفعّل.');
            }

            $step = '1-create-objects';
            Log::info("ZATCA STEP: {$step}");
            $ubl = new \COM('MyUBLGenerator.ModifiedUBLGenerator');
            $inv = new \COM('ZatcaUBLXMLGenerator.Invoice');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2-basic-fields';
            Log::info("ZATCA STEP: {$step}");
            $inv->id        = (string) $invoice->id;
            $inv->IssueDate = $invoice->invoice_date->format('Y-m-d');
            $inv->IssueTime = now()->format('H:i:s');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2b-invoice-type-code';
            Log::info("ZATCA STEP: {$step}");
            $inv->InvoiceTypeCode->id   = 388;
            $inv->InvoiceTypeCode->name = '0200000';
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2c-currency';
            Log::info("ZATCA STEP: {$step}");
            $inv->DocumentCurrencyCode = 'SAR';
            $inv->TaxCurrencyCode      = 'SAR';
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2d-pih';
            Log::info("ZATCA STEP: {$step}");
            $pih = $this->getPreviousInvoiceHash($invoice);
            $inv->AdditionalDocumentReferencePIH->EmbeddedDocumentBinaryObject = $pih;
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2e-icv';
            Log::info("ZATCA STEP: {$step}");
            $inv->AdditionalDocumentReferenceICV->UUID = (string) $invoice->id;
            Log::info("ZATCA STEP OK: {$step}");

            $step = '3-supplier-identification';
            Log::info("ZATCA STEP: {$step}");
            // مهم: PartyIdentification.id = السجل التجاري (CR)، مش الرقم الضريبي
            $inv->SupplierParty->PartyIdentification->id       = Setting::get('company_cr', '');
            $inv->SupplierParty->PartyIdentification->schemeID = 'CRN';
            Log::info("ZATCA STEP OK: {$step}", ['cr' => Setting::get('company_cr', '')]);

            $step = '3b-supplier-address';
            Log::info("ZATCA STEP: {$step}");
            $inv->SupplierParty->PostalAddress->StreetName           = Setting::get('company_street', '');
            $inv->SupplierParty->PostalAddress->AdditionalStreetName = '';
            $inv->SupplierParty->PostalAddress->BuildingNumber       = Setting::get('company_building', '');
            $inv->SupplierParty->PostalAddress->PlotIdentification   = Setting::get('company_plot', '');
            $inv->SupplierParty->PostalAddress->CityName             = Setting::get('company_city', '');
            $inv->SupplierParty->PostalAddress->PostalZone           = Setting::get('company_postal', '');
            $inv->SupplierParty->PostalAddress->CountrySubentity     = Setting::get('company_district', '');
            $inv->SupplierParty->PostalAddress->CitySubdivisionName  = Setting::get('company_district', '');
            $inv->SupplierParty->PostalAddress->Country->IdentificationCode = Setting::get('company_country', 'SA');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '3c-supplier-legal';
            Log::info("ZATCA STEP: {$step}");
            $inv->SupplierParty->PartyLegalEntity->RegistrationName = Setting::get('company_name', '');
            $inv->SupplierParty->PartyTaxScheme->CompanyID          = Setting::get('company_tax_number', '');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '4-customer';
            Log::info("ZATCA STEP: {$step}");
            $customer = $invoice->customer;

            $inv->CustomerParty->PartyIdentification->schemeID = 'CRN';

            $inv->CustomerParty->PostalAddress->StreetName           = $customer->street_name ?? '';
            $inv->CustomerParty->PostalAddress->AdditionalStreetName = '';
            $inv->CustomerParty->PostalAddress->BuildingNumber       = $customer->building_number ?? '';
            $inv->CustomerParty->PostalAddress->PlotIdentification   = '';
            $inv->CustomerParty->PostalAddress->CityName             = $customer->city_name ?? '';
            $inv->CustomerParty->PostalAddress->PostalZone           = $customer->postal_code ?? '';
            $inv->CustomerParty->PostalAddress->CountrySubentity     = $customer->district ?? '';
            $inv->CustomerParty->PostalAddress->CitySubdivisionName  = $customer->district ?? '';
            $inv->CustomerParty->PostalAddress->Country->IdentificationCode = $customer->country_code ?? 'SA';

            $inv->CustomerParty->PartyLegalEntity->RegistrationName = $customer->name ?? 'عميل نقدي';
            $inv->CustomerParty->PartyTaxScheme->CompanyID          = $customer->tax_number ?? '';
            Log::info("ZATCA STEP OK: {$step}");

            $step = '5-invoice-lines';
            Log::info("ZATCA STEP: {$step}");
            $lineIndex = 0;
            foreach ($invoice->items as $item) {
                $lineIndex++;
                Log::info("ZATCA STEP: {$step} - line {$lineIndex}");

                $invLine = new \COM('ZatcaUBLXMLGenerator.InvoiceLine');

                $invLine->InvoiceQuantity = (float) $item->quantity;
                $invLine->Item->name      = $item->product_name;

                $vatType    = $item->vat_type ?? 'S';
                $vatPercent = (float) ($item->tax_percent ?? 15);

                Log::info("ZATCA LINE DATA", [
                    'line'               => $lineIndex,
                    'product_name'       => $item->product_name,
                    'vat_type_raw'       => $item->vat_type,
                    'vat_type_used'      => $vatType,
                    'tax_percent_raw'    => $item->tax_percent,
                    'vat_percent_used'   => $vatPercent,
                    'discount_amount'    => $item->discount_amount,
                    'unit_price'         => $item->unit_price,
                ]);

                $invLine->TaxTotal->TaxSubtotal->TaxCategory->id      = $vatType;
                $invLine->TaxTotal->TaxSubtotal->TaxCategory->Percent = $vatPercent;
                $invLine->Item->ClassifiedTaxCategory->id             = $vatType;
                $invLine->Item->ClassifiedTaxCategory->Percent        = $vatPercent;

                if ($vatPercent <= 0) {
                    $invLine->TaxTotal->TaxSubtotal->TaxCategory->TaxExemptionReason     = $item->vat_exemption_reason ?? '';
                    $invLine->TaxTotal->TaxSubtotal->TaxCategory->TaxExemptionReasonCode = $item->vat_exemption_reason_code ?? '';
                }

                $invLine->Price->EncludingVat = true;
                $invLine->Price->PriceAmount  = (float) $item->unit_price;

                if ((float) $item->discount_amount > 0) {
                    $allowance = new \COM('ZatcaUBLXMLGenerator.AllowanceCharge');
                    $allowance->ChargeIndicator         = false;
                    $allowance->AllowanceChargeReason    = 'خصم على المنتج';
                    $allowance->amount                   = (float) $item->discount_amount;
                    $allowance->MultiplierFactorNumeric  = 0;
                    $allowance->BaseAmount               = 0;
                    // لازم نحدد نفس نسبة ونوع الضريبة بتاعة الصنف على الخصم
                    // عشان نطابق BR-KSA-84 (لازم تكون 15 أو 5 لو النوع S)
                    $allowance->TaxCategory->ID      = $vatType;
                    $allowance->TaxCategory->Percent = $vatPercent;
                    $invLine->allowanceCharges->add($allowance);
                }

                $inv->InvoiceLines->add($invLine);
                Log::info("ZATCA STEP OK: {$step} - line {$lineIndex}");
            }

            $step = '6-certificate';
            Log::info("ZATCA STEP: {$step}");
            $certPath   = Setting::get('zatca_cert_path', '');
            $keyPath    = Setting::get('zatca_key_path', '');
            $secretPath = Setting::get('zatca_secret_path', '');

            if (!$certPath || !file_exists($certPath)) {
                throw new \RuntimeException('ملف الشهادة (cert.pem) غير موجود. راجع إعدادات النظام → زاتكا');
            }
            if (!$keyPath || !file_exists($keyPath)) {
                throw new \RuntimeException('ملف المفتاح الخاص (key.pem) غير موجود. راجع إعدادات النظام → زاتكا');
            }

            $inv->CSIDInfo->CertPem     = file_get_contents($certPath);
            $inv->CSIDInfo->PrivateKey  = file_get_contents($keyPath);
            $secretKey = $secretPath && file_exists($secretPath)
                ? trim(file_get_contents($secretPath))
                : Setting::get('zatca_secret', '');

            Log::info("ZATCA CERT CHECK", [
                'cert_len'        => strlen($inv->CSIDInfo->CertPem),
                'cert_starts'     => substr($inv->CSIDInfo->CertPem, 0, 40),
                'key_len'         => strlen($inv->CSIDInfo->PrivateKey),
                'secret_len'      => strlen($secretKey),
                'secret_preview'  => substr($secretKey, 0, 6) . '...',
                'tax_number_used' => Setting::get('company_tax_number', ''),
                'cr_used'         => Setting::get('company_cr', ''),
            ]);
            Log::info("ZATCA STEP OK: {$step}");

            $step = '7-generate-xml';
            Log::info("ZATCA STEP: {$step}");
            $result = $ubl->GenerateInvoiceXML($inv, $rootPath);
            Log::info("ZATCA STEP OK: {$step}");

            if (is_null($result)) {
                throw new \RuntimeException('GenerateInvoiceXML رجع نتيجة فارغة.');
            }
            if (!$result->IsValid) {
                throw new \RuntimeException('فشل توليد XML: ' . ($result->ErrorMessage ?? 'خطأ غير معروف'));
            }

            $encodedInvoice = $result->EncodedInvoice;
            $invoiceHash    = $result->InvoiceHash;
            $uuid           = $result->UUID;
            Log::info("ZATCA XML RESULT", ['uuid' => $uuid, 'hash_len' => strlen($invoiceHash ?? '')]);

            $step = '8-api-request-logic-create';
            Log::info("ZATCA STEP: {$step}");
            $apiReqLogic = new \COM('ZatcaUBLXMLGenerator.BLL.ApiRequestLogic');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '8b-request-body';
            Log::info("ZATCA STEP: {$step}");
            $invRequestBody = new \COM('ZatcaUBLXMLGenerator.HelperContracts.InvoiceReportingRequest');
            $invRequestBody->Invoice     = $encodedInvoice;
            $invRequestBody->InvoiceHash = $invoiceHash;
            $invRequestBody->UUID        = $uuid;
            Log::info("ZATCA STEP OK: {$step}");

            $step = '8c-set-api-request-logic';
            Log::info("ZATCA STEP: {$step} - sendingMode={$sendingMode}");
            $apiReqLogic->SetApiRequestLogic($sendingMode);
            Log::info("ZATCA STEP OK: {$step}");

            $step = '8d-set-save-cleared-xml';
            Log::info("ZATCA STEP: {$step} - rootPath={$rootPath}");
            $apiReqLogic->SetToSaveClearedXMLFile($rootPath, true);
            Log::info("ZATCA STEP OK: {$step}");

            if ($sendingMode === 0) {
                $step = '9a-compliance-csid';
                Log::info("ZATCA STEP: {$step}");
                $tokenResponse = $apiReqLogic->GetComplianceCSIDAPI('12345', '', $rootPath);
                Log::info("ZATCA STEP OK: {$step}");

                if (is_null($tokenResponse)) {
                    throw new \RuntimeException('استجابة Compliance CSID فارغة.');
                }

                // نقرأ كل property لوحده بأمان - لو أي واحدة مش موجودة منكسرش الباقي
                $safeGet = function ($obj, string $prop) {
                    try {
                        $val = $obj->{$prop};
                        return is_string($val) ? $val : (is_null($val) ? null : (string) $val);
                    } catch (\Throwable $e) {
                        return "[[property '{$prop}' not accessible: {$e->getMessage()}]]";
                    }
                };

                $rawError = $safeGet($tokenResponse, 'ErrorMessage');

                Log::info("ZATCA COMPLIANCE CSID RESPONSE", [
                    'ErrorMessage_raw'      => $rawError,
                    'ErrorMessage_len'      => $rawError !== null ? strlen($rawError) : null,
                    'ErrorMessage_hex'      => $rawError !== null ? bin2hex(substr($rawError, 0, 60)) : null,
                    'ErrorMessage_win1256'  => $rawError !== null && $rawError !== '' ? @mb_convert_encoding($rawError, 'UTF-8', 'Windows-1256') : null,
                    'BinarySecurityToken'   => substr($safeGet($tokenResponse, 'BinarySecurityToken') ?? '', 0, 30) . '...',
                    'Secret'                => substr($safeGet($tokenResponse, 'Secret') ?? '', 0, 10) . '...',
                    'RequestID'             => $safeGet($tokenResponse, 'RequestID'),
                    'DispositionMessage'    => $safeGet($tokenResponse, 'DispositionMessage'),
                    'IsSuccess'             => $safeGet($tokenResponse, 'IsSuccess'),
                    'StatusCode'            => $safeGet($tokenResponse, 'StatusCode'),
                ]);

                if (!empty($rawError)) {
                    throw new \RuntimeException('خطأ Compliance CSID: ' . $rawError);
                }

                $step = '9b-compliance-invoice-api';
                Log::info("ZATCA STEP: {$step}");
                $reportingModel = $apiReqLogic->CallComplianceInvoiceAPI(
                    $tokenResponse->BinarySecurityToken,
                    $tokenResponse->Secret,
                    $invRequestBody
                );
                Log::info("ZATCA STEP OK: {$step}");
            } else {
                $step = '9c-to-base64-encode';
                Log::info("ZATCA STEP: {$step}");
                $certBase64 = $apiReqLogic->ToBase64Encode($inv->CSIDInfo->CertPem);
                Log::info("ZATCA STEP OK: {$step}");

                $step = '9d-call-reporting-api';
                Log::info("ZATCA STEP: {$step}");
                $reportingModel = $apiReqLogic->CallReportingAPI(
                    $certBase64,
                    $secretKey,
                    $invRequestBody
                );
                Log::info("ZATCA STEP OK: {$step}");
            }

            if (is_null($reportingModel)) {
                throw new \RuntimeException('استجابة API زاتكا فارغة.');
            }

            $step = '10-read-response';
            Log::info("ZATCA STEP: {$step}");
            $isSuccess  = (bool) $reportingModel->IsSuccess;
            $statusCode = (int) ($reportingModel->StatusCode ?? 0);
            $errorMsg   = $isSuccess ? null : ($reportingModel->ErrorMessage ?? 'خطأ غير معروف');
            Log::info("ZATCA STEP OK: {$step}");

            Log::info('ZATCA Submit', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'environment'    => $environment,
                'status_code'    => $statusCode,
                'uuid'           => $uuid,
                'is_success'     => $isSuccess,
                'error'          => $errorMsg,
            ]);

            $success = $isSuccess && $statusCode === 202;

            if ($success) {
                $invoice->update([
                    'zatca_sent'         => true,
                    'zatca_environment'  => $environment,
                    'zatca_status_code'  => $statusCode,
                    'zatca_uuid'         => $uuid,
                    'zatca_invoice_hash' => $invoiceHash,
                    'zatca_error'        => null,
                    'zatca_sent_at'      => now(),
                    'zatca_response'     => "Status: {$statusCode}",
                ]);
            } else {
                $invoice->update([
                    'zatca_sent'        => false,
                    'zatca_status_code' => $statusCode,
                    'zatca_error'       => $errorMsg,
                    'zatca_response'    => "Status: {$statusCode}",
                ]);
            }

            return [
                'success'      => $success,
                'status_code'  => $statusCode,
                'uuid'         => $uuid,
                'invoice_hash' => $invoiceHash,
                'environment'  => $environment,
                'error'        => $errorMsg,
                'raw_response' => "Status: {$statusCode}",
            ];

        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            Log::error("ZATCA FAILED AT STEP: {$step}", [
                'invoice_id' => $invoice->id,
                'error'      => $errorMsg,
                'trace'      => $e->getTraceAsString(),
            ]);
            $invoice->update(['zatca_sent' => false, 'zatca_error' => "[{$step}] {$errorMsg}"]);
            return [
                'success'      => false,
                'status_code'  => null,
                'uuid'         => $uuid,
                'invoice_hash' => $invoiceHash,
                'environment'  => $environment,
                'error'        => "[{$step}] {$errorMsg}",
                'raw_response' => null,
            ];
        }
    }

    protected function getPreviousInvoiceHash(Invoice $current): string
    {
        $initial = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';

        $previous = Invoice::whereNotNull('zatca_invoice_hash')
            ->where('zatca_sent', true)
            ->where('id', '<', $current->id)
            ->where('type', $current->type)
            ->orderByDesc('id')
            ->first();

        return $previous?->zatca_invoice_hash ?? $initial;
    }

    public function smokeTest(): array
    {
        if (!extension_loaded('com_dotnet')) {
            return ['error' => 'PHP COM extension غير مفعّل'];
        }
        $tests = [
            'Invoice'         => 'ZatcaUBLXMLGenerator.Invoice',
            'UBL'             => 'MyUBLGenerator.ModifiedUBLGenerator',
            'InvLine'         => 'ZatcaUBLXMLGenerator.InvoiceLine',
            'ApiRequestLogic' => 'ZatcaUBLXMLGenerator.BLL.ApiRequestLogic',
        ];
        $results = [];
        foreach ($tests as $label => $progId) {
            try {
                new \COM($progId);
                $results['com'][$label] = '✅ OK';
            } catch (\Throwable $e) {
                $results['com'][$label] = '❌ ' . $e->getMessage();
            }
        }
        $results['settings'] = [
            'cert_path'   => Setting::get('zatca_cert_path'),
            'cert_exists' => file_exists(Setting::get('zatca_cert_path', '') ?: ''),
            'key_path'    => Setting::get('zatca_key_path'),
            'key_exists'  => file_exists(Setting::get('zatca_key_path', '') ?: ''),
        ];
        return $results;
    }
}