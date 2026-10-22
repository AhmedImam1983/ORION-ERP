<x-filament-panels::page>
    @include('filament.admin.resources.customers.pages.partials.customer-inner-styles')
    @php
        $r = $this->getRecord();
        $typeLabel = match($r->type){'company'=>'شركة','government'=>'جهة حكومية',default=>'فرد'};
    @endphp
    <div class="orion-customer-inner">
        <div class="ci-hero">
            <div class="ci-hero-main"><div class="ci-avatar">{{ mb_substr($r->name ?? 'C',0,1) }}</div><div><div class="ci-eyebrow">CUSTOMER 360</div><div class="ci-title">{{ $r->name }}</div><div class="ci-sub">نظرة موحدة على بيانات العميل والتواصل والموقف المالي المسجل داخل Orion CRM.</div></div></div>
            <div class="ci-chips"><div class="ci-chip {{ $r->is_active ? 'green' : 'amber' }}"><span class="ci-dot"></span>{{ $r->is_active ? 'نشط' : 'غير نشط' }}</div><div class="ci-chip blue">{{ $typeLabel }}</div><div class="ci-chip">الكود <strong>{{ $r->code }}</strong></div><div class="ci-chip">حد الائتمان <strong>{{ number_format((float)$r->credit_limit,0) }}</strong></div></div>
        </div>
        <div class="ci-content">{{ $this->content }}</div>
    </div>
</x-filament-panels::page>
