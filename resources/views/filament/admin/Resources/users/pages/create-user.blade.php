<x-filament-panels::page>
    @include('filament.admin.resources.users.pages.partials.user-inner-styles')

    <div class="orion-user-inner">
        <div class="oui-hero">
            <div class="oui-hero-main">
                <div class="oui-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M19 8v6M16 11h6"/></svg>
                </div>
                <div>
                    <div class="oui-eyebrow">NEW USER</div>
                    <div class="oui-title">إضافة مستخدم جديد</div>
                    <div class="oui-sub">أنشئ حسابًا جديدًا وحدد دوره الوظيفي ونطاق وصوله إلى الفروع.</div>
                </div>
            </div>
            <div class="oui-chips">
                <div class="oui-chip green"><span class="oui-dot"></span>حساب جديد</div>
                <div class="oui-chip">كلمة المرور <strong>8+</strong></div>
                <div class="oui-chip blue">الدور والصلاحيات</div>
            </div>
        </div>

        <div class="oui-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
