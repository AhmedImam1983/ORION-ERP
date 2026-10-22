<x-filament-panels::page>
    @include('filament.admin.resources.customers.pages.partials.customer-inner-styles')
    <div class="orion-customer-inner">
        <div class="ci-hero">
            <div class="ci-hero-main"><div class="ci-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M19 8v6M16 11h6"/></svg></div><div><div class="ci-eyebrow">NEW CUSTOMER</div><div class="ci-title">إضافة عميل جديد</div><div class="ci-sub">أنشئ ملف العميل وحدد بيانات التواصل والتصنيف والشروط المالية من شاشة واحدة.</div></div></div>
            <div class="ci-chips"><div class="ci-chip green"><span class="ci-dot"></span>ملف جديد</div><div class="ci-chip blue">CRM</div><div class="ci-chip">العملة <strong>SAR</strong></div></div>
        </div>
        <div class="ci-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
