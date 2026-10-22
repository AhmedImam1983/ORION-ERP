<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --pl-bg:#07101d;--pl-panel:#0d1728;--pl-panel2:#101c30;--pl-line:#22324d;
    --pl-text:#eef3fb;--pl-muted:#71829b;--pl-purple:#8b5cf6;--pl-green:#34d399;
    --pl-red:#fb7185;--pl-amber:#fbbf24;--pl-blue:#38bdf8
}
.pl-v2{direction:rtl;color:var(--pl-text);padding-bottom:30px}
.pl-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:17px}
.pl-kicker{font-size:10px;color:#957fff;font-weight:900;letter-spacing:.8px;margin-bottom:6px}.pl-title{font-size:28px;font-weight:950;line-height:1.1}.pl-sub{font-size:11px;color:var(--pl-muted);margin-top:6px}
.pl-actions{display:flex;gap:7px;flex-wrap:wrap}.pl-btn{height:39px;border:1px solid #2b3d5b;border-radius:9px;background:#101b2d;color:#b9c5d6;padding:0 14px;font-size:10px;font-weight:900;cursor:pointer}.pl-btn.primary{background:linear-gradient(135deg,#7c4dff,#925eff);border-color:#9270ff;color:white}.pl-btn.green{color:#5be0af;border-color:rgba(52,211,153,.25);background:rgba(52,211,153,.06)}
.pl-periods{display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap}.pl-period{height:32px;border:1px solid #263956;background:#0e192b;color:#8293ad;border-radius:8px;padding:0 11px;font-size:9px;font-weight:900;cursor:pointer}
.pl-filter{display:grid;grid-template-columns:1fr 1fr 180px auto auto;gap:8px;align-items:end;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:10px;margin-bottom:12px}.pl-field label{display:block;font-size:8px;color:#697b95;font-weight:900;margin-bottom:5px}.pl-input,.pl-select{width:100%;height:39px;border:1px solid #263956;background:#0f1b2f;color:#e9eff8;border-radius:9px;padding:0 10px;outline:none;font-size:10px}.pl-check{display:flex;align-items:center;gap:7px;height:39px;padding:0 10px;border:1px solid #263956;background:#0f1b2f;border-radius:9px;color:#8fa0b7;font-size:9px;font-weight:800}.pl-check input{accent-color:#8b5cf6}
.pl-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:12px}.pl-kpi{position:relative;background:linear-gradient(145deg,#0e192b,#0a1423);border:1px solid #22334f;border-radius:14px;padding:16px 17px;min-height:102px}.pl-code{position:absolute;left:12px;top:12px;font-size:8px;color:#5f718e;border:1px solid #283b59;border-radius:7px;padding:4px 6px;font-weight:900}.pl-label{font-size:10px;color:#8d9bb1;font-weight:800}.pl-value{font-size:21px;font-weight:950;margin-top:9px;direction:ltr;text-align:right}.pl-note{font-size:8px;color:#5e708a;margin-top:4px}.c-green{color:var(--pl-green)}.c-red{color:var(--pl-red)}.c-purple{color:#b5a2ff}.c-amber{color:var(--pl-amber)}.c-blue{color:#62c9f5}
.pl-control{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:11px 13px;margin-bottom:12px}.pl-control-title{font-size:10px;font-weight:950}.pl-control-sub{font-size:8px;color:#60728b;margin-top:3px}.pl-margins{display:flex;gap:15px;margin-top:5px;font-size:8px;color:#7889a1}.pl-margins strong{color:#cbd5e3}
.pl-card{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;overflow:hidden}.pl-card-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #1b2a40}.pl-card-title{font-size:14px;font-weight:950}.pl-card-sub{font-size:8px;color:#61728b;margin-top:3px}.pl-period-label{font-size:8px;color:#7d8da5;border:1px solid #2a3b58;border-radius:999px;padding:5px 8px;font-weight:900}
.pl-section{border-bottom:1px solid #17253a}.pl-section:last-child{border-bottom:0}.pl-section-head{display:flex;justify-content:space-between;align-items:center;padding:11px 14px;background:#0e192b;cursor:pointer}.pl-section-title{font-size:10px;font-weight:950}.pl-section-total{font-size:11px;font-weight:950;direction:ltr}.pl-section-body{padding:0 11px}
.pl-row{display:grid;grid-template-columns:minmax(210px,1fr) 115px 115px 95px 55px;gap:7px;align-items:center;min-height:44px;border-bottom:1px solid #17253a;font-size:8px}.pl-row:last-child{border-bottom:0}.pl-account{display:flex;align-items:center;gap:8px;min-width:0}.pl-acct-code{color:#9681ef;font-size:8px;font-weight:900;min-width:43px}.pl-acct-name{font-size:9px;color:#dbe4ef;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.pl-current{font-size:9px;font-weight:950;direction:ltr;text-align:right}.pl-previous{font-size:8px;color:#8293a9;direction:ltr;text-align:right}.pl-var{font-size:8px;font-weight:900;direction:ltr;text-align:right}.pl-ledger-btn{border:0;background:transparent;color:#9e89f6;font-size:8px;font-weight:900;cursor:pointer}
.pl-reverse{box-shadow:inset -2px 0 #fbbf24}.pl-reverse .pl-acct-name:after{content:" عكس الطبيعة";margin-right:5px;color:#f6ca4e;font-size:7px}
.pl-subtotal{display:flex;justify-content:space-between;padding:10px 14px;background:#0d1728;font-size:9px;font-weight:950}
.pl-result{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;margin:10px;border-radius:11px;padding:13px 15px;background:rgba(139,92,246,.07);border:1px solid rgba(139,92,246,.18)}.pl-result-label{font-size:10px;font-weight:950}.pl-result-sub{font-size:8px;color:#677990;margin-top:3px}.pl-result-value{font-size:17px;font-weight:950;direction:ltr}
.pl-grand{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;margin:12px;border-radius:13px;padding:16px;background:linear-gradient(135deg,rgba(139,92,246,.12),rgba(139,92,246,.04));border:1px solid rgba(139,92,246,.3)}.pl-grand-title{font-size:13px;font-weight:950}.pl-grand-sub{font-size:8px;color:#70829b;margin-top:4px}.pl-grand-value{font-size:25px;font-weight:950;direction:ltr}
.pl-empty{padding:28px;text-align:center;color:#5d6f89;font-size:9px}
.pl-drawer{position:fixed;inset:0;z-index:1200;background:rgba(2,6,13,.72);backdrop-filter:blur(5px);display:flex;justify-content:flex-start}.pl-drawer-panel{width:min(760px,94vw);height:100%;background:#0a1423;border-right:1px solid #2b3c59;box-shadow:20px 0 70px rgba(0,0,0,.45);overflow:auto}.pl-drawer-head{position:sticky;top:0;z-index:4;background:#0d1829;border-bottom:1px solid #21314a;padding:16px;display:flex;justify-content:space-between}.pl-drawer-title{font-size:16px;font-weight:950}.pl-drawer-sub{font-size:8px;color:#63758f;margin-top:4px}.pl-close{width:31px;height:31px;border-radius:8px;border:1px solid #2b3d5b;background:#101c30;color:#91a2bb;cursor:pointer}.pl-mini-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;padding:12px}.pl-mini{background:#0e1a2c;border:1px solid #21334f;border-radius:10px;padding:10px}.pl-mini span{display:block;font-size:7px;color:#61738d}.pl-mini strong{display:block;font-size:11px;margin-top:5px;direction:ltr;text-align:right}.pl-ledger-head,.pl-ledger-row{display:grid;grid-template-columns:85px 105px minmax(180px,1fr) 90px 90px 105px;align-items:center}.pl-ledger-head{padding:9px 12px;color:#61728b;font-size:7px;font-weight:900;background:#0e1828;border-block:1px solid #1b2a40}.pl-ledger-row{padding:10px 12px;border-bottom:1px solid #17253a;font-size:8px}.pl-entry{color:#9984f3;font-weight:900;text-decoration:none}.pl-desc{color:#cbd5e3}
@media(max-width:1100px){.pl-kpis{grid-template-columns:1fr 1fr}.pl-filter{grid-template-columns:1fr 1fr 1fr}}
@media(max-width:700px){.pl-head{align-items:flex-start;flex-direction:column}.pl-kpis,.pl-filter{grid-template-columns:1fr}.pl-title{font-size:23px}.pl-row{grid-template-columns:minmax(180px,1fr) 100px 50px}.pl-previous,.pl-var{display:none}.pl-mini-kpis{grid-template-columns:1fr}.pl-ledger-head,.pl-ledger-row{min-width:700px}.pl-drawer-panel{overflow-x:auto}}
</style>

@php
$revenueChange = $showComparison && abs($previousTotalRevenue) >= .01
    ? (($totalRevenue-$previousTotalRevenue)/abs($previousTotalRevenue))*100
    : null;

$grossChange = $showComparison && abs($previousGrossProfit) >= .01
    ? (($grossProfit-$previousGrossProfit)/abs($previousGrossProfit))*100
    : null;

$operatingChange = $showComparison && abs($previousOperatingProfit) >= .01
    ? (($operatingProfit-$previousOperatingProfit)/abs($previousOperatingProfit))*100
    : null;

$netChange = $showComparison && abs($previousNetProfit) >= .01
    ? (($netProfit-$previousNetProfit)/abs($previousNetProfit))*100
    : null;
@endphp

<div class="pl-v2">
    <div class="pl-head">
        <div>
            <div class="pl-kicker">FINANCIAL STATEMENTS · INCOME STATEMENT</div>
            <div class="pl-title">قائمة الأرباح والخسائر</div>
            <div class="pl-sub">تقرير تشغيلي مبني بالكامل على القيود المرحلة في الأستاذ العام</div>
        </div>

        @if($loaded)
        <div class="pl-actions">
            <button wire:click="exportExcel" class="pl-btn green">Excel CSV</button>
            <button wire:click="exportPdf" class="pl-btn green">PDF</button>
        </div>
        @endif
    </div>

    <div class="pl-periods">
        <button wire:click="setPeriod('today')" class="pl-period">اليوم</button>
        <button wire:click="setPeriod('month')" class="pl-period">هذا الشهر</button>
        <button wire:click="setPeriod('quarter')" class="pl-period">الربع الحالي</button>
        <button wire:click="setPeriod('year')" class="pl-period">السنة الحالية</button>
    </div>

    <div class="pl-filter">
        <div class="pl-field">
            <label>من تاريخ</label>
            <input wire:model="dateFrom" type="date" class="pl-input">
        </div>

        <div class="pl-field">
            <label>إلى تاريخ</label>
            <input wire:model="dateTo" type="date" class="pl-input">
        </div>

        <div class="pl-field">
            <label>طريقة العرض</label>
            <select wire:model="viewMode" class="pl-select">
                <option value="detailed">تفصيلي</option>
                <option value="summary">ملخص</option>
            </select>
        </div>

        <label class="pl-check">
            <input type="checkbox" wire:model="showComparison">
            مقارنة بنفس الفترة من العام السابق
        </label>

        <button wire:click="loadReport" class="pl-btn primary">عرض التقرير</button>
    </div>

    @if($loaded)
    <div class="pl-kpis">
        <div class="pl-kpi">
            <div class="pl-code">REV</div>
            <div class="pl-label">إجمالي الإيرادات</div>
            <div class="pl-value c-green">{{ number_format($totalRevenue,2) }} ر.س</div>
            <div class="pl-note">@if($revenueChange !== null) مقارنة: {{ $revenueChange >= 0 ? '+' : '' }}{{ number_format($revenueChange,1) }}% @else الفترة الحالية @endif</div>
        </div>

        <div class="pl-kpi">
            <div class="pl-code">GROSS</div>
            <div class="pl-label">مجمل الربح</div>
            <div class="pl-value {{ $grossProfit >= 0 ? 'c-blue':'c-red' }}">{{ number_format($grossProfit,2) }} ر.س</div>
            <div class="pl-note">Gross Margin {{ number_format($grossProfitMargin,1) }}% @if($grossChange !== null) · {{ $grossChange >= 0 ? '+' : '' }}{{ number_format($grossChange,1) }}% @endif</div>
        </div>

        <div class="pl-kpi">
            <div class="pl-code">OP</div>
            <div class="pl-label">الربح التشغيلي</div>
            <div class="pl-value {{ $operatingProfit >= 0 ? 'c-purple':'c-red' }}">{{ number_format($operatingProfit,2) }} ر.س</div>
            <div class="pl-note">Operating Margin {{ number_format($operatingProfitMargin,1) }}% @if($operatingChange !== null) · {{ $operatingChange >= 0 ? '+' : '' }}{{ number_format($operatingChange,1) }}% @endif</div>
        </div>

        <div class="pl-kpi">
            <div class="pl-code">NET</div>
            <div class="pl-label">صافي {{ $netProfit >= 0 ? 'الربح':'الخسارة' }}</div>
            <div class="pl-value {{ $netProfit >= 0 ? 'c-green':'c-red' }}">{{ number_format(abs($netProfit),2) }} ر.س</div>
            <div class="pl-note">Net Margin {{ number_format($netProfitMargin,1) }}% @if($netChange !== null) · {{ $netChange >= 0 ? '+' : '' }}{{ number_format($netChange,1) }}% @endif</div>
        </div>
    </div>

    <div class="pl-control">
        <div>
            <div class="pl-control-title">الفترة {{ $dateFrom }} إلى {{ $dateTo }}</div>
            <div class="pl-control-sub">
                القيود المرحلة فقط
                @if($showComparison)
                    · مقارنة {{ $comparisonFrom }} إلى {{ $comparisonTo }}
                @endif
                · {{ $reverseNatureCount }} حساب برصيد عكس الطبيعة
            </div>
            <div class="pl-margins">
                <span>Gross Margin: <strong>{{ number_format($grossProfitMargin,1) }}%</strong></span>
                <span>Operating Margin: <strong>{{ number_format($operatingProfitMargin,1) }}%</strong></span>
                <span>Net Margin: <strong>{{ number_format($netProfitMargin,1) }}%</strong></span>
            </div>
        </div>
        <span class="pl-period-label">GL Based</span>
    </div>

    <div class="pl-card">
        <div class="pl-card-head">
            <div>
                <div class="pl-card-title">قائمة الدخل</div>
                <div class="pl-card-sub">اضغط "الأستاذ" أمام أي حساب لمراجعة القيود المكوّنة للقيمة</div>
            </div>
            <span class="pl-period-label">{{ $viewMode === 'detailed' ? 'تفصيلي':'ملخص' }}</span>
        </div>

        <div class="pl-section" x-data="{open:true}">
            <div class="pl-section-head" x-on:click="open=!open">
                <span class="pl-section-title">إيرادات المبيعات</span>
                <span class="pl-section-total c-green">{{ number_format($salesRevenue,2) }} ر.س</span>
            </div>
            <div class="pl-section-body" x-show="open">
                @if($viewMode === 'detailed')
                    @forelse($salesRevenueDetails as $item)
                        <div class="pl-row {{ $item['reverse_nature'] ? 'pl-reverse':'' }}">
                            <div class="pl-account"><span class="pl-acct-code">{{ $item['code'] }}</span><span class="pl-acct-name">{{ $item['name'] }}</span></div>
                            <div class="pl-current {{ $item['amount'] >= 0 ? 'c-green':'c-red' }}">{{ number_format($item['amount'],2) }}</div>
                            <div class="pl-previous">{{ $showComparison ? number_format($item['comparison_amount'],2) : '-' }}</div>
                            <div class="pl-var {{ $item['variance'] >= 0 ? 'c-green':'c-red' }}">
                                @if($showComparison)
                                    {{ $item['variance'] >= 0 ? '+' : '' }}{{ number_format($item['variance'],2) }}
                                    @if($item['variance_pct'] !== null) <small>({{ number_format($item['variance_pct'],1) }}%)</small> @endif
                                @else - @endif
                            </div>
                            <button wire:click="openAccount({{ $item['id'] }})" class="pl-ledger-btn">الأستاذ</button>
                        </div>
                    @empty
                        <div class="pl-empty">لا توجد إيرادات مبيعات خلال الفترة</div>
                    @endforelse
                @endif
            </div>
            <div class="pl-subtotal"><span>صافي إيرادات المبيعات</span><span class="c-green">{{ number_format($salesRevenue,2) }} ر.س</span></div>
        </div>

        <div class="pl-section" x-data="{open:true}">
            <div class="pl-section-head" x-on:click="open=!open">
                <span class="pl-section-title">تكلفة المبيعات</span>
                <span class="pl-section-total c-red">{{ number_format($totalCogs,2) }} ر.س</span>
            </div>
            <div class="pl-section-body" x-show="open">
                @if($viewMode === 'detailed')
                    @forelse($cogsDetails as $item)
                        <div class="pl-row {{ $item['reverse_nature'] ? 'pl-reverse':'' }}">
                            <div class="pl-account"><span class="pl-acct-code">{{ $item['code'] }}</span><span class="pl-acct-name">{{ $item['name'] }}</span></div>
                            <div class="pl-current {{ $item['amount'] >= 0 ? 'c-red':'c-green' }}">{{ number_format($item['amount'],2) }}</div>
                            <div class="pl-previous">{{ $showComparison ? number_format($item['comparison_amount'],2) : '-' }}</div>
                            <div class="pl-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                            <button wire:click="openAccount({{ $item['id'] }})" class="pl-ledger-btn">الأستاذ</button>
                        </div>
                    @empty
                        <div class="pl-empty">لا توجد تكلفة مبيعات خلال الفترة</div>
                    @endforelse
                @endif
            </div>
            <div class="pl-subtotal"><span>إجمالي تكلفة المبيعات</span><span class="c-red">{{ number_format($totalCogs,2) }} ر.س</span></div>
        </div>

        <div class="pl-result">
            <div>
                <div class="pl-result-label">مجمل الربح</div>
                <div class="pl-result-sub">إيرادات المبيعات ناقص تكلفة المبيعات · هامش {{ number_format($grossProfitMargin,1) }}%</div>
            </div>
            <div class="pl-result-value {{ $grossProfit >= 0 ? 'c-blue':'c-red' }}">{{ number_format($grossProfit,2) }} ر.س</div>
        </div>

        <div class="pl-section" x-data="{open:true}">
            <div class="pl-section-head" x-on:click="open=!open">
                <span class="pl-section-title">المصروفات التشغيلية</span>
                <span class="pl-section-total c-amber">{{ number_format($totalOpex,2) }} ر.س</span>
            </div>
            <div class="pl-section-body" x-show="open">
                @if($viewMode === 'detailed')
                    @forelse($opexDetails as $item)
                        <div class="pl-row {{ $item['reverse_nature'] ? 'pl-reverse':'' }}">
                            <div class="pl-account"><span class="pl-acct-code">{{ $item['code'] }}</span><span class="pl-acct-name">{{ $item['name'] }}</span></div>
                            <div class="pl-current c-amber">{{ number_format($item['amount'],2) }}</div>
                            <div class="pl-previous">{{ $showComparison ? number_format($item['comparison_amount'],2) : '-' }}</div>
                            <div class="pl-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                            <button wire:click="openAccount({{ $item['id'] }})" class="pl-ledger-btn">الأستاذ</button>
                        </div>
                    @empty
                        <div class="pl-empty">لا توجد مصروفات تشغيلية</div>
                    @endforelse
                @endif
            </div>
            <div class="pl-subtotal"><span>إجمالي المصروفات التشغيلية</span><span class="c-amber">{{ number_format($totalOpex,2) }} ر.س</span></div>
        </div>

        <div class="pl-result">
            <div>
                <div class="pl-result-label">الربح التشغيلي</div>
                <div class="pl-result-sub">مجمل الربح ناقص المصروفات التشغيلية · هامش {{ number_format($operatingProfitMargin,1) }}%</div>
            </div>
            <div class="pl-result-value {{ $operatingProfit >= 0 ? 'c-purple':'c-red' }}">{{ number_format($operatingProfit,2) }} ر.س</div>
        </div>

        @if(!empty($otherRevenueDetails) || $viewMode === 'summary')
        <div class="pl-section" x-data="{open:true}">
            <div class="pl-section-head" x-on:click="open=!open">
                <span class="pl-section-title">إيرادات أخرى</span>
                <span class="pl-section-total c-green">{{ number_format($otherRevenue,2) }} ر.س</span>
            </div>
            <div class="pl-section-body" x-show="open">
                @if($viewMode === 'detailed')
                    @foreach($otherRevenueDetails as $item)
                        <div class="pl-row {{ $item['reverse_nature'] ? 'pl-reverse':'' }}">
                            <div class="pl-account"><span class="pl-acct-code">{{ $item['code'] }}</span><span class="pl-acct-name">{{ $item['name'] }}</span></div>
                            <div class="pl-current c-green">{{ number_format($item['amount'],2) }}</div>
                            <div class="pl-previous">{{ $showComparison ? number_format($item['comparison_amount'],2) : '-' }}</div>
                            <div class="pl-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                            <button wire:click="openAccount({{ $item['id'] }})" class="pl-ledger-btn">الأستاذ</button>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="pl-subtotal"><span>إجمالي الإيرادات الأخرى</span><span class="c-green">{{ number_format($otherRevenue,2) }} ر.س</span></div>
        </div>
        @endif

        @if(!empty($financialDetails) || $viewMode === 'summary')
        <div class="pl-section" x-data="{open:true}">
            <div class="pl-section-head" x-on:click="open=!open">
                <span class="pl-section-title">المصروفات التمويلية</span>
                <span class="pl-section-total c-red">{{ number_format($totalFinancial,2) }} ر.س</span>
            </div>
            <div class="pl-section-body" x-show="open">
                @if($viewMode === 'detailed')
                    @foreach($financialDetails as $item)
                        <div class="pl-row {{ $item['reverse_nature'] ? 'pl-reverse':'' }}">
                            <div class="pl-account"><span class="pl-acct-code">{{ $item['code'] }}</span><span class="pl-acct-name">{{ $item['name'] }}</span></div>
                            <div class="pl-current c-red">{{ number_format($item['amount'],2) }}</div>
                            <div class="pl-previous">{{ $showComparison ? number_format($item['comparison_amount'],2) : '-' }}</div>
                            <div class="pl-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                            <button wire:click="openAccount({{ $item['id'] }})" class="pl-ledger-btn">الأستاذ</button>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="pl-subtotal"><span>إجمالي المصروفات التمويلية</span><span class="c-red">{{ number_format($totalFinancial,2) }} ر.س</span></div>
        </div>
        @endif

        @if(!empty($otherExpenseDetails) || $viewMode === 'summary')
        <div class="pl-section" x-data="{open:true}">
            <div class="pl-section-head" x-on:click="open=!open">
                <span class="pl-section-title">مصروفات أخرى</span>
                <span class="pl-section-total c-red">{{ number_format($totalOtherExpenses,2) }} ر.س</span>
            </div>
            <div class="pl-section-body" x-show="open">
                @if($viewMode === 'detailed')
                    @foreach($otherExpenseDetails as $item)
                        <div class="pl-row {{ $item['reverse_nature'] ? 'pl-reverse':'' }}">
                            <div class="pl-account"><span class="pl-acct-code">{{ $item['code'] }}</span><span class="pl-acct-name">{{ $item['name'] }}</span></div>
                            <div class="pl-current c-red">{{ number_format($item['amount'],2) }}</div>
                            <div class="pl-previous">{{ $showComparison ? number_format($item['comparison_amount'],2) : '-' }}</div>
                            <div class="pl-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                            <button wire:click="openAccount({{ $item['id'] }})" class="pl-ledger-btn">الأستاذ</button>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="pl-subtotal"><span>إجمالي المصروفات الأخرى</span><span class="c-red">{{ number_format($totalOtherExpenses,2) }} ر.س</span></div>
        </div>
        @endif

        <div class="pl-grand">
            <div>
                <div class="pl-grand-title">صافي {{ $netProfit >= 0 ? 'الربح':'الخسارة' }}</div>
                <div class="pl-grand-sub">النتيجة النهائية للفترة · Net Margin {{ number_format($netProfitMargin,1) }}%</div>
            </div>
            <div class="pl-grand-value {{ $netProfit >= 0 ? 'c-green':'c-red' }}">{{ number_format(abs($netProfit),2) }} ر.س</div>
        </div>
    </div>
    @endif

    @if($selectedAccountId)
    <div class="pl-drawer">
        <div class="pl-drawer-panel">
            <div class="pl-drawer-head">
                <div>
                    <div class="pl-drawer-title">{{ $selectedAccountCode }} · {{ $selectedAccountName }}</div>
                    <div class="pl-drawer-sub">تفاصيل الأستاذ من {{ $dateFrom }} إلى {{ $dateTo }} · آخر 50 حركة معروضة</div>
                </div>
                <button wire:click="closeAccount" class="pl-close">×</button>
            </div>

            <div class="pl-mini-kpis">
                <div class="pl-mini"><span>إجمالي المدين</span><strong class="c-red">{{ number_format($selectedSummary['debit'] ?? 0,2) }} ر.س</strong></div>
                <div class="pl-mini"><span>إجمالي الدائن</span><strong class="c-green">{{ number_format($selectedSummary['credit'] ?? 0,2) }} ر.س</strong></div>
                <div class="pl-mini"><span>صافي أثر الحساب</span><strong>{{ number_format($selectedSummary['net'] ?? 0,2) }} ر.س</strong></div>
            </div>

            <div style="overflow:auto">
                <div class="pl-ledger-head"><div>التاريخ</div><div>المرجع</div><div>البيان</div><div>مدين</div><div>دائن</div><div>الأثر</div></div>
                @forelse($selectedLedger as $line)
                    <div class="pl-ledger-row">
                        <div>{{ $line['date'] }}</div>
                        <div>
                            @if($line['journal_entry_id'])
                            <a class="pl-entry" href="{{ \App\Filament\Admin\Resources\JournalEntries\JournalEntryResource::getUrl('view',['record'=>$line['journal_entry_id']]) }}">
                                {{ $line['reference'] ?: $line['entry_number'] }}
                            </a>
                            @else -
                            @endif
                        </div>
                        <div class="pl-desc">{{ $line['description'] ?: '-' }}</div>
                        <div class="c-red">{{ $line['debit'] > 0 ? number_format($line['debit'],2) : '-' }}</div>
                        <div class="c-green">{{ $line['credit'] > 0 ? number_format($line['credit'],2) : '-' }}</div>
                        <div>{{ number_format($line['effect'],2) }}</div>
                    </div>
                @empty
                    <div class="pl-empty">لا توجد قيود على هذا الحساب خلال الفترة</div>
                @endforelse
            </div>

            <div style="padding:14px">
                <a class="pl-btn" style="display:inline-flex;align-items:center;text-decoration:none"
                   href="{{ \App\Filament\Admin\Resources\Accounts\AccountResource::getUrl('view',['record'=>$selectedAccountId]) }}">
                    فتح صفحة الحساب
                </a>
            </div>
        </div>

        <div style="flex:1" wire:click="closeAccount"></div>
    </div>
    @endif
</div>
</x-filament-panels::page>