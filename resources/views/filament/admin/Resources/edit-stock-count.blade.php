<x-filament-panels::page>
@php
    $items = $this->record->items()->with('product')->orderBy('id')->get();
    $totalItems = $items->count();
    $countedItems = $items->whereNotNull('actual_quantity')->count();
    $remainingItems = max(0, $totalItems - $countedItems);
    $progress = $totalItems > 0 ? round(($countedItems / $totalItems) * 100) : 0;
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --sc-panel:#0d1728;--sc-panel2:#111b2e;--sc-border:#26344c;--sc-border2:#1b2940;
    --sc-text:#f8fafc;--sc-muted:#8ea0b8;--sc-muted2:#607089;--sc-purple:#8b5cf6;
    --sc-green:#34d399;--sc-red:#f87171;--sc-amber:#fbbf24;--sc-blue:#60a5fa;
}
.stock-entry{direction:rtl;color:var(--sc-text)}
.sc-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px;flex-wrap:wrap}
.sc-title{font-size:28px;font-weight:950;margin:0;color:#fff}.sc-sub{font-size:11px;color:var(--sc-muted);margin-top:5px}
.sc-actions{display:flex;gap:8px;flex-wrap:wrap}.sc-btn{height:38px;border-radius:9px;padding:0 13px;border:1px solid #33445e;background:#101a2b;color:#d9e2ef;font-size:11px;font-weight:900;cursor:pointer}.sc-btn:hover{border-color:var(--sc-purple);color:#fff}.sc-btn.primary{background:linear-gradient(135deg,#8b5cf6,#6d28d9);border-color:#8b5cf6;color:#fff}.sc-btn.green{border-color:rgba(52,211,153,.32);background:rgba(52,211,153,.07);color:#6ee7b7}
.sc-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}.sc-kpi{background:linear-gradient(145deg,var(--sc-panel),#0a1422);border:1px solid var(--sc-border);border-radius:13px;padding:14px}.sc-kpi-label{font-size:10px;color:var(--sc-muted);font-weight:800}.sc-kpi-val{font-size:20px;font-weight:950;margin-top:6px}.sc-kpi-val.green{color:var(--sc-green)}.sc-kpi-val.amber{color:var(--sc-amber)}
.progress-shell{height:7px;background:#162239;border-radius:999px;overflow:hidden;margin-top:9px}.progress-bar{height:100%;background:linear-gradient(90deg,#8b5cf6,#34d399);border-radius:inherit;transition:.2s;width:{{ $progress }}%}
.entry-panel{background:linear-gradient(145deg,var(--sc-panel),#0a1422);border:1px solid var(--sc-border);border-radius:15px;padding:14px}
.entry-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap}.entry-note{font-size:10px;color:var(--sc-muted)}.entry-note b{color:#fff}.search-input{width:260px;height:36px;border:1px solid #2b3954;background:#0b1525;border-radius:9px;padding:0 11px;color:#fff;outline:none;font-size:11px}.search-input:focus{border-color:var(--sc-purple)}
.entry-wrap{border:1px solid var(--sc-border2);border-radius:11px;overflow:auto}.entry-table{width:100%;border-collapse:collapse;min-width:850px}.entry-table th{background:#111b2e;color:#8191a8;font-size:10px;font-weight:900;text-align:right;padding:10px 12px;border-bottom:1px solid var(--sc-border2)}.entry-table td{padding:9px 12px;border-bottom:1px solid #18243a;font-size:11px;color:#cad5e4;vertical-align:middle}.entry-table tr:last-child td{border-bottom:0}.entry-table tbody tr:hover{background:rgba(139,92,246,.035)}.entry-table tbody tr.filled{background:rgba(52,211,153,.025)}
.row-no{font-weight:950;color:#a78bfa}.product-name{font-weight:900;color:#f2f6fb}.product-code{direction:ltr;text-align:right;color:#9eb0c7;font-variant-numeric:tabular-nums}.sys-qty{font-weight:900;color:#d8e2ee;font-variant-numeric:tabular-nums}.count-input{width:130px;height:38px;border:1px solid #35445f;background:#091423;border-radius:8px;color:#fff;padding:0 10px;font-size:15px;font-weight:950;text-align:center;outline:none;font-variant-numeric:tabular-nums}.count-input:focus{border-color:#8b5cf6;box-shadow:0 0 0 2px rgba(139,92,246,.12)}.diff-preview{font-weight:900;font-variant-numeric:tabular-nums}.diff-preview.pos{color:var(--sc-green)}.diff-preview.neg{color:var(--sc-red)}.diff-preview.zero{color:#8796ac}.diff-preview.empty{color:#55667f}
.entry-bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;flex-wrap:wrap}.tip{font-size:10px;color:#718198}.tip strong{color:#fbbf24}

.print-sheet{display:none}
@page{size:A4 portrait;margin:10mm}
@media print{
    body *{visibility:hidden!important}
    .print-sheet,.print-sheet *{visibility:visible!important}
    .print-sheet{display:block!important;position:absolute;inset:0;width:100%;background:#fff;color:#000;direction:rtl;font-family:Arial,sans-serif!important}
    .print-sheet.blind .system-col{display:none!important}
    .ps-head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #111;padding-bottom:8px;margin-bottom:10px}
    .ps-title{font-size:20px;font-weight:800}.ps-meta{font-size:10px;line-height:1.8}.ps-table{width:100%;border-collapse:collapse;font-size:10px}.ps-table th,.ps-table td{border:1px solid #333;padding:6px;text-align:right}.ps-table th{background:#eee;font-weight:800}.ps-blank{height:24px;min-width:75px}.ps-sign{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-top:18px;font-size:10px}.ps-sign div{border-top:1px solid #444;padding-top:5px;text-align:center}
}
@media(max-width:900px){.sc-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.sc-grid{grid-template-columns:1fr}.search-input{width:100%}}
</style>

<div class="stock-entry">
    <div class="sc-head">
        <div>
            <h1 class="sc-title">إدخال الجرد السريع</h1>
            <div class="sc-sub">{{ $this->record->count_number }} · {{ $this->record->warehouse?->name ?? 'المخزن' }} · أدخل الأرقام بنفس ترتيب ورقة الجرد واضغط Enter للانتقال للصنف التالي.</div>
        </div>
        <div class="sc-actions no-print">
            <button type="button" class="sc-btn" onclick="printStockCount(false)">طباعة جرد أعمى</button>
            <button type="button" class="sc-btn" onclick="printStockCount(true)">طباعة مع رصيد النظام</button>
            <button type="button" class="sc-btn primary" onclick="focusFirstUncounted()">ابدأ الإدخال</button>
        </div>
    </div>

    <div class="sc-grid no-print">
        <div class="sc-kpi"><div class="sc-kpi-label">إجمالي الأصناف</div><div class="sc-kpi-val">{{ $totalItems }}</div></div>
        <div class="sc-kpi"><div class="sc-kpi-label">تم إدخاله</div><div class="sc-kpi-val green" id="client-counted">{{ $countedItems }}</div></div>
        <div class="sc-kpi"><div class="sc-kpi-label">متبقي</div><div class="sc-kpi-val amber" id="client-remaining">{{ $remainingItems }}</div></div>
        <div class="sc-kpi">
            <div class="sc-kpi-label">نسبة الإنجاز</div>
            <div class="sc-kpi-val" id="client-progress">{{ $progress }}%</div>
            <div class="progress-shell"><div class="progress-bar" id="client-progress-bar"></div></div>
        </div>
    </div>

    <div class="entry-panel no-print">
        <div class="entry-toolbar">
            <div class="entry-note"><b>وضع الإدخال المتتابع:</b> اكتب الكمية الفعلية ثم Enter. القيمة 0 صحيحة وتعني أن الصنف غير موجود فعليًا.</div>
            <input type="search" class="search-input" placeholder="بحث بالكود أو اسم الصنف..." oninput="filterCountRows(this.value)">
        </div>

        <div class="entry-wrap">
            <table class="entry-table" id="count-entry-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الكود</th>
                        <th>الصنف</th>
                        <th>كمية النظام</th>
                        <th>الكمية الفعلية</th>
                        <th>الفرق المتوقع</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="count-row {{ $item->actual_quantity !== null ? 'filled' : '' }}" data-search="{{ mb_strtolower(($item->product?->code ?? '').' '.($item->product?->name ?? '')) }}">
                            <td class="row-no">{{ $loop->iteration }}</td>
                            <td class="product-code">{{ $item->product?->code ?? '-' }}</td>
                            <td class="product-name">{{ $item->product?->name ?? 'صنف محذوف' }}</td>
                            <td class="sys-qty">{{ number_format((float)$item->system_quantity, 2) }}</td>
                            <td>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    inputmode="decimal"
                                    class="count-input"
                                    data-item-id="{{ $item->id }}"
                                    data-system="{{ (float)$item->system_quantity }}"
                                    wire:model.defer="quantities.{{ $item->id }}"
                                    onkeydown="handleCountEnter(event,this)"
                                    oninput="handleCountInput(this)"
                                    value="{{ $item->actual_quantity !== null ? (float)$item->actual_quantity : '' }}"
                                    placeholder="0"
                                    autocomplete="off"
                                >
                            </td>
                            <td>
                                @php $initialDiff = $item->actual_quantity !== null ? ((float)$item->actual_quantity - (float)$item->system_quantity) : null; @endphp
                                <span class="diff-preview {{ $initialDiff === null ? 'empty' : ($initialDiff > 0 ? 'pos' : ($initialDiff < 0 ? 'neg' : 'zero')) }}">
                                    {{ $initialDiff === null ? '—' : number_format($initialDiff,2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:#64748b">لا توجد أصناف محملة في هذا الجرد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="entry-bottom">
            <div class="tip">أفضل ممارسة: استخدم <strong>الجرد الأعمى</strong> في الورقة المطبوعة حتى لا يتأثر العد بكمية النظام.</div>
            <button type="button" class="sc-btn green" wire:click="saveQuantities">حفظ كل الكميات</button>
        </div>
    </div>
</div>

<div class="print-sheet blind" id="stock-count-print-sheet">
    <div class="ps-head">
        <div>
            <div class="ps-title">ورقة جرد مخزني</div>
            <div class="ps-meta">رقم الجرد: <strong>{{ $this->record->count_number }}</strong></div>
            <div class="ps-meta">المخزن: <strong>{{ $this->record->warehouse?->name ?? '-' }}</strong></div>
        </div>
        <div class="ps-meta">
            <div>تاريخ الجرد: {{ optional($this->record->count_date)->format('d/m/Y') ?? $this->record->count_date }}</div>
            <div>نوع الجرد: {{ $this->record->type === 'full' ? 'جرد كامل' : 'جرد جزئي' }}</div>
            <div>عدد الأصناف: {{ $totalItems }}</div>
        </div>
    </div>

    <table class="ps-table">
        <thead>
            <tr>
                <th style="width:35px">#</th>
                <th style="width:90px">الكود</th>
                <th>الصنف</th>
                <th class="system-col" style="width:90px">كمية النظام</th>
                <th style="width:100px">الكمية الفعلية</th>
                <th style="width:120px">ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->code ?? '-' }}</td>
                    <td>{{ $item->product?->name ?? 'صنف محذوف' }}</td>
                    <td class="system-col">{{ number_format((float)$item->system_quantity,2) }}</td>
                    <td class="ps-blank"></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ps-sign">
        <div>اسم القائم بالجرد / التوقيع</div>
        <div>المراجع / التوقيع</div>
        <div>مدير المخزن / التوقيع</div>
    </div>
</div>

<script>
function getVisibleCountInputs(){
    return Array.from(document.querySelectorAll('#count-entry-table .count-row'))
        .filter(row => row.style.display !== 'none')
        .map(row => row.querySelector('.count-input'))
        .filter(Boolean);
}

function handleCountEnter(event, input){
    if(event.key !== 'Enter') return;
    event.preventDefault();
    const inputs = getVisibleCountInputs();
    const index = inputs.indexOf(input);
    if(index >= 0 && index < inputs.length - 1){
        inputs[index + 1].focus();
        inputs[index + 1].select();
    } else {
        input.blur();
    }
}

function handleCountInput(input){
    const row = input.closest('.count-row');
    const diffEl = row.querySelector('.diff-preview');
    const raw = input.value.trim();
    const systemQty = Number(input.dataset.system || 0);

    if(raw === ''){
        row.classList.remove('filled');
        diffEl.textContent = '—';
        diffEl.className = 'diff-preview empty';
    } else {
        row.classList.add('filled');
        const diff = Number(raw) - systemQty;
        diffEl.textContent = diff.toFixed(2);
        diffEl.className = 'diff-preview ' + (diff > 0 ? 'pos' : (diff < 0 ? 'neg' : 'zero'));
    }
    refreshCountProgress();
}

function refreshCountProgress(){
    const inputs = Array.from(document.querySelectorAll('#count-entry-table .count-input'));
    const counted = inputs.filter(i => i.value.trim() !== '').length;
    const total = inputs.length;
    const remaining = Math.max(0,total-counted);
    const pct = total ? Math.round((counted/total)*100) : 0;
    document.getElementById('client-counted').textContent = counted;
    document.getElementById('client-remaining').textContent = remaining;
    document.getElementById('client-progress').textContent = pct + '%';
    document.getElementById('client-progress-bar').style.width = pct + '%';
}

function focusFirstUncounted(){
    const inputs = getVisibleCountInputs();
    const target = inputs.find(i => i.value.trim() === '') || inputs[0];
    if(target){ target.focus(); target.select(); }
}

function filterCountRows(term){
    const q = (term || '').trim().toLowerCase();
    document.querySelectorAll('#count-entry-table .count-row').forEach(row => {
        row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
    });
}

function printStockCount(showSystem){
    const sheet = document.getElementById('stock-count-print-sheet');
    sheet.classList.toggle('blind', !showSystem);
    window.print();
}

document.addEventListener('DOMContentLoaded', refreshCountProgress);
document.addEventListener('livewire:navigated', refreshCountProgress);
</script>
</x-filament-panels::page>