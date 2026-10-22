<x-filament-panels::page>
    @include('filament.admin.resources.salaries.pages.partials.salary-inner-styles')
    @php
        $record = $this->getRecord();
        $name = $record->employee?->name ?? 'الموظف';
        $monthName = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'][$record->month] ?? $record->month;
        $statusLabel = match($record->status) { 'paid' => 'مصروف', 'approved' => 'معتمد', 'draft' => 'مسودة', default => $record->status };
        $statusClass = match($record->status) { 'paid' => 'green', 'approved' => 'blue', 'draft' => 'amber', default => '' };
    @endphp
    <div class="orion-salary-inner">
        <div class="osi-hero">
            <div class="osi-hero-main">
                <div class="osi-avatar">{{ mb_substr($name,0,1) }}</div>
                <div><div class="osi-eyebrow">EDIT PAYROLL RECORD</div><div class="osi-title">تعديل راتب {{ $name }}</div><div class="osi-sub">راجع المستحقات والخصومات وطريقة الصرف، وسيظل صافي الراتب محسوبًا من نفس معادلة النظام.</div></div>
            </div>
            <div class="osi-chips"><div class="osi-chip {{ $statusClass }}"><span class="osi-dot"></span>{{ $statusLabel }}</div><div class="osi-chip blue">{{ $monthName }} {{ $record->year }}</div><div class="osi-chip">الصافي <strong>{{ number_format((float)$record->net_salary,2) }} SAR</strong></div></div>
        </div>
        <div class="osi-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
