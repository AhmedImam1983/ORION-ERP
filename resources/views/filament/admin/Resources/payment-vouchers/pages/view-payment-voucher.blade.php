<x-filament-panels::page>
@php
    $voucher = $this->getRecord()->load([
        'branch','supplier','invoice','expenseAccount',
        'treasury','bankAccount','journalEntry.lines.account','createdBy',
    ]);

    $journal = $voucher->journalEntry;

    $reversal = null;
    if (\Illuminate\Support\Facades\Schema::hasTable('accounting_reversals')) {
        $reversal = \App\Models\AccountingReversal::query()
            ->with(['originalJournalEntry','reversalJournalEntry','createdBy'])
            ->where('source_type', 'payment_voucher')
            ->where('source_id', $voucher->id)
            ->first();
    }

    $statusLabel = match ($voucher->status) {
        'posted'   => 'مرحّل',
        'reversed' => 'معكوس',
        default    => $voucher->status,
    };
@endphp

<style>
.vv{direction:rtl;display:grid;gap:16px}.vv-card,.vv-hero{background:#0d1728;border:1px solid #26344c;border-radius:16px}
.vv-hero{padding:20px;display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
.vv-k{font-size:10px;color:#8b5cf6;font-weight:900;letter-spacing:.12em}.vv-title{font-size:23px;color:#fff;font-weight:950;margin-top:5px}
.vv-sub{font-size:11px;color:#8ea0b8;margin-top:5px}.vv-card{padding:18px}.vv-ct{font-size:15px;color:#fff;font-weight:950;margin-bottom:14px}
.vv-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.vv-f{background:#0a1422;border:1px solid #1c2940;border-radius:12px;padding:12px}
.vv-l{font-size:10px;color:#71839c;font-weight:800;margin-bottom:5px}.vv-v{font-size:12px;color:#f1f5f9;font-weight:900;word-break:break-word}
.vv-amt{font-size:21px;color:#a78bfa;font-weight:950}.vv-status{padding:6px 11px;border-radius:999px;font-size:11px;font-weight:900}
.vv-status.p{color:#55e0a4;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.25)}
.vv-status.r{color:#fb7185;background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.25)}
.vv-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.vv-link{color:#a78bfa;text-decoration:none;font-weight:900}
.vv-tw{overflow:auto;border:1px solid #1c2940;border-radius:12px}.vv-table{width:100%;border-collapse:collapse;min-width:680px}
.vv-table th,.vv-table td{padding:11px;border-bottom:1px solid #1c2940;text-align:right;font-size:11px}.vv-table th{color:#8191a8;background:#111b2e}
.vv-table td{color:#e5edf7}.vv-note{font-size:11px;color:#8ea0b8;line-height:1.8}.vv-rev{border-color:rgba(251,113,133,.25)}
@media(max-width:1000px){.vv-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.vv-grid{grid-template-columns:1fr}}
</style>

<div class="vv">
    <div class="vv-hero">
        <div>
            <div class="vv-k">PAYMENT VOUCHER</div>
            <div class="vv-title">{{ $voucher->voucher_number }}</div>
            <div class="vv-sub">{{ $voucher->payment_date?->format('d/m/Y') }} · {{ $voucher->purpose_label }}</div>
        </div>
        <span class="vv-status {{ $voucher->status === 'reversed' ? 'r' : 'p' }}">{{ $statusLabel }}</span>
    </div>

    <div class="vv-card">
        <div class="vv-ct">بيانات سند الصرف</div>
        <div class="vv-grid">
            <div class="vv-f"><div class="vv-l">رقم السند</div><div class="vv-v">{{ $voucher->voucher_number }}</div></div>
            <div class="vv-f"><div class="vv-l">التاريخ</div><div class="vv-v">{{ $voucher->payment_date?->format('d/m/Y') }}</div></div>
            <div class="vv-f"><div class="vv-l">الغرض</div><div class="vv-v">{{ $voucher->purpose_label }}</div></div>
            <div class="vv-f"><div class="vv-l">المبلغ</div><div class="vv-amt">SAR {{ number_format((float)$voucher->amount,2) }}</div></div>
            <div class="vv-f"><div class="vv-l">المورد</div><div class="vv-v">{{ $voucher->supplier?->name ?? '—' }}</div></div>
            <div class="vv-f"><div class="vv-l">الفاتورة</div><div class="vv-v">{{ $voucher->invoice?->invoice_number ?? '—' }}</div></div>
            <div class="vv-f"><div class="vv-l">حساب المصروف</div><div class="vv-v">@if($voucher->expenseAccount){{ $voucher->expenseAccount->code }} — {{ $voucher->expenseAccount->name }}@else—@endif</div></div>
            <div class="vv-f"><div class="vv-l">مصدر الصرف</div><div class="vv-v">{{ $voucher->source_label }}</div></div>
            <div class="vv-f"><div class="vv-l">طريقة الدفع</div><div class="vv-v">{{ $voucher->payment_method_label }}</div></div>
            <div class="vv-f"><div class="vv-l">المستفيد</div><div class="vv-v">{{ $voucher->beneficiary_name ?: '—' }}</div></div>
            <div class="vv-f"><div class="vv-l">مرجع إضافي</div><div class="vv-v">{{ $voucher->reference ?: '—' }}</div></div>
            <div class="vv-f"><div class="vv-l">أنشأ بواسطة</div><div class="vv-v">{{ $voucher->createdBy?->name ?? 'System' }}</div></div>
        </div>
        @if($voucher->description)
            <div class="vv-f" style="margin-top:10px"><div class="vv-l">البيان</div><div class="vv-v">{{ $voucher->description }}</div></div>
        @endif
    </div>

    <div class="vv-card">
        <div class="vv-head">
            <div><div class="vv-ct" style="margin:0">القيد المحاسبي المرتبط</div><div class="vv-note">القيد الأصلي يظل محفوظًا حتى بعد العكس.</div></div>
            @if($journal)<a class="vv-link" href="{{ url('/admin/journal-entries/'.$journal->id) }}">فتح {{ $journal->entry_number }}</a>@endif
        </div>

        @if($journal)
            <div class="vv-grid" style="margin:12px 0">
                <div class="vv-f"><div class="vv-l">رقم القيد</div><div class="vv-v">{{ $journal->entry_number }}</div></div>
                <div class="vv-f"><div class="vv-l">المرجع</div><div class="vv-v">{{ $journal->reference }}</div></div>
                <div class="vv-f"><div class="vv-l">إجمالي المدين</div><div class="vv-v">SAR {{ number_format((float)$journal->total_debit,2) }}</div></div>
                <div class="vv-f"><div class="vv-l">إجمالي الدائن</div><div class="vv-v">SAR {{ number_format((float)$journal->total_credit,2) }}</div></div>
            </div>
            <div class="vv-tw"><table class="vv-table">
                <thead><tr><th>الحساب</th><th>الكود</th><th>البيان</th><th>مدين</th><th>دائن</th></tr></thead>
                <tbody>
                @foreach($journal->lines as $line)
                    <tr>
                        <td>{{ $line->account?->name ?? '—' }}</td>
                        <td>{{ $line->account?->code ?? '—' }}</td>
                        <td>{{ $line->description ?: '—' }}</td>
                        <td>{{ number_format((float)$line->debit,2) }}</td>
                        <td>{{ number_format((float)$line->credit,2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @else
            <div class="vv-note" style="margin-top:12px">لا يوجد قيد مرتبط بهذا السند.</div>
        @endif
    </div>

    @if($reversal)
        <div class="vv-card vv-rev">
            <div class="vv-ct">العكس / الإلغاء</div>
            <div class="vv-grid">
                <div class="vv-f"><div class="vv-l">رقم العكس</div><div class="vv-v">{{ $reversal->reversal_number }}</div></div>
                <div class="vv-f"><div class="vv-l">التاريخ</div><div class="vv-v">{{ $reversal->reversal_date?->format('d/m/Y') }}</div></div>
                <div class="vv-f"><div class="vv-l">القيد العكسي</div><div class="vv-v">@if($reversal->reversalJournalEntry)<a class="vv-link" href="{{ url('/admin/journal-entries/'.$reversal->reversalJournalEntry->id) }}">{{ $reversal->reversalJournalEntry->entry_number }}</a>@else—@endif</div></div>
                <div class="vv-f"><div class="vv-l">نفذ بواسطة</div><div class="vv-v">{{ $reversal->createdBy?->name ?? 'System' }}</div></div>
            </div>
            <div class="vv-f" style="margin-top:10px"><div class="vv-l">سبب العكس</div><div class="vv-v">{{ $reversal->reason }}</div></div>
        </div>
    @endif
</div>
</x-filament-panels::page>
