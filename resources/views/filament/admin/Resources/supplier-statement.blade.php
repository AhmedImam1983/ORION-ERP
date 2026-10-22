<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
.stmt-wrap{display:flex;flex-direction:column;gap:14px;direction:rtl;color:#eef2ff}
.stmt-card{background:linear-gradient(180deg,#151d2f,#111827);border:1px solid #22304a;border-radius:16px;padding:18px 20px;box-shadow:0 12px 35px rgba(0,0,0,.14)}
.stmt-filters{display:grid;grid-template-columns:minmax(300px,1.7fr) 180px 180px 150px 145px;gap:12px;align-items:end}
.stmt-label{font-size:12px;color:#91a0b8;font-weight:800;margin:0 0 7px 2px}
.stmt-select,.stmt-date{width:100%;height:48px;background:#202838;border:1px solid #34415b;border-radius:10px;padding:0 14px;color:#f1f5ff;font-size:14px;outline:none}
.stmt-select:focus,.stmt-date:focus{border-color:#7c4dff;box-shadow:0 0 0 3px rgba(124,77,255,.13)}
.btn-load,.btn-pdf{height:48px;border:0;border-radius:10px;padding:0 18px;cursor:pointer;font-size:14px;font-weight:900;white-space:nowrap}
.btn-load{background:linear-gradient(135deg,#7c4dff,#6840e7);color:#fff;box-shadow:0 8px 22px rgba(124,77,255,.22)}
.btn-pdf{background:linear-gradient(135deg,#16b95c,#18a957);color:#fff;box-shadow:0 8px 22px rgba(22,185,92,.15)}
.supplier-info{display:grid;grid-template-columns:1.45fr 1fr 1fr 1fr;gap:10px}
.info-item{background:#0b111d;border:1px solid #202c43;border-radius:11px;padding:14px 16px;min-height:72px}
.info-label{font-size:11px;color:#71809b;margin-bottom:6px;font-weight:800}
.info-value{font-size:15px;font-weight:900;color:#f4f7ff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.kpi{border-radius:15px;padding:18px 20px;min-height:122px;position:relative;overflow:hidden;border:1px solid rgba(255,255,255,.06)}
.kpi:after{content:"";position:absolute;width:100px;height:100px;border-radius:50%;left:-35px;bottom:-55px;background:rgba(255,255,255,.06)}
.kpi-l{font-size:13px;opacity:.82;margin-bottom:9px;font-weight:800;color:rgba(255,255,255,.8)}
.kpi-v{font-size:28px;line-height:1;font-weight:950;letter-spacing:-.5px}
.stmt-table{width:100%;border-collapse:collapse;font-size:13px;min-width:850px}
.stmt-table th{text-align:right;padding:13px 15px;font-weight:900;color:#7f8da5;border-bottom:1px solid #26334b;background:#0d1422;font-size:12px;white-space:nowrap}
.stmt-table td{padding:14px 15px;border-bottom:1px solid #172238;color:#e7edf8;font-size:13px;font-weight:650}
.stmt-table tbody tr:hover td{background:rgba(124,77,255,.045)}
.stmt-table tr.opening td{background:rgba(124,77,255,.07)}
.stmt-table tr.payment td{background:rgba(53,220,123,.035)}
.stmt-table tfoot td{background:linear-gradient(90deg,rgba(124,77,255,.12),rgba(124,77,255,.04));border-top:2px solid rgba(124,77,255,.28);padding:16px 15px;font-weight:950;font-size:14px}
@media(max-width:1100px){.stmt-filters{grid-template-columns:1fr 1fr}.supplier-info{grid-template-columns:1fr 1fr}.kpi-row{grid-template-columns:1fr 1fr}}
@media(max-width:640px){.stmt-filters,.supplier-info,.kpi-row{grid-template-columns:1fr}.stmt-card{padding:14px}}
</style>

<div class="stmt-wrap">
    <div class="stmt-card">
        <div class="stmt-filters">
            <div>
                <div class="stmt-label">المورد</div>
                <select wire:model="supplierId" class="stmt-select">
                    <option value="">اختر مورداً...</option>
                    @foreach(\App\Models\Supplier::where('is_active',true)->orderBy('name')->get() as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="stmt-label">من</div>
                <input type="date" wire:model="dateFrom" class="stmt-date">
            </div>
            <div>
                <div class="stmt-label">إلى</div>
                <input type="date" wire:model="dateTo" class="stmt-date">
            </div>
            <button wire:click="loadStatement" class="btn-load">🔍 عرض الكشف</button>
            @if(!empty($statement))
            <button wire:click="exportPdf" class="btn-pdf">📄 تصدير PDF</button>
            @endif
        </div>
    </div>

    @if($supplier)
    <div class="stmt-card">
        <div class="supplier-info">
            <div class="info-item"><div class="info-label">اسم المورد</div><div class="info-value">{{ $supplier->name }}</div></div>
            <div class="info-item"><div class="info-label">التليفون</div><div class="info-value">{{ $supplier->phone ?? '-' }}</div></div>
            <div class="info-item"><div class="info-label">النوع</div><div class="info-value">{{ $supplier->type === 'company' ? 'شركة' : 'فرد' }}</div></div>
            <div class="info-item"><div class="info-label">الرقم الضريبي</div><div class="info-value">{{ $supplier->tax_number ?? '-' }}</div></div>
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi" style="background:linear-gradient(135deg,#1e3a5f,#1a2d4a)">
            <div class="kpi-l">رصيد أول المدة</div>
            <div class="kpi-v" style="color:{{ $openingBalance >= 0 ? '#f87171' : '#4ade80' }}">{{ number_format(abs($openingBalance), 2) }} ر.س</div>
            <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px">{{ $openingBalance >= 0 ? 'مدين' : 'دائن' }}</div>
        </div>
        <div class="kpi" style="background:linear-gradient(135deg,#3d1a1a,#2d1515)">
            <div class="kpi-l">إجمالي المشتريات</div>
            <div class="kpi-v" style="color:#f87171">{{ number_format($totalDebit, 2) }} ر.س</div>
        </div>
        <div class="kpi" style="background:linear-gradient(135deg,#1a3d1a,#152d15)">
            <div class="kpi-l">إجمالي المدفوعات</div>
            <div class="kpi-v" style="color:#4ade80">{{ number_format($totalCredit, 2) }} ر.س</div>
        </div>
        <div class="kpi" style="background:linear-gradient(135deg,#3d2a1a,#2d1f15)">
            <div class="kpi-l">الرصيد الختامي</div>
            <div class="kpi-v" style="color:{{ $closingBalance >= 0 ? '#fb923c' : '#4ade80' }}">{{ number_format(abs($closingBalance), 2) }} ر.س</div>
            <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px">{{ $closingBalance >= 0 ? 'علينا للمورد' : 'له رصيد' }}</div>
        </div>
    </div>

    <div class="stmt-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <div style="font-size:13px;font-weight:700;color:#94a3b8">📋 كشف الحساب من {{ $dateFrom }} إلى {{ $dateTo }}</div>
            <div style="font-size:12px;color:#475569">{{ count($statement) }} سطر</div>
        </div>
        @if(empty($statement))
        <div style="text-align:center;opacity:.4;padding:40px;font-size:14px">لا توجد حركات في هذه الفترة</div>
        @else
        <table class="stmt-table">
            <thead>
                <tr>
                    <th>التاريخ</th><th>البيان</th><th>المرجع</th>
                    <th>مدين</th><th>دائن</th><th>الرصيد</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement as $row)
                <tr class="{{ $row['type'] }}">
                    <td style="color:#64748b">{{ $row['date'] }}</td>
                    <td style="color:#e2e8f0">{{ $row['description'] }}</td>
                    <td style="color:#7c6dff;font-family:monospace">{{ $row['reference'] }}</td>
                    <td style="color:#f87171;font-weight:700">{{ $row['debit'] > 0 ? number_format($row['debit'], 2).' ر.س' : '-' }}</td>
                    <td style="color:#4ade80;font-weight:700">{{ $row['credit'] > 0 ? number_format($row['credit'], 2).' ر.س' : '-' }}</td>
                    <td style="font-weight:800;color:{{ $row['balance'] >= 0 ? '#fb923c' : '#4ade80' }}">
                        {{ number_format(abs($row['balance']), 2) }} ر.س
                        <span style="font-size:10px;opacity:.7">{{ $row['balance'] >= 0 ? 'علينا' : 'له' }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:rgba(91,79,255,.1);border-top:2px solid rgba(91,79,255,.3)">
                    <td colspan="3" style="font-weight:700;color:#a78bfa;padding:10px">الإجمالي</td>
                    <td style="font-weight:800;color:#f87171;padding:10px">{{ number_format($totalDebit, 2) }} ر.س</td>
                    <td style="font-weight:800;color:#4ade80;padding:10px">{{ number_format($totalCredit, 2) }} ر.س</td>
                    <td style="font-weight:900;color:{{ $closingBalance >= 0 ? '#fb923c' : '#4ade80' }};font-size:15px;padding:10px">
                        {{ number_format(abs($closingBalance), 2) }} ر.س
                        <span style="font-size:11px">{{ $closingBalance >= 0 ? 'علينا' : 'له' }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>
    @endif
</div>
</x-filament-panels::page>