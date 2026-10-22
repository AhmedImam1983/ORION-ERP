<x-filament-panels::page>
    @include('filament.admin.resources.categories.pages.partials.category-inner-styles')

    @php $record = $this->getRecord(); @endphp

    <div class="orion-category-inner">
        <div class="oci-hero">
            <div class="oci-hero-main">
                <div class="oci-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                    </svg>
                </div>
                <div>
                    <div class="oci-eyebrow">EDIT CATEGORY</div>
                    <div class="oci-title">تعديل بيانات {{ $record->name }}</div>
                    <div class="oci-sub">حدّث الاسم والكود والوصف والصورة والحالة ثم احفظ التغييرات.</div>
                </div>
            </div>
            <div class="oci-chips">
                <div class="oci-chip {{ $record->is_active ? 'green' : 'gray' }}"><span class="oci-dot"></span>{{ $record->is_active ? 'نشطة' : 'غير نشطة' }}</div>
                <div class="oci-chip">آخر تحديث <strong>{{ optional($record->updated_at)->format('d/m/Y') }}</strong></div>
            </div>
        </div>

        <div class="oci-content">
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
