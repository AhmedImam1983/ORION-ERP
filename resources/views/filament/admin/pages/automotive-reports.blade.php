<x-filament-panels::page>
    <div style="display:flex;flex-direction:column;gap:16px;direction:rtl;">
        <div style="border:1px solid rgba(59,130,246,.20);border-radius:16px;background:rgba(15,23,42,.34);padding:16px;">
            {{ $this->form }}
            <div style="margin-top:12px;display:flex;justify-content:flex-start;">
                <x-filament::button wire:click="refreshReports" icon="heroicon-o-arrow-path">تحديث التقارير</x-filament::button>
            </div>
        </div>

        @php($summary = $this->getSummary())
        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;">
            @foreach([
                ['label'=>'قيمة المبيعات','value'=>number_format($summary['sales'],2).' ر.س'],
                ['label'=>'الكمية المباعة','value'=>number_format($summary['qty'],3)],
                ['label'=>'عدد الفواتير','value'=>number_format($summary['invoices'])],
                ['label'=>'الرصيد الحالي','value'=>number_format($summary['stock'],3)],
            ] as $card)
                <div style="min-height:108px;border:1px solid rgba(167,139,250,.22);border-radius:16px;background:linear-gradient(135deg,rgba(16,32,56,.96),rgba(12,22,39,.96));padding:16px 18px;">
                    <div style="font-size:12px;color:#cbd5e1;font-weight:700;">{{ $card['label'] }}</div>
                    <div style="margin-top:9px;font-size:25px;color:#c4b5fd;font-weight:850;">{{ $card['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="border:1px solid rgba(59,130,246,.20);border-radius:16px;background:rgba(15,23,42,.34);padding:16px;">
                <div style="font-size:15px;font-weight:800;color:#e5e7eb;margin-bottom:12px;">الأصناف الأكثر مبيعًا</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @forelse($this->getTopProducts() as $row)
                        <div style="display:grid;grid-template-columns:100px 1fr 80px 100px;gap:10px;align-items:center;padding:10px 12px;border:1px solid rgba(148,163,184,.14);border-radius:10px;background:rgba(15,23,42,.35);">
                            <span style="color:#94a3b8;font-size:11px;">{{ $row->code }}</span>
                            <div><div style="font-weight:750;color:#e5e7eb;font-size:12px;">{{ $row->name }}</div><div style="font-size:10px;color:#7d8aa0;">{{ $row->brand_name ?? 'بدون علامة' }}</div></div>
                            <span style="color:#34d399;font-weight:800;font-size:12px;">{{ number_format($row->quantity,3) }}</span>
                            <span style="color:#c4b5fd;font-weight:800;font-size:12px;">{{ number_format($row->total,2) }} ر.س</span>
                        </div>
                    @empty
                        <div style="padding:20px;text-align:center;color:#94a3b8;">لا توجد بيانات ضمن الفترة المحددة</div>
                    @endforelse
                </div>
            </div>

            <div style="border:1px solid rgba(59,130,246,.20);border-radius:16px;background:rgba(15,23,42,.34);padding:16px;">
                <div style="font-size:15px;font-weight:800;color:#e5e7eb;margin-bottom:12px;">المخزون حسب العلامة التجارية</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @forelse($this->getBrandStock() as $row)
                        <div style="display:grid;grid-template-columns:1fr 90px 100px;gap:10px;align-items:center;padding:10px 12px;border:1px solid rgba(148,163,184,.14);border-radius:10px;background:rgba(15,23,42,.35);">
                            <span style="color:#e5e7eb;font-weight:750;font-size:12px;">{{ $row->brand_name }}</span>
                            <span style="color:#93c5fd;font-size:11px;">{{ number_format($row->products) }} صنف</span>
                            <span style="color:#34d399;font-weight:800;font-size:12px;">{{ number_format($row->quantity,3) }}</span>
                        </div>
                    @empty
                        <div style="padding:20px;text-align:center;color:#94a3b8;">لا توجد بيانات مخزون</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
