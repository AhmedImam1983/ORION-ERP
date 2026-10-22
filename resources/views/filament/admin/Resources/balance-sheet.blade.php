<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important}
.bs-wrap{display:flex;flex-direction:column;gap:14px}
.bs-card{background:#161c2d;border:1px solid #1e2d45;border-radius:12px;padding:16px}
.bs-filters{display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap}
.bs-label{font-size:12px;color:#475569;font-weight:600;margin-bottom:5px}
.bs-date{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:9px 12px;color:#e2e8f0;font-size:13px;font-family:Cairo,sans-serif;outline:none}
.btn-load{background:#5b4fff;border:none;color:#fff;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;font-family:Cairo,sans-serif}
.btn-pdf{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;font-family:Cairo,sans-serif}
.bs-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.bs-section{margin-bottom:10px}
.bs-section-title{font-size:13px;font-weight:800;padding:9px 12px;border-radius:7px;margin-bottom:6px;display:flex;justify-content:space-between}
.bs-row{display:flex;justify-content:space-between;padding:7px 12px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px}
.bs-row:hover{background:rgba(255,255,255,.02)}
.bs-row-label{color:#94a3b8;padding-right:16px}
.bs-row-value{font-weight:600}
.bs-subtotal{display:flex;justify-content:space-between;padding:9px 12px;border-top:1px solid rgba(255,255,255,.1);font-weight:700;font-size:13px}
.bs-total{display:flex;justify-content:space-between;padding:12px 16px;border-radius:8px;font-weight:900;font-size:15px;margin-top:8px}
.balance-status{text-align:center;padding:14px;border-radius:10px;font-size:15px;font-weight:800;margin-top:8px}
</style>

<div class="bs-wrap">
    {{-- Filters --}}
    <div class="bs-card">
        <div class="bs-filters">
            <div>
                <div class="bs-label">بتاريخ</div>
                <input type="date" wire:model="asOfDate" class="bs-date">
            </div>
            <button wire:click="loadReport" class="btn-load">⚖️ عرض الميزانية</button>
            @if($loaded)
            <button wire:click="exportPdf" class="btn-pdf">📄 تصدير PDF</button>
            @endif
        </div>
    </div>

    @if($loaded)
    {{-- Balance Status --}}
    <div class="balance-status" style="background:{{ $isBalanced ? 'rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80' : 'rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171' }}">
        {{ $isBalanced ? '✅ الميزانية متوازنة' : '⚠️ الميزانية غير متوازنة — تحقق من القيود' }}
    </div>

    <div class="bs-grid">
        {{-- الأصول --}}
        <div class="bs-card">
            <div style="font-size:16px;font-weight:900;color:#60a5fa;margin-bottom:14px;text-align:center">
                🏦 الأصول
                <div style="font-size:13px;color:#475569;margin-top:2px">{{ number_format($totalAssets, 2) }} ر.س</div>
            </div>

            {{-- الأصول الثابتة --}}
            @if(!empty($fixedAssets))
            <div class="bs-section">
                <div class="bs-section-title" style="background:rgba(96,165,250,.1);color:#60a5fa">
                    <span>الأصول الثابتة</span>
                    <span>{{ number_format($totalFixedAssets, 2) }} ر.س</span>
                </div>
                @foreach($fixedAssets as $item)
                <div class="bs-row">
                    <span class="bs-row-label">{{ $item['name'] }}</span>
                    <span class="bs-row-value" style="color:#60a5fa">{{ number_format($item['balance'], 2) }} ر.س</span>
                </div>
                @endforeach
                <div class="bs-subtotal">
                    <span>إجمالي الأصول الثابتة</span>
                    <span style="color:#60a5fa">{{ number_format($totalFixedAssets, 2) }} ر.س</span>
                </div>
            </div>
            @endif

            {{-- الأصول المتداولة --}}
            @if(!empty($currentAssets))
            <div class="bs-section">
                <div class="bs-section-title" style="background:rgba(96,165,250,.08);color:#93c5fd">
                    <span>الأصول المتداولة</span>
                    <span>{{ number_format($totalCurrentAssets, 2) }} ر.س</span>
                </div>
                @foreach($currentAssets as $item)
                <div class="bs-row">
                    <span class="bs-row-label">{{ $item['name'] }}</span>
                    <span class="bs-row-value" style="color:#93c5fd">{{ number_format($item['balance'], 2) }} ر.س</span>
                </div>
                @endforeach
                <div class="bs-subtotal">
                    <span>إجمالي الأصول المتداولة</span>
                    <span style="color:#93c5fd">{{ number_format($totalCurrentAssets, 2) }} ر.س</span>
                </div>
            </div>
            @endif

            <div class="bs-total" style="background:rgba(96,165,250,.15);color:#60a5fa">
                <span>إجمالي الأصول</span>
                <span>{{ number_format($totalAssets, 2) }} ر.س</span>
            </div>
        </div>

        {{-- الخصوم وحقوق الملكية --}}
        <div class="bs-card">
            <div style="font-size:16px;font-weight:900;color:#f87171;margin-bottom:14px;text-align:center">
                💼 الخصوم وحقوق الملكية
                <div style="font-size:13px;color:#475569;margin-top:2px">{{ number_format($totalLiabilitiesAndEquity, 2) }} ر.س</div>
            </div>

            {{-- الخصوم المتداولة --}}
            @if(!empty($currentLiabilities))
            <div class="bs-section">
                <div class="bs-section-title" style="background:rgba(248,113,113,.1);color:#f87171">
                    <span>الخصوم المتداولة</span>
                    <span>{{ number_format($totalCurrentLiabilities, 2) }} ر.س</span>
                </div>
                @foreach($currentLiabilities as $item)
                <div class="bs-row">
                    <span class="bs-row-label">{{ $item['name'] }}</span>
                    <span class="bs-row-value" style="color:#f87171">{{ number_format($item['balance'], 2) }} ر.س</span>
                </div>
                @endforeach
                <div class="bs-subtotal">
                    <span>إجمالي الخصوم المتداولة</span>
                    <span style="color:#f87171">{{ number_format($totalCurrentLiabilities, 2) }} ر.س</span>
                </div>
            </div>
            @endif

            {{-- الخصوم طويلة الأجل --}}
            @if(!empty($longTermLiabilities))
            <div class="bs-section">
                <div class="bs-section-title" style="background:rgba(248,113,113,.08);color:#fca5a5">
                    <span>الخصوم طويلة الأجل</span>
                    <span>{{ number_format($totalLongTermLiabilities, 2) }} ر.س</span>
                </div>
                @foreach($longTermLiabilities as $item)
                <div class="bs-row">
                    <span class="bs-row-label">{{ $item['name'] }}</span>
                    <span class="bs-row-value" style="color:#fca5a5">{{ number_format($item['balance'], 2) }} ر.س</span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- حقوق الملكية --}}
            <div class="bs-section">
                <div class="bs-section-title" style="background:rgba(167,139,250,.1);color:#a78bfa">
                    <span>حقوق الملكية</span>
                    <span>{{ number_format($totalEquity, 2) }} ر.س</span>
                </div>
                @foreach($equityAccounts as $item)
                <div class="bs-row">
                    <span class="bs-row-label">{{ $item['name'] }}</span>
                    <span class="bs-row-value" style="color:#a78bfa">{{ number_format($item['balance'], 2) }} ر.س</span>
                </div>
                @endforeach
                @if($retainedEarnings != 0)
                <div class="bs-row">
                    <span class="bs-row-label">الأرباح المحتجزة</span>
                    <span class="bs-row-value" style="color:{{ $retainedEarnings >= 0 ? '#4ade80' : '#f87171' }}">
                        {{ number_format($retainedEarnings, 2) }} ر.س
                    </span>
                </div>
                @endif
                <div class="bs-subtotal">
                    <span>إجمالي حقوق الملكية</span>
                    <span style="color:#a78bfa">{{ number_format($totalEquity, 2) }} ر.س</span>
                </div>
            </div>

            <div class="bs-total" style="background:rgba(248,113,113,.12);color:#f87171">
                <span>إجمالي الخصوم وحقوق الملكية</span>
                <span>{{ number_format($totalLiabilitiesAndEquity, 2) }} ر.س</span>
            </div>
        </div>
    </div>
    @endif
</div>
</x-filament-panels::page>