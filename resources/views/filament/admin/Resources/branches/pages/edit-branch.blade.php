<x-filament-panels::page>
    @include('filament.admin.resources.branches.pages.partials.branch-inner-styles')
    @php $record = $this->getRecord(); @endphp
    <div class="orion-br-inner">
        <div class="bi-hero">
            <div class="bi-hero-main">
                <div class="bi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h4l11-11-4-4L4 16z"/><path d="m13.5 6.5 4 4"/></svg></div>
                <div><div class="bi-eyebrow">EDIT BRANCH</div><div class="bi-title">تعديل {{ $record->name }}</div><div class="bi-sub">تحديث بيانات الفرع، المخزن الافتراضي وإعدادات التشغيل.</div></div>
            </div>
            <div class="bi-chips">
                <div class="bi-chip {{ $record->is_active ? 'green' : 'gray' }}"><span class="bi-dot"></span>{{ $record->is_active ? 'نشط' : 'غير نشط' }}</div>
                @if($record->is_main)<div class="bi-chip amber"><span class="bi-dot"></span>الفرع الرئيسي</div>@endif
                <div class="bi-chip">الكود <strong>{{ $record->code }}</strong></div>
            </div>
        </div>
        <div class="bi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
