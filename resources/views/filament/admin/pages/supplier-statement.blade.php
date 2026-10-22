<x-filament-panels::page>
@php
    $movementCount = count($statement);
    $purchaseCount = collect($statement)->where('type', 'purchase')->count();
    $returnCount = collect($statement)->where('type', 'purchase_return')->count();
    $paymentCount = collect($statement)->where('type', 'payment')->count();
    $refundCount = collect($statement)->where('type', 'supplier_refund')->count();
    $reversalCount = collect($statement)->where('type', 'reversal')->count();
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --ss-bg:#08111f;--ss-panel:#0d1728;--ss-panel2:#101b2f;--ss-border:#263650;
    --ss-text:#f4f7fb;--ss-muted:#7e8fa8;--ss-purple:#8b5cf6;--ss-green:#34d399;
    --ss-red:#fb7185;--ss-amber:#fbbf24;--ss-cyan:#38bdf8;
}
.ss-wrap{direction:rtl;color:var(--ss-text);display:flex;flex-direction:column;gap:13px}
.ss-panel{background:linear-gradient(180deg,var(--ss-panel),#0a1423);border:1px solid var(--ss-border);border-radius:16px;box-shadow:0 14px 35px rgba(0,0,0,.12)}
.ss-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;padding:3px 2px 8px}
.ss-kicker{font-size:10px;font-weight:900;color:#8c9ab0;margin-bottom:8px}
.ss-title{font-size:28px;font-weight:950;color:#fff;line-height:1.2}
.ss-subtitle{font-size:11px;color:var(--ss-muted);margin-top:6px}
.ss-chip{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:999px;border:1px solid rgba(52,211,153,.2);background:rgba(52,211,153,.06);color:#6ee7b7;font-size:9px;font-weight:900;margin-top:9px}
.ss-chip i{width:6px;height:6px;border-radius:50%;background:#34d399}

.ss-filter{padding:14px;display:grid;grid-template-columns:minmax(260px,1.5fr) 175px 175px 130px 125px;gap:10px;align-items:end}
.ss-field label{display:block;font-size:10px;color:#8696ad;font-weight:850;margin:0 0 6px 2px}
.ss-select,.ss-date{width:100%;height:42px;background:#101a2c;border:1px solid #2a3a55;border-radius:10px;color:#eef4fb;padding:0 12px;outline:none;font-size:11px}
.ss-select:focus,.ss-date:focus{border-color:var(--ss-purple);box-shadow:0 0 0 3px rgba(139,92,246,.09)}
.ss-btn{height:42px;border-radius:10px;border:1px solid #35445e;background:#101a2c;color:#dce5f2;font-size:11px;font-weight:900;cursor:pointer;padding:0 14px;white-space:nowrap}
.ss-btn.primary{background:linear-gradient(135deg,#9b5cf6,#7c3aed);border-color:#9b5cf6;color:#fff;box-shadow:0 8px 20px rgba(124,58,237,.18)}
.ss-btn.pdf{border-color:rgba(52,211,153,.25);background:rgba(52,211,153,.06);color:#6ee7b7}
.ss-btn:disabled{opacity:.55;cursor:wait}

.supplier-card{padding:15px 17px;display:grid;grid-template-columns:minmax(250px,1.6fr) repeat(3,minmax(150px,1fr));gap:10px}
.supplier-main{display:flex;align-items:center;gap:12px;background:#0b1424;border:1px solid #1e2d44;border-radius:12px;padding:13px}
.supplier-mark{width:42px;height:42px;border-radius:11px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:950;color:#fff;direction:ltr}
.supplier-name{font-size:14px;font-weight:950;color:#fff}.supplier-meta{font-size:9px;color:#71839d;margin-top:3px}
.ss-info{background:#0b1424;border:1px solid #1e2d44;border-radius:12px;padding:13px}
.ss-info-label{font-size:9px;color:#75869f;font-weight:850}.ss-info-value{font-size:12px;color:#eef3fa;font-weight:900;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}
.kpi{padding:15px 17px;min-height:102px;position:relative;overflow:hidden}
.kpi:after{content:"";position:absolute;left:0;right:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent)}
.kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.kpi-label{font-size:10px;color:#a6b3c4;font-weight:850}.kpi-code{font-size:8px;font-weight:900;color:#667890;border:1px solid #2b3b55;border-radius:999px;padding:3px 7px;direction:ltr}
.kpi-value{font-size:19px;font-weight:950;color:var(--accent);margin-top:13px;font-variant-numeric:tabular-nums;white-space:nowrap}.kpi-note{font-size:9px;color:#64748b;margin-top:3px}

.ss-mini{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;padding:10px}
.mini{background:#0b1424;border:1px solid #1c2a40;border-radius:10px;padding:10px 12px;display:flex;align-items:center;justify-content:space-between;gap:10px}.mini span{font-size:9px;color:#72839c;font-weight:850}.mini b{font-size:11px;color:#edf2f8}

.statement-card{padding:12px;overflow:hidden}
.statement-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:2px 3px 12px}.statement-title{font-size:15px;font-weight:950;color:#fff}.statement-sub{font-size:9px;color:#6e8099;margin-top:3px}.statement-count{font-size:9px;color:#8ea0b8;border:1px solid #263650;border-radius:999px;padding:5px 9px}
.table-scroll{overflow:auto;border:1px solid #1c2a40;border-radius:12px}
.ss-table{width:100%;border-collapse:collapse;min-width:920px}
.ss-table th{background:#101a2d;color:#7e8ea6;text-align:right;padding:11px 12px;font-size:9px;font-weight:900;border-bottom:1px solid #213149;white-space:nowrap}
.ss-table td{padding:12px;border-bottom:1px solid #17253a;font-size:10px;color:#dce5f1;font-weight:700;vertical-align:middle}.ss-table tbody tr:last-child td{border-bottom:0}.ss-table tbody tr:hover td{background:rgba(139,92,246,.035)}
.type-badge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:4px 8px;font-size:8px;font-weight:950;border:1px solid}.type-badge.opening{color:#c4b5fd;background:rgba(139,92,246,.07);border-color:rgba(139,92,246,.18)}.type-badge.purchase{color:#fda4af;background:rgba(251,113,133,.06);border-color:rgba(251,113,133,.18)}.type-badge.purchase_return{color:#67e8f9;background:rgba(56,189,248,.06);border-color:rgba(56,189,248,.18)}.type-badge.payment{color:#6ee7b7;background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.18)}.type-badge.supplier_refund{color:#fde68a;background:rgba(251,191,36,.07);border-color:rgba(251,191,36,.2)}.type-badge.reversal{color:#c4b5fd;background:rgba(139,92,246,.07);border-color:rgba(139,92,246,.2)}
.ref{color:#a78bfa;font-weight:900;direction:ltr;text-align:right;font-variant-numeric:tabular-nums}.money{font-variant-numeric:tabular-nums;white-space:nowrap}.money.debit{color:#fda4af;font-weight:900}.money.credit{color:#6ee7b7;font-weight:900}.balance.pos{color:#fbbf24;font-weight:950}.balance.neg{color:#6ee7b7;font-weight:950}
.ss-table tfoot td{background:rgba(139,92,246,.06);border-top:1px solid rgba(139,92,246,.22);font-weight:950}
.empty{padding:52px 20px;text-align:center}.empty-code{font-size:10px;color:#7c6dff;font-weight:950;direction:ltr}.empty-title{font-size:14px;font-weight:900;color:#dbe5f2;margin-top:7px}.empty-text{font-size:10px;color:#667790;margin-top:4px}
.loading-text{font-size:9px;color:#c4b5fd;margin-top:4px}

@media(max-width:1150px){.ss-filter{grid-template-columns:1fr 1fr 1fr}.supplier-card{grid-template-columns:1fr 1fr}.kpis{grid-template-columns:1fr 1fr}}
@media(max-width:720px){.ss-hero{flex-direction:column;align-items:flex-start}.ss-filter,.supplier-card,.kpis,.ss-mini{grid-template-columns:1fr}.ss-title{font-size:24px}}
</style>

<div class="ss-wrap">
    <div class="ss-hero">
        <div>
            <div class="ss-kicker">الحسابات / الموردون</div>
            <div class="ss-title">كشف حساب المورد</div>
            <div class="ss-subtitle">متابعة المشتريات والمدفوعات والمرتجعات واستردادات المورد والرصيد المتحرك من القيود المرحلة</div>
            <div class="ss-chip"><i></i> Supplier Ledger · SAR</div>
        </div>
    </div>

    <div class="ss-panel ss-filter">
        <div class="ss-field">
            <label>المورد</label>
            <select wire:model="supplierId" class="ss-select">
                <option value="">اختر مورداً...</option>
                @foreach(\App\Models\Supplier::where('is_active',true)->orderBy('name')->get() as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ss-field"><label>من تاريخ</label><input type="date" wire:model="dateFrom" class="ss-date"></div>
        <div class="ss-field"><label>إلى تاريخ</label><input type="date" wire:model="dateTo" class="ss-date"></div>
        <button type="button" wire:click="loadStatement" wire:loading.attr="disabled" class="ss-btn primary">
            <span wire:loading.remove wire:target="loadStatement">عرض الكشف</span>
            <span wire:loading wire:target="loadStatement">جارٍ التحميل...</span>
        </button>
        @if($supplier)
            <button type="button" wire:click="exportPdf" wire:loading.attr="disabled" class="ss-btn pdf">
                <span wire:loading.remove wire:target="exportPdf">تصدير PDF</span>
                <span wire:loading wire:target="exportPdf">جارٍ التجهيز...</span>
            </button>
        @endif
    </div>

    @if($supplier)
        <div class="ss-panel supplier-card">
            <div class="supplier-main">
                <div class="supplier-mark">SUP</div>
                <div style="min-width:0">
                    <div class="supplier-name">{{ $supplier->name }}</div>
                    <div class="supplier-meta">{{ $supplier->type === 'company' ? 'شركة' : 'فرد' }} · كشف من {{ date('d/m/Y', strtotime($dateFrom)) }} إلى {{ date('d/m/Y', strtotime($dateTo)) }}</div>
                </div>
            </div>
            <div class="ss-info"><div class="ss-info-label">رقم الهاتف</div><div class="ss-info-value">{{ $supplier->phone ?? '-' }}</div></div>
            <div class="ss-info"><div class="ss-info-label">الرقم الضريبي</div><div class="ss-info-value">{{ $supplier->tax_number ?? '-' }}</div></div>
            <div class="ss-info"><div class="ss-info-label">حالة الرصيد</div><div class="ss-info-value" style="color:{{ $closingBalance > 0 ? '#fbbf24' : ($closingBalance < 0 ? '#6ee7b7' : '#cbd5e1') }}">{{ $closingBalance > 0 ? 'مستحق للمورد' : ($closingBalance < 0 ? 'رصيد لصالحنا' : 'مسدد') }}</div></div>
        </div>

        <div class="kpis">
            <div class="ss-panel kpi" style="--accent:#a78bfa">
                <div class="kpi-top"><span class="kpi-label">رصيد أول المدة</span><span class="kpi-code">OPEN</span></div>
                <div class="kpi-value">{{ number_format(abs($openingBalance), 2) }} ر.س</div>
                <div class="kpi-note">{{ $openingBalance > 0 ? 'مستحق للمورد' : ($openingBalance < 0 ? 'رصيد لصالحنا' : 'بدون رصيد افتتاحي') }}</div>
            </div>
            <div class="ss-panel kpi" style="--accent:#fb7185">
                <div class="kpi-top"><span class="kpi-label">إجمالي المشتريات</span><span class="kpi-code">PUR</span></div>
                <div class="kpi-value">{{ number_format($totalPurchases, 2) }} ر.س</div>
                <div class="kpi-note">{{ $purchaseCount }} فاتورة شراء بحركة على ذمم الموردين</div>
            </div>
            <div class="ss-panel kpi" style="--accent:#34d399">
                <div class="kpi-top"><span class="kpi-label">إجمالي المدفوعات</span><span class="kpi-code">PAY</span></div>
                <div class="kpi-value">{{ number_format($totalPayments, 2) }} ر.س</div>
                <div class="kpi-note">{{ $paymentCount }} حركة دفع خلال الفترة</div>
            </div>
            <div class="ss-panel kpi" style="--accent:{{ $closingBalance > 0 ? '#fbbf24' : '#34d399' }}">
                <div class="kpi-top"><span class="kpi-label">الرصيد الحالي</span><span class="kpi-code">BAL</span></div>
                <div class="kpi-value">{{ number_format(abs($closingBalance), 2) }} ر.س</div>
                <div class="kpi-note">{{ $closingBalance > 0 ? 'مستحق علينا للمورد' : ($closingBalance < 0 ? 'رصيد لصالحنا / دفعة مقدمة' : 'الحساب مسدد') }}</div>
            </div>
        </div>

        <div class="ss-panel ss-mini">
            <div class="mini"><span>مرتجعات الشراء</span><b>{{ number_format($totalPurchaseReturns, 2) }} ر.س</b></div>
            <div class="mini"><span>استردادات من المورد</span><b>{{ number_format($totalSupplierRefunds, 2) }} ر.س · {{ $refundCount }}</b></div>
            <div class="mini"><span>عدد المرتجعات</span><b>{{ $returnCount }}</b></div>
            <div class="mini"><span>عدد حركات الكشف</span><b>{{ $movementCount }}</b></div>
            <div class="mini"><span>حركة الحساب</span><b>{{ number_format($totalCredit - $totalDebit, 2) }} ر.س</b></div>
        </div>

        <div class="ss-panel statement-card">
            <div class="statement-head">
                <div>
                    <div class="statement-title">حركات حساب المورد</div>
                    <div class="statement-sub">من {{ date('d/m/Y', strtotime($dateFrom)) }} إلى {{ date('d/m/Y', strtotime($dateTo)) }}</div>
                </div>
                <div class="statement-count">{{ $movementCount }} حركة</div>
            </div>

            @if(empty($statement))
                <div class="empty">
                    <div class="empty-code">NO TRANSACTIONS</div>
                    <div class="empty-title">لا توجد حركات مالية في الفترة المحددة</div>
                    <div class="empty-text">جرّب توسيع الفترة أو اختر مورداً آخر. الفواتير النقدية التي لا تمر على حساب 2101 لا تظهر كذمم.</div>
                </div>
            @else
                <div class="table-scroll">
                    <table class="ss-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>نوع الحركة</th>
                                <th>البيان</th>
                                <th>المرجع</th>
                                <th>مدين</th>
                                <th>دائن</th>
                                <th>الرصيد بعد الحركة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statement as $row)
                                @php
                                    $typeLabel = match($row['type']) {
                                        'opening' => 'رصيد أول المدة',
                                        'purchase' => 'فاتورة شراء',
                                        'purchase_return' => 'مرتجع شراء',
                                        'payment' => 'دفعة مورد',
                                        'supplier_refund' => 'استرداد من مورد',
                                        'reversal' => 'عكس محاسبي',
                                        default => $row['type'],
                                    };
                                @endphp
                                <tr>
                                    <td style="color:#93a4bb;white-space:nowrap">{{ $row['date'] }}</td>
                                    <td><span class="type-badge {{ $row['type'] }}">{{ $typeLabel }}</span></td>
                                    <td>{{ $row['description'] }}</td>
                                    <td class="ref">{{ $row['reference'] }}</td>
                                    <td class="money debit">{{ $row['debit'] > 0 ? number_format($row['debit'], 2).' ر.س' : '—' }}</td>
                                    <td class="money credit">{{ $row['credit'] > 0 ? number_format($row['credit'], 2).' ر.س' : '—' }}</td>
                                    <td class="money balance {{ $row['balance'] >= 0 ? 'pos' : 'neg' }}">
                                        {{ number_format(abs($row['balance']), 2) }} ر.س
                                        <span style="font-size:8px;opacity:.7">{{ $row['balance'] > 0 ? 'مستحق' : ($row['balance'] < 0 ? 'لصالحنا' : 'مسدد') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">إجمالي حركة الفترة</td>
                                <td class="money debit">{{ number_format($totalDebit, 2) }} ر.س</td>
                                <td class="money credit">{{ number_format($totalCredit, 2) }} ر.س</td>
                                <td class="money balance {{ $closingBalance >= 0 ? 'pos' : 'neg' }}">{{ number_format(abs($closingBalance), 2) }} ر.س</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    @else
        <div class="ss-panel empty">
            <div class="empty-code">SUPPLIER STATEMENT</div>
            <div class="empty-title">اختر المورد والفترة لعرض كشف الحساب</div>
            <div class="empty-text">سيتم احتساب رصيد أول المدة، المشتريات، المرتجعات، دفعات المورد، استردادات المورد والعكوس من القيود المرحلة.</div>
        </div>
    @endif
</div>
</x-filament-panels::page>