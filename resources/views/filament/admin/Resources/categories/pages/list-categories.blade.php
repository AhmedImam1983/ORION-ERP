<x-filament-panels::page>
    @php
        $stats = $this->getCategoryStats();
        $resource = \App\Filament\Admin\Resources\Categories\CategoryResource::class;
    @endphp

    <style>
        .fi-header { display: none !important; }

        .orion-cat-v2,
        .orion-cat-v2 * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        .orion-cat-v2 {
            --oc-bg: #08111f;
            --oc-panel: #0c1728;
            --oc-panel-2: #101b2e;
            --oc-border: #21324d;
            --oc-border-soft: #17263c;
            --oc-text: #f4f7fb;
            --oc-muted: #7c8da7;
            --oc-muted-2: #53657f;
            --oc-purple: #8b5cf6;
            --oc-purple-2: #a78bfa;
            --oc-green: #34d399;
            --oc-red: #fb7185;
            --oc-amber: #fbbf24;
            --oc-blue: #60a5fa;
            width: 100%;
            direction: rtl;
            color: var(--oc-text);
            padding-bottom: 24px;
        }

        .oc-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .oc-breadcrumb {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #60728e;
            font-size: 10px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .oc-breadcrumb strong { color: #9c88f5; }

        .oc-title-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .oc-title-icon {
            width: 46px;
            height: 46px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            color: #c0afff;
            border: 1px solid rgba(139, 92, 246, .38);
            background: linear-gradient(145deg, rgba(124, 58, 237, .25), rgba(76, 29, 149, .12));
            box-shadow: 0 12px 30px rgba(76, 29, 149, .15);
        }

        .oc-title-icon svg { width: 22px; height: 22px; }

        .oc-title {
            margin: 0;
            color: #f5f7fb;
            font-size: 27px;
            line-height: 1.1;
            font-weight: 950;
        }

        .oc-subtitle {
            margin-top: 5px;
            color: var(--oc-muted);
            font-size: 10px;
            font-weight: 650;
        }

        .oc-create {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 15px;
            border-radius: 10px;
            text-decoration: none;
            color: #fff;
            font-size: 10px;
            font-weight: 950;
            border: 1px solid rgba(167, 139, 250, .45);
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            box-shadow: 0 12px 24px rgba(91, 33, 182, .2);
            transition: .18s ease;
        }

        .oc-create:hover { transform: translateY(-1px); filter: brightness(1.06); }
        .oc-create svg { width: 15px; height: 15px; }

        .oc-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 13px;
        }

        .oc-kpi {
            position: relative;
            overflow: hidden;
            min-height: 105px;
            padding: 14px 15px;
            border-radius: 13px;
            border: 1px solid var(--oc-border);
            background: linear-gradient(145deg, #0e192b, #0a1423);
        }

        .oc-kpi::after {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            left: -28px;
            bottom: -40px;
            border-radius: 50%;
            opacity: .13;
            background: currentColor;
            filter: blur(2px);
        }

        .oc-kpi.total { color: #a78bfa; }
        .oc-kpi.active { color: #34d399; }
        .oc-kpi.inactive { color: #fb7185; }
        .oc-kpi.empty { color: #fbbf24; }

        .oc-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .oc-kpi-label {
            color: #8b9ab0;
            font-size: 9px;
            font-weight: 850;
        }

        .oc-kpi-icon {
            width: 29px;
            height: 29px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: currentColor;
            border: 1px solid color-mix(in srgb, currentColor 32%, transparent);
            background: color-mix(in srgb, currentColor 8%, transparent);
        }

        .oc-kpi-icon svg { width: 14px; height: 14px; }

        .oc-kpi-value {
            margin-top: 7px;
            color: currentColor;
            font-size: 25px;
            line-height: 1;
            font-weight: 950;
            direction: ltr;
            text-align: right;
        }

        .oc-kpi-note {
            margin-top: 7px;
            color: #5f708a;
            font-size: 8px;
            font-weight: 700;
        }

        .oc-table-panel {
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid var(--oc-border);
            background: linear-gradient(145deg, #0b1524, #091321);
            box-shadow: 0 18px 50px rgba(0, 0, 0, .12);
        }

        .oc-table-panel-head {
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 15px;
            border-bottom: 1px solid var(--oc-border-soft);
        }

        .oc-panel-title {
            color: #e7edf7;
            font-size: 11px;
            font-weight: 950;
        }

        .oc-panel-sub {
            margin-top: 3px;
            color: #61738e;
            font-size: 8px;
            font-weight: 650;
        }

        .oc-count-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            border: 1px solid rgba(139, 92, 246, .22);
            background: rgba(139, 92, 246, .07);
            color: #aa96f8;
            font-size: 8px;
            font-weight: 900;
        }

        .oc-count-chip span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #8b5cf6;
            box-shadow: 0 0 10px rgba(139, 92, 246, .7);
        }

        .oc-table-content { padding: 0; }

        /* Filament table harmonization */
        .orion-cat-v2 .fi-ta-ctn,
        .orion-cat-v2 .fi-section,
        .orion-cat-v2 .fi-ta {
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .orion-cat-v2 .fi-ta-header {
            padding: 11px 14px !important;
            background: rgba(255, 255, 255, .015) !important;
            border-bottom: 1px solid #18283f !important;
        }

        .orion-cat-v2 .fi-ta-table thead {
            background: #0e192a !important;
        }

        .orion-cat-v2 .fi-ta-table thead th {
            color: #657892 !important;
            font-size: 9px !important;
            font-weight: 900 !important;
            border-bottom-color: #1b2b43 !important;
        }

        .orion-cat-v2 .fi-ta-row {
            background: transparent !important;
            border-bottom: 1px solid #15243a !important;
        }

        .orion-cat-v2 .fi-ta-row:hover {
            background: rgba(139, 92, 246, .035) !important;
        }

        .orion-cat-v2 .fi-ta-cell {
            color: #c9d3e1 !important;
            font-size: 9px !important;
        }

        .orion-cat-v2 .fi-input-wrp {
            border-color: #263a59 !important;
            background: #0e1a2d !important;
            box-shadow: none !important;
        }

        .orion-cat-v2 .fi-input {
            color: #dce5ef !important;
            font-size: 9px !important;
        }

        .orion-cat-v2 .fi-ta-pagination {
            border-top: 1px solid #18283f !important;
            background: rgba(255,255,255,.01) !important;
        }

        @media (max-width: 1000px) {
            .oc-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 680px) {
            .oc-head { align-items: stretch; flex-direction: column; }
            .oc-create { width: 100%; }
            .oc-kpis { grid-template-columns: 1fr; }
            .oc-title { font-size: 23px; }
        }
    </style>

    <div class="orion-cat-v2">
        <div class="oc-head">
            <div>
                <div class="oc-breadcrumb">
                    <span>المخازن</span>
                    <span>‹</span>
                    <strong>المجموعات</strong>
                </div>

                <div class="oc-title-row">
                    <div class="oc-title-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="7" height="7" rx="1.5"/>
                            <rect x="14" y="4" width="7" height="7" rx="1.5"/>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="oc-title">المجموعات</h1>
                        <div class="oc-subtitle">تنظيم مجموعات الأصناف ومتابعة حالتها وعدد المنتجات التابعة لكل مجموعة</div>
                    </div>
                </div>
            </div>

            @if($resource::canCreate())
                <a href="{{ $resource::getUrl('create') }}" class="oc-create">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    إضافة مجموعة جديدة
                </a>
            @endif
        </div>

        <div class="oc-kpis">
            <div class="oc-kpi total">
                <div class="oc-kpi-top">
                    <div class="oc-kpi-label">إجمالي المجموعات</div>
                    <div class="oc-kpi-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>
                    </div>
                </div>
                <div class="oc-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="oc-kpi-note">كل المجموعات المسجلة حاليًا</div>
            </div>

            <div class="oc-kpi active">
                <div class="oc-kpi-top">
                    <div class="oc-kpi-label">المجموعات النشطة</div>
                    <div class="oc-kpi-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                    </div>
                </div>
                <div class="oc-kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="oc-kpi-note">متاحة للاستخدام وربط الأصناف</div>
            </div>

            <div class="oc-kpi inactive">
                <div class="oc-kpi-top">
                    <div class="oc-kpi-label">غير النشطة</div>
                    <div class="oc-kpi-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>
                    </div>
                </div>
                <div class="oc-kpi-value">{{ number_format($stats['inactive']) }}</div>
                <div class="oc-kpi-note">موقوفة حاليًا عن الاستخدام</div>
            </div>

            <div class="oc-kpi empty">
                <div class="oc-kpi-top">
                    <div class="oc-kpi-label">بدون أصناف</div>
                    <div class="oc-kpi-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16v12H4z"/><path d="M8 7V5h8v2M9 13h6"/></svg>
                    </div>
                </div>
                <div class="oc-kpi-value">{{ number_format($stats['empty']) }}</div>
                <div class="oc-kpi-note">مجموعات لا تحتوي على أصناف</div>
            </div>
        </div>

        <div class="oc-table-panel">
            <div class="oc-table-panel-head">
                <div>
                    <div class="oc-panel-title">دليل المجموعات</div>
                    <div class="oc-panel-sub">ابحث، صفِّ، واعرض تفاصيل المجموعة من نفس الشاشة</div>
                </div>
                <div class="oc-count-chip"><span></span>{{ number_format($stats['total']) }} مجموعة</div>
            </div>

            <div class="oc-table-content">
                {{ $this->content }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
