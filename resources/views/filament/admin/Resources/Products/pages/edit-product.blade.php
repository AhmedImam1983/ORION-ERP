<x-filament-panels::page>
    @php
        $record = $this->record;
        $isService = $record->isService();
        $isLow = !$isService && (float) ($record->quantity ?? 0) <= (float) ($record->min_quantity ?? 0);
        $margin = (float) ($record->sale_price ?? 0) - (float) ($record->cost_price ?? 0);
    @endphp

    <style>
        .orion-product-form {
            direction: rtl;
            --opf-panel:#0d1728;--opf-panel-2:#111c30;--opf-border:#263853;--opf-border-soft:#1b2a41;--opf-text:#f8fafc;--opf-muted:#8091aa;--opf-purple:#8b5cf6;--opf-green:#34d399;--opf-amber:#fbbf24;--opf-red:#f87171;--opf-blue:#60a5fa;
        }
        .orion-product-form *{box-sizing:border-box}
        .opf-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}
        .opf-metric{position:relative;overflow:hidden;min-height:88px;padding:13px 15px;border:1px solid var(--opf-border);border-radius:14px;background:linear-gradient(145deg,var(--opf-panel-2),#0b1525)}
        .opf-metric::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.75}
        .opf-metric-label{font-size:9px;color:#8b9bb1;font-weight:850}.opf-metric-value{margin-top:10px;color:var(--accent);font-size:19px;font-weight:950;white-space:nowrap;font-variant-numeric:tabular-nums}.opf-metric-note{margin-top:5px;color:#61728a;font-size:8px}
        .opf-layout{display:grid;grid-template-columns:minmax(0,1fr) 285px;gap:14px;align-items:start}
        .opf-shell,.opf-side-card{border:1px solid var(--opf-border);border-radius:16px;background:linear-gradient(145deg,#0d1728,#091321);box-shadow:0 16px 38px rgba(0,0,0,.14)}
        .opf-shell{overflow:hidden}.opf-shell-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 17px;border-bottom:1px solid var(--opf-border-soft);background:#0f192b}
        .opf-shell-title{font-size:15px;font-weight:950;color:var(--opf-text)}.opf-shell-sub{margin-top:3px;font-size:9px;color:#6b7d96}.opf-code{direction:ltr;display:inline-flex;padding:5px 9px;border:1px solid rgba(139,92,246,.23);border-radius:8px;background:rgba(139,92,246,.07);color:#c9baff;font-size:9px;font-weight:950}
        .opf-form{padding:16px}.opf-actions{margin-top:16px;padding-top:14px;border-top:1px solid var(--opf-border-soft)}
        .opf-side{display:grid;gap:12px;position:sticky;top:16px}.opf-side-card{padding:15px}.opf-side-kicker{font-size:8px;letter-spacing:.08em;color:#7d6ab0;font-weight:950;direction:ltr;text-align:right}.opf-side-title{margin-top:5px;color:#f5f8fc;font-size:15px;font-weight:950}.opf-side-sub{margin-top:4px;color:#6f819a;font-size:8px;direction:ltr;text-align:right}.opf-side-balance{margin-top:16px;color:{{ $isLow ? 'var(--opf-red)' : 'var(--opf-green)' }};font-size:24px;font-weight:950;font-variant-numeric:tabular-nums}.opf-side-label{margin-top:3px;color:#6b7c94;font-size:8px}
        .opf-side-rows{margin-top:13px;border-top:1px solid #1b2a41}.opf-row{min-height:39px;display:flex;align-items:center;justify-content:space-between;gap:8px;border-bottom:1px solid #17243a}.opf-row:last-child{border-bottom:0}.opf-row span{color:#71839b;font-size:8px}.opf-row strong{color:#e4ebf4;font-size:9px;font-weight:900}
        .opf-state{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:999px;border:1px solid {{ $record->is_active ? 'rgba(52,211,153,.18)' : 'rgba(248,113,113,.18)' }};background:{{ $record->is_active ? 'rgba(52,211,153,.055)' : 'rgba(248,113,113,.055)' }};color:{{ $record->is_active ? '#6ee7b7' : '#fca5a5' }};font-size:8px;font-weight:900}
        .opf-warning{border-color:rgba(251,191,36,.2);background:linear-gradient(145deg,rgba(251,191,36,.06),#0b1525)}.opf-warning strong{color:#f8d77a;font-size:9px}.opf-warning p{margin:6px 0 0;color:#8b805f;font-size:8px;line-height:1.7}
        .orion-product-form .fi-sc-section{border:1px solid #22334d!important;border-radius:13px!important;background:#0f192b!important;box-shadow:none!important;overflow:hidden;margin-bottom:12px!important}.orion-product-form .fi-sc-section-header{background:#111c30!important;border-bottom:1px solid #1e2d45!important;padding:12px 14px!important}.orion-product-form .fi-sc-section-header-heading{color:#f3f6fb!important;font-size:11px!important;font-weight:950!important}.orion-product-form .fi-sc-section-header-description{color:#71829a!important;font-size:8px!important}.orion-product-form .fi-sc-section-content{padding:14px!important}.orion-product-form .fi-fo-field-wrp-label span{font-size:9px!important;font-weight:850!important;color:#9fafc3!important}.orion-product-form .fi-input-wrp{border-color:#2a3b56!important;background:#0b1525!important;border-radius:9px!important;box-shadow:none!important}.orion-product-form .fi-input-wrp:focus-within{border-color:var(--opf-purple)!important;box-shadow:0 0 0 1px rgba(139,92,246,.18)!important}.orion-product-form .fi-input,.orion-product-form .fi-select-input{font-size:10px!important;color:#eef3f8!important}.orion-product-form .fi-fo-field-wrp-helper-text{font-size:8px!important;color:#60718a!important}
        @media(max-width:1100px){.opf-layout{grid-template-columns:1fr}.opf-side{position:static;grid-template-columns:repeat(2,1fr)}.opf-metrics{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.opf-metrics,.opf-side{grid-template-columns:1fr}.opf-shell-head{align-items:flex-start;flex-direction:column}}
    </style>

    <div class="orion-product-form">
        <div class="opf-metrics">
            @if(!$isService)
                <div class="opf-metric" style="--accent:{{ $isLow ? 'var(--opf-red)' : 'var(--opf-green)' }}"><div class="opf-metric-label">الرصيد الحالي</div><div class="opf-metric-value">{{ number_format((float) $record->quantity, 2) }}</div><div class="opf-metric-note">{{ $record->unit ?: 'وحدة' }}</div></div>
                <div class="opf-metric" style="--accent:var(--opf-amber)"><div class="opf-metric-label">الحد الأدنى</div><div class="opf-metric-value">{{ number_format((float) $record->min_quantity, 2) }}</div><div class="opf-metric-note">حد إعادة الطلب والتنبيه</div></div>
            @else
                <div class="opf-metric" style="--accent:var(--opf-blue)"><div class="opf-metric-label">نوع العنصر</div><div class="opf-metric-value">خدمة</div><div class="opf-metric-note">لا تُدار كمخزون</div></div>
                <div class="opf-metric" style="--accent:var(--opf-green)"><div class="opf-metric-label">المخزون</div><div class="opf-metric-value">غير مخزني</div><div class="opf-metric-note">لا تنشأ حركات مخزون</div></div>
            @endif
            <div class="opf-metric" style="--accent:var(--opf-blue)"><div class="opf-metric-label">سعر البيع</div><div class="opf-metric-value">{{ number_format((float) $record->sale_price, 2) }} ر.س</div><div class="opf-metric-note">السعر الرئيسي الحالي</div></div>
            @if(!$isService)
                <div class="opf-metric" style="--accent:{{ $margin >= 0 ? 'var(--opf-purple)' : 'var(--opf-red)' }}"><div class="opf-metric-label">هامش السعر</div><div class="opf-metric-value">{{ number_format($margin, 2) }} ر.س</div><div class="opf-metric-note">سعر البيع ناقص التكلفة</div></div>
            @else
                <div class="opf-metric" style="--accent:var(--opf-purple)"><div class="opf-metric-label">المجموعة</div><div class="opf-metric-value" style="font-size:14px">{{ $record->category?->name ?? 'الخدمات' }}</div><div class="opf-metric-note">تصنيف الخدمة</div></div>
            @endif
        </div>

        <div class="opf-layout">
            <div class="opf-shell">
                <div class="opf-shell-head">
                    <div><div class="opf-shell-title">تعديل بيانات الصنف</div><div class="opf-shell-sub">راجع البيانات الأساسية والمخزون والأسعار والضريبة قبل حفظ التعديلات</div></div>
                    <span class="opf-code">{{ $record->code }}</span>
                </div>
                <form wire:submit="save" id="product-edit-form" class="opf-form">
                    {{ $this->form }}
                    <div class="opf-actions"><x-filament::actions :actions="$this->getFormActions()" /></div>
                </form>
            </div>

            <aside class="opf-side">
                <div class="opf-side-card">
                    <div class="opf-side-kicker">PRODUCT SNAPSHOT</div>
                    <div class="opf-side-title">{{ $record->name }}</div>
                    <div class="opf-side-sub">{{ $record->name_en ?: $record->code }}</div>
                    <div class="opf-side-balance">{{ $isService ? 'خدمة' : number_format((float) $record->quantity, 2) }}</div>
                    <div class="opf-side-label">{{ $isService ? 'لا يوجد رصيد مخزني' : 'الرصيد الحالي · ' . $record->unit }}</div>
                    <div class="opf-side-rows">
                        <div class="opf-row"><span>المجموعة</span><strong>{{ $record->category?->name ?? 'غير محددة' }}</strong></div>
                        <div class="opf-row"><span>المخزن</span><strong>{{ $isService ? 'غير مطلوب' : ($record->warehouse?->name ?? 'غير محدد') }}</strong></div>
                        @if(!$isService)
                            <div class="opf-row"><span>التكلفة</span><strong>{{ number_format((float) $record->cost_price, 2) }} ر.س</strong></div>
                        @endif
                        <div class="opf-row"><span>الحالة</span><strong class="opf-state">{{ $record->is_active ? 'نشط' : 'غير نشط' }}</strong></div>
                    </div>
                </div>
                @if(!$isService && (!$record->warehouse_id || $isLow))
                    <div class="opf-side-card opf-warning">
                        <strong>تنبيه تشغيلي</strong>
                        <p>
                            @if(!$record->warehouse_id) الصنف لا يحتوي على مخزن افتراضي. @endif
                            @if($isLow) الرصيد الحالي عند الحد الأدنى أو أقل. @endif
                        </p>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-filament-panels::page>
