<x-filament-panels::page>
    @include('filament.admin.resources.fixed-assets.pages.partials.fixed-asset-inner-styles')
    <div class="orion-fa-inner">
        <div class="fai-hero">
            <div class="fai-hero-main">
                <div class="fai-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V5h10v16M9 9h2M9 13h2M18 8v8M15 12h6"/></svg></div>
                <div>
                    <div class="fai-eyebrow">NEW FIXED ASSET</div>
                    <div class="fai-title">إضافة أصل ثابت جديد</div>
                    <div class="fai-sub">سجّل بيانات الأصل ومكانه وتكلفة الاقتناء والعمر الافتراضي، وسيعرض Orion القيم الدفترية المحسوبة مباشرة.</div>
                </div>
            </div>
            <div class="fai-chips"><div class="fai-chip green"><span class="fai-dot"></span>أصل جديد</div><div class="fai-chip blue">بيانات شراء</div><div class="fai-chip amber">سياسة استهلاك</div></div>
        </div>
        <div class="fai-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
