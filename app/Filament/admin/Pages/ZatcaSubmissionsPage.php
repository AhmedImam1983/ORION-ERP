<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\ZatcaInvoiceSubmission;
use App\Services\ZatcaService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ZatcaSubmissionsPage extends Page
{
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'إرسال ومتابعة زاتكا';
    protected static ?string $title = 'إرسال ومتابعة فواتير زاتكا';
    protected static ?string $slug = 'zatca-submissions';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.admin.pages.zatca-submissions-page';

    public string $environment = 'test';
    public string $statusFilter = 'all';
    public string $search = '';
    public ?int $confirmingInvoiceId = null;

    public ?int $reconcilingSubmissionId = null;
    public string $reconciliationOutcome = 'unresolved';
    public string $reconciliationEvidenceType = 'zatca_portal';
    public string $reconciliationEvidenceReference = '';
    public string $reconciliationNotes = '';
    public string $reconciliationTargetEnvironment = 'test';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant', 'cashier']) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات · Sales';
    }

    public function updatedEnvironment(): void
    {
        if (!in_array($this->environment, ['test', 'production'], true)) {
            $this->environment = 'test';
        }

        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        if (!in_array($this->statusFilter, ['all', 'not_sent', 'sent', 'failed', 'rejected', 'processing', 'pending', 'uncertain'], true)) {
            $this->statusFilter = 'all';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getCurrencySymbolProperty(): string
    {
        return (string) Setting::get('currency_symbol', 'ر.س');
    }

    protected function eligibleInvoicesQuery(): Builder
    {
        return Invoice::query()
            ->where('invoices.type', 'sale')
            ->whereIn('invoices.status', ['confirmed', 'partial', 'paid']);
    }

    protected function invoicesWithEnvironmentQuery(): Builder
    {
        $environment = $this->environment === 'production' ? 'production' : 'test';

        $query = $this->eligibleInvoicesQuery()
            ->leftJoin('zatca_invoice_submissions as zatca_current', function ($join) use ($environment) {
                $join->on('zatca_current.invoice_id', '=', 'invoices.id')
                    ->where('zatca_current.environment', '=', $environment);
            })
            ->leftJoin('zatca_invoice_submissions as zatca_legacy_unknown', function ($join) {
                $join->on('zatca_legacy_unknown.invoice_id', '=', 'invoices.id')
                    ->where('zatca_legacy_unknown.environment', '=', 'unknown')
                    ->where('zatca_legacy_unknown.status', '!=', ZatcaInvoiceSubmission::STATUS_RECONCILED);
            })
            ->leftJoinSub(
                DB::table('zatca_submission_reconciliations')
                    ->select('submission_id', DB::raw('MAX(id) as latest_id'))
                    ->groupBy('submission_id'),
                'zatca_latest_reconciliation_key',
                function ($join) {
                    $join->on('zatca_latest_reconciliation_key.submission_id', '=', 'zatca_current.id');
                }
            )
            ->leftJoin('zatca_submission_reconciliations as zatca_latest_reconciliation', 'zatca_latest_reconciliation.id', '=', 'zatca_latest_reconciliation_key.latest_id')
            ->leftJoin('users as zatca_reconciler', 'zatca_reconciler.id', '=', 'zatca_latest_reconciliation.reconciled_by')
            ->select([
                'invoices.*',
                'zatca_current.id as zatca_page_submission_id',
                'zatca_current.status as zatca_page_status',
                'zatca_current.attempts as zatca_page_attempts',
                'zatca_current.status_code as zatca_page_status_code',
                'zatca_current.response as zatca_page_response',
                'zatca_current.uuid as zatca_page_uuid',
                'zatca_current.last_attempt_at as zatca_page_last_attempt_at',
                'zatca_current.sent_at as zatca_page_sent_at',
                'zatca_current.error as zatca_page_error',
                'zatca_current.sending_mode as zatca_page_sending_mode',
                'zatca_legacy_unknown.id as zatca_page_legacy_unknown_id',
                'zatca_legacy_unknown.error as zatca_page_legacy_unknown_error',
                'zatca_latest_reconciliation.outcome as zatca_page_reconciliation_outcome',
                'zatca_latest_reconciliation.evidence_type as zatca_page_reconciliation_evidence_type',
                'zatca_latest_reconciliation.evidence_reference as zatca_page_reconciliation_evidence_reference',
                'zatca_latest_reconciliation.notes as zatca_page_reconciliation_notes',
                'zatca_latest_reconciliation.reconciled_at as zatca_page_reconciled_at',
                'zatca_reconciler.name as zatca_page_reconciled_by_name',
            ]);

        if (trim($this->search) !== '') {
            $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], trim($this->search)) . '%';
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('invoices.invoice_number', 'like', $search)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('name', 'like', $search));
            });
        }

        if ($this->statusFilter === 'not_sent') {
            $query->where(function (Builder $inner) {
                $inner->whereNull('zatca_current.id')
                    ->orWhere('zatca_current.status', '!=', ZatcaInvoiceSubmission::STATUS_SENT);
            });
        } elseif ($this->statusFilter === 'uncertain') {
            $query->where(function (Builder $inner) {
                $inner->where('zatca_current.status', ZatcaInvoiceSubmission::STATUS_UNCERTAIN)
                    ->orWhere(function (Builder $legacy) {
                        $legacy->whereNotNull('zatca_legacy_unknown.id')
                            ->where(function (Builder $current) {
                                $current->whereNull('zatca_current.id')
                                    ->orWhere('zatca_current.status', '!=', ZatcaInvoiceSubmission::STATUS_SENT);
                            });
                    });
            });
        } elseif (in_array($this->statusFilter, ['sent', 'failed', 'rejected', 'processing', 'pending'], true)) {
            $query->where('zatca_current.status', $this->statusFilter);
        }

        return $query->orderByDesc('invoices.invoice_date')->orderByDesc('invoices.id');
    }

    public function getInvoicesProperty()
    {
        return $this->invoicesWithEnvironmentQuery()
            ->with('customer')
            ->paginate(15);
    }

    public function getStatsProperty(): array
    {
        $eligible = $this->eligibleInvoicesQuery()->count('invoices.id');
        $environment = $this->environment === 'production' ? 'production' : 'test';

        $counts = $this->eligibleInvoicesQuery()
            ->leftJoin('zatca_invoice_submissions as zatca_stats', function ($join) use ($environment) {
                $join->on('zatca_stats.invoice_id', '=', 'invoices.id')
                    ->where('zatca_stats.environment', '=', $environment);
            })
            ->selectRaw("COALESCE(zatca_stats.status, 'not_sent') as submission_status, COUNT(DISTINCT invoices.id) as total")
            ->groupBy('submission_status')
            ->pluck('total', 'submission_status');

        $sent = (int) ($counts[ZatcaInvoiceSubmission::STATUS_SENT] ?? 0);
        $uncertain = $this->eligibleInvoicesQuery()
            ->leftJoin('zatca_invoice_submissions as zatca_uncertain_current', function ($join) use ($environment) {
                $join->on('zatca_uncertain_current.invoice_id', '=', 'invoices.id')
                    ->where('zatca_uncertain_current.environment', '=', $environment);
            })
            ->leftJoin('zatca_invoice_submissions as zatca_uncertain_legacy', function ($join) {
                $join->on('zatca_uncertain_legacy.invoice_id', '=', 'invoices.id')
                    ->where('zatca_uncertain_legacy.environment', '=', 'unknown')
                    ->where('zatca_uncertain_legacy.status', '!=', ZatcaInvoiceSubmission::STATUS_RECONCILED);
            })
            ->where(function (Builder $inner) {
                $inner->where('zatca_uncertain_current.status', ZatcaInvoiceSubmission::STATUS_UNCERTAIN)
                    ->orWhere(function (Builder $legacy) {
                        $legacy->whereNotNull('zatca_uncertain_legacy.id')
                            ->where(function (Builder $current) {
                                $current->whereNull('zatca_uncertain_current.id')
                                    ->orWhere('zatca_uncertain_current.status', '!=', ZatcaInvoiceSubmission::STATUS_SENT);
                            });
                    });
            })
            ->distinct()
            ->count('invoices.id');

        return [
            'eligible' => (int) $eligible,
            'sent' => $sent,
            'failed' => (int) ($counts[ZatcaInvoiceSubmission::STATUS_FAILED] ?? 0),
            'processing' => (int) ($counts[ZatcaInvoiceSubmission::STATUS_PROCESSING] ?? 0),
            'uncertain' => (int) $uncertain,
            'not_sent' => max(0, (int) $eligible - $sent),
        ];
    }

    /** Open confirmation only when the stored state is safe to attempt. */
    public function requestSendConfirmation(int $invoiceId): void
    {
        $invoice = $this->eligibleInvoicesQuery()
            ->where('invoices.id', $invoiceId)
            ->first(['invoices.id']);

        if (!$invoice) {
            Notification::make()
                ->title('الفاتورة غير متاحة للإرسال')
                ->body('تأكد أن الفاتورة بيع مؤكدة أو مدفوعة جزئيًا أو بالكامل.')
                ->warning()
                ->send();
            return;
        }

        $environment = $this->environment === 'production' ? 'production' : 'test';
        $current = ZatcaInvoiceSubmission::query()
            ->where('invoice_id', $invoiceId)
            ->where('environment', $environment)
            ->first();
        $legacyUnknownExists = ZatcaInvoiceSubmission::query()
            ->where('invoice_id', $invoiceId)
            ->where('environment', ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN)
            ->where('status', '!=', ZatcaInvoiceSubmission::STATUS_RECONCILED)
            ->exists();

        if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_SENT) {
            Notification::make()
                ->title('الفاتورة مرسلة بالفعل')
                ->body('لن نكرر الإرسال إلى البيئة نفسها.')
                ->warning()
                ->send();
            return;
        }

        if ($legacyUnknownExists
            || ($current && $current->status === ZatcaInvoiceSubmission::STATUS_UNCERTAIN)
            || ($current && $current->status === ZatcaInvoiceSubmission::STATUS_FAILED && $current->hasAmbiguousOutcome())
            || ($current && $current->status === ZatcaInvoiceSubmission::STATUS_PROCESSING
                && (!$current->last_attempt_at || $current->last_attempt_at->lte(now()->subMinutes(30))))) {
            $this->inspectSubmissionState($invoiceId);
            return;
        }

        if ($current && $current->status === ZatcaInvoiceSubmission::STATUS_PROCESSING) {
            Notification::make()
                ->title('الإرسال قيد التنفيذ')
                ->body('انتظر انتهاء المحاولة الحالية قبل اتخاذ أي إجراء.')
                ->warning()
                ->send();
            return;
        }

        $this->confirmingInvoiceId = (int) $invoice->id;
    }

    /** Repair a stale local state only; this action never sends a request to ZATCA. */
    public function inspectSubmissionState(int $invoiceId): void
    {
        if (!$this->eligibleInvoicesQuery()->where('invoices.id', $invoiceId)->exists()) {
            Notification::make()
                ->title('الفاتورة غير متاحة للمراجعة')
                ->warning()
                ->send();
            return;
        }

        $environment = $this->environment === 'production' ? 'production' : 'test';
        $result = app(ZatcaService::class)->protectStaleSubmission($invoiceId, $environment);

        $status = $result['status'] ?? 'failed';
        $message = $result['error'] ?? 'لم تتغير حالة السجل.';

        if ($status === 'uncertain') {
            Notification::make()
                ->title('نتيجة الإرسال غير محسومة — لا تعاود الإرسال')
                ->body($message)
                ->warning()
                ->send();
            return;
        }

        if ($status === 'processing') {
            Notification::make()
                ->title('الإرسال ما زال قيد التنفيذ')
                ->body($message)
                ->info()
                ->send();
            return;
        }

        Notification::make()
            ->title($status === 'sent' ? 'الفاتورة مرسلة بالفعل' : 'تم فحص حالة المحاولة')
            ->body($message)
            ->warning()
            ->send();
    }

    private function canManageManualReconciliation(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function requestManualReconciliation(int $submissionId): void
    {
        if (!$this->canManageManualReconciliation()) {
            Notification::make()->title('ليس لديك صلاحية توثيق تسوية زاتكا')->danger()->send();
            return;
        }

        $submission = ZatcaInvoiceSubmission::query()->find($submissionId);
        if (!$submission || !$this->eligibleInvoicesQuery()->where('invoices.id', $submission->invoice_id)->exists()) {
            Notification::make()->title('سجل زاتكا غير متاح للمراجعة')->warning()->send();
            return;
        }

        $unknownEnvironment = $submission->environment === ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN;
        $staleProcessing = $submission->status === ZatcaInvoiceSubmission::STATUS_PROCESSING
            && (!$submission->last_attempt_at || $submission->last_attempt_at->lte(now()->subMinutes(30)));
        $ambiguousFailed = $submission->status === ZatcaInvoiceSubmission::STATUS_FAILED
            && $submission->hasAmbiguousOutcome();
        if (!$unknownEnvironment
            && $submission->status !== ZatcaInvoiceSubmission::STATUS_UNCERTAIN
            && !$staleProcessing
            && !$ambiguousFailed) {
            Notification::make()->title('السجل لا يحتاج إلى تسوية يدوية')->warning()->send();
            return;
        }

        $this->reconcilingSubmissionId = (int) $submission->id;
        $this->reconciliationOutcome = 'unresolved';
        $this->reconciliationEvidenceType = 'zatca_portal';
        $this->reconciliationEvidenceReference = '';
        $this->reconciliationNotes = '';
        $this->reconciliationTargetEnvironment = in_array($submission->environment, ['test', 'production'], true)
            ? $submission->environment
            : ($this->environment === 'production' ? 'production' : 'test');
        $this->resetValidation();
    }

    public function getReconcilingSubmissionProperty(): ?ZatcaInvoiceSubmission
    {
        if (!$this->reconcilingSubmissionId || !$this->canManageManualReconciliation()) {
            return null;
        }

        $submission = ZatcaInvoiceSubmission::query()
            ->with('reconciliations.reconciledBy')
            ->find($this->reconcilingSubmissionId);

        if (!$submission || !$this->eligibleInvoicesQuery()->where('invoices.id', $submission->invoice_id)->exists()) {
            return null;
        }

        return $submission;
    }

    public function getReconciliationHistoryProperty()
    {
        $submission = $this->reconcilingSubmission;
        if (!$submission) {
            return collect();
        }

        return $submission->reconciliations()
            ->with('reconciledBy')
            ->orderByDesc('id')
            ->get();
    }

    public function cancelManualReconciliation(): void
    {
        $this->reconcilingSubmissionId = null;
        $this->resetValidation();
    }

    public function saveManualReconciliation(): void
    {
        if (!$this->canManageManualReconciliation()) {
            Notification::make()->title('ليس لديك صلاحية توثيق تسوية زاتكا')->danger()->send();
            return;
        }

        $submission = $this->reconcilingSubmission;
        if (!$submission) {
            Notification::make()->title('سجل زاتكا غير متاح للمراجعة')->danger()->send();
            $this->reconcilingSubmissionId = null;
            return;
        }

        $rules = [
            'reconciliationOutcome' => 'required|in:accepted,rejected,unresolved',
            'reconciliationEvidenceType' => 'required|in:zatca_portal,official_response,other',
            'reconciliationEvidenceReference' => 'required|string|min:3|max:255',
            'reconciliationNotes' => 'required|string|min:10|max:3000',
        ];
        if ($submission->environment === ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN
            && $this->reconciliationOutcome !== 'unresolved') {
            $rules['reconciliationTargetEnvironment'] = 'required|in:test,production';
        }
        $this->validate($rules);

        if ($this->reconciliationOutcome !== 'unresolved' && $this->reconciliationEvidenceType === 'other') {
            $this->addError('reconciliationEvidenceType', 'قرار القبول أو الرفض يتطلب دليلًا رسميًا من بوابة زاتكا أو استجابة رسمية.');
            return;
        }

        try {
            $result = app(ZatcaService::class)->recordManualReconciliation(
                (int) $submission->id,
                $this->reconciliationOutcome,
                $this->reconciliationEvidenceType,
                $this->reconciliationEvidenceReference,
                $this->reconciliationNotes,
                $submission->environment === ZatcaInvoiceSubmission::ENVIRONMENT_UNKNOWN
                    ? $this->reconciliationTargetEnvironment
                    : $submission->environment
            );

            $status = $result['status'] ?? 'uncertain';
            $title = match ($status) {
                ZatcaInvoiceSubmission::STATUS_SENT => 'تم توثيق قبول الفاتورة',
                ZatcaInvoiceSubmission::STATUS_REJECTED => 'تم توثيق رفض الفاتورة',
                ZatcaInvoiceSubmission::STATUS_RECONCILED => 'تمت أرشفة سجل البيئة القديمة',
                default => 'تم حفظ المراجعة — النتيجة غير محسومة',
            };

            $this->reconcilingSubmissionId = null;
            $this->resetValidation();
            Notification::make()
                ->title($title)
                ->body($result['message'] ?? 'تم حفظ حدث التسوية في سجل التدقيق.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('تعذر حفظ تسوية زاتكا')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelSendConfirmation(): void
    {
        $this->confirmingInvoiceId = null;
    }

    public function getConfirmingInvoiceProperty(): ?Invoice
    {
        if (!$this->confirmingInvoiceId) {
            return null;
        }

        return $this->eligibleInvoicesQuery()
            ->where('invoices.id', $this->confirmingInvoiceId)
            ->first(['invoices.id', 'invoices.invoice_number', 'invoices.type', 'invoices.status']);
    }

    public function confirmSendInvoice(): void
    {
        $invoiceId = $this->confirmingInvoiceId;
        $this->confirmingInvoiceId = null;

        if (!$invoiceId) {
            return;
        }

        $this->sendInvoice((int) $invoiceId);
    }

    public function sendInvoice(int $invoiceId): void
    {
        $environment = $this->environment === 'production' ? 'production' : 'test';

        // A second server-side guard protects against forged Livewire requests.
        if ($environment === 'production' && !config('services.zatca.allow_production_sending', false)) {
            Notification::make()
                ->title('الإرسال إلى Production معطّل')
                ->body('الإرسال الحقيقي متوقف على هذا الخادم. اختر Test للاختبار.')
                ->warning()
                ->send();
            return;
        }

        $invoice = Invoice::query()
            ->with(['customer', 'items'])
            ->where('type', 'sale')
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->find($invoiceId);

        if (!$invoice) {
            Notification::make()
                ->title('الفاتورة غير متاحة للإرسال')
                ->body('تأكد أن الفاتورة بيع مؤكدة أو مدفوعة جزئيًا أو بالكامل.')
                ->warning()
                ->send();
            return;
        }

        try {
            $result = app(ZatcaService::class)->submitInvoice($invoice, $environment);
            $label = $environment === 'production' ? 'Production' : 'Test';

            if (!empty($result['already_sent'])) {
                Notification::make()
                    ->title('الفاتورة مرسلة بالفعل')
                    ->body(($result['error'] ?? 'لن نكرر الإرسال تلقائيًا.') . ' | البيئة: ' . $label)
                    ->warning()
                    ->send();
                return;
            }

            if (!empty($result['in_progress'])) {
                Notification::make()
                    ->title('الإرسال قيد التنفيذ')
                    ->body(($result['error'] ?? 'توجد محاولة إرسال جارية.') . ' | البيئة: ' . $label)
                    ->info()
                    ->send();
                return;
            }

            if (!empty($result['needs_reconciliation'])) {
                Notification::make()
                    ->title('نتيجة إرسال زاتكا غير محسومة — لا تعاود الإرسال')
                    ->body(($result['error'] ?? 'راجع السجل قبل أي محاولة جديدة.') . ' | البيئة: ' . $label)
                    ->warning()
                    ->send();
                return;
            }

            if (!empty($result['success'])) {
                $acceptedTitle = !empty($result['accepted_with_warnings'])
                    ? 'تم قبول الفاتورة مع تحذيرات من زاتكا'
                    : 'تم قبول إرسال الفاتورة من زاتكا';
                $apiMessage = !empty($result['api_message'])
                    ? ' | ملاحظات زاتكا: ' . $result['api_message']
                    : '';

                Notification::make()
                    ->title($acceptedTitle)
                    ->body('UUID: ' . ($result['uuid'] ?? '-') . ' | الحالة: ' . ($result['status_code'] ?? '-') . ' | البيئة: ' . $label . $apiMessage)
                    ->success()
                    ->send();
                return;
            }

            Notification::make()
                ->title('فشل إرسال الفاتورة إلى زاتكا')
                ->body(($result['error'] ?? 'خطأ غير معروف') . ' | البيئة: ' . $label)
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('تعذر تنفيذ إرسال زاتكا')
                ->body('حدث خطأ داخلي أثناء تجهيز الإرسال. راجع سجل التطبيق لمعرفة التفاصيل.')
                ->danger()
                ->send();
        }
    }

    public function getInvoiceViewUrl(int $invoiceId): string
    {
        return InvoiceResource::getUrl('view', ['record' => $invoiceId]);
    }
}
