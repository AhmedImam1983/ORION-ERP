<x-filament-panels::page>
@php
    $rows = $this->getReturnRows();
    $estimate = $this->getReturnEstimate();
@endphp

<style>
    .osr-wrap{direction:rtl;display:grid;gap:16px}
    .osr-card{background:#0c1526;border:1px solid #24344e;border-radius:16px;padding:18px}
    .osr-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap}
    .osr-kicker{font-size:11px;font-weight:900;letter-spacing:.12em;color:#8b5cf6}
    .osr-title{font-size:22px;font-weight:950;color:#f4f7fb;margin-top:4px}
    .osr-muted{color:#8798b1;font-size:12px;margin-top:5px}
    .osr-badge{padding:7px 11px;border-radius:999px;background:#13233a;border:1px solid #2b4265;color:#d7e2f2;font-size:12px;font-weight:900}
    .osr-alert{background:#281d0c;border:1px solid #674917;color:#f8d694;border-radius:13px;padding:12px 14px;font-size:12px;line-height:1.8}
    .osr-table{overflow:auto;border:1px solid #1f3049;border-radius:14px}
    .osr-row{display:grid;grid-template-columns:minmax(190px,2fr) repeat(5,minmax(90px,1fr));gap:0;min-width:820px;border-bottom:1px solid #1a2940;align-items:center}
    .osr-row:last-child{border-bottom:0}.osr-row>div{padding:12px 11px}
    .osr-row.head{background:#101a2d;color:#8798b1;font-size:11px;font-weight:900}
    .osr-name{color:#f4f7fb;font-weight:900}.osr-sub{color:#6f819c;font-size:11px;margin-top:3px}
    .osr-num{font-variant-numeric:tabular-nums;color:#dce7f7}.osr-avail{color:#22e56b;font-weight:950}
    .osr-input{width:100%;background:#07111f;border:1px solid #31445f;border-radius:10px;color:#fff;padding:9px 10px;outline:none}
    .osr-form{display:grid;grid-template-columns:1fr 310px;gap:16px}.osr-label{display:block;color:#a8b7ca;font-size:12px;font-weight:900;margin-bottom:7px}
    .osr-textarea{width:100%;min-height:110px;resize:vertical;background:#07111f;border:1px solid #31445f;border-radius:12px;color:#fff;padding:12px;outline:none}
    .osr-summary{display:grid;gap:9px}.osr-sline{display:flex;justify-content:space-between;gap:14px;color:#a8b7ca;font-size:12px}
    .osr-sline strong{color:#f4f7fb;font-variant-numeric:tabular-nums}.osr-total{border-top:1px solid #263955;padding-top:10px;margin-top:4px;font-size:15px}.osr-total strong{color:#22e56b;font-size:20px}
    .osr-btn{width:100%;margin-top:14px;background:#7c3aed;border:1px solid #8b5cf6;color:#fff;border-radius:12px;padding:12px 14px;font-weight:950;cursor:pointer}
    .osr-zero{opacity:.55}@media(max-width:900px){.osr-form{grid-template-columns:1fr}}
</style>

<div class="osr-wrap">
    <div class="osr-card">
        <div class="osr-head">
            <div>
                <div class="osr-kicker">SALES RETURN</div>
                <div class="osr-title">إنشاء مرتجع بيع مرتبط بالفاتورة الأصلية</div>
                <div class="osr-muted">
                    {{ $record->invoice_number }} · {{ $record->customer?->name ?? 'عميل نقدي' }}
                    · إجمالي الأصل {{ number_format((float) $record->total, 2) }} ر.س
                </div>
            </div>
            <div class="osr-badge">الأسعار والخصومات والضريبة من الفاتورة الأصلية</div>
        </div>
    </div>

    <div class="osr-alert">
        إنشاء المرتجع سيعكس المبيعات والضريبة وتكلفة البضاعة ويعيد الكمية للمخزن الأصلي. لن يتم رد النقدية تلقائيًا؛
        بعد إنشاء المرتجع يمكنك تسجيل رد جزئي أو كامل من شاشة المرتجع.
    </div>

    <div class="osr-card">
        <div class="osr-table">
            <div class="osr-row head"><div>الصنف</div><div>المباع</div><div>مرتجع سابق</div><div>المتاح</div><div>السعر</div><div>كمية المرتجع</div></div>
            @forelse($rows as $row)
                <div class="osr-row {{ $row['available_quantity'] <= 0 ? 'osr-zero' : '' }}">
                    <div><div class="osr-name">{{ $row['product_name'] }}</div><div class="osr-sub">{{ $row['product_code'] ?: 'بدون كود' }} · ضريبة {{ number_format($row['tax_percent'], 2) }}%</div></div>
                    <div class="osr-num">{{ number_format($row['sold_quantity'], 3) }} {{ $row['unit'] }}</div>
                    <div class="osr-num">{{ number_format($row['returned_quantity'], 3) }}</div>
                    <div class="osr-avail">{{ number_format($row['available_quantity'], 3) }}</div>
                    <div class="osr-num">{{ number_format($row['unit_price'], 2) }} ر.س</div>
                    <div><input type="number" min="0" max="{{ $row['available_quantity'] }}" step="0.001" class="osr-input" wire:model.live.debounce.300ms="returnQuantities.{{ $row['item_id'] }}" @disabled($row['available_quantity'] <= 0)></div>
                </div>
            @empty
                <div style="padding:20px;color:#8798b1">لا توجد أصناف قابلة للإرجاع.</div>
            @endforelse
        </div>
    </div>

    <div class="osr-form">
        <div class="osr-card">
            <label class="osr-label">سبب المرتجع *</label>
            <textarea class="osr-textarea" wire:model="reason" placeholder="مثال: عيب في الصنف / كمية زائدة / عدم مطابقة..."></textarea>
            <div class="osr-muted">لا يمكن إرجاع أكثر من الكمية الأصلية ناقص أي مرتجعات سابقة.</div>
        </div>
        <div class="osr-card">
            <div class="osr-summary">
                <div class="osr-sline"><span>قبل الخصم</span><strong>{{ number_format($estimate['subtotal'], 2) }} ر.س</strong></div>
                <div class="osr-sline"><span>الخصم</span><strong>{{ number_format($estimate['discount'], 2) }} ر.س</strong></div>
                <div class="osr-sline"><span>الضريبة</span><strong>{{ number_format($estimate['tax'], 2) }} ر.س</strong></div>
                <div class="osr-sline osr-total"><span>إجمالي المرتجع</span><strong>{{ number_format($estimate['total'], 2) }} ر.س</strong></div>
            </div>
            <button type="button" class="osr-btn" wire:click="submitReturn">تأكيد وإنشاء مرتجع البيع</button>
        </div>
    </div>
</div>
</x-filament-panels::page>
