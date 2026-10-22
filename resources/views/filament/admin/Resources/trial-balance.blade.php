<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important}
.tb-wrap{display:flex;flex-direction:column;gap:14px}
.tb-card{background:#161c2d;border:1px solid #1e2d45;border-radius:12px;padding:16px}
.tb-filters{display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap}
.tb-label{font-size:12px;color:#475569;font-weight:600;margin-bottom:5px}
.tb-date{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:9px 12px;color:#e2e8f0;font-size:13px;font-family:Cairo,sans-serif;outline:none}
.btn-load{background:#5b4fff;border:none;color:#fff;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;font-family:Cairo,sans-serif}
.btn-pdf{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;font-family:Cairo,sans-serif}
.kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.kpi{border-radius:10px;padding:14px 16px;text-align:center}
.kpi-l{font-size:11px;opacity:.8;margin-bottom:5px;font-weight:600;color:rgba(255,255,255,.7)}
.kpi-v{font-size:20px;font-weight:900;color:#fff}
.tb-table{width:100%;border-collapse:collapse;font-size:12px}
.tb-table th{text-align:center;padding:8px 6px;font-weight:700;color:#334155;border-bottom:2px solid #1e2d45;font-size:11px;background:#0d1117}
.tb-table th.text-right{text-align:right}
.tb-table td{padding:7px 6px;border-bottom:1px solid #0f1723;text-align:center}
.tb-table td.text-right{text-align:right}
.tb-table tr:hover td{background:rgba(255,255,255,.02)}
.tb-table tr.main-row td{background:rgba(91,79,255,.08);font-weight:700}
.tb-table tr.sub-row td{padding-right:20px}
.tb-table tr.total-row td{background:rgba(91,79,255,.15);font-weight:900;font-size:13px;border-top:2px solid rgba(91,79,255,.3)}
.type-badge{padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700}
</style>

<div class="tb-wrap">
    {{-- Filters --}}
    <div class="tb-card">
        <div class="tb-filters">
            <div>
                <div class="tb-label">من</div>
                <input type="date" wire:model="dateFrom" class="tb-date">
            </div>
            <div>
                <div class="tb-label">إلى</div>
                <input type="date" wire:model="dateTo" class="tb-date">
            </div>
            <button wire:click="loadTrialBalance" class="btn-load">⚖️ عرض الميزان</button>
            @if($loaded)
            <button wire:click="exportPdf" class="btn-pdf">📄 تصدير PDF</button>
            @endif
        </div>
    </div>

    @if($loaded)
    {{-- KPIs --}}
    <div class="kpi-row">
        <div class="kpi" style="background:linear-gradient(135deg,#1e3a5f,#1a2d4a)">
            <div class="kpi-l">إجمالي المدين</div>
            <div class="kpi-v" style="color:#f87171">{{ number_format($totalDebit, 2) }} ر.س</div>
        </div>
        <div class="kpi" style="background:linear-gradient(135deg,#1a3d1a,#152d15)">
            <div class="kpi-l">إجمالي الدائن</div>
            <div class="kpi-v" style="color:#4ade80">{{ number_format($totalCredit, 2) }} ر.س</div>
        </div>
        <div class="kpi" style="background:linear-gradient(135deg,{{ abs($totalDebit - $totalCredit) < 0.01 ? '#1a3d1a,#152d15' : '#3d1a1a,#2d1515' }})">
            <div class="kpi-l">الفرق</div>
            <div class="kpi-v" style="color:{{ abs($totalDebit - $totalCredit) < 0.01 ? '#4ade80' : '#f87171' }}">
                {{ number_format(abs($totalDebit - $totalCredit), 2) }} ر.س
                {{ abs($totalDebit - $totalCredit) < 0.01 ? '✅ متوازن' : '⚠️ غير متوازن' }}
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="tb-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="font-size:13px;font-weight:700;color:#94a3b8">⚖️ ميزان المراجعة من {{ $dateFrom }} إلى {{ $dateTo }}</div>
            <div style="font-size:12px;color:#475569">{{ count($accounts) }} حساب</div>
        </div>
        @if(empty($accounts))
        <div style="text-align:center;opacity:.4;padding:40px;font-size:14px">لا توجد حركات في هذه الفترة</div>
        @else
        <div style="overflow-x:auto">
        <table class="tb-table">
            <thead>
                <tr>
                    <th class="text-right" width="8%">الكود</th>
                    <th class="text-right" width="20%">اسم الحساب</th>
                    <th class="text-right" width="8%">النوع</th>
                    <th colspan="2" style="background:#1a2440;border-bottom:1px solid #1e2d45">الرصيد الافتتاحي</th>
                    <th colspan="2" style="background:#1a2440;border-bottom:1px solid #1e2d45">حركة الفترة</th>
                    <th colspan="2" style="background:#1a2440;border-bottom:1px solid #1e2d45">الإجمالي</th>
                    <th colspan="2" style="background:#1a2440;border-bottom:1px solid #1e2d45">الرصيد الختامي</th>
                </tr>
                <tr>
                    <th></th><th></th><th></th>
                    <th style="color:#f87171">مدين</th>
                    <th style="color:#4ade80">دائن</th>
                    <th style="color:#f87171">مدين</th>
                    <th style="color:#4ade80">دائن</th>
                    <th style="color:#f87171">مدين</th>
                    <th style="color:#4ade80">دائن</th>
                    <th style="color:#f87171">مدين</th>
                    <th style="color:#4ade80">دائن</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $acc)
                <tr class="{{ $acc['level'] === 'main' ? 'main-row' : ($acc['level'] === 'sub' ? 'sub-row' : '') }}">
                    <td class="text-right" style="font-family:monospace;color:#7c6dff">{{ $acc['code'] }}</td>
                    <td class="text-right" style="color:#e2e8f0;{{ $acc['level'] === 'detail' ? 'padding-right:24px' : '' }}">
                        {{ $acc['name'] }}
                    </td>
                    <td>
                        @php
                            $typeMap = ['asset'=>['أصول','#60a5fa'],'liability'=>['خصوم','#f87171'],'equity'=>['ملكية','#a78bfa'],'revenue'=>['إيرادات','#4ade80'],'expense'=>['مصروفات','#fb923c']];
                            $t = $typeMap[$acc['type']] ?? [$acc['type'],'#94a3b8'];
                        @endphp
                        <span class="type-badge" style="background:{{ $t[1] }}20;color:{{ $t[1] }}">{{ $t[0] }}</span>
                    </td>
                    <td style="color:#f87171">{{ $acc['opening_debit'] > 0 ? number_format($acc['opening_debit'], 2) : '-' }}</td>
                    <td style="color:#4ade80">{{ $acc['opening_credit'] > 0 ? number_format($acc['opening_credit'], 2) : '-' }}</td>
                    <td style="color:#f87171">{{ $acc['period_debit'] > 0 ? number_format($acc['period_debit'], 2) : '-' }}</td>
                    <td style="color:#4ade80">{{ $acc['period_credit'] > 0 ? number_format($acc['period_credit'], 2) : '-' }}</td>
                    <td style="color:#f87171">{{ $acc['total_debit'] > 0 ? number_format($acc['total_debit'], 2) : '-' }}</td>
                    <td style="color:#4ade80">{{ $acc['total_credit'] > 0 ? number_format($acc['total_credit'], 2) : '-' }}</td>
                    <td style="color:#f87171;font-weight:700">{{ $acc['balance_debit'] > 0 ? number_format($acc['balance_debit'], 2) : '-' }}</td>
                    <td style="color:#4ade80;font-weight:700">{{ $acc['balance_credit'] > 0 ? number_format($acc['balance_credit'], 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;color:#a78bfa">الإجمالي</td>
                    <td colspan="6"></td>
                    <td style="color:#f87171;font-size:14px">{{ number_format($totalDebit, 2) }}</td>
                    <td style="color:#4ade80;font-size:14px">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>
    @endif
</div>
</x-filament-panels::page>