<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
.pl-wrap{direction:rtl;display:flex;flex-direction:column;gap:18px;max-width:100%}
.pl-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:4px 4px 2px}
.pl-hero-title{display:flex;align-items:center;gap:12px}
.pl-hero-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(145deg,#26345c,#11182c);border:1px solid rgba(124,77,255,.35);box-shadow:0 8px 30px rgba(0,0,0,.22);font-size:24px}
.pl-hero h1{margin:0;color:#f8fafc;font-size:26px;font-weight:900;line-height:1.2}
.pl-hero p{margin:6px 0 0;color:#71809b;font-size:12px;font-weight:600}
.pl-card{background:linear-gradient(145deg,#151d30,#0d1423);border:1px solid #22304a;border-radius:16px;padding:18px;box-shadow:0 12px 35px rgba(0,0,0,.12)}
.pl-filters{display:grid;grid-template-columns:1fr 1fr auto auto;align-items:end;gap:12px}
.pl-field{min-width:0}
.pl-label{font-size:12px;color:#8290a8;font-weight:700;margin:0 0 7px}
.pl-date{width:100%;height:44px;background:#1b2436;border:1px solid #30405d;border-radius:11px;padding:9px 13px;color:#e8edf7;font-size:13px;font-weight:700;outline:none;transition:.2s}
.pl-date:focus{border-color:#7c4dff;box-shadow:0 0 0 3px rgba(124,77,255,.12)}
.btn-load,.btn-pdf{height:44px;border-radius:11px;padding:0 20px;cursor:pointer;font-size:13px;font-weight:800;font-family:Cairo,sans-serif;transition:.2s;white-space:nowrap}
.btn-load{background:linear-gradient(135deg,#7c4dff,#5b35d5);border:1px solid #8f6cff;color:#fff;box-shadow:0 7px 18px rgba(92,53,213,.22)}
.btn-load:hover{transform:translateY(-1px);filter:brightness(1.08)}
.btn-pdf{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.4);color:#4ade80}
.btn-pdf:hover{background:rgba(34,197,94,.18)}

.kpi-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.kpi{position:relative;overflow:hidden;min-height:150px;border-radius:16px;padding:19px 20px;display:flex;flex-direction:column;justify-content:space-between;border:1px solid rgba(255,255,255,.09);box-shadow:0 12px 30px rgba(0,0,0,.16)}
.kpi:after{content:"";position:absolute;width:110px;height:110px;border-radius:50%;left:-35px;bottom:-55px;background:rgba(255,255,255,.08);filter:blur(1px)}
.kpi-head{display:flex;align-items:center;justify-content:space-between;gap:10px}
.kpi-l{font-size:13px;color:rgba(255,255,255,.88);font-weight:800}
.kpi-icon{width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.12);display:grid;place-items:center;font-size:18px}
.kpi-v{font-size:30px;line-height:1;font-weight:900;color:#fff;letter-spacing:.2px;margin-top:8px}
.kpi-unit{font-size:12px;font-weight:800;margin-right:4px}
.kpi-sub{font-size:11px;margin-top:7px;color:rgba(255,255,255,.72);font-weight:600}

.pl-statement{padding:20px}
.pl-statement-head{display:flex;align-items:center;justify-content:space-between;gap:15px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06)}
.pl-statement-title{display:flex;align-items:center;gap:10px}
.pl-statement-title h2{margin:0;color:#f1f5f9;font-size:18px;font-weight:900}
.pl-statement-title span{font-size:18px}
.pl-period{color:#71809b;font-size:11px;font-weight:600}
.pl-section{margin-top:16px}
.pl-section-title{font-size:14px;font-weight:900;color:#c4b5fd;padding:12px 14px;background:linear-gradient(90deg,rgba(124,77,255,.13),rgba(124,77,255,.035));border:1px solid rgba(124,77,255,.16);border-radius:11px;display:flex;justify-content:space-between;align-items:center}
.pl-section-title .amount{font-size:13px;font-weight:900}
.pl-row{display:flex;justify-content:space-between;align-items:center;gap:20px;min-height:48px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.045)}
.pl-row:hover{background:rgba(255,255,255,.018)}
.pl-row-label{font-size:13px;color:#9aa7bd;font-weight:600}
.pl-row-label.indent{padding-right:20px;font-size:12px;color:#73819a}
.pl-row-value{font-size:13px;font-weight:900;white-space:nowrap}
.pl-total{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:13px 14px;margin-top:4px;background:rgba(255,255,255,.025);border-radius:9px}
.pl-total-label{font-size:13px;font-weight:900;color:#e2e8f0}
.pl-total-value{font-size:16px;font-weight:900;white-space:nowrap}
.pl-grand-total{margin-top:20px;position:relative;overflow:hidden;background:linear-gradient(135deg,rgba(91,79,255,.24),rgba(91,79,255,.07));border:1px solid rgba(124,77,255,.42);border-radius:14px;padding:18px 20px;display:flex;justify-content:space-between;align-items:center;gap:20px}
.pl-grand-total:after{content:"";position:absolute;left:-30px;bottom:-65px;width:180px;height:180px;border-radius:50%;background:rgba(124,77,255,.12)}
.pl-grand-label{position:relative;z-index:1;font-size:15px;font-weight:900;color:#eef2ff}
.pl-grand-label small{display:block;font-size:11px;color:#7886a0;font-weight:600;margin-top:4px}
.pl-grand-value{position:relative;z-index:1;font-size:30px;font-weight:900;white-space:nowrap}
.empty-report{padding:35px;text-align:center;color:#71809b;font-size:13px}

@media(max-width:1100px){
.kpi-row{grid-template-columns:repeat(2,minmax(0,1fr))}
.pl-filters{grid-template-columns:1fr 1fr}
}
@media(max-width:650px){
.pl-hero{align-items:flex-start}
.pl-hero h1{font-size:20px}
.pl-filters{grid-template-columns:1fr}
.kpi-row{grid-template-columns:1fr}
.kpi-v{font-size:26px}
.pl-statement-head{align-items:flex-start;flex-direction:column}
.pl-grand-total{align-items:flex-start;flex-direction:column}
}
</style>

<div class="pl-wrap">

    <div class="pl-hero">
        <div class="pl-hero-title">
            <div class="pl-hero-icon">📈</div>
            <div>
                <h1>قائمة الأرباح والخسائر · P&amp;L</h1>
                <p>التقارير المالية &nbsp; / &nbsp; قائمة الأرباح والخسائر</p>
            </div>
        </div>
    </div>

    <div class="pl-card">
        <div class="pl-filters">
            <div class="pl-field">
                <div class="pl-label">من تاريخ</div>
                <input type="date" wire:model="dateFrom" class="pl-date">
            </div>

            <div class="pl-field">
                <div class="pl-label">إلى تاريخ</div>
                <input type="date" wire:model="dateTo" class="pl-date">
            </div>

            <button wire:click="loadReport" class="btn-load">📊 عرض التقرير</button>

            @if($loaded)
                <button wire:click="exportPdf" class="btn-pdf">📄 تصدير PDF</button>
            @endif
        </div>
    </div>

    @if($loaded)
        <div class="kpi-row">

            <div class="kpi" style="background:linear-gradient(145deg,#08752f,#064f25)">
                <div class="kpi-head">
                    <div class="kpi-l">إجمالي الإيرادات</div>
                    <div class="kpi-icon">📈</div>
                </div>
                <div class="kpi-v">{{ number_format($totalRevenue, 0) }} <span class="kpi-unit">ر.س</span></div>
                <div class="kpi-sub">إجمالي الإيرادات خلال الفترة</div>
            </div>

            <div class="kpi" style="background:linear-gradient(145deg,#b91c1c,#7f1d1d)">
                <div class="kpi-head">
                    <div class="kpi-l">تكلفة البضاعة المباعة</div>
                    <div class="kpi-icon">📦</div>
                </div>
                <div class="kpi-v">{{ number_format($totalCogs, 0) }} <span class="kpi-unit">ر.س</span></div>
                <div class="kpi-sub">تكلفة المبيعات خلال الفترة</div>
            </div>

            <div class="kpi" style="background:linear-gradient(145deg,#2459d9,#173e9d)">
                <div class="kpi-head">
                    <div class="kpi-l">مجمل الربح</div>
                    <div class="kpi-icon">💹</div>
                </div>
                <div class="kpi-v">{{ number_format($grossProfit, 0) }} <span class="kpi-unit">ر.س</span></div>
                <div class="kpi-sub">هامش الربح {{ $grossProfitMargin }}%</div>
            </div>

            <div class="kpi" style="background:linear-gradient(145deg,{{ $netProfit >= 0 ? '#6d28d9,#4c1d95' : '#b91c1c,#7f1d1d' }})">
                <div class="kpi-head">
                    <div class="kpi-l">صافي {{ $netProfit >= 0 ? 'الربح' : 'الخسارة' }}</div>
                    <div class="kpi-icon">💼</div>
                </div>
                <div class="kpi-v">{{ number_format(abs($netProfit), 0) }} <span class="kpi-unit">ر.س</span></div>
                <div class="kpi-sub">هامش {{ $netProfitMargin }}%</div>
            </div>

        </div>

        <div class="pl-card pl-statement">

            <div class="pl-statement-head">
                <div class="pl-statement-title">
                    <span>📊</span>
                    <div>
                        <h2>قائمة الأرباح والخسائر</h2>
                        <div class="pl-period">من {{ $dateFrom }} إلى {{ $dateTo }}</div>
                    </div>
                </div>
                <div class="pl-period">التقرير المالي للفترة المحددة</div>
            </div>

            <div class="pl-section">
                <div class="pl-section-title">
                    <span>📈 الإيرادات</span>
                    <span class="amount" style="color:#4ade80">{{ number_format($totalRevenue, 2) }} ر.س</span>
                </div>

                <div class="pl-row">
                    <span class="pl-row-label indent">إيرادات المبيعات</span>
                    <span class="pl-row-value" style="color:#4ade80">{{ number_format($salesRevenue, 2) }} ر.س</span>
                </div>

                @if($otherRevenue > 0)
                    <div class="pl-row">
                        <span class="pl-row-label indent">إيرادات أخرى</span>
                        <span class="pl-row-value" style="color:#4ade80">{{ number_format($otherRevenue, 2) }} ر.س</span>
                    </div>
                @endif

                <div class="pl-total">
                    <span class="pl-total-label">إجمالي الإيرادات</span>
                    <span class="pl-total-value" style="color:#4ade80">{{ number_format($totalRevenue, 2) }} ر.س</span>
                </div>
            </div>

            <div class="pl-section">
                <div class="pl-section-title">
                    <span>📦 تكلفة البضاعة المباعة</span>
                    <span class="amount" style="color:#f87171">{{ number_format($totalCogs, 2) }} ر.س</span>
                </div>

                <div class="pl-total">
                    <span class="pl-total-label">مجمل الربح</span>
                    <span class="pl-total-value" style="color:{{ $grossProfit >= 0 ? '#4ade80' : '#f87171' }}">
                        {{ number_format($grossProfit, 2) }} ر.س
                        <span style="font-size:12px;opacity:.7">({{ $grossProfitMargin }}%)</span>
                    </span>
                </div>
            </div>

            @if($totalOpex > 0)
                <div class="pl-section">
                    <div class="pl-section-title">
                        <span>💼 المصروفات التشغيلية</span>
                        <span class="amount" style="color:#fb923c">{{ number_format($totalOpex, 2) }} ر.س</span>
                    </div>

                    @foreach($opexDetails as $opex)
                        <div class="pl-row">
                            <span class="pl-row-label indent">{{ $opex['name'] }}</span>
                            <span class="pl-row-value" style="color:#fb923c">{{ number_format($opex['amount'], 2) }} ر.س</span>
                        </div>
                    @endforeach

                    <div class="pl-total">
                        <span class="pl-total-label">إجمالي المصروفات التشغيلية</span>
                        <span class="pl-total-value" style="color:#fb923c">{{ number_format($totalOpex, 2) }} ر.س</span>
                    </div>
                </div>
            @endif

            @if($totalFinancial > 0)
                <div class="pl-section">
                    <div class="pl-section-title">
                        <span>🏦 المصروفات التمويلية</span>
                        <span class="amount" style="color:#f87171">{{ number_format($totalFinancial, 2) }} ر.س</span>
                    </div>

                    <div class="pl-total">
                        <span class="pl-total-label">إجمالي المصروفات التمويلية</span>
                        <span class="pl-total-value" style="color:#f87171">{{ number_format($totalFinancial, 2) }} ر.س</span>
                    </div>
                </div>
            @endif

            <div class="pl-grand-total">
                <div class="pl-grand-label">
                    {{ $netProfit >= 0 ? '✅ صافي الربح' : '❌ صافي الخسارة' }}
                    <small>هامش الربح: {{ $netProfitMargin }}%</small>
                </div>

                <div class="pl-grand-value" style="color:{{ $netProfit >= 0 ? '#4ade80' : '#f87171' }}">
                    {{ number_format(abs($netProfit), 2) }} ر.س
                </div>
            </div>

        </div>
    @else
        <div class="pl-card empty-report">
            اختر الفترة الزمنية ثم اضغط «عرض التقرير» لعرض قائمة الأرباح والخسائر.
        </div>
    @endif

</div>
</x-filament-panels::page>
