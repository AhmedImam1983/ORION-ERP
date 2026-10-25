<x-filament-panels::page>
    <style>
        .zsp-page{--zsp-bg:#fff;--zsp-bg-soft:#f8fafc;--zsp-border:#e2e8f0;--zsp-text:#0f172a;--zsp-muted:#64748b;--zsp-input:#fff;display:flex;flex-direction:column;gap:18px;color:var(--zsp-text);direction:rtl}
        .dark .zsp-page{--zsp-bg:#0d1420;--zsp-bg-soft:#111b2a;--zsp-border:#26364d;--zsp-text:#e5edf8;--zsp-muted:#9aaec5;--zsp-input:#0a111c}
        .zsp-card{min-width:0;background:var(--zsp-bg);border:1px solid var(--zsp-border);border-radius:15px;padding:18px;box-shadow:0 5px 18px rgba(2,8,23,.05)}
        .zsp-head{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
        .zsp-title{margin:0;font-size:19px;line-height:1.6;font-weight:800;color:var(--zsp-text)}
        .zsp-description{margin:5px 0 0;color:var(--zsp-muted);font-size:13px;line-height:1.9}
        .zsp-environments{display:flex;gap:5px;flex-wrap:wrap;padding:5px;border:1px solid var(--zsp-border);border-radius:12px;background:var(--zsp-bg-soft)}
        .zsp-env-button{border:1px solid transparent;border-radius:9px;padding:9px 14px;font-size:12px;font-weight:800;cursor:pointer;color:var(--zsp-muted);background:transparent;transition:background .15s,border-color .15s}
        .zsp-env-button:hover{color:var(--zsp-text);background:rgba(148,163,184,.12)}
        .zsp-env-button.is-active{color:#fff;background:#7653e9;border-color:#7653e9;box-shadow:0 3px 8px rgba(118,83,233,.2)}
        .zsp-alert{display:flex;align-items:flex-start;gap:10px;margin-top:15px;padding:12px 14px;border:1px solid #bae6fd;border-radius:11px;background:#f0f9ff;color:#075985;font-size:12px;line-height:1.9}
        .dark .zsp-alert{border-color:#075985;background:rgba(3,105,161,.14);color:#bae6fd}
        .zsp-alert.is-warning{border-color:#fcd34d;background:#fffbeb;color:#92400e}
        .dark .zsp-alert.is-warning{border-color:#92400e;background:rgba(217,119,6,.12);color:#fde68a}
        .zsp-stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
        .zsp-stat{position:relative;min-width:0;overflow:hidden;background:var(--zsp-bg);border:1px solid var(--zsp-border);border-radius:14px;padding:15px 16px}
        .zsp-stat:before{content:"";position:absolute;inset:0 0 auto;height:3px;background:#64748b}
        .zsp-stat.is-purple:before{background:#8b5cf6}.zsp-stat.is-green:before{background:#10b981}.zsp-stat.is-red:before{background:#f43f5e}.zsp-stat.is-amber:before{background:#f59e0b}
        .zsp-stat-label{color:var(--zsp-muted);font-size:11px;font-weight:700;line-height:1.8}
        .zsp-stat-value{margin-top:5px;font-size:26px;font-weight:900;line-height:1.4;color:var(--zsp-text);font-variant-numeric:tabular-nums}
        .zsp-toolbar{display:grid;grid-template-columns:minmax(220px,1fr) 220px;gap:12px;align-items:end;margin-bottom:16px}
        .zsp-field-label{display:block;margin-bottom:6px;color:var(--zsp-muted);font-size:12px;font-weight:800}
        .zsp-input{display:block;width:100%;min-height:41px;border:1px solid var(--zsp-border);border-radius:9px;padding:9px 12px;background:var(--zsp-input);color:var(--zsp-text);font-size:12px;outline:none}
        .zsp-input:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.12)}
        .zsp-table-wrap{width:100%;overflow-x:auto;border:1px solid var(--zsp-border);border-radius:11px}
        .zsp-table{width:100%;min-width:1060px;border-collapse:separate;border-spacing:0;text-align:right;font-size:12px}
        .zsp-table thead th{padding:12px 13px;white-space:nowrap;background:var(--zsp-bg-soft);border-bottom:1px solid var(--zsp-border);color:var(--zsp-muted);font-size:11px;font-weight:900}
        .zsp-table tbody td{padding:13px;border-bottom:1px solid var(--zsp-border);vertical-align:top;color:var(--zsp-text);line-height:1.8}
        .zsp-table tbody tr:last-child td{border-bottom:0}.zsp-table tbody tr:hover{background:rgba(148,163,184,.055)}
        .zsp-invoice-link{font-weight:900;color:#8b5cf6;text-decoration:none}.zsp-invoice-link:hover{text-decoration:underline}
        .zsp-muted{color:var(--zsp-muted)!important}.zsp-small{font-size:11px}.zsp-nowrap{white-space:nowrap}.zsp-cell-error{max-width:230px;white-space:normal;overflow-wrap:anywhere;color:#e11d48;font-size:11px}
        .zsp-badge{display:inline-flex;align-items:center;justify-content:center;gap:5px;border-radius:999px;padding:4px 9px;font-size:10px;font-weight:900;white-space:nowrap}
        .zsp-badge.is-sent{background:#d1fae5;color:#065f46}.dark .zsp-badge.is-sent{background:rgba(16,185,129,.16);color:#6ee7b7}
        .zsp-badge.is-warning{background:#fef3c7;color:#92400e}.dark .zsp-badge.is-warning{background:rgba(245,158,11,.16);color:#fcd34d}
        .zsp-badge.is-failed{background:#ffe4e6;color:#9f1239}.dark .zsp-badge.is-failed{background:rgba(244,63,94,.16);color:#fda4af}
        .zsp-badge.is-processing{background:#fef3c7;color:#92400e}.dark .zsp-badge.is-processing{background:rgba(245,158,11,.16);color:#fcd34d}
        .zsp-badge.is-uncertain{background:#ffedd5;color:#9a3412}.dark .zsp-badge.is-uncertain{background:rgba(234,88,12,.16);color:#fdba74}
        .zsp-badge.is-rejected{background:#dbeafe;color:#1d4ed8}.dark .zsp-badge.is-rejected{background:rgba(37,99,235,.16);color:#93c5fd}
        .zsp-badge.is-neutral{background:#e2e8f0;color:#334155}.dark .zsp-badge.is-neutral{background:#26364d;color:#cbd5e1}
        .zsp-action{display:inline-flex;align-items:center;justify-content:center;min-height:34px;border:1px solid transparent;border-radius:8px;padding:7px 10px;font-size:11px;font-weight:900;white-space:nowrap;cursor:pointer;transition:opacity .15s,transform .15s}
        .zsp-action:hover:not(:disabled){transform:translateY(-1px)}.zsp-action:disabled{cursor:not-allowed;opacity:.65}
        .zsp-action.is-primary{background:#7653e9;color:#fff}.zsp-action.is-production{background:#b45309;color:#fff}.zsp-action.is-done{background:#d1fae5;color:#065f46}.dark .zsp-action.is-done{background:rgba(16,185,129,.14);color:#6ee7b7}.zsp-action.is-wait{background:#fef3c7;color:#92400e}
        .zsp-empty{padding:38px 15px!important;text-align:center!important;color:var(--zsp-muted)!important}
       /* ZATCA pagination styling */
.zsp-pagination nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    width: 100%;
}

.zsp-pagination nav > div {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
    min-width: 0;
}

.zsp-pagination nav svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px !important;
    display: block;
    flex-shrink: 0;
}

.zsp-pagination nav a,
.zsp-pagination nav button,
.zsp-pagination nav span[aria-current="page"] > span,
.zsp-pagination nav span[aria-disabled="true"] > span {
    box-sizing: border-box;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    min-height: 36px;
    padding: 0 10px;
    border: 1px solid var(--zsp-border);
    border-radius: 8px;
    background: var(--zsp-bg);
    color: var(--zsp-text);
    text-decoration: none;
}

.zsp-pagination nav span[aria-current="page"] > span {
    background: #7653e9;
    color: #fff;
    border-color: #7653e9;
}

.zsp-pagination nav span[aria-disabled="true"] > span {
    opacity: .45;
}

.zsp-pagination nav a:hover {
    background: var(--zsp-bg-soft);
}
        .zsp-note{margin:0;color:var(--zsp-muted);font-size:11px;line-height:1.9}
        .zsp-modal-backdrop{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(2,6,23,.72);backdrop-filter:blur(4px)}
        .zsp-modal{width:min(100%,520px);background:var(--zsp-bg);color:var(--zsp-text);border:1px solid var(--zsp-border);border-radius:18px;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.38);text-align:right}
        .zsp-modal-heading{display:flex;align-items:center;gap:12px;margin-bottom:14px}
        .zsp-modal-icon{display:flex;align-items:center;justify-content:center;flex:0 0 44px;width:44px;height:44px;border-radius:13px;background:rgba(118,83,233,.14);color:#a78bfa;font-size:22px}
        .zsp-modal-title{margin:0;font-size:17px;font-weight:900;line-height:1.7;color:var(--zsp-text)}
        .zsp-modal-copy{margin:0 0 16px;font-size:13px;line-height:2;color:var(--zsp-muted)}
        .zsp-modal-invoice{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 14px;margin-bottom:16px;background:var(--zsp-bg-soft);border:1px solid var(--zsp-border);border-radius:11px}
        .zsp-modal-invoice-number{font-weight:900;color:var(--zsp-text)}
        .zsp-modal-env{display:inline-flex;padding:5px 9px;border-radius:999px;background:rgba(118,83,233,.15);color:#a78bfa;font-size:11px;font-weight:900;white-space:nowrap}
        .zsp-modal-warning{margin-bottom:16px;padding:10px 12px;border:1px solid #f59e0b;border-radius:10px;background:rgba(245,158,11,.1);color:#fbbf24;font-size:12px;line-height:1.9}
        .zsp-modal-actions{display:flex;gap:9px;justify-content:flex-start;flex-wrap:wrap;margin-top:20px}
        .zsp-modal-btn{min-height:40px;padding:9px 15px;border:1px solid var(--zsp-border);border-radius:9px;font-size:12px;font-weight:900;cursor:pointer;background:var(--zsp-bg-soft);color:var(--zsp-text)}
        .zsp-modal-btn.is-confirm{border-color:#7653e9;background:#7653e9;color:#fff}.zsp-modal-btn.is-production{border-color:#b45309;background:#b45309;color:#fff}.zsp-modal-btn:disabled{opacity:.5;cursor:not-allowed}
        .zsp-modal-close{margin-right:auto;border:0;background:transparent;color:var(--zsp-muted);font-size:23px;cursor:pointer;line-height:1}
        @media(max-width:1100px){.zsp-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:760px){.zsp-card{padding:13px}.zsp-head{align-items:stretch}.zsp-environments{width:100%}.zsp-env-button{flex:1;padding:9px 8px}.zsp-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.zsp-toolbar{grid-template-columns:1fr}.zsp-title{font-size:17px}.zsp-stat-value{font-size:23px}}
        @media(max-width:420px){.zsp-stats{grid-template-columns:1fr 1fr}.zsp-stat{padding:12px}.zsp-stat-label{font-size:10px}}
    </style>

    <div class="zsp-page" dir="rtl">
        <section class="zsp-card">
            <div class="zsp-head">
                <div>
                    <h2 class="zsp-title">إدارة إرسال الفواتير إلى زاتكا</h2>
                    <p class="zsp-description">اختر البيئة، راجع حالة كل فاتورة، وأعد المحاولة للفواتير التي لم تنجح فقط.</p>
                </div>
                <div class="zsp-environments" role="group" aria-label="بيئة الإرسال">
                    <button type="button" wire:click="$set('environment', 'test')" class="zsp-env-button {{ $environment === 'test' ? 'is-active' : '' }}">Test — بيئة الاختبار</button>
                    <button type="button" wire:click="$set('environment', 'production')" class="zsp-env-button {{ $environment === 'production' ? 'is-active' : '' }}">Production — البيئة الفعلية</button>
                </div>
            </div>

            @if ($environment === 'production' && !config('services.zatca.allow_production_sending', false))
                <div class="zsp-alert is-warning" role="alert">
                    <strong>تنبيه أمان:</strong>
                    <div><strong>الإرسال إلى Production معطّل على هذا الخادم.</strong><br>يمكنك مراجعة السجلات، لكن لن يتم إرسال أي فاتورة حقيقية ما دام مفتاح الحماية غير مفعّل.</div>
                </div>
            @elseif ($environment === 'test')
                <div class="zsp-alert" role="status">
                    <div><strong>بيئة الاختبار Test:</strong> الإرسال يستخدم إعدادات الاختبار فقط. نجاح الفاتورة في Test لا يعني أنها أُرسلت إلى Production.</div>
                </div>
            @endif
        </section>

        @php($stats = $this->stats)
        <div class="zsp-stats">
            <div class="zsp-stat is-purple"><div class="zsp-stat-label">فواتير مؤهلة للإرسال</div><div class="zsp-stat-value">{{ number_format($stats['eligible']) }}</div></div>
            <div class="zsp-stat is-green"><div class="zsp-stat-label">مرسلة بنجاح في هذه البيئة</div><div class="zsp-stat-value">{{ number_format($stats['sent']) }}</div></div>
            <div class="zsp-stat is-red"><div class="zsp-stat-label">محاولات فاشلة</div><div class="zsp-stat-value">{{ number_format($stats['failed']) }}</div></div>
            <div class="zsp-stat is-amber"><div class="zsp-stat-label">قيد الإرسال</div><div class="zsp-stat-value">{{ number_format($stats['processing']) }}</div></div>
            <div class="zsp-stat is-amber"><div class="zsp-stat-label">نتيجة غير محسومة — مراجعة مطلوبة</div><div class="zsp-stat-value">{{ number_format($stats['uncertain']) }}</div></div>
            <div class="zsp-stat"><div class="zsp-stat-label">لم تُرسل بنجاح بعد</div><div class="zsp-stat-value">{{ number_format($stats['not_sent']) }}</div></div>
        </div>

        <section class="zsp-card">
            <div class="zsp-toolbar">
                <label>
                    <span class="zsp-field-label">بحث بالفاتورة أو اسم العميل</span>
                    <input type="search" wire:model.live.debounce.350ms="search" placeholder="رقم الفاتورة أو اسم العميل..." class="zsp-input">
                </label>
                <label>
                    <span class="zsp-field-label">فلترة الحالة</span>
                    <select wire:model.live="statusFilter" class="zsp-input">
                        <option value="all">كل الفواتير</option>
                        <option value="not_sent">لم تُرسل بنجاح</option>
                        <option value="sent">مرسلة بنجاح</option>
                        <option value="failed">فشلت</option>
                        <option value="rejected">رفض موثّق</option>
                        <option value="processing">قيد الإرسال</option>
                        <option value="pending">معلقة</option>
                        <option value="uncertain">نتيجة غير محسومة</option>
                    </select>
                </label>
            </div>

            <div class="zsp-table-wrap">
                <table class="zsp-table">
                    <thead><tr>
                        <th>الفاتورة</th><th>التاريخ</th><th>العميل</th><th>الإجمالي</th><th>حالة زاتكا</th><th>المحاولات</th><th>آخر إرسال</th><th>الإجراء</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($this->invoices as $invoice)
                            @php($hasLegacyUnknown = !empty($invoice->zatca_page_legacy_unknown_id) && $invoice->zatca_page_status !== 'sent')
                            @php($submissionStatus = $hasLegacyUnknown ? 'uncertain' : $invoice->zatca_page_status)
                            <tr>
                                <td class="zsp-nowrap">
                                    <a href="{{ $this->getInvoiceViewUrl($invoice->id) }}" class="zsp-invoice-link">{{ $invoice->invoice_number }}</a>
                                    <div class="zsp-small zsp-muted">#{{ $invoice->id }}</div>
                                </td>
                                <td class="zsp-nowrap zsp-muted">{{ $invoice->invoice_date ? \Illuminate\Support\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : '—' }}</td>
                                <td>{{ $invoice->customer?->name ?? 'عميل نقدي' }}</td>
                                <td class="zsp-nowrap">{{ number_format((float) $invoice->total, 2) }} {{ $this->currencySymbol }}</td>
                                <td>
                                    @if ($submissionStatus === 'sent' && ($invoice->zatca_page_reconciliation_outcome ?? null) === 'accepted' && $invoice->zatca_page_status_code === null)
                                        <span class="zsp-badge is-sent">قبول مؤكد بالمراجعة</span>
                                        @if ($invoice->zatca_page_reconciliation_evidence_reference)
                                            <div class="zsp-small zsp-muted">مرجع الدليل: {{ $invoice->zatca_page_reconciliation_evidence_reference }}</div>
                                        @endif
                                        @if ($invoice->zatca_page_reconciliation_notes)
                                            <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">ملاحظات المراجعة</summary><p>{{ $invoice->zatca_page_reconciliation_notes }}</p></details>
                                        @endif
                                    @elseif ($submissionStatus === 'sent')
                                        @php($zatcaResponseDetails = json_decode((string) ($invoice->zatca_page_response ?? ''), true))
                                        @if ((int) ($invoice->zatca_page_status_code ?? 0) === 202)
                                            <span class="zsp-badge is-warning">مقبولة مع تحذيرات</span>
                                            @if (!empty($zatcaResponseDetails['api_message']))
                                                <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">تفاصيل تحذيرات زاتكا</summary><p>{{ $zatcaResponseDetails['api_message'] }}</p></details>
                                            @endif
                                        @else
                                            <span class="zsp-badge is-sent">مرسلة بنجاح</span>
                                        @endif
                                        <div class="zsp-small zsp-muted">HTTP {{ $invoice->zatca_page_status_code ?? '—' }}</div>
                                    @elseif ($submissionStatus === 'uncertain')
                                        <span class="zsp-badge is-uncertain">نتيجة غير محسومة</span>
                                        <div class="zsp-small">لا تعاود الإرسال قبل مراجعة السجل.</div>
                                        @if ($hasLegacyUnknown)
                                            <div class="zsp-small zsp-cell-error">بيئة الإرسال القديمة غير معروفة.</div>
                                            @if ($invoice->zatca_page_legacy_unknown_error)
                                                <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">تفاصيل السجل القديم</summary><p>{{ $invoice->zatca_page_legacy_unknown_error }}</p></details>
                                            @endif
                                        @elseif ($invoice->zatca_page_error)
                                            <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">تفاصيل المحاولة</summary><p>{{ $invoice->zatca_page_error }}</p></details>
                                        @endif
                                    @elseif ($submissionStatus === 'rejected')
                                        <span class="zsp-badge is-rejected">رفض موثّق</span>
                                        @if ($invoice->zatca_page_reconciliation_evidence_reference)
                                            <div class="zsp-small zsp-muted">مرجع الدليل: {{ $invoice->zatca_page_reconciliation_evidence_reference }}</div>
                                        @endif
                                        @if ($invoice->zatca_page_reconciliation_notes)
                                            <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">ملاحظات المراجعة</summary><p>{{ $invoice->zatca_page_reconciliation_notes }}</p></details>
                                        @elseif ($invoice->zatca_page_error)
                                            <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">تفاصيل الخطأ</summary><p>{{ $invoice->zatca_page_error }}</p></details>
                                        @endif
                                    @elseif ($submissionStatus === 'failed')
                                        <span class="zsp-badge is-failed">فشلت</span>
                                        @if ($invoice->zatca_page_error)
                                            <details class="zsp-cell-error"><summary style="cursor:pointer;margin-top:5px">تفاصيل الخطأ</summary><p>{{ $invoice->zatca_page_error }}</p></details>
                                        @endif
                                    @elseif ($submissionStatus === 'processing')
                                        <span class="zsp-badge is-processing">قيد الإرسال</span>
                                    @elseif ($submissionStatus === 'pending')
                                        <span class="zsp-badge is-neutral">معلقة</span>
                                    @else
                                        <span class="zsp-badge is-neutral">لم تُرسل</span>
                                    @endif
                                    @if ($invoice->zatca_page_sending_mode)
                                        <div class="zsp-small zsp-muted">{{ $invoice->zatca_page_sending_mode }}</div>
                                    @endif
                                </td>
                                <td class="zsp-nowrap">{{ (int) ($invoice->zatca_page_attempts ?? 0) }}</td>
                                <td class="zsp-nowrap zsp-muted zsp-small">
                                    @if ($invoice->zatca_page_sent_at)
                                        {{ \Illuminate\Support\Carbon::parse($invoice->zatca_page_sent_at)->format('Y-m-d H:i') }}
                                    @elseif ($invoice->zatca_page_last_attempt_at)
                                        {{ \Illuminate\Support\Carbon::parse($invoice->zatca_page_last_attempt_at)->format('Y-m-d H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="zsp-nowrap">
                                    @if ($submissionStatus === 'sent')
                                        <button type="button" disabled class="zsp-action is-done">تم الإرسال</button>
                                    @elseif ($submissionStatus === 'rejected')
                                        <button type="button" wire:click="requestSendConfirmation({{ $invoice->id }})" wire:loading.attr="disabled" class="zsp-action is-primary">إعادة المحاولة بعد الرفض الموثّق</button>
                                    @elseif ($submissionStatus === 'uncertain')
                                        @php($reconciliationSubmissionId = $hasLegacyUnknown ? $invoice->zatca_page_legacy_unknown_id : $invoice->zatca_page_submission_id)
                                        @if (auth()->user()?->hasAnyRole(['super_admin', 'admin']) && $reconciliationSubmissionId)
                                            <button type="button" wire:click="requestManualReconciliation({{ (int) $reconciliationSubmissionId }})" wire:loading.attr="disabled" class="zsp-action is-wait">توثيق نتيجة المراجعة</button>
                                        @else
                                            <button type="button" disabled class="zsp-action is-wait">تحتاج مراجعة — ممنوع الإرسال</button>
                                        @endif
                                    @elseif ($submissionStatus === 'processing')
                                        @if ($invoice->zatca_page_last_attempt_at && \Illuminate\Support\Carbon::parse($invoice->zatca_page_last_attempt_at)->gt(now()->subMinutes(30)))
                                            <button type="button" disabled class="zsp-action is-wait">قيد التنفيذ</button>
                                        @else
                                            <button type="button" wire:click="inspectSubmissionState({{ $invoice->id }})" wire:loading.attr="disabled" class="zsp-action is-wait">فحص المحاولة القديمة</button>
                                        @endif
                                    @else
                                        <button type="button" wire:click="requestSendConfirmation({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="requestSendConfirmation({{ $invoice->id }})" class="zsp-action {{ $environment === 'production' ? 'is-production' : 'is-primary' }}">
                                            {{ $submissionStatus === 'failed' ? 'إعادة المحاولة' : 'إرسال الآن' }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="zsp-empty"><strong>لا توجد فواتير مطابقة</strong><div class="zsp-small" style="margin-top:6px">راجع الفلتر أو تأكد من وجود فواتير بيع مؤكدة أو مدفوعة جزئيًا أو بالكامل.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="zsp-pagination">{{ $this->invoices->links() }}</div>
        </section>

        <p class="zsp-note">ملاحظة: لكل فاتورة سجل مستقل لكل بيئة. الفاتورة التي حالتها «مرسلة» لا يُعاد إرسالها تلقائيًا إلى البيئة نفسها؛ أما البيئة الأخرى فلها سجل مستقل.</p>

        @if ($this->confirmingInvoice)
            <div class="zsp-modal-backdrop" wire:keydown.escape.window="cancelSendConfirmation" wire:click.self="cancelSendConfirmation">
                <section class="zsp-modal" role="dialog" aria-modal="true" aria-labelledby="zsp-confirm-title" dir="rtl">
                    <div class="zsp-modal-heading">
                        <div class="zsp-modal-icon" aria-hidden="true">↗</div>
                        <div style="flex:1;min-width:0">
                            <h2 id="zsp-confirm-title" class="zsp-modal-title">تأكيد إرسال الفاتورة إلى زاتكا</h2>
                            <p class="zsp-description">راجع الفاتورة والبيئة قبل تنفيذ الإرسال.</p>
                        </div>
                        <button type="button" class="zsp-modal-close" wire:click="cancelSendConfirmation" aria-label="إغلاق">×</button>
                    </div>

                    <p class="zsp-modal-copy">هل تريد بالفعل إرسال هذه الفاتورة؟ بعد نجاح الإرسال، لن نكرر الإرسال إلى البيئة نفسها تلقائيًا.</p>

                    <div class="zsp-modal-invoice">
                        <div>
                            <div class="zsp-field-label" style="margin-bottom:3px">رقم الفاتورة</div>
                            <div class="zsp-modal-invoice-number">{{ $this->confirmingInvoice->invoice_number }}</div>
                        </div>
                        <span class="zsp-modal-env">{{ $environment === 'production' ? 'Production — البيئة الفعلية' : 'Test — بيئة الاختبار' }}</span>
                    </div>

                    @if ($environment === 'production' && !config('services.zatca.allow_production_sending', false))
                        <div class="zsp-modal-warning" role="alert"><strong>الإرسال الفعلي معطّل.</strong> لا يمكن إرسال الفاتورة إلى Production حاليًا. اختر Test للاختبار.</div>
                    @endif

                    <div class="zsp-modal-actions">
                        <button type="button" class="zsp-modal-btn" wire:click="cancelSendConfirmation">إلغاء</button>
                        @if ($environment === 'production')
                            <button type="button" class="zsp-modal-btn is-production" wire:click="confirmSendInvoice" @disabled(!config('services.zatca.allow_production_sending', false))>
                                {{ config('services.zatca.allow_production_sending', false) ? 'تأكيد الإرسال إلى Production' : 'الإرسال الفعلي معطّل' }}
                            </button>
                        @else
                            <button type="button" class="zsp-modal-btn is-confirm" wire:click="confirmSendInvoice" wire:loading.attr="disabled" wire:target="confirmSendInvoice">
                                <span wire:loading.remove wire:target="confirmSendInvoice">نعم، أرسل إلى Test</span>
                                <span wire:loading wire:target="confirmSendInvoice">جارٍ تجهيز الإرسال...</span>
                            </button>
                        @endif
                    </div>
                </section>
            </div>
        @endif

        @if ($this->reconcilingSubmission)
            <div class="zsp-modal-backdrop" wire:keydown.escape.window="cancelManualReconciliation" wire:click.self="cancelManualReconciliation">
                <section class="zsp-modal" role="dialog" aria-modal="true" aria-labelledby="zsp-reconcile-title" dir="rtl">
                    <div class="zsp-modal-heading">
                        <div class="zsp-modal-icon" aria-hidden="true">✓</div>
                        <div style="flex:1;min-width:0">
                            <h2 id="zsp-reconcile-title" class="zsp-modal-title">توثيق مراجعة نتيجة زاتكا</h2>
                            <p class="zsp-description">الإجراء يسجل مراجعة محلية فقط ولا يستعلم من زاتكا ولا يرسل فاتورة.</p>
                        </div>
                        <button type="button" class="zsp-modal-close" wire:click="cancelManualReconciliation" aria-label="إغلاق">×</button>
                    </div>

                    <div class="zsp-modal-warning" role="alert">
                        <strong>تنبيه أمان:</strong> اختر «مقبولة» أو «مرفوضة» فقط بعد التحقق من بوابة زاتكا الرسمية أو استجابة رسمية محفوظة. لو ما زال الدليل غير حاسم، اختر «لا تزال غير محسومة»؛ وسيظل الإرسال محظورًا.
                    </div>

                    <div class="zsp-modal-invoice">
                        <div>
                            <div class="zsp-field-label" style="margin-bottom:3px">رقم الفاتورة</div>
                            <div class="zsp-modal-invoice-number">{{ $this->reconcilingSubmission->invoice?->invoice_number ?? ('#' . $this->reconcilingSubmission->invoice_id) }}</div>
                            <div class="zsp-small zsp-muted">حالة السجل الحالية: {{ $this->reconcilingSubmission->status }}</div>
                        </div>
                        <span class="zsp-modal-env">{{ $this->reconcilingSubmission->environment === 'unknown' ? 'البيئة القديمة غير معروفة' : strtoupper($this->reconcilingSubmission->environment) }}</span>
                    </div>

                    <div style="display:grid;gap:12px">
                        <label>
                            <span class="zsp-field-label">نتيجة المراجعة</span>
                            <select wire:model.live="reconciliationOutcome" class="zsp-input">
                                <option value="unresolved">لا تزال غير محسومة — أبقِ الحماية</option>
                                <option value="accepted">قبول مؤكد بدليل رسمي</option>
                                <option value="rejected">رفض مؤكد بدليل رسمي</option>
                            </select>
                            @error('reconciliationOutcome') <span class="zsp-cell-error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            <span class="zsp-field-label">مصدر الدليل</span>
                            <select wire:model="reconciliationEvidenceType" class="zsp-input">
                                <option value="zatca_portal">سجل بوابة زاتكا الرسمية</option>
                                <option value="official_response">استجابة رسمية / رد محفوظ</option>
                                <option value="other">مصدر آخر — لتوثيق نتيجة غير محسومة فقط</option>
                            </select>
                            @error('reconciliationEvidenceType') <span class="zsp-cell-error">{{ $message }}</span> @enderror
                        </label>

                        @if ($this->reconcilingSubmission->environment === 'unknown' && $reconciliationOutcome !== 'unresolved')
                            <label>
                                <span class="zsp-field-label">البيئة المثبتة بالدليل</span>
                                <select wire:model="reconciliationTargetEnvironment" class="zsp-input">
                                    <option value="test">Test</option>
                                    <option value="production">Production</option>
                                </select>
                                @error('reconciliationTargetEnvironment') <span class="zsp-cell-error">{{ $message }}</span> @enderror
                            </label>
                        @endif

                        <label>
                            <span class="zsp-field-label">مرجع الدليل (UUID / مرجع البوابة / رقم الرد)</span>
                            <input type="text" wire:model="reconciliationEvidenceReference" maxlength="255" class="zsp-input" placeholder="أدخل مرجعًا يمكن الرجوع إليه لاحقًا">
                            @error('reconciliationEvidenceReference') <span class="zsp-cell-error">{{ $message }}</span> @enderror
                        </label>

                        <label>
                            <span class="zsp-field-label">ملاحظات المراجعة</span>
                            <textarea wire:model="reconciliationNotes" rows="3" maxlength="3000" class="zsp-input" placeholder="اشرح ما تمت مراجعته ولماذا يدعم الدليل النتيجة المختارة..."></textarea>
                            @error('reconciliationNotes') <span class="zsp-cell-error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div style="margin-top:18px;border-top:1px solid var(--zsp-border);padding-top:14px">
                        <h3 class="zsp-title" style="font-size:14px">سجل المراجعات السابقة</h3>
                        @forelse ($this->reconciliationHistory as $event)
                            <div style="padding:10px 0;border-bottom:1px solid var(--zsp-border);font-size:11px;line-height:1.9">
                                <strong>{{ $event->outcome === 'accepted' ? 'قبول موثّق' : ($event->outcome === 'rejected' ? 'رفض موثّق' : 'غير محسوم') }}</strong>
                                — {{ $event->reconciledBy?->name ?? 'مستخدم غير متاح' }}
                                — {{ $event->reconciled_at?->format('Y-m-d H:i') ?? '—' }}
                                <div class="zsp-muted">مصدر الدليل: {{ $event->evidence_type }} | المرجع: {{ $event->evidence_reference }}</div>
                                <div>{{ $event->notes }}</div>
                            </div>
                        @empty
                            <p class="zsp-note">لا توجد مراجعات موثقة سابقة لهذا السجل.</p>
                        @endforelse
                    </div>

                    <div class="zsp-modal-actions">
                        <button type="button" class="zsp-modal-btn" wire:click="cancelManualReconciliation">إلغاء</button>
                        <button type="button" class="zsp-modal-btn is-confirm" wire:click="saveManualReconciliation" wire:loading.attr="disabled" wire:target="saveManualReconciliation">
                            <span wire:loading.remove wire:target="saveManualReconciliation">حفظ المراجعة الموثّقة</span>
                            <span wire:loading wire:target="saveManualReconciliation">جارٍ الحفظ...</span>
                        </button>
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
