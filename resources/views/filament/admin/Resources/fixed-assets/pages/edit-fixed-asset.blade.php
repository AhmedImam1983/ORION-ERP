<x-filament-panels::page>
    @include('filament.admin.resources.fixed-assets.pages.partials.fixed-asset-inner-styles')
    @php
        $record = $this->getRecord();
        $statusLabel = match($record->status) { 'active' => 'نشط', 'disposed' => 'تالف', 'sold' => 'مباع', default => $record->status };
        $statusClass = match($record->status) { 'active' => 'green', 'disposed' => 'red', 'sold' => 'amber', default => '' };
    @endphp
    <div class="orion-fa-inner">
        <div class="fai-hero">
            <div class="fai-hero-main">
                <div class="fai-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V5h14v16M8 9h8M8 13h5"/><path d="m15 16 4-4 2 2-4 4-3 1z"/></svg></div>
                <div>
                    <div class="fai-eyebrow">EDIT FIXED ASSET</div>
                    <div class="fai-title">تعديل {{ $record->name }}</div>
                    <div class="fai-sub">حدّث بيانات الأصل وموقعه وسياسة الاستهلاك مع متابعة صافي القيمة الدفترية الحالية.</div>
                </div>
            </div>
            <div class="fai-chips">
                <div class="fai-chip {{ $statusClass }}"><span class="fai-dot"></span>{{ $statusLabel }}</div>
                <div class="fai-chip blue">{{ $record->category ?: 'بدون فئة' }}</div>
                <div class="fai-chip">الكود <strong>{{ $record->code }}</strong></div>
                <div class="fai-chip green">NBV <strong>{{ number_format((float)$record->net_book_value,2) }} SAR</strong></div>
                <div class="fai-chip amber">{{ number_format((float)$record->depreciation_rate,2) }}%</div>
            </div>
        </div>
        <div class="fai-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
