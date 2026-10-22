<x-filament-panels::page>
    @include('filament.admin.resources.users.pages.partials.user-inner-styles')

    @php
        $record = $this->getRecord();
        $roles = $record->roles->pluck('name');
        $roleLabel = $roles->map(fn ($role) => match($role) {
            'super_admin' => 'مدير النظام', 'admin' => 'مدير', 'cashier' => 'كاشير',
            'accountant' => 'محاسب', 'storekeeper' => 'أمين مخزن', default => $role,
        })->implode('، ');
    @endphp

    <div class="orion-user-inner">
        <div class="oui-hero">
            <div class="oui-hero-main">
                <div class="oui-avatar">{{ mb_substr($record->name, 0, 1) }}</div>
                <div>
                    <div class="oui-eyebrow">EDIT USER</div>
                    <div class="oui-title">تعديل بيانات {{ $record->name }}</div>
                    <div class="oui-sub">حدّث بيانات الحساب أو الدور أو الفرع. اترك كلمة المرور فارغة إذا لم ترغب في تغييرها.</div>
                </div>
            </div>
            <div class="oui-chips">
                <div class="oui-chip amber">{{ $roleLabel ?: 'بدون دور' }}</div>
                <div class="oui-chip blue">{{ $record->branch?->name ?: 'كل الفروع' }}</div>
                <div class="oui-chip">آخر تحديث <strong>{{ optional($record->updated_at)->format('d/m/Y') }}</strong></div>
            </div>
        </div>

        <div class="oui-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
