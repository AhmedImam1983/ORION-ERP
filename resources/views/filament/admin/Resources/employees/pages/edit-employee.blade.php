<x-filament-panels::page>
    @include('filament.admin.resources.employees.pages.partials.employee-inner-styles')
    @php
        $record = $this->getRecord();
        $statusLabel = match($record->status) { 'active' => 'نشط', 'inactive' => 'غير نشط', 'suspended' => 'موقوف', default => $record->status };
        $statusClass = match($record->status) { 'active' => 'green', 'suspended' => 'red', default => 'amber' };
        $typeLabel = match($record->employment_type) { 'full_time' => 'دوام كامل', 'part_time' => 'دوام جزئي', 'contract' => 'عقد', default => $record->employment_type };
    @endphp
    <div class="orion-employee-inner">
        <div class="oei-hero">
            <div class="oei-hero-main">
                <div class="oei-avatar">{{ mb_substr($record->name,0,1) }}</div>
                <div>
                    <div class="oei-eyebrow">EDIT EMPLOYEE</div>
                    <div class="oei-title">تعديل بيانات {{ $record->name }}</div>
                    <div class="oei-sub">حدّث الملف الوظيفي والراتب وبيانات التواصل والحالة مع الحفاظ على سجل الموظف.</div>
                </div>
            </div>
            <div class="oei-chips">
                <div class="oei-chip {{ $statusClass }}"><span class="oei-dot"></span>{{ $statusLabel }}</div>
                <div class="oei-chip blue">{{ $record->department ?: 'بدون قسم' }}</div>
                <div class="oei-chip amber">{{ $typeLabel }}</div>
                <div class="oei-chip">الكود <strong>{{ $record->code }}</strong></div>
            </div>
        </div>
        <div class="oei-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
