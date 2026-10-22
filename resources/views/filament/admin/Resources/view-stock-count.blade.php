<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important}
.count-header{background:#0d1117;border:1px solid #1e2d45;border-radius:12px;padding:16px 20px;margin-bottom:14px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.count-stat{text-align:center}
.count-stat-label{font-size:11px;color:#475569;font-weight:600}
.count-stat-value{font-size:18px;font-weight:900;color:#e2e8f0;margin-top:4px}

.items-table{width:100%;border-collapse:collapse;font-size:12px}
.items-table th{text-align:right;padding:10px 12px;font-weight:700;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px;background:#0d1117}
.items-table td{padding:9px 12px;border-bottom:1px solid #0a0f1a}
.items-table tr:hover td{background:rgba(255,255,255,.015)}

.qty-input{background:rgba(255,255,255,.08);border:1px solid #1e2d45;border-radius:6px;padding:6px 10px;color:#e2e8f0;font-size:13px;width:90px;text-align:center;font-family:Cairo,sans-serif;outline:none}
.qty-input:focus{border-color:#7c6dff}
.qty-input[readonly]{background:rgba(255,255,255,.03);color:#334155}

.diff-positive{color:#4ade80;font-weight:700}
.diff-negative{color:#f87171;font-weight:700}
.diff-zero{color:#334155}

.status-badge{padding:3px 8px;border-radius:5px;font-size:11px;font-weight:700}
.filter-bar{display:flex;gap:10px;margin-bottom:12px;align-items:center}
.search-input{background:rgba(255,255,255,.06);border:1px solid #1e2d45;border-radius:8px;padding:8px 12px;color:#e2e8f0;font-size:13px;font-family:Cairo,sans-serif;outline:none;flex:1}
.summary-row{background:#161c2d;font-weight:700}
</style>

@php
    $record = $this->record;
    $items  = $record->items()->with('product.category')->get();
    $totalDiff = $items->sum('difference_value');
    $countedItems = $items->whereNotNull('actual_quantity')->count();
    $remainingItems = $items->whereNull('actual_quantity')->count();
@endphp

{{-- Header Stats --}}
<div class="count-header">
    <div class="count-stat">
        <div class="count-stat-label">رقم الجرد</div>
        <div class="count-stat-value" style="color:#7c6dff;font-size:14px">{{ $record->count_number }}</div>
    </div>
    <div class="count-stat">
        <div class="count-stat-label">إجمالي الأصناف</div>
        <div class="count-stat-value">{{ $items->count() }}</div>
    </div>
    <div class="count-stat">
        <div class="count-stat-label">تم جرده</div>
        <div class="count-stat-value" style="color:#4ade80">{{ $countedItems }}</div>
    </div>
    <div class="count-stat">
        <div class="count-stat-label">قيمة الفروقات</div>
        <div class="count-stat-value" style="color:{{ $totalDiff >= 0 ? '#4ade80' : '#f87171' }}">
            {{ $totalDiff >= 0 ? '+' : '' }}{{ number_format($totalDiff, 2) }} ر.س
        </div>
    </div>
</div>

{{-- Items Table --}}
@if($items->count() > 0)
<div style="background:#0d1117;border:1px solid #1e2d45;border-radius:12px;overflow:hidden">
    <div class="filter-bar" style="padding:12px 16px;border-bottom:1px solid #1e2d45">
        <span style="font-size:13px;font-weight:700;color:#94a3b8">📋 أصناف الجرد</span>
        <span style="font-size:12px;color:#475569">متبقي: {{ $remainingItems }} صنف</span>
    </div>
    <div style="overflow-x:auto">
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>الصنف</th>
                <th>المجموعة</th>
                <th>كمية النظام</th>
                <th>الكمية الفعلية</th>
                <th>الفرق</th>
                <th>سعر التكلفة</th>
                <th>قيمة الفرق</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td style="color:#334155">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:700;color:#e2e8f0">{{ $item->product->name }}</div>
                    <div style="font-size:10px;color:#334155">{{ $item->product->code }}</div>
                </td>
                <td style="color:#64748b">{{ $item->product->category?->name ?? '-' }}</td>
                <td style="color:#94a3b8;font-weight:700">{{ number_format($item->system_quantity, 2) }}</td>
                <td>
                    @if($record->status === 'in_progress')
                    <input type="number"
                        class="qty-input"
                        value="{{ $item->actual_quantity ?? '' }}"
                        placeholder="أدخل"
                        min="0"
                        step="0.01"
                        wire:change="updateActualQuantity({{ $item->id }}, $event.target.value)">
                    @else
                    <span style="color:#e2e8f0;font-weight:700">{{ $item->actual_quantity !== null ? number_format($item->actual_quantity, 2) : '-' }}</span>
                    @endif
                </td>
                <td class="{{ $item->difference > 0 ? 'diff-positive' : ($item->difference < 0 ? 'diff-negative' : 'diff-zero') }}">
                    {{ $item->actual_quantity !== null ? ($item->difference > 0 ? '+' : '') . number_format($item->difference, 2) : '-' }}
                </td>
                <td style="color:#64748b">{{ number_format($item->cost_price, 2) }} ر.س</td>
                <td class="{{ $item->difference_value > 0 ? 'diff-positive' : ($item->difference_value < 0 ? 'diff-negative' : 'diff-zero') }}">
                    {{ $item->actual_quantity !== null ? ($item->difference_value > 0 ? '+' : '') . number_format($item->difference_value, 2) . ' ر.س' : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="7" style="padding:10px 12px;color:#94a3b8;text-align:right">الإجمالي</td>
                <td style="padding:10px 12px;color:{{ $totalDiff >= 0 ? '#4ade80' : '#f87171' }};font-size:14px">
                    {{ $totalDiff >= 0 ? '+' : '' }}{{ number_format($totalDiff, 2) }} ر.س
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
@else
<div style="text-align:center;padding:60px;background:#0d1117;border:1px solid #1e2d45;border-radius:12px;color:#1e2d45;font-size:14px">
    اضغط "تحميل الأصناف" لبدء الجرد
</div>
@endif
</x-filament-panels::page>