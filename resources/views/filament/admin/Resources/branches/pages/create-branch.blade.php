<x-filament-panels::page>
    @include('filament.admin.resources.branches.pages.partials.branch-inner-styles')
    <div class="orion-br-inner">
        <div class="bi-hero">
            <div class="bi-hero-main">
                <div class="bi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M12 9v8M8 13h8"/></svg></div>
                <div><div class="bi-eyebrow">NEW BRANCH</div><div class="bi-title">إضافة فرع جديد</div><div class="bi-sub">سجّل بيانات الفرع وحدد المخزن الافتراضي وحالة التشغيل داخل النظام.</div></div>
            </div>
            <div class="bi-chips"><div class="bi-chip green"><span class="bi-dot"></span>سجل جديد</div></div>
        </div>
        <div class="bi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
