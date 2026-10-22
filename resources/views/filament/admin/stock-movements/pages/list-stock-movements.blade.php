<x-filament-panels::page>
    @php
        $stats = $this->getMovementStats();
        $quick = request()->query('quick');
        $baseUrl = url()->current();
        $netPositive = $stats['net_qty'] >= 0;
    @endphp

    <style>
        .orion-stock-movements {
            direction: rtl;
            --osm-panel:#0d1728;
            --osm-panel-2:#111c30;
            --osm-border:#263853;
            --osm-border-soft:#1a2940;
            --osm-text:#f8fafc;
            --osm-muted:#8091aa;
            --osm-purple:#8b5cf6;
            --osm-green:#34d399;
            --osm-red:#f87171;
            --osm-blue:#60a5fa;
            --osm-amber:#fbbf24;
        }
        .orion-stock-movements *{box-sizing:border-box}

        .osm-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:0 0 14px}
        .osm-kpi{
            --accent:var(--osm-blue);
            position:relative;display:block;overflow:hidden;min-height:104px;padding:15px 17px;
            border:1px solid var(--osm-border);border-radius:15px;
            background:linear-gradient(145deg,var(--osm-panel-2),#0b1525);
            color:inherit;text-decoration:none;box-shadow:0 12px 28px rgba(0,0,0,.11);
            transition:.15s ease;
        }
        .osm-kpi:hover{transform:translateY(-2px);border-color:color-mix(in srgb,var(--accent) 48%,var(--osm-border))}
        .osm-kpi.active{border-color:color-mix(in srgb,var(--accent) 72%,var(--osm-border));background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 8%,#111c30),#0b1525)}
        .osm-kpi::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.78}
        .osm-kpi-head{display:flex;align-items:center;justify-content:space-between;gap:10px}
        .osm-kpi-label{color:#b8c4d5;font-size:11px;font-weight:850}
        .osm-kpi-code{direction:ltr;padding:3px 8px;border:1px solid #2c3d59;border-radius:999px;color:#72839c;font-size:8px;font-weight:900;letter-spacing:.06em}
        .osm-kpi-value{margin-top:12px;color:var(--accent);font-size:22px;font-weight:950;line-height:1;font-variant-numeric:tabular-nums;white-space:nowrap}
        .osm-kpi-note{margin-top:7px;color:#65768e;font-size:9px}

        .osm-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;padding:8px 10px;border:1px solid #22334c;border-radius:12px;background:rgba(13,23,40,.72)}
        .osm-filters{display:flex;gap:6px;flex-wrap:wrap}
        .osm-chip{display:inline-flex;align-items:center;justify-content:center;height:31px;padding:0 11px;border:1px solid #2b3b55;border-radius:9px;background:#0d1728;color:#9fb0c5;text-decoration:none;font-size:9px;font-weight:900;transition:.15s ease}
        .osm-chip:hover{border-color:var(--osm-purple);color:#fff}
        .osm-chip.active{border-color:#8b5cf6;background:rgba(139,92,246,.12);color:#d8ccff}
        .osm-note{color:#65768e;font-size:9px}

        .osm-shell{overflow:hidden;border:1px solid #22334c;border-radius:16px;background:linear-gradient(145deg,#0c1627,#091321);box-shadow:0 16px 38px rgba(0,0,0,.15)}
        .osm-shell-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 15px;border-bottom:1px solid #1e2d45;background:#0f192b}
        .osm-title{color:#f8fafc;font-size:15px;font-weight:950}
        .osm-sub{margin-top:3px;color:#677991;font-size:9px}
        .osm-legend{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
        .osm-legend-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;font-size:9px;font-weight:900;border:1px solid}
        .osm-legend-pill::before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}
        .osm-legend-pill.in{color:#6ee7b7;border-color:rgba(52,211,153,.18);background:rgba(52,211,153,.055)}
        .osm-legend-pill.out{color:#fca5a5;border-color:rgba(248,113,113,.18);background:rgba(248,113,113,.055)}

        .orion-stock-movements .fi-ta{border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}
        .orion-stock-movements .fi-ta-header,
        .orion-stock-movements .fi-ta-header-ctn{background:#0d1728!important;border-color:#1e2d45!important}
        .orion-stock-movements .fi-ta-row{transition:background-color .15s ease}
        .orion-stock-movements .fi-ta-row:hover{background:rgba(139,92,246,.04)!important}
        .orion-stock-movements .fi-ta-cell,
        .orion-stock-movements .fi-ta-header-cell{border-color:#1a2940!important}
        .orion-stock-movements .fi-ta-header-cell-label{color:#8292aa!important;font-size:9px!important;font-weight:900!important}
        .orion-stock-movements .fi-ta-text-item-label{font-size:10px}
        .orion-stock-movements .fi-ta-image{box-shadow:0 0 0 1px #2a3b56}

        .osm-alert{margin-top:10px;padding:10px 12px;border:1px solid rgba(96,165,250,.17);border-radius:11px;background:rgba(96,165,250,.045);color:#8195b1;font-size:9px;line-height:1.8}
        .osm-alert strong{color:#bdd7ff}

        @media(max-width:1100px){.osm-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:720px){.osm-kpis{grid-template-columns:1fr}.osm-toolbar,.osm-shell-head{align-items:flex-start;flex-direction:column}.osm-note{display:none}}
    </style>

    <div class="orion-stock-movements">
        <div class="osm-kpis">
            <a href="{{ $baseUrl }}" class="osm-kpi {{ !$quick ? 'active' : '' }}" style="--accent:var(--osm-blue)">
                <div class="osm-kpi-head"><span class="osm-kpi-label">إجمالي الحركات</span><span class="osm-kpi-code">TOTAL</span></div>
                <div class="osm-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="osm-kpi-note">{{ number_format($stats['today']) }} حركة اليوم</div>
            </a>

            <a href="{{ $baseUrl }}?quick=incoming" class="osm-kpi {{ $quick === 'incoming' ? 'active' : '' }}" style="--accent:var(--osm-green)">
                <div class="osm-kpi-head"><span class="osm-kpi-label">إجمالي الكمية الواردة</span><span class="osm-kpi-code">IN</span></div>
                <div class="osm-kpi-value">+{{ number_format($stats['incoming_qty'], 2) }}</div>
                <div class="osm-kpi-note">شراء، مرتجعات بيع، تحويل وارد وتسويات زيادة</div>
            </a>

            <a href="{{ $baseUrl }}?quick=outgoing" class="osm-kpi {{ $quick === 'outgoing' ? 'active' : '' }}" style="--accent:var(--osm-red)">
                <div class="osm-kpi-head"><span class="osm-kpi-label">إجمالي الكمية الصادرة</span><span class="osm-kpi-code">OUT</span></div>
                <div class="osm-kpi-value">-{{ number_format($stats['outgoing_qty'], 2) }}</div>
                <div class="osm-kpi-note">بيع، مرتجعات شراء، تحويل صادر وتسويات نقص</div>
            </a>

            <div class="osm-kpi" style="--accent:{{ $netPositive ? 'var(--osm-green)' : 'var(--osm-red)' }}">
                <div class="osm-kpi-head"><span class="osm-kpi-label">صافي حركة الكمية</span><span class="osm-kpi-code">NET</span></div>
                <div class="osm-kpi-value">{{ $stats['net_qty'] >= 0 ? '+' : '' }}{{ number_format($stats['net_qty'], 2) }}</div>
                <div class="osm-kpi-note">الوارد ناقص الصادر عبر سجل الحركات</div>
            </div>
        </div>

        <div class="osm-toolbar">
            <div class="osm-filters">
                <a href="{{ $baseUrl }}" class="osm-chip {{ !$quick ? 'active' : '' }}">الكل</a>
                <a href="{{ $baseUrl }}?quick=incoming" class="osm-chip {{ $quick === 'incoming' ? 'active' : '' }}">وارد</a>
                <a href="{{ $baseUrl }}?quick=outgoing" class="osm-chip {{ $quick === 'outgoing' ? 'active' : '' }}">صادر</a>
                <a href="{{ $baseUrl }}?quick=transfers" class="osm-chip {{ $quick === 'transfers' ? 'active' : '' }}">تحويلات</a>
                <a href="{{ $baseUrl }}?quick=adjustments" class="osm-chip {{ $quick === 'adjustments' ? 'active' : '' }}">تسويات</a>
                <a href="{{ $baseUrl }}?quick=today" class="osm-chip {{ $quick === 'today' ? 'active' : '' }}">اليوم ({{ $stats['today'] }})</a>
            </div>
            <div class="osm-note">استخدم الفلاتر السريعة مع بحث وفلاتر Filament الموجودة داخل الجدول</div>
        </div>

        <div class="osm-shell">
            <div class="osm-shell-head">
                <div>
                    <div class="osm-title">سجل حركة المخزون</div>
                    <div class="osm-sub">الصنف، نوع الحركة، الكمية، القيمة، الرصيد قبل وبعد، المرجع والمخزن</div>
                </div>
                <div class="osm-legend">
                    <span class="osm-legend-pill in">وارد</span>
                    <span class="osm-legend-pill out">صادر</span>
                </div>
            </div>
            {{ $this->table }}
        </div>

        <div class="osm-alert">
            <strong>ملاحظة تشغيلية:</strong>
            زر «تسوية مخزون يدوية» مخصص للتسوية بالزيادة أو النقص أو إدخال رصيد افتتاحي. أما حركات البيع والشراء والتحويلات فتظل مسجلة حسب دورة المستندات في النظام.
        </div>
    </div>
</x-filament-panels::page>
