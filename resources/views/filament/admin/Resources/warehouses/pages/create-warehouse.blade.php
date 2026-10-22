<x-filament-panels::page>
    @include('filament.admin.resources.warehouses.pages.partials.warehouse-inner-styles')

    <div class="orion-wh-inner">
        <div class="wi-hero">
            <div class="wi-hero-main">
                <div class="wi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5 12 4l9 5.5V20H3z"/><path d="M7 20v-6h10v6M12 8v8M8 12h8"/></svg></div>
                <div><div class="wi-eyebrow">NEW WAREHOUSE</div><div class="wi-title">إضافة مخزن جديد</div><div class="wi-sub">سجّل بيانات المخزن وحدد حالته وما إذا كان سيستخدم كمخزن افتراضي.</div></div>
            </div>
            <div class="wi-chips"><div class="wi-chip green"><span class="wi-dot"></span>سجل جديد</div></div>
        </div>
        <div class="wi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
