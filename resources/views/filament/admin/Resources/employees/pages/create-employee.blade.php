<x-filament-panels::page>
    @include('filament.admin.resources.employees.pages.partials.employee-inner-styles')

    <div class="orion-employee-inner">
        <div class="oei-hero">
            <div class="oei-hero-main">
                <div class="oei-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M19 8v6M16 11h6"/></svg>
                </div>
                <div>
                    <div class="oei-eyebrow">NEW EMPLOYEE</div>
                    <div class="oei-title">إضافة موظف جديد</div>
                    <div class="oei-sub">أنشئ ملف الموظف وحدد بياناته الوظيفية والراتب والحالة من شاشة واحدة.</div>
                </div>
            </div>
            <div class="oei-chips">
                <div class="oei-chip green"><span class="oei-dot"></span>ملف جديد</div>
                <div class="oei-chip blue">بيانات وظيفية</div>
                <div class="oei-chip amber">راتب وتعويضات</div>
            </div>
        </div>
        <div class="oei-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
