<x-filament-panels::page>
    @php($stats = $this->getLocationStats())

    <div style="display:flex;flex-direction:column;gap:16px;direction:rtl;">
        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;">
            @foreach([
                ['label'=>'إجمالي المواقع','value'=>$stats['total'],'hint'=>'مواقع التخزين المسجلة','tag'=>'TOTAL'],
                ['label'=>'مخازن مستخدمة','value'=>$stats['warehouses'],'hint'=>'مواقع مرتبطة بالمخازن','tag'=>'WAREHOUSE'],
                ['label'=>'مواقع بها رصيد','value'=>$stats['stocked'],'hint'=>'جاهزة لصرف الأصناف','tag'=>'STOCK'],
                ['label'=>'مواقع بدون رصيد','value'=>$stats['empty'],'hint'=>'تحتاج مراجعة أو إعادة توزيع','tag'=>'EMPTY'],
            ] as $card)
                <div style="min-height:110px;border:1px solid rgba(59,130,246,.22);border-radius:16px;background:linear-gradient(135deg,rgba(16,32,56,.96),rgba(12,22,39,.96));padding:16px 18px;box-shadow:0 6px 22px rgba(0,0,0,.18);">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                        <div>
                            <div style="font-size:12px;font-weight:700;color:#cbd5e1;">{{ $card['label'] }}</div>
                            <div style="margin-top:8px;font-size:28px;line-height:1;font-weight:800;color:#c4b5fd;">{{ number_format($card['value']) }}</div>
                            <div style="margin-top:8px;font-size:11px;color:#94a3b8;">{{ $card['hint'] }}</div>
                        </div>
                        <span style="padding:4px 8px;border-radius:9px;border:1px solid rgba(167,139,250,.24);background:rgba(167,139,250,.10);font-size:10px;color:#c4b5fd;">{{ $card['tag'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="border:1px solid rgba(59,130,246,.20);border-radius:16px;background:rgba(15,23,42,.34);padding:4px;box-shadow:0 6px 22px rgba(0,0,0,.16);">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
