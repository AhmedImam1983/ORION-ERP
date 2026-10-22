<x-filament-panels::page>
    @php
        $stats = $this->getProductStats();
        $quick = request()->query('quick');
        $baseUrl = url()->current();
    @endphp

    <style>
        .orion-products-v3 {
            direction: rtl;
            --op-panel: #0d1728;
            --op-panel-2: #111c30;
            --op-border: #263853;
            --op-border-soft: #1a2940;
            --op-text: #f8fafc;
            --op-muted: #8091aa;
            --op-purple: #8b5cf6;
            --op-green: #34d399;
            --op-amber: #fbbf24;
            --op-red: #f87171;
            --op-blue: #60a5fa;
        }

        .orion-products-v3 * { box-sizing: border-box; }

        .orion-products-v3 .op-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin: 0 0 14px;
        }

        .orion-products-v3 .op-kpi {
            position: relative;
            display: block;
            overflow: hidden;
            min-height: 102px;
            padding: 15px 17px;
            border: 1px solid var(--op-border);
            border-radius: 15px;
            background: linear-gradient(145deg, var(--op-panel-2), #0b1525);
            color: inherit;
            text-decoration: none;
            box-shadow: 0 12px 28px rgba(0,0,0,.11);
            transition: transform .15s ease, border-color .15s ease, background .15s ease;
        }

        .orion-products-v3 .op-kpi:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--accent) 46%, var(--op-border));
        }

        .orion-products-v3 .op-kpi.active {
            border-color: color-mix(in srgb, var(--accent) 70%, var(--op-border));
            background: linear-gradient(145deg, color-mix(in srgb, var(--accent) 9%, #111c30), #0b1525);
            box-shadow: 0 0 0 1px color-mix(in srgb, var(--accent) 18%, transparent), 0 12px 28px rgba(0,0,0,.11);
        }

        .orion-products-v3 .op-kpi::after {
            content: '';
            position: absolute;
            right: 0;
            left: 0;
            bottom: 0;
            height: 2px;
            opacity: .78;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }

        .orion-products-v3 .op-kpi-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .orion-products-v3 .op-kpi-label { color: #b8c4d5; font-size: 11px; font-weight: 850; }
        .orion-products-v3 .op-kpi-code {
            direction: ltr;
            padding: 3px 8px;
            border: 1px solid #2c3d59;
            border-radius: 999px;
            color: #72839c;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: .06em;
        }
        .orion-products-v3 .op-kpi-value {
            margin-top: 12px;
            color: var(--accent);
            font-size: 23px;
            font-weight: 950;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .orion-products-v3 .op-kpi-note { margin-top: 7px; color: #65768e; font-size: 9px; }

        .orion-products-v3 .op-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
            padding: 8px 10px;
            border: 1px solid #22334c;
            border-radius: 12px;
            background: rgba(13,23,40,.72);
        }

        .orion-products-v3 .op-quick-filters { display: flex; gap: 6px; flex-wrap: wrap; }
        .orion-products-v3 .op-filter-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 31px;
            padding: 0 11px;
            border: 1px solid #2b3b55;
            border-radius: 9px;
            background: #0d1728;
            color: #9fb0c5;
            text-decoration: none;
            font-size: 9px;
            font-weight: 900;
            transition: .15s ease;
        }
        .orion-products-v3 .op-filter-chip:hover { border-color: var(--op-purple); color: #fff; }
        .orion-products-v3 .op-filter-chip.active {
            border-color: #8b5cf6;
            background: rgba(139,92,246,.12);
            color: #d8ccff;
        }
        .orion-products-v3 .op-toolbar-note { color: #65768e; font-size: 9px; }

        .orion-products-v3 .op-table-shell {
            overflow: hidden;
            border: 1px solid #22334c;
            border-radius: 16px;
            background: linear-gradient(145deg, #0c1627, #091321);
            box-shadow: 0 16px 38px rgba(0,0,0,.15);
        }

        .orion-products-v3 .op-table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 15px;
            border-bottom: 1px solid #1e2d45;
            background: #0f192b;
        }
        .orion-products-v3 .op-table-title { color: #f8fafc; font-size: 15px; font-weight: 950; }
        .orion-products-v3 .op-table-sub { margin-top: 3px; color: #677991; font-size: 9px; }
        .orion-products-v3 .op-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border: 1px solid rgba(52,211,153,.18);
            border-radius: 999px;
            background: rgba(52,211,153,.055);
            color: #6ee7b7;
            font-size: 9px;
            font-weight: 900;
        }
        .orion-products-v3 .op-status::before { content:''; width:5px; height:5px; border-radius:50%; background:var(--op-green); }

        .orion-products-v3 .op-table-shell .fi-ta {
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .orion-products-v3 .op-table-shell .fi-ta-header,
        .orion-products-v3 .op-table-shell .fi-ta-header-ctn { background: #0d1728 !important; border-color: #1e2d45 !important; }
        .orion-products-v3 .op-table-shell .fi-ta-row { transition: background-color .15s ease; }
        .orion-products-v3 .op-table-shell .fi-ta-row:hover { background: rgba(139,92,246,.04) !important; }
        .orion-products-v3 .op-table-shell .fi-ta-cell,
        .orion-products-v3 .op-table-shell .fi-ta-header-cell { border-color: #1a2940 !important; }
        .orion-products-v3 .op-table-shell .fi-ta-header-cell-label { color:#8292aa !important; font-size:9px !important; font-weight:900 !important; }
        .orion-products-v3 .op-table-shell .fi-ta-text-item-label { font-size: 10px; }
        .orion-products-v3 .op-table-shell .fi-ta-actions { gap: 4px !important; }
        .orion-products-v3 .op-table-shell .fi-icon-btn {
            border: 1px solid #2b3b55 !important;
            border-radius: 8px !important;
            background: #0d1728 !important;
        }
        .orion-products-v3 .op-table-shell .fi-icon-btn:hover { border-color:#8b5cf6 !important; background:rgba(139,92,246,.08) !important; }

        .orion-products-v3 .op-alert {
            margin-top: 10px;
            padding: 9px 12px;
            border: 1px solid rgba(251,191,36,.16);
            border-radius: 11px;
            background: rgba(251,191,36,.045);
            color: #a89b78;
            font-size: 9px;
        }
        .orion-products-v3 .op-alert strong { color:#f6d988; }

        @media (max-width: 1100px) {
            .orion-products-v3 .op-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 720px) {
            .orion-products-v3 .op-kpis { grid-template-columns: 1fr; }
            .orion-products-v3 .op-toolbar, .orion-products-v3 .op-table-head { align-items:flex-start; flex-direction:column; }
            .orion-products-v3 .op-toolbar-note { display:none; }
        }
    </style>

    <div class="orion-products-v3">
        <div class="op-kpis">
            <a href="{{ $baseUrl }}" class="op-kpi {{ !$quick ? 'active' : '' }}" style="--accent:var(--op-blue)">
                <div class="op-kpi-head"><span class="op-kpi-label">إجمالي الأصناف</span><span class="op-kpi-code">TOTAL</span></div>
                <div class="op-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="op-kpi-note">اضغط لعرض جميع الأصناف</div>
            </a>

            <a href="{{ $baseUrl }}?quick=active" class="op-kpi {{ $quick === 'active' ? 'active' : '' }}" style="--accent:var(--op-green)">
                <div class="op-kpi-head"><span class="op-kpi-label">الأصناف النشطة</span><span class="op-kpi-code">ACTIVE</span></div>
                <div class="op-kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="op-kpi-note">الأصناف المتاحة للاستخدام والحركة</div>
            </a>

            <a href="{{ $baseUrl }}?quick=low_stock" class="op-kpi {{ $quick === 'low_stock' ? 'active' : '' }}" style="--accent:{{ $stats['low_stock'] > 0 ? 'var(--op-red)' : 'var(--op-amber)' }}">
                <div class="op-kpi-head"><span class="op-kpi-label">منخفض المخزون</span><span class="op-kpi-code">LOW</span></div>
                <div class="op-kpi-value">{{ number_format($stats['low_stock']) }}</div>
                <div class="op-kpi-note">الرصيد الحالي عند الحد الأدنى أو أقل</div>
            </a>

            <a href="{{ $baseUrl }}?quick=unassigned" class="op-kpi {{ $quick === 'unassigned' ? 'active' : '' }}" style="--accent:var(--op-amber)">
                <div class="op-kpi-head"><span class="op-kpi-label">بدون مخزن افتراضي</span><span class="op-kpi-code">WH</span></div>
                <div class="op-kpi-value">{{ number_format($stats['without_warehouse']) }}</div>
                <div class="op-kpi-note">تحتاج تحديد مخزن افتراضي</div>
            </a>
        </div>

        <div class="op-toolbar">
            <div class="op-quick-filters">
                <a href="{{ $baseUrl }}" class="op-filter-chip {{ !$quick ? 'active' : '' }}">الكل</a>
                <a href="{{ $baseUrl }}?quick=low_stock" class="op-filter-chip {{ $quick === 'low_stock' ? 'active' : '' }}">منخفض المخزون</a>
                <a href="{{ $baseUrl }}?quick=unassigned" class="op-filter-chip {{ $quick === 'unassigned' ? 'active' : '' }}">بدون مخزن</a>
                <a href="{{ $baseUrl }}?quick=inactive" class="op-filter-chip {{ $quick === 'inactive' ? 'active' : '' }}">غير نشط ({{ $stats['inactive'] }})</a>
            </div>
            <div class="op-toolbar-note">اضغط على كارت المؤشر أو الفلتر السريع لتصفية القائمة</div>
        </div>

        <div class="op-table-shell">
            <div class="op-table-head">
                <div>
                    <div class="op-table-title">قائمة الأصناف</div>
                    <div class="op-table-sub">بحث، تصفية، عرض، تعديل وطباعة باركود من نفس الجدول</div>
                </div>
                <div class="op-status">جاهز للتشغيل</div>
            </div>
            {{ $this->table }}
        </div>

        @if($stats['low_stock'] > 0 || $stats['without_warehouse'] > 0)
            <div class="op-alert">
                <strong>تنبيه تشغيل:</strong>
                يوجد {{ $stats['low_stock'] }} صنف منخفض المخزون و{{ $stats['without_warehouse'] }} صنف بدون مخزن افتراضي.
            </div>
        @endif
    </div>
</x-filament-panels::page>