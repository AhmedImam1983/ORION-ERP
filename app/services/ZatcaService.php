<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\ZatcaInvoiceSubmission;
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

        try {
            $environment = strtolower(trim($environment));
            if (!in_array($environment, ['test', 'production'], true)) {
                throw new \InvalidArgumentException('بيئة زاتكا غير صحيحة. اختر test أو production.');
            }

            // Production sending is disabled by default, particularly on Staging.
            // Enable it deliberately through ZATCA_ALLOW_PRODUCTION_SENDING=true
            // only on the real production server after validating its credentials.
            if ($environment === 'production' && !config('services.zatca.allow_production_sending', false)) {
                throw new \RuntimeException(
                    'الإرسال إلى Production معطل افتراضيًا. اختبر على Test، ولا تفعّل الإرسال الحقيقي إلا من إعدادات خادم الإنتاج.'
                );
            }

            // Test always uses the separately selected test mode. Production always
            // uses the production mode; there is no implicit production fallback.
            $sendingModeName = $environment === 'production'
                ? 'production'
                : strtolower(trim((string) Setting::get('zatca_test_mode', 'simulation')));

            if ($environment === 'test'
                && !in_array($sendingModeName, ['simulation', 'compliance'], true)) {
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
            $claim = DB::transaction(function () use ($invoice, $environment, $sendingModeName) {
                $lockedInvoice = DB::table('invoices')
                    ->where('id', $invoice->getKey())
                    ->lockForUpdate()
                    ->first();

                if (!$lockedInvoice) {
                    throw new \RuntimeException('الفاتورة غير موجودة.');
                }

                $current = ZatcaInvoiceSubmission::query()
                    ->where('invoice_id', $invoice->getKey())
                    ->where('environment', $environment)
                    ->lockForUpdate()
                    ->first();

                if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_SENT) {
                    return ['state' => 'sent', 'submission' => $current];
                }

                if (
                    $current
                    && $current->status === ZatcaInvoiceSubmission::STATUS_PROCESSING
                    && $current->last_attempt_at
                    && $current->last_attempt_at->gt(now()->subMinutes(30))
                ) {
                    return ['state' => 'processing', 'submission' => $current];
                }

                // A processing record older than 30 minutes is treated as stale,
                // so a crashed request cannot permanently block future retries.
                $current ??= new ZatcaInvoiceSubmission();
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

            if (!is_dir($rootPath) && !@mkdir($rootPath, 0775, true) && !is_dir($rootPath)) {
                throw new \RuntimeException('تعذر إنشاء مجلد ملفات زاتكا: ' . $rootPath);
            }

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
            $certPath   = $config['cert_path'];
            $keyPath    = $config['key_path'];
            $secretPath = $config['secret_path'];

            if (!$certPath || !file_exists($certPath)) {
                throw new \RuntimeException('ملف الشهادة (cert.pem) غير موجود. راجع إعدادات النظام → زاتكا');
            }
            if (!$keyPath || !file_exists($keyPath)) {
                throw new \RuntimeException('ملف المفتاح الخاص (key.pem) غير موجود. راجع إعدادات النظام → زاتكا');
            }

            $inv->CSIDInfo->CertPem     = file_get_contents($certPath);
            $inv->CSIDInfo->PrivateKey  = file_get_contents($keyPath);
            $secretKey = $secretPath && file_exists($secretPath)
                ? trim((string) file_get_contents($secretPath))
                : (string) $config['secret'];

            // Never log Secret, private-key content, or authentication tokens.
            Log::info("ZATCA CERT CHECK", [
                'environment'     => $environment,
                'cert_len'        => strlen($inv->CSIDInfo->CertPem),
                'key_len'         => strlen($inv->CSIDInfo->PrivateKey),
                'secret_configured' => $secretKey !== '',
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
                    'has_error'             => !empty($rawError),
                    'error_message'         => $rawError,
                    'request_id'            => $safeGet($tokenResponse, 'RequestID'),
                    'disposition_message'   => $safeGet($tokenResponse, 'DispositionMessage'),
                    'is_success'             => $safeGet($tokenResponse, 'IsSuccess'),
                    'status_code'            => $safeGet($tokenResponse, 'StatusCode'),
                    // Authentication token and Secret intentionally omitted.
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

            $responseText = json_encode([
                'status_code' => $statusCode,
                'is_success' => $isSuccess,
                'error' => $errorMsg,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $submission->update([
                'status' => $success
                    ? ZatcaInvoiceSubmission::STATUS_SENT
                    : ZatcaInvoiceSubmission::STATUS_FAILED,
                'status_code' => $statusCode,
                'uuid' => $uuid,
                'invoice_hash' => $invoiceHash,
                'error' => $errorMsg,
                'response' => $responseText,
                'sent_at' => $success ? now() : null,
                'sending_mode' => $sendingModeName,
            ]);

            return [
                'success'      => $success,
                'already_sent' => false,
                'in_progress'  => false,
                'status'       => $success ? 'sent' : 'failed',
                'status_code'  => $statusCode,
                'uuid'         => $uuid,
                'invoice_hash' => $invoiceHash,
                'environment'  => $environment,
                'sending_mode' => $sendingModeName,
                'error'        => $errorMsg,
                'raw_response' => $responseText,
            ];

        } catch (\Throwable $e) {
            $errorMsg = "[{$step}] " . $e->getMessage();
            Log::error("ZATCA FAILED AT STEP: {$step}", [
                'invoice_id' => $invoice->id,
                'environment' => $environment,
                'sending_mode' => $sendingModeName,
                'error' => $e->getMessage(),
            ]);

            if ($submission instanceof ZatcaInvoiceSubmission) {
                try {
                    $submission->update([
                        'status' => ZatcaInvoiceSubmission::STATUS_FAILED,
                        'status_code' => $statusCode,
                        'uuid' => $uuid,
                        'invoice_hash' => $invoiceHash,
                        'error' => $errorMsg,
                        'response' => null,
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
                'success'      => false,
                'already_sent' => false,
                'in_progress'  => false,
                'status'       => 'failed',
                'status_code'  => $statusCode,
                'uuid'         => $uuid,
                'invoice_hash' => $invoiceHash,
                'environment'  => $environment,
                'sending_mode' => $sendingModeName,
                'error'        => $errorMsg,
                'raw_response' => null,
            ];
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
            $key = $prefix . '_' . $suffix;

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