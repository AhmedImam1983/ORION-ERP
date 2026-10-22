<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --tb-bg:#07101d;--tb-panel:#0d1728;--tb-panel2:#101c30;--tb-line:#22324d;
    --tb-text:#eef3fb;--tb-muted:#71829b;--tb-purple:#8b5cf6;--tb-green:#34d399;
    --tb-red:#fb7185;--tb-amber:#fbbf24;--tb-blue:#38bdf8
}
.tb-v2{direction:rtl;color:var(--tb-text);padding-bottom:30px}
.tb-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:17px}
.tb-kicker{font-size:10px;color:#957fff;font-weight:900;letter-spacing:.8px;margin-bottom:6px}
.tb-title{font-size:28px;font-weight:950;line-height:1.1}.tb-sub{font-size:11px;color:var(--tb-muted);margin-top:6px}
.tb-actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
.tb-btn{height:39px;border:1px solid #2b3d5b;border-radius:9px;background:#101b2d;color:#b9c5d6;padding:0 14px;font-size:10px;font-weight:900;cursor:pointer}
.tb-btn.primary{background:linear-gradient(135deg,#7c4dff,#925eff);border-color:#9270ff;color:white}.tb-btn.green{color:#5be0af;border-color:rgba(52,211,153,.25);background:rgba(52,211,153,.06)}
.tb-periods{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}.tb-period{height:32px;padding:0 11px;border-radius:8px;border:1px solid #263956;background:#0e192b;color:#8092ad;font-size:9px;font-weight:900;cursor:pointer}.tb-period.active{border-color:#7c4dff;color:#c0adff;background:rgba(139,92,246,.09)}
.tb-filter-card{display:grid;grid-template-columns:1fr 1fr 170px 170px auto;gap:8px;align-items:end;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:10px;margin-bottom:12px}
.tb-field label{display:block;font-size:8px;color:#697b95;font-weight:900;margin-bottom:5px}.tb-input,.tb-select{width:100%;height:39px;border:1px solid #263956;background:#0f1b2f;color:#e9eff8;border-radius:9px;padding:0 10px;outline:none;font-size:10px}.tb-input:focus,.tb-select:focus{border-color:#8b5cf6}
.tb-check{display:flex;align-items:center;gap:7px;height:39px;padding:0 10px;border:1px solid #263956;background:#0f1b2f;border-radius:9px;color:#8fa0b7;font-size:9px;font-weight:800}.tb-check input{accent-color:#8b5cf6}
.tb-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:12px}.tb-kpi{position:relative;background:linear-gradient(145deg,#0e192b,#0a1423);border:1px solid #22334f;border-radius:14px;padding:16px 17px;min-height:100px}.tb-code{position:absolute;left:12px;top:12px;font-size:8px;color:#5f718e;border:1px solid #283b59;border-radius:7px;padding:4px 6px;font-weight:900}.tb-label{font-size:10px;color:#8d9bb1;font-weight:800}.tb-value{font-size:21px;font-weight:950;margin-top:9px;direction:ltr;text-align:right}.tb-note{font-size:8px;color:#5e708a;margin-top:4px}.c-red{color:var(--tb-red)}.c-green{color:var(--tb-green)}.c-purple{color:#b5a2ff}.c-amber{color:var(--tb-amber)}
.tb-status{display:flex;justify-content:space-between;align-items:center;gap:12px;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:10px 13px;margin-bottom:12px}.tb-status-main{font-size:10px;font-weight:900}.tb-status-sub{font-size:8px;color:#60728b;margin-top:3px}.tb-pill{display:inline-flex;border-radius:999px;padding:6px 9px;font-size:8px;font-weight:900}.tb-pill.ok{color:#59dfad;background:rgba(52,211,153,.07);border:1px solid rgba(52,211,153,.2)}.tb-pill.bad{color:#fb91a1;background:rgba(251,113,133,.07);border:1px solid rgba(251,113,133,.2)}
.tb-card{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;overflow:hidden}.tb-card-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #1b2a40}.tb-card-title{font-size:14px;font-weight:950}.tb-card-sub{font-size:8px;color:#61728b;margin-top:3px}.tb-count{font-size:8px;color:#8496af;border:1px solid #2a3b58;border-radius:999px;padding:5px 8px;font-weight:900}
.tb-scroll{overflow:auto}.tb-table{width:100%;border-collapse:separate;border-spacing:0;min-width:1180px;font-size:9px}.tb-table th{padding:9px 7px;background:#0e1828;color:#60728c;font-size:8px;font-weight:900;text-align:center;border-bottom:1px solid #1c2b42;position:sticky;top:0;z-index:2}.tb-table th.right{text-align:right}.tb-table .band{background:#101d32;color:#8293ac}.tb-table td{padding:10px 7px;text-align:center;border-bottom:1px solid #17253a;color:#aebbd0}.tb-table tr:hover td{background:#101c2f}.tb-table td.right{text-align:right}.tb-code-cell{color:#9a85f5!important;font-weight:900}.tb-name-cell{color:#e1e8f2!important;font-weight:850}.tb-type{display:inline-flex;border-radius:7px;padding:4px 7px;font-size:7px;font-weight:900;border:1px solid}.type-asset{color:#5be0af;background:rgba(52,211,153,.06);border-color:rgba(52,211,153,.18)}.type-liability{color:#fb91a1;background:rgba(251,113,133,.06);border-color:rgba(251,113,133,.18)}.type-equity{color:#b7a4ff;background:rgba(139,92,246,.07);border-color:rgba(139,92,246,.2)}.type-revenue{color:#5fcdf5;background:rgba(56,189,248,.06);border-color:rgba(56,189,248,.18)}.type-expense{color:#f4c94e;background:rgba(251,191,36,.06);border-color:rgba(251,191,36,.18)}
.tb-dr{color:#fb91a1!important;font-weight:900}.tb-cr{color:#5be0af!important;font-weight:900}.tb-zero{color:#43546d!important}.tb-reverse td{box-shadow:inset 0 0 0 999px rgba(251,191,36,.015)}.tb-reverse .tb-name-cell:after{content:" رصيد عكس الطبيعة";margin-right:6px;color:#f4c94e;font-size:7px;font-weight:800}.tb-link{background:none;border:0;color:#9c87f5;font-size:8px;font-weight:900;cursor:pointer}.tb-total td{position:sticky;bottom:0;background:#111b30!important;border-top:1px solid #3b3566;font-weight:950;z-index:1}
.tb-empty{text-align:center;padding:55px 20px;color:#5c6e89;font-size:10px}
.tb-drawer{position:fixed;inset:0;z-index:1100;background:rgba(2,6,13,.72);backdrop-filter:blur(5px);display:flex;justify-content:flex-start}.tb-drawer-panel{width:min(760px,94vw);height:100%;background:#0a1423;border-right:1px solid #2b3c59;box-shadow:20px 0 70px rgba(0,0,0,.45);overflow:auto}.tb-drawer-head{position:sticky;top:0;z-index:4;background:#0d1829;border-bottom:1px solid #21314a;padding:16px;display:flex;justify-content:space-between;align-items:flex-start}.tb-drawer-title{font-size:16px;font-weight:950}.tb-drawer-sub{font-size:8px;color:#63758f;margin-top:4px}.tb-close{width:31px;height:31px;border-radius:8px;border:1px solid #2b3d5b;background:#101c30;color:#91a2bb;cursor:pointer}.tb-mini-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;padding:12px}.tb-mini{background:#0e1a2c;border:1px solid #21334f;border-radius:10px;padding:10px}.tb-mini span{display:block;font-size:7px;color:#61738d}.tb-mini strong{display:block;font-size:11px;margin-top:5px;direction:ltr;text-align:right}.tb-ledger-head,.tb-ledger-row{display:grid;grid-template-columns:85px 105px minmax(180px,1fr) 95px 95px 105px;align-items:center}.tb-ledger-head{padding:9px 12px;color:#61728b;font-size:7px;font-weight:900;background:#0e1828;border-block:1px solid #1b2a40}.tb-ledger-row{padding:10px 12px;border-bottom:1px solid #17253a;font-size:8px}.tb-ledger-ref{color:#9984f3;font-weight:900}.tb-ledger-desc{color:#cbd5e3}.tb-open-entry{color:#9d88f6;text-decoration:none;font-weight:900}
@media(max-width:1100px){.tb-kpis{grid-template-columns:1fr 1fr}.tb-filter-card{grid-template-columns:1fr 1fr 1fr}.tb-check{grid-column:auto}}
@media(max-width:650px){.tb-head{align-items:flex-start;flex-direction:column}.tb-kpis,.tb-filter-card{grid-template-columns:1fr}.tb-title{font-size:23px}.tb-mini-kpis{grid-template-columns:1fr}}
</style>

@php
$typeMap = [
    'asset'=>['أصول','type-asset'],
    'liability'=>['خصوم','type-liability'],
    'equity'=>['حقوق ملكية','type-equity'],
    'revenue'=>['إيرادات','type-revenue'],
    'expense'=>['مصروفات','type-expense'],
];
$diff = abs($totalDebit - $totalCredit);
@endphp

<div class="tb-v2">

    <div class="tb-head">
        <div>
            <div class="tb-kicker">GENERAL LEDGER · CONTROL REPORT</div>
            <div class="tb-title">ميزان المراجعة</div>
            <div class="tb-sub">مراجعة أرصدة الحسابات وحركة الفترة من القيود المرحلة فقط مع Drill-down للأستاذ</div>
        </div>

        @if($loaded)
        <div class="tb-actions">
            <button wire:click="exportExcel" class="tb-btn green">Excel CSV</button>
            <button wire:click="exportPdf" class="tb-btn green">PDF</button>
        </div>
        @endif
    </div>

    <div class="tb-periods">
        <button wire:click="setPeriod('today')" class="tb-period {{ $quickPeriod === 'today' ? 'active' : '' }}">اليوم</button>
        <button wire:click="setPeriod('month')" class="tb-period {{ $quickPeriod === 'month' ? 'active' : '' }}">هذا الشهر</button>
        <button wire:click="setPeriod('quarter')" class="tb-period {{ $quickPeriod === 'quarter' ? 'active' : '' }}">الربع الحالي</button>
        <button wire:click="setPeriod('year')" class="tb-period {{ $quickPeriod === 'year' ? 'active' : '' }}">السنة الحالية</button>
    </div>

    <div class="tb-filter-card">
        <div class="tb-field">
            <label>من تاريخ</label>
            <input type="date" wire:model="dateFrom" class="tb-input">
        </div>
        <div class="tb-field">
            <label>إلى تاريخ</label>
            <input type="date" wire:model="dateTo" class="tb-input">
        </div>
        <div class="tb-field">
            <label>نوع الحساب</label>
            <select wire:model="accountType" class="tb-select">
                <option value="all">كل الأنواع</option>
                <option value="asset">أصول</option>
                <option value="liability">خصوم</option>
                <option value="equity">حقوق الملكية</option>
                <option value="revenue">إيرادات</option>
                <option value="expense">مصروفات</option>
            </select>
        </div>
        <div class="tb-field">
            <label>المستوى</label>
            <select wire:model="levelFilter" class="tb-select">
                <option value="detail">تفصيلي فقط</option>
                <option value="all">كل المستويات ذات الرصيد</option>
                <option value="main">رئيسي</option>
                <option value="sub">فرعي</option>
            </select>
        </div>
        <div style="display:flex;gap:7px;align-items:end">
            <label class="tb-check">
                <input type="checkbox" wire:model="onlyWithBalance">
                ذات الرصيد/الحركة فقط
            </label>
            <button wire:click="loadTrialBalance" class="tb-btn primary">عرض الميزان</button>
        </div>
    </div>

    @if($loaded)
    <div class="tb-kpis">
        <div class="tb-kpi">
            <div class="tb-code">DR</div>
            <div class="tb-label">الرصيد الختامي المدين</div>
            <div class="tb-value c-red">{{ number_format($totalDebit,2) }} ر.س</div>
            <div class="tb-note">إجمالي الأرصدة المدينة في نهاية الفترة</div>
        </div>
        <div class="tb-kpi">
            <div class="tb-code">CR</div>
            <div class="tb-label">الرصيد الختامي الدائن</div>
            <div class="tb-value c-green">{{ number_format($totalCredit,2) }} ر.س</div>
            <div class="tb-note">إجمالي الأرصدة الدائنة في نهاية الفترة</div>
        </div>
        <div class="tb-kpi">
            <div class="tb-code">DIFF</div>
            <div class="tb-label">فرق الميزان</div>
            <div class="tb-value {{ $diff < 0.01 ? 'c-green' : 'c-red' }}">{{ number_format($diff,2) }} ر.س</div>
            <div class="tb-note">{{ $diff < 0.01 ? 'الميزان متوازن' : 'توجد مشكلة تحتاج مراجعة' }}</div>
        </div>
        <div class="tb-kpi">
            <div class="tb-code">ACCT</div>
            <div class="tb-label">الحسابات الظاهرة</div>
            <div class="tb-value c-purple">{{ $accountsCount }}</div>
            <div class="tb-note">{{ $reverseNatureCount }} حساب برصيد عكس الطبيعة</div>
        </div>
    </div>

    <div class="tb-status">
        <div>
            <div class="tb-status-main">حركة الفترة: مدين {{ number_format($periodDebit,2) }} ر.س · دائن {{ number_format($periodCredit,2) }} ر.س</div>
            <div class="tb-status-sub">الفترة من {{ $dateFrom }} إلى {{ $dateTo }} · القيود المرحلة فقط</div>
        </div>
        <span class="tb-pill {{ $diff < 0.01 ? 'ok' : 'bad' }}">{{ $diff < 0.01 ? 'متوازن' : 'غير متوازن' }}</span>
    </div>

    <div class="tb-card">
        <div class="tb-card-head">
            <div>
                <div class="tb-card-title">تفاصيل ميزان المراجعة</div>
                <div class="tb-card-sub">اضغط "الأستاذ" بجانب أي حساب لمراجعة القيود المكوّنة للرصيد</div>
            </div>
            <span class="tb-count">{{ count($accounts) }} حساب</span>
        </div>

        @if(empty($accounts))
            <div class="tb-empty">لا توجد حسابات مطابقة للفترة والفلاتر الحالية</div>
        @else
        <div class="tb-scroll">
            <table class="tb-table">
                <thead>
                    <tr>
                        <th class="right" rowspan="2">الكود</th>
                        <th class="right" rowspan="2">اسم الحساب</th>
                        <th rowspan="2">النوع</th>
                        <th colspan="2" class="band">الرصيد الافتتاحي</th>
                        <th colspan="2" class="band">حركة الفترة</th>
                        <th colspan="2" class="band">الإجمالي</th>
                        <th colspan="2" class="band">الرصيد الختامي</th>
                        <th rowspan="2">الحركات</th>
                        <th rowspan="2">التفاصيل</th>
                    </tr>
                    <tr>
                        <th>مدين</th><th>دائن</th>
                        <th>مدين</th><th>دائن</th>
                        <th>مدين</th><th>دائن</th>
                        <th>مدين</th><th>دائن</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $acc)
                        @php $type = $typeMap[$acc['type']] ?? [$acc['type'],'']; @endphp
                        <tr class="{{ $acc['reverse_nature'] ? 'tb-reverse' : '' }}">
                            <td class="right tb-code-cell">{{ $acc['code'] }}</td>
                            <td class="right tb-name-cell">{{ $acc['name'] }}</td>
                            <td><span class="tb-type {{ $type[1] }}">{{ $type[0] }}</span></td>

                            <td class="{{ $acc['opening_debit'] > 0 ? 'tb-dr' : 'tb-zero' }}">{{ $acc['opening_debit'] > 0 ? number_format($acc['opening_debit'],2) : '-' }}</td>
                            <td class="{{ $acc['opening_credit'] > 0 ? 'tb-cr' : 'tb-zero' }}">{{ $acc['opening_credit'] > 0 ? number_format($acc['opening_credit'],2) : '-' }}</td>

                            <td class="{{ $acc['period_debit'] > 0 ? 'tb-dr' : 'tb-zero' }}">{{ $acc['period_debit'] > 0 ? number_format($acc['period_debit'],2) : '-' }}</td>
                            <td class="{{ $acc['period_credit'] > 0 ? 'tb-cr' : 'tb-zero' }}">{{ $acc['period_credit'] > 0 ? number_format($acc['period_credit'],2) : '-' }}</td>

                            <td class="{{ $acc['total_debit'] > 0 ? 'tb-dr' : 'tb-zero' }}">{{ $acc['total_debit'] > 0 ? number_format($acc['total_debit'],2) : '-' }}</td>
                            <td class="{{ $acc['total_credit'] > 0 ? 'tb-cr' : 'tb-zero' }}">{{ $acc['total_credit'] > 0 ? number_format($acc['total_credit'],2) : '-' }}</td>

                            <td class="{{ $acc['balance_debit'] > 0 ? 'tb-dr' : 'tb-zero' }}">{{ $acc['balance_debit'] > 0 ? number_format($acc['balance_debit'],2) : '-' }}</td>
                            <td class="{{ $acc['balance_credit'] > 0 ? 'tb-cr' : 'tb-zero' }}">{{ $acc['balance_credit'] > 0 ? number_format($acc['balance_credit'],2) : '-' }}</td>

                            <td>{{ $acc['movement_count'] }}</td>
                            <td><button wire:click="openLedger({{ $acc['id'] }})" class="tb-link">الأستاذ</button></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="tb-total">
                        <td colspan="5" class="right">الإجمالي</td>
                        <td class="tb-dr">{{ number_format($periodDebit,2) }}</td>
                        <td class="tb-cr">{{ number_format($periodCredit,2) }}</td>
                        <td colspan="2"></td>
                        <td class="tb-dr">{{ number_format($totalDebit,2) }}</td>
                        <td class="tb-cr">{{ number_format($totalCredit,2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
    @endif

    @if($selectedAccountId)
    <div class="tb-drawer" wire:key="ledger-{{ $selectedAccountId }}">
        <div class="tb-drawer-panel">
            <div class="tb-drawer-head">
                <div>
                    <div class="tb-drawer-title">{{ $selectedAccountCode }} · {{ $selectedAccountName }}</div>
                    <div class="tb-drawer-sub">كشف الأستاذ خلال الفترة {{ $dateFrom }} إلى {{ $dateTo }}</div>
                </div>
                <button wire:click="closeLedger" class="tb-close">×</button>
            </div>

            <div class="tb-mini-kpis">
                <div class="tb-mini">
                    <span>رصيد أول المدة</span>
                    <strong>{{ number_format(($selectedSummary['opening_debit'] ?? 0) ?: ($selectedSummary['opening_credit'] ?? 0),2) }} ر.س</strong>
                </div>
                <div class="tb-mini">
                    <span>حركة الفترة</span>
                    <strong>{{ number_format(($selectedSummary['period_debit'] ?? 0) + ($selectedSummary['period_credit'] ?? 0),2) }} ر.س</strong>
                </div>
                <div class="tb-mini">
                    <span>الرصيد الختامي</span>
                    <strong>{{ number_format(($selectedSummary['closing_debit'] ?? 0) ?: ($selectedSummary['closing_credit'] ?? 0),2) }} ر.س</strong>
                </div>
            </div>

            <div class="tb-ledger-head">
                <div>التاريخ</div><div>المرجع</div><div>البيان</div><div>مدين</div><div>دائن</div><div>الرصيد</div>
            </div>

            @forelse($selectedLedger as $line)
                <div class="tb-ledger-row">
                    <div>{{ $line['date'] }}</div>
                    <div class="tb-ledger-ref">
                        @if($line['journal_entry_id'])
                        <a class="tb-open-entry" href="{{ \App\Filament\Admin\Resources\JournalEntries\JournalEntryResource::getUrl('view',['record'=>$line['journal_entry_id']]) }}">
                            {{ $line['reference'] ?: $line['entry_number'] }}
                        </a>
                        @else
                        {{ $line['reference'] ?: '-' }}
                        @endif
                    </div>
                    <div class="tb-ledger-desc">{{ $line['description'] ?: '-' }}</div>
                    <div class="tb-dr">{{ $line['debit'] > 0 ? number_format($line['debit'],2) : '-' }}</div>
                    <div class="tb-cr">{{ $line['credit'] > 0 ? number_format($line['credit'],2) : '-' }}</div>
                    <div>
                        {{ $line['balance_debit'] > 0 ? number_format($line['balance_debit'],2).' مدين' : number_format($line['balance_credit'],2).' دائن' }}
                    </div>
                </div>
            @empty
                <div class="tb-empty">لا توجد قيود على هذا الحساب خلال الفترة المحددة</div>
            @endforelse

            <div style="padding:14px">
                <a class="tb-btn" style="display:inline-flex;align-items:center;text-decoration:none"
                   href="{{ \App\Filament\Admin\Resources\Accounts\AccountResource::getUrl('view',['record'=>$selectedAccountId]) }}">
                    فتح صفحة الحساب
                </a>
            </div>
        </div>
        <div style="flex:1" wire:click="closeLedger"></div>
    </div>
    @endif
</div>
</x-filament-panels::page>