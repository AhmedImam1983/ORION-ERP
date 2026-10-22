<x-filament-panels::page>
    @include('filament.admin.resources.categories.pages.partials.category-inner-styles')

    @php
        $record = $this->getRecord();
        $productsCount = $record->products()->count();
    @endphp

    <div class="orion-category-inner">
        <div class="oci-hero">
            <div class="oci-hero-main">
                <div class="oci-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="6" rx="2"/><rect x="3" y="14" width="18" height="6" rx="2"/><path d="M7 7h.01M7 17h.01"/>
                    </svg>
                </div>
                <div>
                    <div class="oci-eyebrow">CATEGORY DETAILS</div>
                    <div class="oci-title">{{ $record->name }}</div>
                    <div class="oci-sub">عرض بيانات المجموعة والأصناف المرتبطة بها وحالتها داخل النظام.</div>
                </div>
            </div>

            <div class="oci-chips">
                <div class="oci-chip {{ $record->is_active ? 'green' : 'gray' }}">
                    <span class="oci-dot"></span>
                    {{ $record->is_active ? 'نشطة' : 'غير نشطة' }}
                </div>
                <div class="oci-chip">الأصناف <strong>{{ number_format($productsCount) }}</strong></div>
                <div class="oci-chip">الكود <strong>{{ $record->code ?: '—' }}</strong></div>
            </div>
        </div>

        <div class="oci-content">
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
