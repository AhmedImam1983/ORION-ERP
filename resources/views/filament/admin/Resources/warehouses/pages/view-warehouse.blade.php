<x-filament-panels::page>
    @include('filament.admin.resources.warehouses.pages.partials.warehouse-inner-styles')

    @php
        $record = $this->getRecord();
        $productsCount = $record->products()->count();
    @endphp

    <div class="orion-wh-inner">
        <div class="wi-hero">
            <div class="wi-hero-main">
                <div class="wi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5 12 4l9 5.5V20H3z"/><path d="M7 20v-6h10v6M8 10h.01M12 10h.01M16 10h.01"/></svg></div>
                <div><div class="wi-eyebrow">WAREHOUSE DETAILS</div><div class="wi-title">{{ $record->name }}</div><div class="wi-sub">عرض بيانات المخزن، إعدادات التشغيل، والأصناف الموجودة داخله.</div></div>
            </div>
            <div class="wi-chips">
                <div class="wi-chip {{ $record->is_active ? 'green' : 'gray' }}"><span class="wi-dot"></span>{{ $record->is_active ? 'نشط' : 'غير نشط' }}</div>
                @if($record->is_default)<div class="wi-chip amber"><span class="wi-dot"></span>المخزن الافتراضي</div>@endif
                <div class="wi-chip">الأصناف <strong>{{ number_format($productsCount) }}</strong></div>
                <div class="wi-chip">الكود <strong>{{ $record->code }}</strong></div>
            </div>
        </div>
        <div class="wi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
