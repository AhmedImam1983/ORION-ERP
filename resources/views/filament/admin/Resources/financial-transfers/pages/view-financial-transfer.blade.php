<x-filament-panels::page>
@php
    $transfer = $this->getRecord()->load([
        'fromTreasury',
        'fromBankAccount',
        'toTreasury',
        'toBankAccount',
        'journalEntry.lines.account',
        'createdBy',
    ]);

    $journal = $transfer->journalEntry;

    $reversal = null;

    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_reversals')) {
        $reversal = \App\Models\AccountingReversal::query()
            ->with(['originalJournalEntry', 'reversalJournalEntry', 'createdBy'])
            ->where('source_type', 'financial_transfer')
            ->where('source_id', $transfer->id)
            ->first();
    }

    $statusLabel = match ($transfer->status) {
        'posted'   => 'مرحّل',
        'reversed' => 'معكوس',
        default    => $transfer->status,
    };

    $fromType = $transfer->from_type === 'treasury' ? 'خزينة' : 'حساب بنكي';
    $toType   = $transfer->to_type === 'treasury' ? 'خزينة' : 'حساب بنكي';
@endphp

<style>
.ft-view{direction:rtl;display:grid;gap:16px}
.ft-hero,.ft-card{
    background:#0d1728;
    border:1px solid #26344c;
    border-radius:16px;
    box-shadow:0 14px 34px rgba(0,0,0,.13);
}
.ft-hero{padding:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.ft-kicker{font-size:10px;font-weight:900;letter-spacing:.13em;color:#8b5cf6}
.ft-title{font-size:23px;font-weight:950;color:#f8fafc;margin-top:5px}
.ft-sub{font-size:11px;color:#8ea0b8;margin-top:5px}
.ft-status{
    display:inline-flex;align-items:center;justify-content:center;
    min-width:82px;height:30px;padding:0 12px;border-radius:999px;
    font-size:11px;font-weight:900
}
.ft-status.posted{color:#55e0a4;border:1px solid rgba(52,211,153,.25);background:rgba(52,211,153,.08)}
.ft-status.reversed{color:#fb7185;border:1px solid rgba(251,113,133,.25);background:rgba(251,113,133,.08)}
.ft-card{padding:18px}
.ft-card-title{font-size:15px;font-weight:950;color:#f8fafc;margin-bottom:14px}
.ft-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.ft-field{background:#0a1422;border:1px solid #1c2940;border-radius:12px;padding:12px;min-width:0}
.ft-label{font-size:10px;color:#71839c;font-weight:800;margin-bottom:5px}
.ft-value{font-size:12px;color:#f1f5f9;font-weight:900;word-break:break-word}
.ft-amount{font-size:22px;color:#a78bfa;font-weight:950}
.ft-route{
    display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:14px;
    background:#0a1422;border:1px solid #1c2940;border-radius:14px;padding:16px
}
.ft-route-box{padding:4px 0}
.ft-route-type{font-size:10px;color:#71839c;font-weight:800}
.ft-route-name{font-size:14px;color:#fff;font-weight:950;margin-top:5px}
.ft-arrow{font-size:22px;color:#8b5cf6;font-weight:950}
.ft-je-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap}
.ft-link{color:#a78bfa;text-decoration:none;font-weight:900}
.ft-link:hover{text-decoration:underline}
.ft-table{width:100%;border-collapse:collapse;min-width:680px}
.ft-table th,.ft-table td{padding:11px;border-bottom:1px solid #1c2940;text-align:right;font-size:11px}
.ft-table th{color:#8191a8;background:#111b2e;font-weight:900}
.ft-table td{color:#e5edf7}
.ft-table-wrap{overflow:auto;border:1px solid #1c2940;border-radius:12px}
.ft-money{font-variant-numeric:tabular-nums;font-weight:900}
.ft-debit{color:#5ee0ad}
.ft-credit{color:#fbbf24}
.ft-note{font-size:11px;color:#8ea0b8;line-height:1.8}
.ft-reversal{border-color:rgba(251,113,133,.22)}
@media(max-width:1000px){.ft-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:650px){
    .ft-grid{grid-template-columns:1fr}
    .ft-route{grid-template-columns:1fr}
    .ft-arrow{transform:rotate(90deg);text-align:center}
}
</style>

<div class="ft-view">

    <div class="ft-hero">
        <div>
            <div class="ft-kicker">FINANCIAL TRANSFER</div>
            <div class="ft-title">{{ $transfer->transfer_number }}</div>
            <div class="ft-sub">
                {{ $transfer->transfer_date?->format('d/m/Y') }}
                · {{ $transfer->route_label }}
            </div>
        </div>

        <span class="ft-status {{ $transfer->status === 'reversed' ? 'reversed' : 'posted' }}">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="ft-card">
        <div class="ft-card-title">مسار التحويل</div>

        <div class="ft-route">
            <div class="ft-route-box">
                <div class="ft-route-type">من · {{ $fromType }}</div>
                <div class="ft-route-name">{{ $transfer->from_label }}</div>
            </div>

            <div class="ft-arrow">←</div>

            <div class="ft-route-box">
                <div class="ft-route-type">إلى · {{ $toType }}</div>
                <div class="ft-route-name">{{ $transfer->to_label }}</div>
            </div>
        </div>
    </div>

    <div class="ft-card">
        <div class="ft-card-title">بيانات التحويل</div>

        <div class="ft-grid">
            <div class="ft-field">
                <div class="ft-label">رقم التحويل</div>
                <div class="ft-value">{{ $transfer->transfer_number }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">التاريخ</div>
                <div class="ft-value">{{ $transfer->transfer_date?->format('d/m/Y') }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">المبلغ</div>
                <div class="ft-amount">SAR {{ number_format((float) $transfer->amount, 2) }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">الحالة</div>
                <div class="ft-value">{{ $statusLabel }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">مرجع خارجي</div>
                <div class="ft-value">{{ $transfer->external_reference ?: '—' }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">أنشأ بواسطة</div>
                <div class="ft-value">{{ $transfer->createdBy?->name ?? 'System' }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">تاريخ الإنشاء</div>
                <div class="ft-value">{{ $transfer->created_at?->format('d/m/Y H:i') }}</div>
            </div>

            <div class="ft-field">
                <div class="ft-label">آخر تعديل</div>
                <div class="ft-value">{{ $transfer->updated_at?->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        @if($transfer->description)
            <div class="ft-field" style="margin-top:10px">
                <div class="ft-label">البيان / الملاحظات</div>
                <div class="ft-value">{{ $transfer->description }}</div>
            </div>
        @endif
    </div>

    <div class="ft-card">
        <div class="ft-je-head">
            <div>
                <div class="ft-card-title" style="margin:0">القيد المحاسبي المرتبط</div>
                <div class="ft-note">القيد الأصلي لا يتم حذفه عند العكس.</div>
            </div>

            @if($journal)
                <a class="ft-link" href="{{ url('/admin/journal-entries/' . $journal->id) }}">
                    فتح {{ $journal->entry_number }}
                </a>
            @endif
        </div>

        @if($journal)
            <div class="ft-grid" style="margin-bottom:12px">
                <div class="ft-field">
                    <div class="ft-label">رقم القيد</div>
                    <div class="ft-value">{{ $journal->entry_number }}</div>
                </div>
                <div class="ft-field">
                    <div class="ft-label">المرجع</div>
                    <div class="ft-value">{{ $journal->reference }}</div>
                </div>
                <div class="ft-field">
                    <div class="ft-label">إجمالي المدين</div>
                    <div class="ft-value">SAR {{ number_format((float) $journal->total_debit, 2) }}</div>
                </div>
                <div class="ft-field">
                    <div class="ft-label">إجمالي الدائن</div>
                    <div class="ft-value">SAR {{ number_format((float) $journal->total_credit, 2) }}</div>
                </div>
            </div>

            <div class="ft-table-wrap">
                <table class="ft-table">
                    <thead>
                    <tr>
                        <th>الحساب</th>
                        <th>الكود</th>
                        <th>البيان</th>
                        <th>مدين</th>
                        <th>دائن</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($journal->lines as $line)
                        <tr>
                            <td>{{ $line->account?->name ?? '—' }}</td>
                            <td>{{ $line->account?->code ?? '—' }}</td>
                            <td>{{ $line->description ?: '—' }}</td>
                            <td class="ft-money ft-debit">
                                {{ number_format((float) $line->debit, 2) }}
                            </td>
                            <td class="ft-money ft-credit">
                                {{ number_format((float) $line->credit, 2) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ft-note">لا يوجد قيد يومية مرتبط بهذا التحويل.</div>
        @endif
    </div>

    @if($reversal)
        <div class="ft-card ft-reversal">
            <div class="ft-card-title">بيانات العكس / الإلغاء</div>

            <div class="ft-grid">
                <div class="ft-field">
                    <div class="ft-label">رقم عملية العكس</div>
                    <div class="ft-value">{{ $reversal->reversal_number }}</div>
                </div>

                <div class="ft-field">
                    <div class="ft-label">تاريخ العكس</div>
                    <div class="ft-value">{{ $reversal->reversal_date?->format('d/m/Y') }}</div>
                </div>

                <div class="ft-field">
                    <div class="ft-label">القيد العكسي</div>
                    <div class="ft-value">
                        @if($reversal->reversalJournalEntry)
                            <a class="ft-link"
                               href="{{ url('/admin/journal-entries/' . $reversal->reversalJournalEntry->id) }}">
                                {{ $reversal->reversalJournalEntry->entry_number }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="ft-field">
                    <div class="ft-label">نفذ بواسطة</div>
                    <div class="ft-value">{{ $reversal->createdBy?->name ?? 'System' }}</div>
                </div>
            </div>

            <div class="ft-field" style="margin-top:10px">
                <div class="ft-label">سبب العكس</div>
                <div class="ft-value">{{ $reversal->reason }}</div>
            </div>
        </div>
    @endif

</div>
</x-filament-panels::page>
