<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --bs-bg:#07101d;--bs-panel:#0d1728;--bs-panel2:#101c30;--bs-line:#22324d;
    --bs-text:#eef3fb;--bs-muted:#71829b;--bs-purple:#8b5cf6;--bs-green:#34d399;
    --bs-red:#fb7185;--bs-amber:#fbbf24;--bs-blue:#38bdf8
}
.bs-v2{direction:rtl;color:var(--bs-text);padding-bottom:30px}
.bs-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:17px}
.bs-kicker{font-size:10px;color:#957fff;font-weight:900;letter-spacing:.8px;margin-bottom:6px}.bs-title{font-size:28px;font-weight:950;line-height:1.1}.bs-sub{font-size:11px;color:var(--bs-muted);margin-top:6px}
.bs-actions{display:flex;gap:7px;flex-wrap:wrap}.bs-btn{height:39px;border:1px solid #2b3d5b;border-radius:9px;background:#101b2d;color:#b9c5d6;padding:0 14px;font-size:10px;font-weight:900;cursor:pointer}.bs-btn.primary{background:linear-gradient(135deg,#7c4dff,#925eff);border-color:#9270ff;color:white}.bs-btn.green{color:#5be0af;border-color:rgba(52,211,153,.25);background:rgba(52,211,153,.06)}
.bs-presets{display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap}.bs-preset{height:32px;border:1px solid #263956;background:#0e192b;color:#8293ad;border-radius:8px;padding:0 11px;font-size:9px;font-weight:900;cursor:pointer}
.bs-filter{display:grid;grid-template-columns:1fr 1fr 180px auto auto;gap:8px;align-items:end;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:10px;margin-bottom:12px}.bs-field label{display:block;font-size:8px;color:#697b95;font-weight:900;margin-bottom:5px}.bs-input,.bs-select{width:100%;height:39px;border:1px solid #263956;background:#0f1b2f;color:#e9eff8;border-radius:9px;padding:0 10px;outline:none;font-size:10px}.bs-check{display:flex;align-items:center;gap:7px;height:39px;padding:0 10px;border:1px solid #263956;background:#0f1b2f;border-radius:9px;color:#8fa0b7;font-size:9px;font-weight:800}.bs-check input{accent-color:#8b5cf6}
.bs-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:12px}.bs-kpi{position:relative;background:linear-gradient(145deg,#0e192b,#0a1423);border:1px solid #22334f;border-radius:14px;padding:16px 17px;min-height:101px}.bs-code{position:absolute;left:12px;top:12px;font-size:8px;color:#5f718e;border:1px solid #283b59;border-radius:7px;padding:4px 6px;font-weight:900}.bs-label{font-size:10px;color:#8d9bb1;font-weight:800}.bs-value{font-size:21px;font-weight:950;margin-top:9px;direction:ltr;text-align:right}.bs-note{font-size:8px;color:#5e708a;margin-top:4px}.c-blue{color:#62c9f5}.c-red{color:var(--bs-red)}.c-green{color:var(--bs-green)}.c-purple{color:#b5a2ff}.c-amber{color:var(--bs-amber)}
.bs-control{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:11px 13px;margin-bottom:12px}.bs-control-title{font-size:10px;font-weight:950}.bs-control-sub{font-size:8px;color:#60728b;margin-top:3px}.bs-pill{display:inline-flex;border-radius:999px;padding:6px 10px;font-size:8px;font-weight:900}.bs-pill.ok{color:#5be0af;background:rgba(52,211,153,.07);border:1px solid rgba(52,211,153,.2)}.bs-pill.bad{color:#fb91a1;background:rgba(251,113,133,.07);border:1px solid rgba(251,113,133,.2)}
.bs-ratios{display:flex;gap:15px;margin-top:5px;font-size:8px;color:#7889a1}.bs-ratios strong{color:#cbd5e3}
.bs-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start}.bs-card{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;overflow:hidden}.bs-card-head{display:flex;justify-content:space-between;align-items:center;padding:15px 16px;border-bottom:1px solid #1b2a40}.bs-card-title{font-size:15px;font-weight:950}.bs-card-sub{font-size:8px;color:#61728b;margin-top:3px}.bs-total-top{font-size:18px;font-weight:950;direction:ltr;text-align:left}
.bs-section{border-bottom:1px solid #17253a}.bs-section:last-child{border-bottom:0}.bs-section-head{display:flex;justify-content:space-between;align-items:center;padding:11px 13px;background:#0e192b;cursor:pointer}.bs-section-title{font-size:10px;font-weight:950}.bs-section-total{font-size:10px;font-weight:950;direction:ltr}.bs-section-body{padding:0 10px}
.bs-row{display:grid;grid-template-columns:minmax(180px,1fr) 105px 105px 88px 55px;align-items:center;gap:7px;min-height:44px;border-bottom:1px solid #17253a;font-size:8px}.bs-row:last-child{border-bottom:0}.bs-account{display:flex;align-items:center;gap:8px;min-width:0}.bs-acct-code{color:#9681ef;font-size:8px;font-weight:900;min-width:43px}.bs-acct-name{font-size:9px;color:#dbe4ef;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bs-current{font-size:9px;font-weight:950;direction:ltr;text-align:right}.bs-previous{font-size:8px;color:#8293a9;direction:ltr;text-align:right}.bs-var{font-size:8px;font-weight:900;direction:ltr;text-align:right}.bs-ledger-btn{border:0;background:transparent;color:#9e89f6;font-size:8px;font-weight:900;cursor:pointer}
.bs-subtotal{display:flex;justify-content:space-between;padding:10px 13px;background:#0d1728;font-size:9px;font-weight:950}.bs-grand{display:flex;justify-content:space-between;padding:13px 15px;margin:10px;border-radius:10px;font-size:12px;font-weight:950}.bs-grand.assets{background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);color:#64caf6}.bs-grand.le{background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.2);color:#b8a5ff}
.bs-contra .bs-acct-name,.bs-contra .bs-current{color:#fb91a1}.bs-reverse{box-shadow:inset -2px 0 #fbbf24}.bs-reverse .bs-acct-name:after{content:" عكس الطبيعة";margin-right:5px;color:#f6ca4e;font-size:7px}
.bs-empty{padding:25px;text-align:center;color:#5d6f89;font-size:9px}
.bs-drawer{position:fixed;inset:0;z-index:1200;background:rgba(2,6,13,.72);backdrop-filter:blur(5px);display:flex;justify-content:flex-start}.bs-drawer-panel{width:min(760px,94vw);height:100%;background:#0a1423;border-right:1px solid #2b3c59;box-shadow:20px 0 70px rgba(0,0,0,.45);overflow:auto}.bs-drawer-head{position:sticky;top:0;z-index:4;background:#0d1829;border-bottom:1px solid #21314a;padding:16px;display:flex;justify-content:space-between}.bs-drawer-title{font-size:16px;font-weight:950}.bs-drawer-sub{font-size:8px;color:#63758f;margin-top:4px}.bs-close{width:31px;height:31px;border-radius:8px;border:1px solid #2b3d5b;background:#101c30;color:#91a2bb;cursor:pointer}.bs-mini-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;padding:12px}.bs-mini{background:#0e1a2c;border:1px solid #21334f;border-radius:10px;padding:10px}.bs-mini span{display:block;font-size:7px;color:#61738d}.bs-mini strong{display:block;font-size:11px;margin-top:5px;direction:ltr;text-align:right}.bs-ledger-head,.bs-ledger-row{display:grid;grid-template-columns:85px 105px minmax(180px,1fr) 90px 90px 105px;align-items:center}.bs-ledger-head{padding:9px 12px;color:#61728b;font-size:7px;font-weight:900;background:#0e1828;border-block:1px solid #1b2a40}.bs-ledger-row{padding:10px 12px;border-bottom:1px solid #17253a;font-size:8px}.bs-entry{color:#9984f3;font-weight:900;text-decoration:none}.bs-desc{color:#cbd5e3}
@media(max-width:1150px){.bs-kpis{grid-template-columns:1fr 1fr}.bs-filter{grid-template-columns:1fr 1fr 1fr}.bs-grid{grid-template-columns:1fr}}
@media(max-width:700px){.bs-head{align-items:flex-start;flex-direction:column}.bs-kpis,.bs-filter{grid-template-columns:1fr}.bs-title{font-size:23px}.bs-row{grid-template-columns:minmax(170px,1fr) 95px 50px}.bs-previous,.bs-var{display:none}.bs-mini-kpis{grid-template-columns:1fr}.bs-ledger-head,.bs-ledger-row{min-width:700px}.bs-drawer-panel{overflow-x:auto}}
</style>

@php
$diff = abs($balanceDifference);
$assetChange = $showComparison && abs($previousTotalAssets) >= .01 ? (($totalAssets-$previousTotalAssets)/abs($previousTotalAssets))*100 : null;
$liabilityChange = $showComparison && abs($previousTotalLiabilities) >= .01 ? (($totalLiabilities-$previousTotalLiabilities)/abs($previousTotalLiabilities))*100 : null;
$equityChange = $showComparison && abs($previousTotalEquity) >= .01 ? (($totalEquity-$previousTotalEquity)/abs($previousTotalEquity))*100 : null;
@endphp

<div class="bs-v2">
    <div class="bs-head">
        <div>
            <div class="bs-kicker">FINANCIAL STATEMENTS · BALANCE SHEET</div>
            <div class="bs-title">الميزانية العمومية</div>
            <div class="bs-sub">المركز المالي في تاريخ محدد مع مقارنة وفحص مباشر للمعادلة المحاسبية</div>
        </div>
        @if($loaded)
        <div class="bs-actions">
            <button wire:click="exportExcel" class="bs-btn green">Excel CSV</button>
            <button wire:click="exportPdf" class="bs-btn green">PDF</button>
        </div>
        @endif
    </div>

    <div class="bs-presets">
        <button wire:click="setPreset('today')" class="bs-preset">اليوم</button>
        <button wire:click="setPreset('previous_month')" class="bs-preset">نهاية الشهر السابق</button>
        <button wire:click="setPreset('previous_year')" class="bs-preset">نهاية السنة السابقة</button>
    </div>

    <div class="bs-filter">
        <div class="bs-field">
            <label>الميزانية بتاريخ</label>
            <input wire:model="asOfDate" type="date" class="bs-input">
        </div>
        <div class="bs-field">
            <label>المقارنة بتاريخ</label>
            <input wire:model="comparisonDate" type="date" class="bs-input" {{ $showComparison ? '' : 'disabled' }}>
        </div>
        <div class="bs-field">
            <label>طريقة العرض</label>
            <select wire:model="viewMode" class="bs-select">
                <option value="detailed">تفصيلي</option>
                <option value="summary">ملخص</option>
            </select>
        </div>
        <label class="bs-check">
            <input type="checkbox" wire:model="showComparison">
            مقارنة
        </label>
        <button wire:click="loadReport" class="bs-btn primary">عرض الميزانية</button>
    </div>

    @if($loaded)
    <div class="bs-kpis">
        <div class="bs-kpi">
            <div class="bs-code">ASSET</div>
            <div class="bs-label">إجمالي الأصول</div>
            <div class="bs-value c-blue">{{ number_format($totalAssets,2) }} ر.س</div>
            <div class="bs-note">
                @if($assetChange !== null) مقارنة: {{ $assetChange >= 0 ? '+' : '' }}{{ number_format($assetChange,1) }}% @else المركز المالي الحالي @endif
            </div>
        </div>
        <div class="bs-kpi">
            <div class="bs-code">LIAB</div>
            <div class="bs-label">إجمالي الخصوم</div>
            <div class="bs-value c-red">{{ number_format($totalLiabilities,2) }} ر.س</div>
            <div class="bs-note">
                @if($liabilityChange !== null) مقارنة: {{ $liabilityChange >= 0 ? '+' : '' }}{{ number_format($liabilityChange,1) }}% @else التزامات المنشأة @endif
            </div>
        </div>
        <div class="bs-kpi">
            <div class="bs-code">EQUITY</div>
            <div class="bs-label">حقوق الملكية</div>
            <div class="bs-value c-purple">{{ number_format($totalEquity,2) }} ر.س</div>
            <div class="bs-note">
                @if($equityChange !== null) مقارنة: {{ $equityChange >= 0 ? '+' : '' }}{{ number_format($equityChange,1) }}% @else تشمل نتيجة الأعمال @endif
            </div>
        </div>
        <div class="bs-kpi">
            <div class="bs-code">CHECK</div>
            <div class="bs-label">فرق المعادلة</div>
            <div class="bs-value {{ $isBalanced ? 'c-green' : 'c-red' }}">{{ number_format($diff,2) }} ر.س</div>
            <div class="bs-note">{{ $isBalanced ? 'الأصول = الخصوم + حقوق الملكية' : 'تحتاج مراجعة محاسبية' }}</div>
        </div>
    </div>

    <div class="bs-control">
        <div>
            <div class="bs-control-title">{{ $isBalanced ? 'المعادلة المحاسبية متوازنة' : 'الميزانية غير متوازنة' }}</div>
            <div class="bs-control-sub">بتاريخ {{ $asOfDate }} · القيود المرحلة فقط · {{ $reverseNatureCount }} حساب برصيد عكس طبيعته</div>
            <div class="bs-ratios">
                <span>رأس المال العامل: <strong>{{ number_format($workingCapital,2) }} ر.س</strong></span>
                <span>Current Ratio: <strong>{{ $currentRatio !== null ? number_format($currentRatio,2).'x' : '-' }}</strong></span>
            </div>
        </div>
        <span class="bs-pill {{ $isBalanced ? 'ok':'bad' }}">{{ $isBalanced ? 'متوازنة' : 'غير متوازنة' }}</span>
    </div>

    <div class="bs-grid">
        <div class="bs-card">
            <div class="bs-card-head">
                <div>
                    <div class="bs-card-title c-blue">الأصول</div>
                    <div class="bs-card-sub">موارد المنشأة الاقتصادية</div>
                </div>
                <div class="bs-total-top c-blue">{{ number_format($totalAssets,2) }} ر.س</div>
            </div>

            <div class="bs-section" x-data="{open:true}">
                <div class="bs-section-head" x-on:click="open=!open">
                    <div class="bs-section-title">الأصول المتداولة</div>
                    <div class="bs-section-total c-blue">{{ number_format($totalCurrentAssets,2) }} ر.س</div>
                </div>
                <div class="bs-section-body" x-show="open">
                    @if($viewMode === 'detailed')
                        @forelse($currentAssets as $item)
                            <div class="bs-row {{ $item['reverse_nature'] ? 'bs-reverse':'' }}">
                                <div class="bs-account"><span class="bs-acct-code">{{ $item['code'] }}</span><span class="bs-acct-name">{{ $item['name'] }}</span></div>
                                <div class="bs-current {{ $item['balance'] < 0 ? 'c-red':'c-blue' }}">{{ number_format($item['balance'],2) }}</div>
                                <div class="bs-previous">{{ $showComparison ? number_format($item['comparison_balance'],2) : '-' }}</div>
                                <div class="bs-var {{ $item['variance'] >= 0 ? 'c-green':'c-red' }}">
                                    @if($showComparison)
                                        {{ $item['variance'] >= 0 ? '+' : '' }}{{ number_format($item['variance'],2) }}
                                        @if($item['variance_pct'] !== null) <small>({{ number_format($item['variance_pct'],1) }}%)</small> @endif
                                    @else - @endif
                                </div>
                                <button wire:click="openLedger({{ $item['id'] }})" class="bs-ledger-btn">الأستاذ</button>
                            </div>
                        @empty
                            <div class="bs-empty">لا توجد أرصدة متداولة</div>
                        @endforelse
                    @endif
                </div>
                <div class="bs-subtotal"><span>إجمالي الأصول المتداولة</span><span class="c-blue">{{ number_format($totalCurrentAssets,2) }} ر.س</span></div>
            </div>

            <div class="bs-section" x-data="{open:true}">
                <div class="bs-section-head" x-on:click="open=!open">
                    <div class="bs-section-title">الأصول الثابتة</div>
                    <div class="bs-section-total c-blue">{{ number_format($totalFixedAssets,2) }} ر.س</div>
                </div>
                <div class="bs-section-body" x-show="open">
                    @if($viewMode === 'detailed')
                        @foreach($fixedAssets as $item)
                            <div class="bs-row">
                                <div class="bs-account"><span class="bs-acct-code">{{ $item['code'] }}</span><span class="bs-acct-name">{{ $item['name'] }}</span></div>
                                <div class="bs-current c-blue">{{ number_format($item['balance'],2) }}</div>
                                <div class="bs-previous">{{ $showComparison ? number_format($item['comparison_balance'],2) : '-' }}</div>
                                <div class="bs-var {{ $item['variance'] >= 0 ? 'c-green':'c-red' }}">{{ $showComparison ? (($item['variance'] >= 0 ? '+' : '').number_format($item['variance'],2)) : '-' }}</div>
                                <button wire:click="openLedger({{ $item['id'] }})" class="bs-ledger-btn">الأستاذ</button>
                            </div>
                        @endforeach

                        @foreach($fixedAssetContra as $item)
                            <div class="bs-row bs-contra">
                                <div class="bs-account"><span class="bs-acct-code">{{ $item['code'] }}</span><span class="bs-acct-name">{{ $item['name'] }}</span></div>
                                <div class="bs-current">{{ number_format($item['balance'],2) }}</div>
                                <div class="bs-previous">{{ $showComparison ? number_format($item['comparison_balance'],2) : '-' }}</div>
                                <div class="bs-var c-red">{{ $showComparison ? (($item['variance'] >= 0 ? '+' : '').number_format($item['variance'],2)) : '-' }}</div>
                                <button wire:click="openLedger({{ $item['id'] }})" class="bs-ledger-btn">الأستاذ</button>
                            </div>
                        @endforeach
                    @endif
                </div>

                @if($totalFixedAssetContra > 0)
                <div class="bs-subtotal"><span>إجمالي تكلفة الأصول الثابتة</span><span>{{ number_format($totalFixedAssetGross,2) }} ر.س</span></div>
                <div class="bs-subtotal"><span>(-) الحسابات المقابلة / مجمع الإهلاك</span><span class="c-red">{{ number_format($totalFixedAssetContra,2) }} ر.س</span></div>
                @endif
                <div class="bs-subtotal"><span>صافي الأصول الثابتة</span><span class="c-blue">{{ number_format($totalFixedAssets,2) }} ر.س</span></div>
            </div>

            <div class="bs-grand assets"><span>إجمالي الأصول</span><span>{{ number_format($totalAssets,2) }} ر.س</span></div>
        </div>

        <div class="bs-card">
            <div class="bs-card-head">
                <div>
                    <div class="bs-card-title c-purple">الخصوم وحقوق الملكية</div>
                    <div class="bs-card-sub">مصادر تمويل الأصول</div>
                </div>
                <div class="bs-total-top c-purple">{{ number_format($totalLiabilitiesAndEquity,2) }} ر.س</div>
            </div>

            <div class="bs-section" x-data="{open:true}">
                <div class="bs-section-head" x-on:click="open=!open">
                    <div class="bs-section-title">الخصوم المتداولة</div>
                    <div class="bs-section-total c-red">{{ number_format($totalCurrentLiabilities,2) }} ر.س</div>
                </div>
                <div class="bs-section-body" x-show="open">
                    @if($viewMode === 'detailed')
                        @forelse($currentLiabilities as $item)
                            <div class="bs-row {{ $item['reverse_nature'] ? 'bs-reverse':'' }}">
                                <div class="bs-account"><span class="bs-acct-code">{{ $item['code'] }}</span><span class="bs-acct-name">{{ $item['name'] }}</span></div>
                                <div class="bs-current c-red">{{ number_format($item['balance'],2) }}</div>
                                <div class="bs-previous">{{ $showComparison ? number_format($item['comparison_balance'],2) : '-' }}</div>
                                <div class="bs-var {{ $item['variance'] >= 0 ? 'c-red':'c-green' }}">{{ $showComparison ? (($item['variance'] >= 0 ? '+' : '').number_format($item['variance'],2)) : '-' }}</div>
                                <button wire:click="openLedger({{ $item['id'] }})" class="bs-ledger-btn">الأستاذ</button>
                            </div>
                        @empty
                            <div class="bs-empty">لا توجد خصوم متداولة</div>
                        @endforelse
                    @endif
                </div>
                <div class="bs-subtotal"><span>إجمالي الخصوم المتداولة</span><span class="c-red">{{ number_format($totalCurrentLiabilities,2) }} ر.س</span></div>
            </div>

            @if(!empty($longTermLiabilities) || $viewMode === 'summary')
            <div class="bs-section" x-data="{open:true}">
                <div class="bs-section-head" x-on:click="open=!open">
                    <div class="bs-section-title">الخصوم طويلة الأجل</div>
                    <div class="bs-section-total c-red">{{ number_format($totalLongTermLiabilities,2) }} ر.س</div>
                </div>
                <div class="bs-section-body" x-show="open">
                    @if($viewMode === 'detailed')
                        @foreach($longTermLiabilities as $item)
                            <div class="bs-row {{ $item['reverse_nature'] ? 'bs-reverse':'' }}">
                                <div class="bs-account"><span class="bs-acct-code">{{ $item['code'] }}</span><span class="bs-acct-name">{{ $item['name'] }}</span></div>
                                <div class="bs-current c-red">{{ number_format($item['balance'],2) }}</div>
                                <div class="bs-previous">{{ $showComparison ? number_format($item['comparison_balance'],2) : '-' }}</div>
                                <div class="bs-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                                <button wire:click="openLedger({{ $item['id'] }})" class="bs-ledger-btn">الأستاذ</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="bs-subtotal"><span>إجمالي الخصوم طويلة الأجل</span><span class="c-red">{{ number_format($totalLongTermLiabilities,2) }} ر.س</span></div>
            </div>
            @endif

            <div class="bs-section" x-data="{open:true}">
                <div class="bs-section-head" x-on:click="open=!open">
                    <div class="bs-section-title">حقوق الملكية</div>
                    <div class="bs-section-total c-purple">{{ number_format($totalEquity,2) }} ر.س</div>
                </div>
                <div class="bs-section-body" x-show="open">
                    @if($viewMode === 'detailed')
                        @foreach($equityAccounts as $item)
                            <div class="bs-row {{ $item['reverse_nature'] ? 'bs-reverse':'' }}">
                                <div class="bs-account"><span class="bs-acct-code">{{ $item['code'] }}</span><span class="bs-acct-name">{{ $item['name'] }}</span></div>
                                <div class="bs-current c-purple">{{ number_format($item['balance'],2) }}</div>
                                <div class="bs-previous">{{ $showComparison ? number_format($item['comparison_balance'],2) : '-' }}</div>
                                <div class="bs-var">{{ $showComparison ? number_format($item['variance'],2) : '-' }}</div>
                                <button wire:click="openLedger({{ $item['id'] }})" class="bs-ledger-btn">الأستاذ</button>
                            </div>
                        @endforeach

                        <div class="bs-row">
                            <div class="bs-account"><span class="bs-acct-code">P&L</span><span class="bs-acct-name">الأرباح المحتجزة / نتيجة الأعمال</span></div>
                            <div class="bs-current {{ $retainedEarnings >= 0 ? 'c-green':'c-red' }}">{{ number_format($retainedEarnings,2) }}</div>
                            <div class="bs-previous">{{ $showComparison ? number_format($previousRetainedEarnings,2) : '-' }}</div>
                            <div class="bs-var">{{ $showComparison ? number_format($retainedEarnings-$previousRetainedEarnings,2) : '-' }}</div>
                            <span></span>
                        </div>
                    @endif
                </div>
                <div class="bs-subtotal"><span>إجمالي حقوق الملكية</span><span class="c-purple">{{ number_format($totalEquity,2) }} ر.س</span></div>
            </div>

            <div class="bs-grand le"><span>إجمالي الخصوم وحقوق الملكية</span><span>{{ number_format($totalLiabilitiesAndEquity,2) }} ر.س</span></div>
        </div>
    </div>
    @endif

    @if($selectedAccountId)
    <div class="bs-drawer">
        <div class="bs-drawer-panel">
            <div class="bs-drawer-head">
                <div>
                    <div class="bs-drawer-title">{{ $selectedAccountCode }} · {{ $selectedAccountName }}</div>
                    <div class="bs-drawer-sub">الأستاذ من {{ $selectedLedgerSummary['from'] ?? '-' }} حتى {{ $asOfDate }} · آخر 50 حركة معروضة</div>
                </div>
                <button wire:click="closeLedger" class="bs-close">×</button>
            </div>

            <div class="bs-mini-kpis">
                <div class="bs-mini"><span>رصيد أول السنة</span><strong>{{ number_format($selectedLedgerSummary['opening'] ?? 0,2) }} ر.س</strong></div>
                <div class="bs-mini"><span>حركة السنة</span><strong>{{ number_format(($selectedLedgerSummary['period_debit'] ?? 0)+($selectedLedgerSummary['period_credit'] ?? 0),2) }} ر.س</strong></div>
                <div class="bs-mini"><span>الرصيد الحالي</span><strong>{{ number_format($selectedLedgerSummary['closing'] ?? 0,2) }} ر.س</strong></div>
            </div>

            <div style="overflow:auto">
                <div class="bs-ledger-head"><div>التاريخ</div><div>المرجع</div><div>البيان</div><div>مدين</div><div>دائن</div><div>الرصيد</div></div>
                @forelse($selectedLedger as $line)
                    <div class="bs-ledger-row">
                        <div>{{ $line['date'] }}</div>
                        <div>
                            @if($line['journal_entry_id'])
                            <a class="bs-entry" href="{{ \App\Filament\Admin\Resources\JournalEntries\JournalEntryResource::getUrl('view',['record'=>$line['journal_entry_id']]) }}">{{ $line['reference'] ?: $line['entry_number'] }}</a>
                            @else -
                            @endif
                        </div>
                        <div class="bs-desc">{{ $line['description'] ?: '-' }}</div>
                        <div class="c-red">{{ $line['debit'] > 0 ? number_format($line['debit'],2) : '-' }}</div>
                        <div class="c-green">{{ $line['credit'] > 0 ? number_format($line['credit'],2) : '-' }}</div>
                        <div>{{ number_format($line['balance'],2) }}</div>
                    </div>
                @empty
                    <div class="bs-empty">لا توجد حركات خلال السنة حتى هذا التاريخ</div>
                @endforelse
            </div>

            <div style="padding:14px">
                <a class="bs-btn" style="display:inline-flex;align-items:center;text-decoration:none" href="{{ \App\Filament\Admin\Resources\Accounts\AccountResource::getUrl('view',['record'=>$selectedAccountId]) }}">فتح صفحة الحساب</a>
            </div>
        </div>
        <div style="flex:1" wire:click="closeLedger"></div>
    </div>
    @endif
</div>
</x-filament-panels::page>