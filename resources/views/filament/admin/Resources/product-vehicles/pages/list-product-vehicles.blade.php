<x-filament-panels::page>
    @php($stats = $this->getFitmentStats())

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;direction:rtl;">
            <div style="min-height:110px;border:1px solid rgba(59,130,246,.28);border-radius:16px;background:linear-gradient(135deg,rgba(16,32,56,.96),rgba(12,22,39,.96));padding:16px 18px;box-shadow:0 6px 22px rgba(0,0,0,.18);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;direction:rtl;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#cbd5e1;">إجمالي التوافقات</div>
                        <div style="margin-top:8px;font-size:28px;line-height:1;font-weight:800;color:#60a5fa;">{{ number_format($stats['total']) }}</div>
                        <div style="margin-top:8px;font-size:11px;color:#94a3b8;">كل روابط القطع بالسيارات</div>
                    </div>
                    <span style="padding:4px 8px;border-radius:9px;border:1px solid rgba(96,165,250,.24);background:rgba(96,165,250,.10);font-size:10px;color:#93c5fd;">إجمالي</span>
                </div>
            </div>

            <div style="min-height:110px;border:1px solid rgba(16,185,129,.24);border-radius:16px;background:linear-gradient(135deg,rgba(16,32,56,.96),rgba(12,22,39,.96));padding:16px 18px;box-shadow:0 6px 22px rgba(0,0,0,.18);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;direction:rtl;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#cbd5e1;">قطع مرتبطة</div>
                        <div style="margin-top:8px;font-size:28px;line-height:1;font-weight:800;color:#34d399;">{{ number_format($stats['products']) }}</div>
                        <div style="margin-top:8px;font-size:11px;color:#94a3b8;">أصناف لها توافقات</div>
                    </div>
                    <span style="padding:4px 8px;border-radius:9px;border:1px solid rgba(52,211,153,.24);background:rgba(52,211,153,.10);font-size:10px;color:#6ee7b7;">قطع</span>
                </div>
            </div>

            <div style="min-height:110px;border:1px solid rgba(245,158,11,.24);border-radius:16px;background:linear-gradient(135deg,rgba(16,32,56,.96),rgba(12,22,39,.96));padding:16px 18px;box-shadow:0 6px 22px rgba(0,0,0,.18);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;direction:rtl;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#cbd5e1;">ماركات مركبات</div>
                        <div style="margin-top:8px;font-size:28px;line-height:1;font-weight:800;color:#fbbf24;">{{ number_format($stats['makes']) }}</div>
                        <div style="margin-top:8px;font-size:11px;color:#94a3b8;">Toyota، Hyundai، Nissan...</div>
                    </div>
                    <span style="padding:4px 8px;border-radius:9px;border:1px solid rgba(251,191,36,.24);background:rgba(251,191,36,.10);font-size:10px;color:#fcd34d;">ماركات</span>
                </div>
            </div>

            <div style="min-height:110px;border:1px solid rgba(167,139,250,.26);border-radius:16px;background:linear-gradient(135deg,rgba(16,32,56,.96),rgba(12,22,39,.96));padding:16px 18px;box-shadow:0 6px 22px rgba(0,0,0,.18);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;direction:rtl;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#cbd5e1;">توافقات محددة بالسنوات</div>
                        <div style="margin-top:8px;font-size:28px;line-height:1;font-weight:800;color:#c4b5fd;">{{ number_format($stats['with_years']) }}</div>
                        <div style="margin-top:8px;font-size:11px;color:#94a3b8;">تحتوي على نطاق سنوات</div>
                    </div>
                    <span style="padding:4px 8px;border-radius:9px;border:1px solid rgba(167,139,250,.24);background:rgba(167,139,250,.10);font-size:10px;color:#c4b5fd;">سنوات</span>
                </div>
            </div>
        </div>

        <div style="border:1px solid rgba(59,130,246,.20);border-radius:16px;background:rgba(15,23,42,.34);padding:4px;box-shadow:0 6px 22px rgba(0,0,0,.16);">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
