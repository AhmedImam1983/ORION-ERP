<x-filament-panels::page>
@php
    $rows = $this->getReturnRows();
    $estimate = $this->getReturnEstimate();
@endphp

<style>
    .opr-wrap{direction:rtl;display:grid;gap:16px}
    .opr-card{background:#0c1526;border:1px solid #24344e;border-radius:16px;padding:18px}
    .opr-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap}
    .opr-kicker{font-size:11px;font-weight:900;letter-spacing:.12em;color:#8b5cf6}
    .opr-title{font-size:22px;font-weight:950;color:#f4f7fb;margin-top:4px}
    .opr-muted{color:#8798b1;font-size:12px;margin-top:5px}
    .opr-badge{padding:7px 11px;border-radius:999px;background:#13233a;border:1px solid #2b4265;color:#d7e2f2;font-size:12px;font-weight:900}
    .opr-alert{background:#281d0c;border:1px solid #674917;color:#f8d694;border-radius:13px;padding:12px 14px;font-size:12px;line-height:1.8}
    .opr-table{overflow:auto;border:1px solid #1f3049;border-radius:14px}
    .opr-row{display:grid;grid-template-columns:minmax(190px,2fr) repeat(5,minmax(90px,1fr));gap:0;min-width:820px;border-bottom:1px solid #1a2940;align-items:center}
    .opr-row:last-child{border-bottom:0}
    .opr-row > div{padding:12px 11px}
    .opr-row.head{background:#101a2d;color:#8798b1;font-size:11px;font-weight:900}
    .opr-name{color:#f4f7fb;font-weight:900}
    .opr-sub{color:#6f819c;font-size:11px;margin-top:3px}
    .opr-num{font-variant-numeric:tabular-nums;color:#dce7f7}
    .opr-avail{color:#22e56b;font-weight:950}
    .opr-input{width:100%;background:#07111f;border:1px solid #31445f;border-radius:10px;color:#fff;padding:9px 10px;outline:none}
    .opr-input:focus{border-color:#8b5cf6}
    .opr-form{display:grid;grid-template-columns:1fr 310px;gap:16px}
    .opr-label{display:block;color:#a8b7ca;font-size:12px;font-weight:900;margin-bottom:7px}
    .opr-textarea{width:100%;min-height:110px;resize:vertical;background:#07111f;border:1px solid #31445f;border-radius:12px;color:#fff;padding:12px;outline:none}
    .opr-summary{display:grid;gap:9px}
    .opr-sline{display:flex;justify-content:space-between;gap:14px;color:#a8b7ca;font-size:12px}
    .opr-sline strong{color:#f4f7fb;font-variant-numeric:tabular-nums}
    .opr-total{border-top:1px solid #263955;padding-top:10px;margin-top:4px;font-size:15px}
    .opr-total strong{color:#22e56b;font-size:20px}
    .opr-btn{width:100%;margin-top:14px;background:#7c3aed;border:1px solid #8b5cf6;color:#fff;border-radius:12px;padding:12px 14px;font-weight:950;cursor:pointer}
    .opr-btn:hover{filter:brightness(1.08)}
    .opr-zero{opacity:.55}
    @media(max-width:900px){.opr-form{grid-template-columns:1fr}}
</style>

<div class="opr-wrap">
    <div class="opr-card">
        <div class="opr-head">
            <div>
                <div class="opr-kicker">PURCHASE RETURN</div>
                <div class="opr-title">إنشاء مرتجع شراء مرتبط بالأصل</div>
                <div class="opr-muted">
                    {{ $record->invoice_number }}
                    · {{ $record->supplier?->name ?? 'مورد' }}
                    · إجمالي الأصل {{ number_format((float)$record->total,2) }} ر.س
                </div>
            </div>
            <div class="opr-badge">المورد والأسعار والضريبة من الفاتورة الأصلية</div>
        </div>
    </div>

    @if((float)$record->paid_amount > 0)
        <div class="opr-alert">
            الفاتورة الأصلية عليها سداد بقيمة {{ number_format((float)$record->paid_amount,2) }} ر.س.
            مرتجع الشراء هنا سيخفض ذمة المورد ويُنشئ رصيدًا مدينًا على المورد عند الحاجة؛
            <strong>لن يُدخل أي مبلغ للخزينة أو البنك تلقائيًا.</strong>
        </div>
    @endif

    <div class="opr-card">
        <div class="opr-table">
            <div class="opr-row head">
                <div>الصنف</div>
                <div>المشترى</div>
                <div>مرتجع سابق</div>
                <div>المتاح</div>
                <div>السعر</div>
                <div>كمية المرتجع</div>
            </div>

            @forelse($rows as $row)
                <div class="opr-row {{ $row['available_quantity'] <= 0 ? 'opr-zero' : '' }}">
                    <div>
                        <div class="opr-name">{{ $row['product_name'] }}</div>
                        <div class="opr-sub">
                            {{ $row['product_code'] ?: 'بدون كود' }}
                            · ضريبة {{ number_format($row['tax_percent'],2) }}%
                        </div>
                    </div>
                    <div class="opr-num">{{ number_format($row['purchased_quantity'],3) }} {{ $row['unit'] }}</div>
                    <div class="opr-num">{{ number_format($row['returned_quantity'],3) }}</div>
                    <div class="opr-avail">{{ number_format($row['available_quantity'],3) }}</div>
                    <div class="opr-num">{{ number_format($row['unit_price'],2) }} ر.س</div>
                    <div>
                        <input
                            type="number"
                            min="0"
                            max="{{ $row['available_quantity'] }}"
                            step="0.001"
                            class="opr-input"
                            wire:model.live.debounce.300ms="returnQuantities.{{ $row['item_id'] }}"
                            @disabled($row['available_quantity'] <= 0)
                        >
                    </div>
                </div>
            @empty
                <div style="padding:20px;color:#8798b1">لا توجد أصناف قابلة للإرجاع.</div>
            @endforelse
        </div>
    </div>

    <div class="opr-form">
        <div class="opr-card">
            <label class="opr-label">سبب المرتجع *</label>
            <textarea
                class="opr-textarea"
                wire:model="reason"
                placeholder="مثال: عيب في الصنف / كمية زائدة / عدم مطابقة..."
            ></textarea>
            <div class="opr-muted">
                لا يمكن إرجاع أكثر من الكمية الأصلية ناقص أي مرتجعات سابقة.
            </div>
        </div>

        <div class="opr-card">
            <div class="opr-summary">
                <div class="opr-sline"><span>قبل الخصم</span><strong>{{ number_format($estimate['subtotal'],2) }} ر.س</strong></div>
                <div class="opr-sline"><span>الخصم</span><strong>{{ number_format($estimate['discount'],2) }} ر.س</strong></div>
                <div class="opr-sline"><span>الضريبة</span><strong>{{ number_format($estimate['tax'],2) }} ر.س</strong></div>
                <div class="opr-sline opr-total"><span>إجمالي المرتجع</span><strong>{{ number_format($estimate['total'],2) }} ر.س</strong></div>
            </div>

            <button type="button" class="opr-btn" wire:click="submitReturn">
                تأكيد وإنشاء مرتجع الشراء
            </button>
        </div>
    </div>
</div>
</x-filament-panels::page>
