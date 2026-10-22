<x-filament-panels::page>
    @include('filament.admin.resources.salaries.pages.partials.salary-inner-styles')

    <div class="orion-salary-inner">
        <div class="osi-hero">
            <div class="osi-hero-main">
                <div class="osi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M12 9v6M9 12h6"/></svg></div>
                <div><div class="osi-eyebrow">NEW PAYROLL RECORD</div><div class="osi-title">إضافة راتب جديد</div><div class="osi-sub">اختر الموظف والفترة ثم راجع المستحقات والخصومات قبل اعتماد صافي الراتب.</div></div>
            </div>
            <div class="osi-chips"><div class="osi-chip amber"><span class="osi-dot"></span>مسودة جديدة</div><div class="osi-chip blue">حساب تلقائي للصافي</div><div class="osi-chip">تأمينات 11%</div></div>
        </div>
        <div class="osi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
