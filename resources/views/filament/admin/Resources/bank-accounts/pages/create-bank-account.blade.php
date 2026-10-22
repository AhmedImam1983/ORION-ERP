<x-filament-panels::page>
    @include('filament.admin.resources.bank-accounts.pages.partials.bank-account-inner-styles')
    <div class="orion-bank-inner">
        <div class="bi-hero">
            <div class="bi-hero-main">
                <div class="bi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M5 10v8M9 10v8M15 10v8M19 10v8M3 18h18M12 3 3 7h18z"/></svg></div>
                <div><div class="bi-eyebrow">BANK ACCOUNT MASTER</div><div class="bi-title">إضافة حساب بنكي جديد</div><div class="bi-sub">سجّل بيانات البنك ورقم الحساب والربط المحاسبي والفرع، ثم عرّف الرصيد الافتتاحي وحالة التشغيل.</div></div>
            </div>
            <div class="bi-chips"><div class="bi-chip green">حساب جديد</div><div class="bi-chip blue">ربط محاسبي</div><div class="bi-chip amber">رقابة سيولة</div></div>
        </div>
        <div class="bi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
