<x-filament-panels::page>
    @include('filament.admin.resources.categories.pages.partials.category-inner-styles')

    <div class="orion-category-inner">
        <div class="oci-hero">
            <div class="oci-hero-main">
                <div class="oci-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 8v8M8 12h8"/>
                    </svg>
                </div>
                <div>
                    <div class="oci-eyebrow">NEW CATEGORY</div>
                    <div class="oci-title">إضافة مجموعة جديدة</div>
                    <div class="oci-sub">أنشئ مجموعة لتنظيم الأصناف داخل المخزون بشكل أوضح.</div>
                </div>
            </div>
            <div class="oci-chips">
                <div class="oci-chip green"><span class="oci-dot"></span>جاهزة للإضافة</div>
                <div class="oci-chip">الحقول المطلوبة <strong>1</strong></div>
            </div>
        </div>

        <div class="oci-content">
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
