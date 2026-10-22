<x-filament-panels::page>

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{--coa-bg:#07101d;--coa-panel:#0d1728;--coa-panel2:#101c30;--coa-line:#22324d;--coa-text:#eef3fb;--coa-muted:#71829b;--coa-purple:#8b5cf6;--coa-green:#34d399;--coa-red:#fb7185;--coa-amber:#fbbf24;--coa-blue:#38bdf8}
[x-cloak]{display:none!important}
.coa-shell{direction:rtl;color:var(--coa-text);padding-bottom:30px}
.coa-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:17px}.coa-kicker{font-size:10px;color:#957fff;font-weight:900;letter-spacing:.8px;margin-bottom:6px}.coa-title{font-size:28px;font-weight:950;line-height:1.1}.coa-sub{font-size:11px;color:var(--coa-muted);margin-top:6px}
.coa-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:12px}.coa-kpi{position:relative;background:linear-gradient(145deg,#0e192b,#0a1423);border:1px solid #22334f;border-radius:14px;padding:16px 17px;min-height:99px}.coa-kpi-code{position:absolute;left:12px;top:12px;font-size:8px;color:#5f718e;border:1px solid #283b59;border-radius:7px;padding:4px 6px;font-weight:900}.coa-kpi-label{font-size:10px;color:#8d9bb1;font-weight:800}.coa-kpi-value{font-size:22px;font-weight:950;margin-top:9px;direction:ltr;text-align:right}.coa-kpi-note{font-size:8px;color:#5e708a;margin-top:4px}.c-purple{color:#b5a2ff}.c-green{color:var(--coa-green)}.c-amber{color:var(--coa-amber)}.c-red{color:var(--coa-red)}
.coa-tools{display:grid;grid-template-columns:minmax(240px,1fr) repeat(3,160px);gap:8px;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:8px;margin-bottom:12px}.coa-input,.coa-select{height:39px;background:#0f1b2f;border:1px solid #263956;border-radius:9px;color:#eaf0f8;padding:0 11px;outline:none;font-size:10px}.coa-input:focus,.coa-select:focus{border-color:#8b5cf6}
.coa-card{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;overflow:hidden}.coa-card-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #1b2a40}.coa-card-title{font-size:14px;font-weight:950}.coa-card-sub{font-size:8px;color:#61728b;margin-top:3px}.coa-badge{display:inline-flex;border:1px solid #2a3b58;border-radius:999px;padding:5px 8px;font-size:8px;color:#8193af;font-weight:900}
.coa-tree-head,.coa-row{display:grid;grid-template-columns:minmax(330px,1.8fr) 115px 110px 120px 105px 130px;align-items:center}.coa-tree-head{min-height:40px;background:#0e1828;color:#60728c;padding:0 12px;font-size:8px;font-weight:900;border-bottom:1px solid #1c2b42}.coa-row{min-height:59px;padding:0 12px;border-bottom:1px solid #17253a;transition:.15s}.coa-row:hover{background:#101c2f}.coa-account{display:flex;align-items:center;min-width:0}.coa-indent{flex:none}.coa-toggle{width:25px;height:25px;border:1px solid #293b59;background:#0e192b;border-radius:7px;color:#9581ed;cursor:pointer;font-size:11px;margin-left:7px}.coa-toggle.blank{visibility:hidden}.coa-code{font-size:9px;color:#8f7cff;font-weight:900;min-width:54px}.coa-name{font-size:10px;font-weight:900;color:#e4ebf5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.coa-parent{font-size:8px;color:#5d708a;margin-top:2px}
.coa-type,.coa-level,.coa-nature,.coa-state{display:inline-flex;width:max-content;border-radius:7px;padding:4px 7px;font-size:8px;font-weight:900;border:1px solid}.type-asset{color:#5be0af;background:rgba(52,211,153,.07);border-color:rgba(52,211,153,.2)}.type-liability{color:#fb91a1;background:rgba(251,113,133,.07);border-color:rgba(251,113,133,.2)}.type-equity{color:#b6a3ff;background:rgba(139,92,246,.08);border-color:rgba(139,92,246,.22)}.type-revenue{color:#5dcdf6;background:rgba(56,189,248,.07);border-color:rgba(56,189,248,.2)}.type-expense{color:#f6cb55;background:rgba(251,191,36,.07);border-color:rgba(251,191,36,.2)}
.coa-level{color:#a9b6ca;background:#111d30;border-color:#2a3d5b}.coa-nature.debit{color:#fb91a1;background:rgba(251,113,133,.05);border-color:rgba(251,113,133,.17)}.coa-nature.credit{color:#5be0af;background:rgba(52,211,153,.05);border-color:rgba(52,211,153,.17)}.coa-state.on{color:#5be0af;background:rgba(52,211,153,.05);border-color:rgba(52,211,153,.17)}.coa-state.off{color:#8a99ad;background:#111b2a;border-color:#263852}.coa-money{font-size:10px;font-weight:900;direction:ltr;text-align:right;color:#dce5f2}.coa-actions{display:flex;gap:5px}.coa-action{height:28px;padding:0 8px;border-radius:7px;border:1px solid #293b59;background:#0e192b;color:#8fa0b9;font-size:8px;font-weight:900;text-decoration:none;display:inline-flex;align-items:center}.coa-action.view{color:#a995ff}.coa-action.edit{color:#5fcdf5}.coa-empty{padding:55px 20px;text-align:center;color:#5d708b;font-size:10px}
.coa-form-layout,.coa-view-grid{display:grid;grid-template-columns:minmax(0,1fr) 285px;gap:12px;align-items:start}.coa-form-card,.coa-side{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;padding:15px}.coa-side{position:sticky;top:12px}.coa-side-title{font-size:13px;font-weight:950;margin-bottom:12px}.coa-rule{padding:10px 0;border-bottom:1px solid #1b2a40}.coa-rule:last-child{border-bottom:0}.coa-rule b{display:block;font-size:9px;color:#dce5f2}.coa-rule span{font-size:8px;color:#62748e}.coa-warning{margin-top:12px;border:1px solid rgba(251,191,36,.2);background:rgba(251,191,36,.05);border-radius:10px;padding:10px;font-size:8px;color:#eec654}
.coa-summary{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;padding:15px}.coa-summary-row{display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid #1b2a40;font-size:8px;color:#6c7e97}.coa-summary-row:last-child{border-bottom:0}.coa-summary-row strong{font-size:9px;color:#dce5f2}
.coa-ledger{margin-top:12px}.coa-ledger-head,.coa-ledger-row{display:grid;grid-template-columns:100px 110px minmax(220px,1fr) 120px 120px;align-items:center}.coa-ledger-head{padding:10px 14px;background:#0e1828;color:#61728c;font-size:8px;font-weight:900;border-bottom:1px solid #1c2b42}.coa-ledger-row{padding:11px 14px;border-bottom:1px solid #17253a;font-size:9px}.coa-ledger-ref{color:#9b86f5;font-weight:900}.coa-ledger-desc{color:#cdd7e5}.coa-ledger-debit{color:#fb91a1;font-weight:900;direction:ltr;text-align:right}.coa-ledger-credit{color:#5be0af;font-weight:900;direction:ltr;text-align:right}
@media(max-width:1100px){.coa-kpis{grid-template-columns:1fr 1fr}.coa-tools{grid-template-columns:1fr 1fr}.coa-tree-wrap{overflow:auto}.coa-tree-head,.coa-row{min-width:900px}.coa-form-layout,.coa-view-grid{grid-template-columns:1fr}.coa-side{position:static}}
@media(max-width:650px){.coa-head{align-items:flex-start;flex-direction:column}.coa-kpis,.coa-tools{grid-template-columns:1fr}.coa-title{font-size:23px}.coa-ledger{overflow:auto}.coa-ledger-head,.coa-ledger-row{min-width:720px}}
</style>

@php
$typeMap = [
    'asset' => ['أصول','type-asset'],
    'liability' => ['خصوم','type-liability'],
    'equity' => ['حقوق ملكية','type-equity'],
    'revenue' => ['إيرادات','type-revenue'],
    'expense' => ['مصروفات','type-expense'],
];
$levelMap = ['main'=>'رئيسي','sub'=>'فرعي','detail'=>'تفصيلي'];
@endphp
@php
$record = $this->getRecord();
$stats = $this->getLedgerStats();
$lines = $this->getRecentLedgerLines();
$type = $typeMap[$record->type] ?? [$record->type,''];
@endphp
<div class="coa-shell">
    <div class="coa-head">
        <div>
            <div class="coa-kicker">GENERAL LEDGER ACCOUNT</div>
            <div class="coa-title">{{ $record->code }} · {{ $record->name }}</div>
            <div class="coa-sub">{{ $record->parent?->name ? 'يتبع '.$record->parent->name : 'حساب رئيسي' }}</div>
        </div>
        <span class="coa-type {{ $type[1] }}">{{ $type[0] }}</span>
    </div>

    <div class="coa-kpis">
        <div class="coa-kpi"><div class="coa-kpi-code">BAL</div><div class="coa-kpi-label">الرصيد الحالي</div><div class="coa-kpi-value {{ $stats['balance_side']==='مدين' ? 'c-red':'c-green' }}">{{ number_format($stats['balance'],2) }} ر.س</div><div class="coa-kpi-note">{{ $stats['balance_side'] }}</div></div>
        <div class="coa-kpi"><div class="coa-kpi-code">DR</div><div class="coa-kpi-label">إجمالي المدين المرحّل</div><div class="coa-kpi-value c-red">{{ number_format($stats['debit'],2) }} ر.س</div><div class="coa-kpi-note">من القيود المرحلة</div></div>
        <div class="coa-kpi"><div class="coa-kpi-code">CR</div><div class="coa-kpi-label">إجمالي الدائن المرحّل</div><div class="coa-kpi-value c-green">{{ number_format($stats['credit'],2) }} ر.س</div><div class="coa-kpi-note">من القيود المرحلة</div></div>
        <div class="coa-kpi"><div class="coa-kpi-code">MOV</div><div class="coa-kpi-label">عدد الحركات</div><div class="coa-kpi-value c-purple">{{ $stats['lines_count'] }}</div><div class="coa-kpi-note">{{ $stats['children_count'] }} حساب فرعي مباشر</div></div>
    </div>

    <div class="coa-view-grid">
        <div class="coa-form-card">{{ $this->infolist }}</div>
        <div class="coa-summary">
            <div class="coa-side-title">Account Snapshot</div>
            <div class="coa-summary-row"><span>الكود</span><strong>{{ $record->code }}</strong></div>
            <div class="coa-summary-row"><span>النوع</span><strong>{{ $type[0] }}</strong></div>
            <div class="coa-summary-row"><span>المستوى</span><strong>{{ $levelMap[$record->level] ?? $record->level }}</strong></div>
            <div class="coa-summary-row"><span>الطبيعة</span><strong>{{ $record->balance_type === 'debit' ? 'مدين' : 'دائن' }}</strong></div>
            <div class="coa-summary-row"><span>الرصيد الافتتاحي</span><strong>{{ number_format((float)$record->opening_balance,2) }} ر.س</strong></div>
            <div class="coa-summary-row"><span>الحالة</span><strong>{{ $record->is_active ? 'نشط' : 'غير نشط' }}</strong></div>
        </div>
    </div>

    <div class="coa-card coa-ledger">
        <div class="coa-card-head"><div><div class="coa-card-title">آخر حركات الأستاذ</div><div class="coa-card-sub">آخر 12 سطر من القيود المرحلة على الحساب</div></div><span class="coa-badge">{{ $lines->count() }} حركة معروضة</span></div>
        <div class="coa-ledger-head"><div>التاريخ</div><div>المرجع</div><div>البيان</div><div>مدين</div><div>دائن</div></div>
        @forelse($lines as $line)
            <div class="coa-ledger-row">
                <div>{{ $line->journalEntry?->entry_date?->format('d/m/Y') ?? '-' }}</div>
                <div class="coa-ledger-ref">{{ $line->journalEntry?->reference ?? $line->journalEntry?->entry_number ?? '-' }}</div>
                <div class="coa-ledger-desc">{{ $line->description ?: $line->journalEntry?->description ?: '-' }}</div>
                <div class="coa-ledger-debit">{{ (float)$line->debit > 0 ? number_format((float)$line->debit,2).' ر.س' : '-' }}</div>
                <div class="coa-ledger-credit">{{ (float)$line->credit > 0 ? number_format((float)$line->credit,2).' ر.س' : '-' }}</div>
            </div>
        @empty
            <div class="coa-empty">لا توجد حركات مرحلة على هذا الحساب حتى الآن</div>
        @endforelse
    </div>
</div>
</x-filament-panels::page>
