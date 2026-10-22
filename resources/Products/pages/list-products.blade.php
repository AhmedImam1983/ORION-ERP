<x-filament-panels::page>
    @php($stats = $this->getProductStats())

    <style>
        :root {
            --op-bg: #08111f;
            --op-panel: #0d1728;
            --op-panel-2: #111b2e;
            --op-border: #24344e;
            --op-border-soft: #1a2940;
            --op-text: #f8fafc;
            --op-muted: #8495ad;
            --op-purple: #8b5cf6;
            --op-purple-2: #a78bfa;
            --op-green: #22e56b;
            --op-red: #ff6673;
            --op-amber: #f6b93b;
            --op-cyan: #38bdf8;
        }

        .op-shell { direction: rtl; color: var(--op-text); }
        .op-shell * { box-sizing: border-box; }

        .op-hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }
        .op-eyebrow { color: #8a9ab2; font-size: 11px; font-weight: 800; margin-bottom: 6px; }
        .op-title { color: #fff; font-size: 31px; line-height: 1.2; font-weight: 950; margin: 0; }
        .op-subtitle { color: #73849c; font-size: 11px; margin-top: 6px; }
        .op-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .op-btn {
            height: 40px; display: inline-flex; align-items: center; justify-content: center;
            padding: 0 14px; border-radius: 10px; border: 1px solid #31425f;
            background: #101a2d; color: #e6ecf5; font-size: 11px; font-weight: 900;
            text-decoration: none; transition: .15s ease;
        }
        .op-btn:hover { border-color: var(--op-purple); color: #fff; transform: translateY(-1px); }
        .op-btn.primary {
            color: #fff; border-color: #8b5cf6;
            background: linear-gradient(135deg, #9b5cf6, #6d28d9);
            box-shadow: 0 9px 24px rgba(109,40,217,.22);
        }

        .op-kpis { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; margin-bottom: 14px; }
        .op-kpi {
            min-height: 105px; padding: 15px 17px; border-radius: 14px;
            border: 1px solid var(--op-border);
            background: linear-gradient(145deg, var(--op-panel-2), #0b1525);
            position: relative; overflow: hidden;
        }
        .op-kpi:after { content: ""; position: absolute; inset: auto 0 0 0; height: 2px; opacity: .72; background: linear-gradient(90deg,transparent,var(--op-purple),transparent); }
        .op-kpi.green:after { background: linear-gradient(90deg,transparent,var(--op-green),transparent); }
        .op-kpi.red:after { background: linear-gradient(90deg,transparent,var(--op-red),transparent); }
        .op-kpi.amber:after { background: linear-gradient(90deg,transparent,var(--op-amber),transparent); }
        .op-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .op-kpi-label { font-size: 11px; color: #b9c4d3; font-weight: 850; }
        .op-kpi-code { direction: ltr; border: 1px solid #2b3b55; color: #718198; border-radius: 999px; padding: 3px 7px; font-size: 8px; font-weight: 950; }
        .op-kpi-value { margin-top: 12px; font-size: 23px; font-weight: 950; color: #fff; font-variant-numeric: tabular-nums; }
        .op-kpi.green .op-kpi-value { color: var(--op-green); }
        .op-kpi.red .op-kpi-value { color: #ff8a94; }
        .op-kpi.amber .op-kpi-value { color: #ffd166; }
        .op-kpi-note { margin-top: 4px; color: #65758d; font-size: 9px; }

        .op-table-card {
            border: 1px solid var(--op-border);
            border-radius: 16px;
            background: linear-gradient(180deg,#0d1728,#0a1422);
            overflow: hidden;
            box-shadow: 0 16px 34px rgba(0,0,0,.14);
        }
        .op-table-head {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 16px; border-bottom: 1px solid var(--op-border-soft);
            background: #0f192b;
        }
        .op-table-title { font-size: 17px; font-weight: 950; color: #fff; }
        .op-table-sub { margin-top: 3px; font-size: 9px; color: #66778f; }
        .op-status {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px;
            border-radius: 999px; border: 1px solid rgba(34,229,107,.18);
            background: rgba(34,229,107,.05); color: #65efa0; font-size: 9px; font-weight: 900;
        }
        .op-status-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--op-green); }
        .op-table-body { padding: 0; }

        /* Safe visual layer over Filament table. No custom table methods required. */
        .op-table-card .fi-ta { background: transparent !important; box-shadow: none !important; border: 0 !important; border-radius: 0 !important; }
        .op-table-card .fi-ta-header { background: #0c1627 !important; border-bottom-color: #1d2b43 !important; }
        .op-table-card .fi-ta-content { border-top-color: #1d2b43 !important; }
        .op-table-card .fi-ta-row { border-bottom-color: #18243a !important; }
        .op-table-card .fi-ta-row:hover { background: rgba(139,92,246,.035) !important; }
        .op-table-card .fi-ta-header-cell, .op-table-card .fi-ta-header-cell-label { color: #7e8fa8 !important; font-size: 10px !important; font-weight: 900 !important; }
        .op-table-card .fi-ta-cell { color: #d9e2ee !important; }
        .op-table-card .fi-ta-text { font-size: 11px !important; }
        .op-table-card .fi-ta-actions { gap: 4px !important; }
        .op-table-card .fi-btn, .op-table-card .fi-link { border-radius: 8px !important; }
        .op-table-card input, .op-table-card select { border-radius: 9px !important; }

        .op-footnote { padding: 10px 2px 0; color: #5f7088; font-size: 9px; }

        @media(max-width: 1000px) {
            .op-kpis { grid-template-columns: repeat(2,1fr); }
            .op-hero { align-items: stretch; flex-direction: column; }
        }
        @media(max-width: 620px) {
            .op-kpis { grid-template-columns: 1fr; }
            .op-title { font-size: 26px; }
            .op-actions { width: 100%; }
            .op-btn { flex: 1; }
        }
    </style>

    <div class="op-shell">
        <div class="op-hero">
            <div>
                <div class="op-eyebrow">المخازن / إدارة الأصناف</div>
                <h1 class="op-title">الأصناف</h1>
                <div class="op-subtitle">إدارة بيانات الأصناف، المخزون، الأسعار، والمخزن الافتراضي من شاشة واحدة.</div>
            </div>

            <div class="op-actions">
                <a href="{{ $this->getCreateUrl() }}" class="op-btn primary">+ إضافة صنف جديد</a>
            </div>
        </div>

        <div class="op-kpis">
            <div class="op-kpi">
                <div class="op-kpi-top"><span class="op-kpi-label">إجمالي الأصناف</span><span class="op-kpi-code">TOTAL</span></div>
                <div class="op-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="op-kpi-note">كل الأصناف المسجلة في النظام</div>
            </div>

            <div class="op-kpi green">
                <div class="op-kpi-top"><span class="op-kpi-label">الأصناف النشطة</span><span class="op-kpi-code">ACTIVE</span></div>
                <div class="op-kpi-value">{{ number_format($stats['active']) }}</div>
                <div class="op-kpi-note">متاحة للاستخدام والحركة</div>
            </div>

            <div class="op-kpi amber">
                <div class="op-kpi-top"><span class="op-kpi-label">منخفض المخزون</span><span class="op-kpi-code">LOW</span></div>
                <div class="op-kpi-value">{{ number_format($stats['low_stock']) }}</div>
                <div class="op-kpi-note">الرصيد الحالي وصل للحد الأدنى أو أقل</div>
            </div>

            <div class="op-kpi red">
                <div class="op-kpi-top"><span class="op-kpi-label">بدون مخزن افتراضي</span><span class="op-kpi-code">WH</span></div>
                <div class="op-kpi-value">{{ number_format($stats['without_warehouse']) }}</div>
                <div class="op-kpi-note">أصناف تحتاج تحديد مخزن افتراضي</div>
            </div>
        </div>

        <div class="op-table-card">
            <div class="op-table-head">
                <div>
                    <div class="op-table-title">قائمة الأصناف</div>
                    <div class="op-table-sub">بحث، تصفية، عرض، تعديل، وطباعة باركود من نفس الجدول</div>
                </div>
                <div class="op-status"><span class="op-status-dot"></span> النظام جاهز</div>
            </div>

            <div class="op-table-body">
                {{ $this->table }}
            </div>
        </div>

        <div class="op-footnote">تم تغيير واجهة العرض فقط. منطق الجدول والفلاتر والإجراءات يظل من ProductsTable.php الحالي.</div>
    </div>
</x-filament-panels::page>
