<x-filament-panels::page>
    @include('filament.admin.resources.warehouses.pages.partials.warehouse-inner-styles')

    @php $record = $this->getRecord(); @endphp

    <div class="orion-wh-inner">
        <div class="wi-hero">
            <div class="wi-hero-main">
                <div class="wi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h4l11-11-4-4L4 16z"/><path d="m13.5 6.5 4 4"/></svg></div>
                <div><div class="wi-eyebrow">EDIT WAREHOUSE</div><div class="wi-title">تعديل {{ $record->name }}</div><div class="wi-sub">تحديث بيانات المخزن والمسؤول والموقع وإعدادات التشغيل.</div></div>
            </div>
            <div class="wi-chips">
                <div class="wi-chip {{ $record->is_active ? 'green' : 'gray' }}"><span class="wi-dot"></span>{{ $record->is_active ? 'نشط' : 'غير نشط' }}</div>
                @if($record->is_default)<div class="wi-chip amber"><span class="wi-dot"></span>افتراضي</div>@endif
                <div class="wi-chip">الكود <strong>{{ $record->code }}</strong></div>
            </div>
        </div>
        <div class="wi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
