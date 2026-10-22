<x-filament-panels::page>
    @include('filament.admin.resources.bank-accounts.pages.partials.bank-account-inner-styles')
    <div class="orion-bank-inner">
        <div class="bi-hero">
            <div class="bi-hero-main">
                <div class="bi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M5 10v8M9 10v8M15 10v8M19 10v8M3 18h18M12 3 3 7h18z"/></svg></div>
                <div><div class="bi-eyebrow">BANK ACCOUNT CONTROL</div><div class="bi-title">{{ $this->record->bank_name ?? 'تعديل الحساب البنكي' }}</div><div class="bi-sub">{{ $this->record->account_name ?? '' }} · {{ $this->record->account_number ?? '' }}. راجع بيانات الحساب والربط المحاسبي والإعدادات قبل الحفظ.</div></div>
            </div>
            <div class="bi-chips"><div class="bi-chip {{ $this->record->is_active ? 'green' : '' }}">{{ $this->record->is_active ? 'نشط' : 'غير نشط' }}</div>@if($this->record->is_default)<div class="bi-chip purple">الحساب الافتراضي</div>@endif<div class="bi-chip blue">{{ $this->record->currency ?? 'SAR' }}</div></div>
        </div>
        <div class="bi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
