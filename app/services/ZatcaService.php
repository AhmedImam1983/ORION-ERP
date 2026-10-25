<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\ZatcaInvoiceSubmission;
use App\Models\ZatcaSubmissionReconciliation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZatcaService
{
    public function submitInvoice(Invoice $invoice, string $environment = 'test'): array
    {
        $uuid = null;
        $invoiceHash = null;
        $statusCode = null;
        $submission = null;
        $sendingModeName = null;
        $rootPath = null;
        $step = 'init';
        $responseText = null;
        $invoiceSubmissionAttempted = false;

        try {
            $environment = strtolower(trim($environment));
            if (! in_array($environment, ['test', 'production'], true)) {
                throw new \InvalidArgumentException('بيئة زاتكا غير صحيحة. اختر test أو production.');
            }

            // Production sending is disabled by default, particularly on Staging.
            // Enable it deliberately through ZATCA_ALLOW_PRODUCTION_SENDING=true
            // only on the real production server after validating its credentials.
            if ($environment === 'production' && ! config('services.zatca.allow_production_sending', false)) {
                throw new \RuntimeException(
                    'الإرسال إلى Production معطل افتراضيًا. اختبر على Test، ولا تفعّل الإرسال الحقيقي إلا من إعدادات خادم الإنتاج.'
                );
            }

            // Production is a privileged operation. UI visibility is not an authorization boundary.
            if ($environment === 'production') {
                $user = auth()->user();
                if (! $user || ! $user->hasAnyRole(['super_admin', 'admin'])) {
                    throw new \RuntimeException('ليس لديك صلاحية الإرسال إلى بيئة Production. يلزم مدير النظام أو المسؤول الأعلى.');
                }
            }

            // Validate before claiming an attempt, so invalid invoices do not create misleading submission rows.
            $this->assertEligibleInvoiceState((string) $invoice->type, (string) $invoice->status);
            if (! $invoice->exists || ! $invoice->getKey()) {
                throw new \RuntimeException('لا يمكن إرسال فاتورة غير محفوظة في قاعدة البيانات.');
            }

            // Test always uses the separately selected test mode. Production always
            // uses the production mode; there is no implicit production fallback.
            $sendingModeName = $environment === 'production'
                ? 'production'
                : strtolower(trim((string) Setting::get('zatca_test_mode', 'simulation')));

            if ($environment === 'test'
                && ! in_array($sendingModeName, ['simulation', 'compliance'], true)) {
                throw new \RuntimeException('نمط بيئة الاختبار يجب أن يكون simulation أو compliance.');
            }

            // Confirmed mapping from the existing COM integration database:
            // simulation = 0, compliance = 0, production = 2.
            $sendingMode = match ($sendingModeName) {
                'simulation', 'compliance' => 0,
                'production' => 2,
                default => throw new \RuntimeException('نمط إرسال زاتكا غير مدعوم.'),
            };

            // Claim this invoice/environment before making any external API call.
            // The invoice row lock serializes simultaneous clicks for the same invoice.
            $claim = $this->claimSubmissionAttempt($invoice, $environment, $sendingModeName);

            if ($claim['state'] === 'sent') {
                return [
                    'success' => false,
                    'already_sent' => true,
                    'in_progress' => false,
                    'status' => 'sent',
                    'status_code' => $claim['submission']->status_code,
                    'uuid' => $claim['submission']->uuid,
                    'invoice_hash' => $claim['submission']->invoice_hash,
                    'environment' => $environment,
                    'sending_mode' => $sendingModeName,
                    'error' => 'تم إرسال الفاتورة بالفعل إلى هذه البيئة. لن نكرر الإرسال تلقائيًا.',
                    'raw_response' => $claim['submission']->response,
                ];
            }

            if ($claim['state'] === 'uncertain') {
                $legacyUnknown = ! empty($claim['legacy_unknown']);
                $uncertainSubmission = $claim['submission'] ?? null;
                $uncertainMessage = $legacyUnknown
                    ? 'يوجد سجل زاتكا قديم لا يحدد بيئة الإرسال. تمت حماية الفاتورة من إعادة الإرسال حتى تتم مراجعة السجل يدويًا.'
                    : 'نتيجة محاولة زاتكا السابقة غير محسومة. لا تعاود الإرسال حتى تتم مراجعة الحالة والرد المحفوظ.';

                if ($uncertainSubmission && ! empty($uncertainSubmission->error)) {
                    $uncertainMessage .= ' التفاصيل المحفوظة: '.$uncertainSubmission->error;
                }

                return [
                    'success' => false,
                    'already_sent' => false,
                    'in_progress' => false,
                    'uncertain' => true,
                    'needs_reconciliation' => true,
                    'status' => 'uncertain',
                    'status_code' => $uncertainSubmission?->status_code,
                    'uuid' => $uncertainSubmission?->uuid,
                    'invoice_hash' => $uncertainSubmission?->invoice_hash,
                    'environment' => $environment,
                    'sending_mode' => $sendingModeName,
                    'error' => $uncertainMessage,
                    'raw_response' => null,
                ];
            }

            if ($claim['state'] === 'processing') {
                return [
                    'success' => false,
                    'already_sent' => false,
                    'in_progress' => true,
                    'status' => 'processing',
                    'status_code' => $claim['submission']->status_code,
                    'uuid' => $claim['submission']->uuid,
                    'invoice_hash' => $claim['submission']->invoice_hash,
                    'environment' => $environment,
                    'sending_mode' => $sendingModeName,
                    'error' => 'يوجد إرسال جارٍ لهذه الفاتورة في البيئة نفسها. انتظر حتى ينتهي قبل المحاولة مرة أخرى.',
                    'raw_response' => null,
                ];
            }

            $submission = $claim['submission'];
            $config = $this->getEnvironmentConfig($environment, $sendingModeName);
            $rootPath = $config['root_path'];

            if (! is_dir($rootPath) && ! @mkdir($rootPath, 0775, true) && ! is_dir($rootPath)) {
                throw new \RuntimeException('تعذر إنشاء مجلد ملفات زاتكا: '.$rootPath);
            }

            if (! extension_loaded('com_dotnet')) {
                throw new \RuntimeException('PHP COM extension غير مفعّل.');
            }

            $step = '1-create-objects';
            Log::info("ZATCA STEP: {$step}");
            $ubl = new \COM('MyUBLGenerator.ModifiedUBLGenerator');
            $inv = new \COM('ZatcaUBLXMLGenerator.Invoice');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2-basic-fields';
            Log::info("ZATCA STEP: {$step}");
            $inv->id = (string) $invoice->id;
            $inv->IssueDate = $invoice->invoice_date->format('Y-m-d');
            $inv->IssueTime = now()->format('H:i:s');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2b-invoice-type-code';
            Log::info("ZATCA STEP: {$step}");
            $inv->InvoiceTypeCode->id = 388;
            $inv->InvoiceTypeCode->name = '0200000';
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2c-currency';
            Log::info("ZATCA STEP: {$step}");
            $inv->DocumentCurrencyCode = 'SAR';
            $inv->TaxCurrencyCode = 'SAR';
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2d-pih';
            Log::info("ZATCA STEP: {$step}");
            $pih = $this->getPreviousInvoiceHash($invoice, $environment);
            $inv->AdditionalDocumentReferencePIH->EmbeddedDocumentBinaryObject = $pih;
            Log::info("ZATCA STEP OK: {$step}");

            $step = '2e-icv';
            Log::info("ZATCA STEP: {$step}");
            $inv->AdditionalDocumentReferenceICV->UUID = (string) $invoice->id;
            Log::info("ZATCA STEP OK: {$step}");

            $step = '3-supplier-identification';
            Log::info("ZATCA STEP: {$step}");
            // مهم: PartyIdentification.id = السجل التجاري (CR)، مش الرقم الضريبي
            $inv->SupplierParty->PartyIdentification->id = Setting::get('company_cr', '');
            $inv->SupplierParty->PartyIdentification->schemeID = 'CRN';
            Log::info("ZATCA STEP OK: {$step}", ['cr' => Setting::get('company_cr', '')]);

            $step = '3b-supplier-address';
            Log::info("ZATCA STEP: {$step}");
            $inv->SupplierParty->PostalAddress->StreetName = Setting::get('company_street', '');
            $inv->SupplierParty->PostalAddress->AdditionalStreetName = '';
            $inv->SupplierParty->PostalAddress->BuildingNumber = Setting::get('company_building', '');
            $inv->SupplierParty->PostalAddress->PlotIdentification = Setting::get('company_plot', '');
            $inv->SupplierParty->PostalAddress->CityName = Setting::get('company_city', '');
            $inv->SupplierParty->PostalAddress->PostalZone = Setting::get('company_postal', '');
            $inv->SupplierParty->PostalAddress->CountrySubentity = Setting::get('company_district', '');
            $inv->SupplierParty->PostalAddress->CitySubdivisionName = Setting::get('company_district', '');
            $inv->SupplierParty->PostalAddress->Country->IdentificationCode = Setting::get('company_country', 'SA');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '3c-supplier-legal';
            Log::info("ZATCA STEP: {$step}");
            $inv->SupplierParty->PartyLegalEntity->RegistrationName = Setting::get('company_name', '');
            $inv->SupplierParty->PartyTaxScheme->CompanyID = Setting::get('company_tax_number', '');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '4-customer';
            Log::info("ZATCA STEP: {$step}");
            $customer = $invoice->customer;

            $inv->CustomerParty->PartyIdentification->schemeID = 'CRN';

            $inv->CustomerParty->PostalAddress->StreetName = $customer->street_name ?? '';
            $inv->CustomerParty->PostalAddress->AdditionalStreetName = '';
            $inv->CustomerParty->PostalAddress->BuildingNumber = $customer->building_number ?? '';
            $inv->CustomerParty->PostalAddress->PlotIdentification = '';
            $inv->CustomerParty->PostalAddress->CityName = $customer->city_name ?? '';
            $inv->CustomerParty->PostalAddress->PostalZone = $customer->postal_code ?? '';
            $inv->CustomerParty->PostalAddress->CountrySubentity = $customer->district ?? '';
            $inv->CustomerParty->PostalAddress->CitySubdivisionName = $customer->district ?? '';
            $inv->CustomerParty->PostalAddress->Country->IdentificationCode = $customer->country_code ?? 'SA';

            $inv->CustomerParty->PartyLegalEntity->RegistrationName = $customer->name ?? 'عميل نقدي';
            $inv->CustomerParty->PartyTaxScheme->CompanyID = $customer->tax_number ?? '';
            Log::info("ZATCA STEP OK: {$step}");

            $step = '5-invoice-lines';
            Log::info("ZATCA STEP: {$step}");
            $lineIndex = 0;
            foreach ($invoice->items as $item) {
                $lineIndex++;
                Log::info("ZATCA STEP: {$step} - line {$lineIndex}");

                $invLine = new \COM('ZatcaUBLXMLGenerator.InvoiceLine');

                $invLine->InvoiceQuantity = (float) $item->quantity;
                $invLine->Item->name = $item->product_name;

                $vatType = $item->vat_type ?? 'S';
                $vatPercent = (float) ($item->tax_percent ?? 15);

                Log::info('ZATCA LINE DATA', [
                    'line' => $lineIndex,
                    'product_name' => $item->product_name,
                    'vat_type_raw' => $item->vat_type,
                    'vat_type_used' => $vatType,
                    'tax_percent_raw' => $item->tax_percent,
                    'vat_percent_used' => $vatPercent,
                    'discount_amount' => $item->discount_amount,
                    'unit_price' => $item->unit_price,
                ]);

                $invLine->TaxTotal->TaxSubtotal->TaxCategory->id = $vatType;
                $invLine->TaxTotal->TaxSubtotal->TaxCategory->Percent = $vatPercent;
                $invLine->Item->ClassifiedTaxCategory->id = $vatType;
                $invLine->Item->ClassifiedTaxCategory->Percent = $vatPercent;

                if ($vatPercent <= 0) {
                    $invLine->TaxTotal->TaxSubtotal->TaxCategory->TaxExemptionReason = $item->vat_exemption_reason ?? '';
                    $invLine->TaxTotal->TaxSubtotal->TaxCategory->TaxExemptionReasonCode = $item->vat_exemption_reason_code ?? '';
                }

                $invLine->Price->EncludingVat = true;
                $invLine->Price->PriceAmount = (float) $item->unit_price;

                if ((float) $item->discount_amount > 0) {
                    $allowance = new \COM('ZatcaUBLXMLGenerator.AllowanceCharge');
                    $allowance->ChargeIndicator = false;
                    $allowance->AllowanceChargeReason = 'خصم على المنتج';
                    $allowance->amount = (float) $item->discount_amount;
                    $allowance->MultiplierFactorNumeric = 0;
                    $allowance->BaseAmount = 0;
                    // لازم نحدد نفس نسبة ونوع الضريبة بتاعة الصنف على الخصم
                    // عشان نطابق BR-KSA-84 (لازم تكون 15 أو 5 لو النوع S)
                    $allowance->TaxCategory->ID = $vatType;
                    $allowance->TaxCategory->Percent = $vatPercent;
                    $invLine->allowanceCharges->add($allowance);
                }

                $inv->InvoiceLines->add($invLine);
                Log::info("ZATCA STEP OK: {$step} - line {$lineIndex}");
            }

            $step = '6-certificate';
            Log::info("ZATCA STEP: {$step}");
            $certPath = $config['cert_path'];
            $keyPath = $config['key_path'];
            $secretPath = $config['secret_path'];

            if (! $certPath || ! file_exists($certPath)) {
                throw new \RuntimeException('ملف الشهادة (cert.pem) غير موجود. راجع إعدادات النظام → زاتكا');
            }
            if (! $keyPath || ! file_exists($keyPath)) {
                throw new \RuntimeException('ملف المفتاح الخاص (key.pem) غير موجود. راجع إعدادات النظام → زاتكا');
            }

            $inv->CSIDInfo->CertPem = file_get_contents($certPath);
            $inv->CSIDInfo->PrivateKey = file_get_contents($keyPath);
            $secretKey = $secretPath && file_exists($secretPath)
                ? trim((string) file_get_contents($secretPath))
                : (string) $config['secret'];

            // Never log Secret, private-key content, or authentication tokens.
            Log::info('ZATCA CERT CHECK', [
                'environment' => $environment,
                'cert_len' => strlen($inv->CSIDInfo->CertPem),
                'key_len' => strlen($inv->CSIDInfo->PrivateKey),
                'secret_configured' => $secretKey !== '',
                'tax_number_used' => Setting::get('company_tax_number', ''),
                'cr_used' => Setting::get('company_cr', ''),
            ]);
            Log::info("ZATCA STEP OK: {$step}");

            $step = '7-generate-xml';
            Log::info("ZATCA STEP: {$step}");
            $result = $ubl->GenerateInvoiceXML($inv, $rootPath);
            Log::info("ZATCA STEP OK: {$step}");

            if (is_null($result)) {
                throw new \RuntimeException('GenerateInvoiceXML رجع نتيجة فارغة.');
            }
            if (! $result->IsValid) {
                throw new \RuntimeException('فشل توليد XML: '.($result->ErrorMessage ?? 'خطأ غير معروف'));
            }

            $encodedInvoice = $result->EncodedInvoice;
            $invoiceHash = $result->InvoiceHash;
            $uuid = $result->UUID;
            Log::info('ZATCA XML RESULT', ['uuid' => $uuid, 'hash_len' => strlen($invoiceHash ?? '')]);

            $step = '8-api-request-logic-create';
            Log::info("ZATCA STEP: {$step}");
            $apiReqLogic = new \COM('ZatcaUBLXMLGenerator.BLL.ApiRequestLogic');
            Log::info("ZATCA STEP OK: {$step}");

            $step = '8b-request-body';
            Log::info("ZATCA STEP: {$step}");
            $invRequestBody = new \COM('ZatcaUBLXMLGenerator.HelperContracts.InvoiceReportingRequest');
            $invRequestBody->Invoice = $encodedInvoice;
            $invRequestBody->InvoiceHash = $invoiceHash;
            $invRequestBody->UUID = $uuid;
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

                Log::info('ZATCA COMPLIANCE CSID RESPONSE', [
                    'has_error' => ! empty($rawError),
                    'error_message' => $rawError,
                    'request_id' => $safeGet($tokenResponse, 'RequestID'),
                    'disposition_message' => $safeGet($tokenResponse, 'DispositionMessage'),
                    'is_success' => $safeGet($tokenResponse, 'IsSuccess'),
                    'status_code' => $safeGet($tokenResponse, 'StatusCode'),
                    // Authentication token and Secret intentionally omitted.
                ]);

                if (!empty($rawError)) {
                    throw new \RuntimeException('خطأ Compliance CSID: '.$rawError);
                }

                $step = '9b-compliance-invoice-api';
                Log::info("ZATCA STEP: {$step}");
                // From this point, the invoice request may have reached ZATCA.
                $invoiceSubmissionAttempted = true;
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
                // From this point, the invoice request may have reached ZATCA.
                $invoiceSubmissionAttempted = true;
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
            $isSuccess = ZatcaInvoiceSubmission::normalizeApiSuccessFlag(
                $reportingModel->IsSuccess ?? null
            );
            $statusCode = (int) ($reportingModel->StatusCode ?? 0);
            $apiMessage = trim((string) ($reportingModel->ErrorMessage ?? ''));
            $accepted = ZatcaInvoiceSubmission::isAcceptedApiStatus($statusCode);

            // The documented HTTP status is authoritative for 200/202. The COM
            // success flag may be absent or false when a 202 carries warnings.
            if ($isSuccess === null && ! $accepted) {
                throw new \RuntimeException('تعذر تفسير حقل IsSuccess في استجابة زاتكا؛ نتيجة الإرسال غير محسومة.');
            }

            $errorMsg = $accepted
                ? null
                : ($apiMessage !== '' ? $apiMessage : 'خطأ غير معروف');
            Log::info("ZATCA STEP OK: {$step}");

            Log::info('ZATCA Submit', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'environment' => $environment,
                'status_code' => $statusCode,
                'uuid' => $uuid,
                'is_success' => $isSuccess,
                'error' => $errorMsg,
            ]);

            $success = $accepted;
            $uncertainResponse = ! $success
                && ZatcaInvoiceSubmission::isAmbiguousApiResponse($statusCode, (bool) ($isSuccess ?? false));

            if ($uncertainResponse) {
                $errorMsg = 'لم يتمكن النظام من تأكيد قبول الفاتورة أو رفضها بصورة قاطعة. لا تعاود الإرسال تلقائيًا.'
                    .($errorMsg ? ' رد زاتكا: '.$errorMsg : ' كود الاستجابة: '.$statusCode);
            }

            $responseText = json_encode([
                'status_code' => $statusCode,
                'is_success' => $isSuccess,
                'error' => $errorMsg,
                // Preserve the COM API message because a 202 may carry warnings
                // while still meaning the invoice was accepted by ZATCA.
                'api_message' => $apiMessage !== '' ? $apiMessage : null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $submissionStatus = $success
                ? ZatcaInvoiceSubmission::STATUS_SENT
                : ($uncertainResponse
                    ? ZatcaInvoiceSubmission::STATUS_UNCERTAIN
                    : ZatcaInvoiceSubmission::STATUS_FAILED);

            $submission->update([
                'status' => $submissionStatus,
                'status_code' => $statusCode,
                'uuid' => $uuid,
                'invoice_hash' => $invoiceHash,
                'error' => $errorMsg,
                'response' => $responseText,
                'sent_at' => $success ? now() : null,
                'sending_mode' => $sendingModeName,
            ]);

            return [
                'success' => $success,
                'already_sent' => false,
                'in_progress' => false,
                'uncertain' => $uncertainResponse,
                'needs_reconciliation' => $uncertainResponse,
                'status' => $success ? 'sent' : ($uncertainResponse ? 'uncertain' : 'failed'),
                'status_code' => $statusCode,
                'uuid' => $uuid,
                'invoice_hash' => $invoiceHash,
                'environment' => $environment,
                'sending_mode' => $sendingModeName,
                'accepted_with_warnings' => $statusCode === 202,
                'api_message' => $apiMessage !== '' ? $apiMessage : null,
                'error' => $errorMsg,
                'raw_response' => $responseText,
            ];

        } catch (\Throwable $e) {
            $errorMsg = "[{$step}] ".$e->getMessage();
            Log::error("ZATCA FAILED AT STEP: {$step}", [
                'invoice_id' => $invoice->id,
                'environment' => $environment,
                'sending_mode' => $sendingModeName,
                'error' => $e->getMessage(),
            ]);

            $uncertainFailure = $invoiceSubmissionAttempted;
            if ($uncertainFailure) {
                $errorMsg = 'اتصال الإرسال إلى زاتكا بدأ لكن النتيجة النهائية غير مؤكدة. لا تعاود الإرسال تلقائيًا. '.$errorMsg;
            }

            if ($submission instanceof ZatcaInvoiceSubmission) {
                try {
                    $submission->update([
                        'status' => $uncertainFailure
                            ? ZatcaInvoiceSubmission::STATUS_UNCERTAIN
                            : ZatcaInvoiceSubmission::STATUS_FAILED,
                        'status_code' => $statusCode,
                        'uuid' => $uuid,
                        'invoice_hash' => $invoiceHash,
                        'error' => $errorMsg,
                        'response' => $responseText,
                        'sent_at' => null,
                        'sending_mode' => $sendingModeName,
                    ]);
                } catch (\Throwable $persistError) {
                    Log::error('ZATCA submission failure could not be saved', [
                        'invoice_id' => $invoice->id,
                        'environment' => $environment,
                        'error' => $persistError->getMessage(),
                    ]);
                }
            }

            return [
                'success' => false,
                'already_sent' => false,
                'in_progress' => false,
                'uncertain' => $uncertainFailure,
                'needs_reconciliation' => $uncertainFailure,
                'status' => $uncertainFailure ? 'uncertain' : 'failed',
                'status_code' => $statusCode,
                'uuid' => $uuid,
                'invoice_hash' => $invoiceHash,
                'environment' => $environment,
                'sending_mode' => $sendingModeName,
                'error' => $errorMsg,
                'raw_response' => $responseText,
            ];
        }
    }

    /**
     * Atomically claim a submission attempt before any COM/API work begins.
     * Protected so feature tests can verify retry policy without making a remote request.
     */
    protected function claimSubmissionAttempt(
        Invoice $invoice,
        string $environment,
        string $sendingModeName
    ): array {
        return DB::transaction(function () use ($invoice, $environment, $sendingModeName) {
            $lockedInvoice = DB::table('invoices')
                ->where('id', $invoice->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedInvoice) {
                throw new \RuntimeException('الفاتورة غير موجودة.');
            }

            // Revalidate authoritative database state while the invoice row is locked.
            // The model passed to this method may have been loaded before cancellation/editing.
            $this->assertEligibleInvoiceState(
                (string) $lockedInvoice->type,
                (string) $lockedInvoice->status
            );

            $current = ZatcaInvoiceSubmission::query()
                ->where('invoice_id', $invoice->getKey())
                ->where('environment', $environment)
                ->lockForUpdate()
                ->first();

            if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_SENT) {
                return ['state' => 'sent', 'submission' => $current];
            }

            // Older code could mark HTTP 200/202 as failed. Recover that row
            // only when the saved response and database status code agree.
            if ($current && $current->hasAcceptedStoredResponse()) {
                $current->status = ZatcaInvoiceSubmission::STATUS_SENT;
                $current->sent_at = $current->sent_at ?? $current->last_attempt_at ?? now();
                $current->save();

                return ['state' => 'sent', 'submission' => $current, 'recovered_legacy_acceptance' => true];
            }

            // Legacy rows with an unknown environment must not be silently
            // treated as unsent in either Test or Production.
            $legacyUnknown = ZatcaInvoiceSubmission::query()
                ->where('invoice_id', $invoice->getKey())
                ->where('environment', ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN)
                ->where('status', '!=', ZatcaInvoiceSubmission::STATUS_RECONCILED)
                ->lockForUpdate()
                ->first();

            if ($legacyUnknown) {
                return [
                    'state' => 'uncertain',
                    'submission' => $legacyUnknown,
                    'legacy_unknown' => true,
                ];
            }

            if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_UNCERTAIN) {
                return ['state' => 'uncertain', 'submission' => $current];
            }

            // Failed records from the previous implementation may hide a timeout
            // after the remote request. Convert those records instead of retrying.
            if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_FAILED
                && $current->hasAmbiguousOutcome()) {
                $current->status = ZatcaInvoiceSubmission::STATUS_UNCERTAIN;
                $current->error = 'نتيجة المحاولة السابقة غير محسومة؛ تم منع إعادة الإرسال التلقائي. '
                    .(string) ($current->error ?? 'لا يوجد رد موثوق محفوظ من زاتكا.');
                $current->save();

                return ['state' => 'uncertain', 'submission' => $current];
            }

            if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_PROCESSING) {
                if ($current->last_attempt_at
                    && $current->last_attempt_at->gt(now()->subMinutes(30))) {
                    return ['state' => 'processing', 'submission' => $current];
                }

                // A stale processing row is not proof the remote request failed.
                $current->status = ZatcaInvoiceSubmission::STATUS_UNCERTAIN;
                $current->error = 'انتهت مهلة انتظار المحاولة السابقة دون نتيجة مؤكدة؛ لا تعاود الإرسال تلقائيًا.';
                $current->save();

                return ['state' => 'uncertain', 'submission' => $current];
            }
            $current ??= new ZatcaInvoiceSubmission;
            $current->invoice_id = $invoice->getKey();
            $current->environment = $environment;
            $current->status = ZatcaInvoiceSubmission::STATUS_PROCESSING;
            $current->attempts = ((int) $current->attempts) + 1;
            $current->status_code = null;
            $current->error = null;
            $current->response = null;
            $current->last_attempt_at = now();
            $current->sent_at = null;
            $current->sending_mode = $sendingModeName;
            $current->save();

            return ['state' => 'claimed', 'submission' => $current];
        });
    }

    /**
     * Inspect a risky/stale previous attempt without issuing any remote request.
     * This method is intentionally side-effect-free outside local state repair.
     */
    public function protectStaleSubmission(int $invoiceId, string $environment): array
    {
        $environment = strtolower(trim($environment));
        if (! in_array($environment, ['test', 'production'], true)) {
            return ['status' => 'failed', 'error' => 'بيئة زاتكا غير صحيحة.'];
        }

        return DB::transaction(function () use ($invoiceId, $environment) {
            $lockedInvoice = DB::table('invoices')
                ->where('id', $invoiceId)
                ->lockForUpdate()
                ->first();

            if (! $lockedInvoice) {
                return ['status' => 'not_found', 'error' => 'الفاتورة غير موجودة.'];
            }

            if ($lockedInvoice->type !== 'sale'
                || ! in_array($lockedInvoice->status, ['confirmed', 'partial', 'paid'], true)) {
                return ['status' => 'not_eligible', 'error' => 'الفاتورة لم تعد مؤهلة للإرسال إلى زاتكا.'];
            }

            $current = ZatcaInvoiceSubmission::query()
                ->where('invoice_id', $invoiceId)
                ->where('environment', $environment)
                ->lockForUpdate()
                ->first();

            if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_SENT) {
                return ['status' => 'sent', 'error' => 'الفاتورة مرسلة بالفعل إلى هذه البيئة.'];
            }

            // Repair old false-failed rows based on their persisted 200/202 response.
            if ($current && $current->hasAcceptedStoredResponse()) {
                $current->status = ZatcaInvoiceSubmission::STATUS_SENT;
                $current->sent_at = $current->sent_at ?? $current->last_attempt_at ?? now();
                $current->save();

                return ['status' => 'sent', 'error' => 'تم استعادة حالة القبول من استجابة زاتكا المحفوظة.'];
            }

            $legacyUnknown = ZatcaInvoiceSubmission::query()
                ->where('invoice_id', $invoiceId)
                ->where('environment', ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN)
                ->where('status', '!=', ZatcaInvoiceSubmission::STATUS_RECONCILED)
                ->lockForUpdate()
                ->first();

            if ($legacyUnknown) {
                return [
                    'status' => 'uncertain',
                    'error' => 'يوجد سجل زاتكا قديم لا يحدد بيئة الإرسال. راجع السجل يدويًا قبل الإرسال.',
                ];
            }

            if (! $current) {
                return ['status' => 'ready', 'error' => 'لا يوجد سجل إرسال قديم يحتاج إلى حماية.'];
            }

            if ($current->status === ZatcaInvoiceSubmission::STATUS_UNCERTAIN) {
                return [
                    'status' => 'uncertain',
                    'error' => $current->error ?: 'نتيجة محاولة زاتكا السابقة غير محسومة. لا تعاود الإرسال تلقائيًا.',
                ];
            }

            if ($current->status === ZatcaInvoiceSubmission::STATUS_FAILED && $current->hasAmbiguousOutcome()) {
                $current->status = ZatcaInvoiceSubmission::STATUS_UNCERTAIN;
                $current->error = 'نتيجة المحاولة السابقة غير محسومة؛ تم منع إعادة الإرسال التلقائي. '
                    .(string) ($current->error ?? 'لا يوجد رد موثوق محفوظ من زاتكا.');
                $current->save();

                return ['status' => 'uncertain', 'error' => $current->error];
            }

            if ($current->status !== ZatcaInvoiceSubmission::STATUS_PROCESSING) {
                return ['status' => 'ready', 'error' => 'تغيّرت حالة السجل. راجع الحالة الحالية قبل أي إرسال جديد.'];
            }

            if ($current->last_attempt_at && $current->last_attempt_at->gt(now()->subMinutes(30))) {
                return ['status' => 'processing', 'error' => 'المحاولة ما زالت ضمن مهلة الانتظار الحالية.'];
            }

            $current->status = ZatcaInvoiceSubmission::STATUS_UNCERTAIN;
            $current->error = 'انتهت مهلة انتظار المحاولة السابقة دون نتيجة مؤكدة؛ لا تعاود الإرسال تلقائيًا.';
            $current->save();

            return ['status' => 'uncertain', 'error' => $current->error];
        });
    }

    /**
     * Record an audited human review of a ZATCA outcome that cannot safely be retried.
     * This method never calls ZATCA. Accepted/rejected decisions require an official evidence source.
     */
    public function recordManualReconciliation(
        int $submissionId,
        string $outcome,
        string $evidenceType,
        string $evidenceReference,
        string $notes,
        ?string $targetEnvironment = null
    ): array {
        $actor = auth()->user();
        if (! $actor || ! $actor->hasAnyRole(['super_admin', 'admin']) || ! $actor->getAuthIdentifier()) {
            throw new \RuntimeException('توثيق تسوية زاتكا متاح لمدير النظام أو المسؤول الأعلى فقط.');
        }

        $outcome = strtolower(trim($outcome));
        $evidenceType = strtolower(trim($evidenceType));
        $evidenceReference = trim($evidenceReference);
        $notes = trim($notes);
        $targetEnvironment = $targetEnvironment === null ? null : strtolower(trim($targetEnvironment));

        if (! in_array($outcome, ['accepted', 'rejected', 'unresolved'], true)) {
            throw new \InvalidArgumentException('نتيجة التسوية غير صحيحة.');
        }
        if (! in_array($evidenceType, ['zatca_portal', 'official_response', 'other'], true)) {
            throw new \InvalidArgumentException('مصدر الدليل غير صحيح.');
        }
        if (strlen($evidenceReference) < 3 || strlen($evidenceReference) > 255) {
            throw new \InvalidArgumentException('أدخل مرجع دليل واضحًا من 3 إلى 255 حرفًا.');
        }
        if (strlen($notes) < 10 || strlen($notes) > 3000) {
            throw new \InvalidArgumentException('الملاحظات مطلوبة ويجب أن تكون بين 10 و3000 حرف.');
        }
        if ($outcome !== 'unresolved' && $evidenceType === 'other') {
            throw new \InvalidArgumentException('قرار القبول أو الرفض يتطلب دليلًا من بوابة زاتكا أو استجابة رسمية.');
        }

        $actorId = (int) $actor->getAuthIdentifier();

        return DB::transaction(function () use (
            $submissionId, $outcome, $evidenceType, $evidenceReference, $notes,
            $targetEnvironment, $actorId
        ) {
            $submission = ZatcaInvoiceSubmission::query()
                ->whereKey($submissionId)
                ->lockForUpdate()
                ->first();

            if (! $submission) {
                throw new \RuntimeException('سجل إرسال زاتكا غير موجود.');
            }

            $isUnknownEnvironment = $submission->environment === ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN;
            $isStaleProcessing = $submission->status === ZatcaInvoiceSubmission::STATUS_PROCESSING
                && (! $submission->last_attempt_at || $submission->last_attempt_at->lte(now()->subMinutes(30)));
            $isAmbiguousFailure = $submission->status === ZatcaInvoiceSubmission::STATUS_FAILED
                && $submission->hasAmbiguousOutcome();
            $isEligible = ($isUnknownEnvironment && $submission->status !== ZatcaInvoiceSubmission::STATUS_RECONCILED)
                || $submission->status === ZatcaInvoiceSubmission::STATUS_UNCERTAIN
                || $isStaleProcessing
                || $isAmbiguousFailure;

            if (! $isEligible) {
                throw new \RuntimeException('هذا السجل لا يحتاج إلى تسوية يدوية أو لم يعد مؤهلًا لها.');
            }

            $resolvedEnvironment = $submission->environment;
            $targetSubmission = null;

            if ($isUnknownEnvironment && $outcome !== 'unresolved') {
                if (! in_array($targetEnvironment, ['test', 'production'], true)) {
                    throw new \InvalidArgumentException('حدد بيئة الإرسال المؤكدة من الدليل: Test أو Production.');
                }
                $resolvedEnvironment = $targetEnvironment;
                $targetSubmission = ZatcaInvoiceSubmission::query()
                    ->where('invoice_id', $submission->invoice_id)
                    ->where('environment', $targetEnvironment)
                    ->lockForUpdate()
                    ->first();

                if ($targetSubmission
                    && $outcome === 'rejected'
                    && $targetSubmission->status === ZatcaInvoiceSubmission::STATUS_SENT) {
                    throw new \RuntimeException('يوجد سجل مرسل بالفعل في البيئة المحددة؛ راجع التعارض قبل اعتماد الرفض.');
                }

                if ($targetSubmission
                    && $targetSubmission->status === ZatcaInvoiceSubmission::STATUS_PROCESSING
                    && $targetSubmission->last_attempt_at
                    && $targetSubmission->last_attempt_at->gt(now()->subMinutes(30))) {
                    throw new \RuntimeException('هناك إرسال جارٍ في البيئة المحددة. انتظر نهايته قبل التسوية.');
                }
            } elseif (! $isUnknownEnvironment && $targetEnvironment !== null
                && $targetEnvironment !== '' && $targetEnvironment !== $submission->environment) {
                throw new \InvalidArgumentException('لا يمكن تغيير بيئة سجل معلوم أثناء التسوية.');
            }

            $capture = static function (ZatcaInvoiceSubmission $row): array {
                return [
                    'status' => $row->status,
                    'environment' => $row->environment,
                    'status_code' => $row->status_code,
                    'response' => $row->response,
                ];
            };

            $applyOutcome = static function (ZatcaInvoiceSubmission $row, string $decision, string $reviewNotes): void {
                if ($decision === 'accepted') {
                    $row->status = ZatcaInvoiceSubmission::STATUS_SENT;
                    // A manual review is not an HTTP response. Preserve the old code/response in the audit event.
                    $row->status_code = null;
                    $row->response = null;
                    $row->error = null;
                } elseif ($decision === 'rejected') {
                    $row->status = ZatcaInvoiceSubmission::STATUS_REJECTED;
                    $row->status_code = null;
                    $row->response = null;
                    $row->error = 'تم توثيق رفض زاتكا يدويًا. راجع الدليل وسبب الرفض قبل إعادة الإرسال.';
                } else {
                    $row->status = ZatcaInvoiceSubmission::STATUS_UNCERTAIN;
                    $row->error = 'لا تزال نتيجة زاتكا غير محسومة بعد المراجعة اليدوية: '.$reviewNotes;
                }
                $row->save();
            };

            $createEvent = function (
                ZatcaInvoiceSubmission $row,
                array $before,
                string $newStatus,
                string $newEnvironment,
                ?string $targetEnv
            ) use ($outcome, $evidenceType, $evidenceReference, $notes, $actorId): ZatcaSubmissionReconciliation {
                $event = new ZatcaSubmissionReconciliation;
                $event->submission_id = $row->id;
                $event->outcome = $outcome;
                $event->previous_status = $before['status'];
                $event->new_status = $newStatus;
                $event->previous_environment = $before['environment'];
                $event->new_environment = $newEnvironment;
                $event->target_environment = $targetEnv;
                $event->previous_status_code = $before['status_code'];
                $event->previous_response = $before['response'];
                $event->evidence_type = $evidenceType;
                $event->evidence_reference = $evidenceReference;
                $event->notes = $notes;
                $event->reconciled_by = $actorId;
                $event->reconciled_at = now();
                $event->save();

                return $event;
            };

            $submissionBefore = $capture($submission);

            if ($outcome === 'unresolved') {
                $applyOutcome($submission, 'unresolved', $notes);
                $event = $createEvent(
                    $submission,
                    $submissionBefore,
                    $submission->status,
                    $submission->environment,
                    $resolvedEnvironment === ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN ? null : $resolvedEnvironment
                );

                return [
                    'status' => 'uncertain',
                    'submission_id' => $submission->id,
                    'audit_event_id' => $event->id,
                    'message' => 'تم توثيق المراجعة، لكن النتيجة لا تزال غير محسومة. ما زالت إعادة الإرسال محظورة.',
                ];
            }

            if ($isUnknownEnvironment && $targetSubmission) {
                $targetBefore = $capture($targetSubmission);

                if (! ($outcome === 'accepted' && $targetSubmission->status === ZatcaInvoiceSubmission::STATUS_SENT)) {
                    $applyOutcome($targetSubmission, $outcome, $notes);
                }

                // Retain the legacy unknown row as an audited archive, but do not let it block
                // the now-identified environment forever. It remains visible in the audit history.
                $submission->status = ZatcaInvoiceSubmission::STATUS_RECONCILED;
                $submission->error = 'تمت تسوية سجل البيئة غير المعروفة بدليل موثق إلى البيئة '.$targetEnvironment.'.';
                $submission->save();

                $legacyEvent = $createEvent(
                    $submission,
                    $submissionBefore,
                    $submission->status,
                    $submission->environment,
                    $targetEnvironment
                );
                $targetEvent = $createEvent(
                    $targetSubmission,
                    $targetBefore,
                    $targetSubmission->status,
                    $targetSubmission->environment,
                    $targetEnvironment
                );

                return [
                    'status' => $targetSubmission->status,
                    'submission_id' => $targetSubmission->id,
                    'audit_event_id' => $targetEvent->id,
                    'archived_legacy_event_id' => $legacyEvent->id,
                    'message' => 'تم حفظ دليل التسوية في السجل الحالي وسجل البيئة القديمة.',
                ];
            }

            if ($isUnknownEnvironment) {
                $submission->environment = $resolvedEnvironment;
            }
            $applyOutcome($submission, $outcome, $notes);
            $event = $createEvent(
                $submission,
                $submissionBefore,
                $submission->status,
                $submission->environment,
                $resolvedEnvironment
            );

            return [
                'status' => $submission->status,
                'submission_id' => $submission->id,
                'audit_event_id' => $event->id,
                'message' => $outcome === 'accepted'
                    ? 'تم توثيق القبول. لن تتكرر عملية الإرسال لهذه البيئة.'
                    : 'تم توثيق الرفض. يمكن بدء محاولة جديدة بعد معالجة سبب الرفض.',
            ];
        });
    }

    private function assertEligibleInvoiceState(string $type, string $status): void
    {
        if ($type !== 'sale') {
            throw new \RuntimeException('تكامل زاتكا متاح لفواتير البيع فقط.');
        }

        if (! in_array($status, ['confirmed', 'partial', 'paid'], true)) {
            throw new \RuntimeException("لا يمكن إرسال فاتورة بالحالة {$status} إلى زاتكا.");
        }
    }

    /**
     * Resolve an isolated environment profile. Legacy credentials are read only
     * when their old sending mode belongs to the same environment.
     */
    protected function getEnvironmentConfig(string $environment, string $sendingModeName): array
    {
        $prefix = $environment === 'test' ? 'zatca_test' : 'zatca_production';
        $legacyMode = strtolower(trim((string) Setting::get('zatca_sending_mode', 'simulation')));
        $legacyMatchesEnvironment = $environment === 'production'
            ? $legacyMode === 'production'
            : in_array($legacyMode, ['simulation', 'compliance'], true);

        $read = function (string $suffix, ?string $legacyKey = null, string $default = '') use (
            $prefix,
            $legacyMatchesEnvironment
        ): string {
            $key = $prefix.'_'.$suffix;

            // If the new setting exists, honor it even when explicitly blank.
            if (Setting::where('key', $key)->exists()) {
                $value = trim((string) Setting::get($key, ''));

                return $value !== '' ? $value : $default;
            }

            if ($legacyMatchesEnvironment && $legacyKey !== null) {
                $legacyValue = trim((string) Setting::get($legacyKey, ''));
                if ($legacyValue !== '') {
                    return $legacyValue;
                }
            }

            return $default;
        };

        return [
            'sending_mode' => $sendingModeName,
            'cert_path' => $read('cert_path', 'zatca_cert_path'),
            'key_path' => $read('key_path', 'zatca_key_path'),
            'secret' => $read('secret', 'zatca_secret'),
            'secret_path' => $read('secret_path', 'zatca_secret_path'),
            'root_path' => $read(
                'root_path',
                'zatca_root_path',
                $environment === 'test'
                    ? (trim((string) Setting::get('zatca_root_path', '')) ?: 'D:\\ZatcaIntegration')
                    : storage_path('app/zatca/production')
            ),
        ];
    }

    protected function getPreviousInvoiceHash(Invoice $current, string $environment): string
    {
        $initial = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';

        $previous = DB::table('zatca_invoice_submissions as submissions')
            ->join('invoices', 'invoices.id', '=', 'submissions.invoice_id')
            ->where('submissions.environment', $environment)
            ->where('submissions.status', ZatcaInvoiceSubmission::STATUS_SENT)
            ->whereNotNull('submissions.invoice_hash')
            ->where('invoices.id', '<', $current->id)
            ->where('invoices.type', $current->type)
            ->orderByDesc('invoices.id')
            ->value('submissions.invoice_hash');

        return $previous ?: $initial;
    }

    public function smokeTest(): array
    {
        if (! extension_loaded('com_dotnet')) {
            return ['error' => 'PHP COM extension غير مفعّل'];
        }
        $tests = [
            'Invoice' => 'ZatcaUBLXMLGenerator.Invoice',
            'UBL' => 'MyUBLGenerator.ModifiedUBLGenerator',
            'InvLine' => 'ZatcaUBLXMLGenerator.InvoiceLine',
            'ApiRequestLogic' => 'ZatcaUBLXMLGenerator.BLL.ApiRequestLogic',
        ];
        $results = [];
        foreach ($tests as $label => $progId) {
            try {
                new \COM($progId);
                $results['com'][$label] = '✅ OK';
            } catch (\Throwable $e) {
                $results['com'][$label] = '❌ '.$e->getMessage();
            }
        }
        foreach (['test', 'production'] as $environment) {
            $mode = $environment === 'production'
                ? 'production'
                : strtolower(trim((string) Setting::get('zatca_test_mode', 'simulation')));
            $config = $this->getEnvironmentConfig($environment, $mode);
            $results['environments'][$environment] = [
                'sending_mode' => $mode,
                'cert_path' => $config['cert_path'],
                'cert_exists' => $config['cert_path'] !== '' && file_exists($config['cert_path']),
                'key_path' => $config['key_path'],
                'key_exists' => $config['key_path'] !== '' && file_exists($config['key_path']),
                'secret_configured' => $config['secret'] !== ''
                    || ($config['secret_path'] !== '' && file_exists($config['secret_path'])),
                'root_path' => $config['root_path'],
            ];
        }

        return $results;
    }
}
