<x-filament-panels::page>
@php
    /*
     * Reports Center V3
     * Design-only upgrade. Keeps the existing ReportsPage.php methods and reportData structure.
     */
    $kpis = [
        ['label' => 'إجمالي المبيعات', 'value' => '0 ر.س', 'tone' => 'green', 'code' => 'SALES'],
        ['label' => 'صافي الربح', 'value' => '0 ر.س', 'tone' => 'purple', 'code' => 'PROFIT'],
        ['label' => 'إجمالي المديونيات', 'value' => '0 ر.س', 'tone' => 'red', 'code' => 'DEBT'],
        ['label' => 'عدد الفواتير', 'value' => '0', 'tone' => 'blue', 'code' => 'COUNT'],
    ];

    $chartLabels = [];
    $chartValues = [];
    $previewTitle = $reportTitle ?: 'تقرير المبيعات';
    $previewSub = 'اختر تقريراً لعرض ملخصه وتحليله خلال الفترة المحددة';

    if ($activeReport === 'sales' && !empty($reportData)) {
        $kpis = [
            ['label' => 'إجمالي المبيعات', 'value' => number_format($reportData['total_sales'] ?? 0, 0) . ' ر.س', 'tone' => 'green', 'code' => 'SALES'],
            ['label' => 'المحصّل', 'value' => number_format($reportData['total_paid'] ?? 0, 0) . ' ر.س', 'tone' => 'purple', 'code' => 'PAID'],
            ['label' => 'المتبقي', 'value' => number_format($reportData['total_remaining'] ?? 0, 0) . ' ر.س', 'tone' => 'red', 'code' => 'DUE'],
            ['label' => 'عدد الفواتير', 'value' => number_format($reportData['count'] ?? 0), 'tone' => 'blue', 'code' => 'COUNT'],
        ];

        $groups = collect($reportData['invoices'] ?? [])
            ->groupBy(fn ($inv) => $inv->invoice_date?->format('d/m') ?? '-')
            ->map(fn ($rows) => (float) $rows->sum('total'))
            ->take(7);

        $chartLabels = $groups->keys()->values()->all();
        $chartValues = $groups->values()->all();
        $previewSub = 'حركة المبيعات والفواتير خلال الفترة المحددة';
    } elseif ($activeReport === 'profit' && !empty($reportData)) {
        $kpis = [
            ['label' => 'إجمالي الإيرادات', 'value' => number_format($reportData['total_revenue'] ?? 0, 0) . ' ر.س', 'tone' => 'green', 'code' => 'REVENUE'],
            ['label' => 'صافي الربح', 'value' => number_format($reportData['gross_profit'] ?? 0, 0) . ' ر.س', 'tone' => 'purple', 'code' => 'PROFIT'],
            ['label' => 'إجمالي التكلفة', 'value' => number_format($reportData['total_cost'] ?? 0, 0) . ' ر.س', 'tone' => 'red', 'code' => 'COST'],
            ['label' => 'هامش الربح', 'value' => number_format($reportData['profit_margin'] ?? 0, 1) . '%', 'tone' => 'blue', 'code' => 'MARGIN'],
        ];

        foreach (collect($reportData['by_product'] ?? [])->take(7) as $p) {
            $chartLabels[] = \Illuminate\Support\Str::limit($p['name'] ?? '-', 12);
            $chartValues[] = max(0, (float) ($p['revenue'] ?? 0));
        }
        $previewSub = 'ربحية الأصناف بناءً على الإيراد والتكلفة';
    } elseif ($activeReport === 'debts' && !empty($reportData)) {
        $kpis = [
            ['label' => 'إجمالي المديونيات', 'value' => number_format($reportData['total_debt'] ?? 0, 0) . ' ر.س', 'tone' => 'red', 'code' => 'DEBT'],
            ['label' => 'العملاء المدينون', 'value' => number_format($reportData['count'] ?? 0), 'tone' => 'purple', 'code' => 'CUSTOMERS'],
            ['label' => 'متوسط المديونية', 'value' => number_format(($reportData['count'] ?? 0) > 0 ? ($reportData['total_debt'] / $reportData['count']) : 0, 0) . ' ر.س', 'tone' => 'amber', 'code' => 'AVG'],
            ['label' => 'حالة التحصيل', 'value' => ($reportData['total_debt'] ?? 0) > 0 ? 'تحتاج متابعة' : 'مستقرة', 'tone' => ($reportData['total_debt'] ?? 0) > 0 ? 'red' : 'green', 'code' => 'STATUS'],
        ];

        foreach (collect($reportData['customers'] ?? [])->take(7) as $c) {
            $chartLabels[] = \Illuminate\Support\Str::limit($c->name ?? '-', 12);
            $chartValues[] = max(0, (float) ($c->total_debt ?? 0));
        }
        $previewSub = 'تركيز المديونيات حسب العملاء';
    } elseif ($activeReport === 'stock' && !empty($reportData)) {
        $good = max(0, (int) ($reportData['count'] ?? 0) - (int) ($reportData['low'] ?? 0));
        $kpis = [
            ['label' => 'إجمالي الأصناف', 'value' => number_format($reportData['count'] ?? 0), 'tone' => 'blue', 'code' => 'ITEMS'],
            ['label' => 'منخفض المخزون', 'value' => number_format($reportData['low'] ?? 0), 'tone' => 'red', 'code' => 'LOW'],
            ['label' => 'مخزون كافٍ', 'value' => number_format($good), 'tone' => 'green', 'code' => 'HEALTHY'],
            ['label' => 'نسبة النقص', 'value' => ($reportData['count'] ?? 0) > 0 ? number_format((($reportData['low'] ?? 0) / $reportData['count']) * 100, 1) . '%' : '0%', 'tone' => 'amber', 'code' => 'RISK'],
        ];

        foreach (collect($reportData['products'] ?? [])->sortByDesc(fn ($p) => (float) ($p->quantity ?? 0))->take(7) as $p) {
            $chartLabels[] = \Illuminate\Support\Str::limit($p->name ?? '-', 12);
            $chartValues[] = max(0, (float) ($p->quantity ?? 0));
        }
        $previewSub = 'مقارنة أرصدة أهم الأصناف خلال الفترة';
    } elseif ($activeReport === 'top_products' && !empty($reportData)) {
        $kpis = [
            ['label' => 'إيراد الأصناف', 'value' => number_format($reportData['total_revenue'] ?? 0, 0) . ' ر.س', 'tone' => 'green', 'code' => 'REVENUE'],
            ['label' => 'عدد الأصناف', 'value' => number_format(count($reportData['products'] ?? [])), 'tone' => 'purple', 'code' => 'ITEMS'],
            ['label' => 'الأعلى مبيعاً', 'value' => \Illuminate\Support\Str::limit(($reportData['products'][0]['name'] ?? '-'), 18), 'tone' => 'amber', 'code' => 'TOP'],
            ['label' => 'كمية الأعلى', 'value' => number_format($reportData['products'][0]['qty'] ?? 0, 0), 'tone' => 'blue', 'code' => 'QTY'],
        ];

        foreach (collect($reportData['products'] ?? [])->take(7) as $p) {
            $chartLabels[] = \Illuminate\Support\Str::limit($p['name'] ?? '-', 12);
            $chartValues[] = max(0, (float) ($p['total'] ?? 0));
        }
        $previewSub = 'ترتيب الأصناف الأعلى مبيعاً حسب قيمة المبيعات';
    } elseif ($activeReport === 'attendance' && !empty($reportData)) {
        $employees = collect($reportData['employees'] ?? []);
        $kpis = [
            ['label' => 'عدد الموظفين', 'value' => number_format($reportData['total'] ?? 0), 'tone' => 'blue', 'code' => 'STAFF'],
            ['label' => 'إجمالي الحضور', 'value' => number_format($employees->sum('present')), 'tone' => 'green', 'code' => 'PRESENT'],
            ['label' => 'إجمالي الغياب', 'value' => number_format($employees->sum('absent')), 'tone' => 'red', 'code' => 'ABSENT'],
            ['label' => 'مرات التأخير', 'value' => number_format($employees->sum('late')), 'tone' => 'amber', 'code' => 'LATE'],
        ];

        foreach ($employees->sortByDesc('total_hours')->take(7) as $e) {
            $chartLabels[] = \Illuminate\Support\Str::limit($e['name'] ?? '-', 12);
            $chartValues[] = max(0, (float) ($e['total_hours'] ?? 0));
        }
        $previewSub = 'ساعات العمل والحضور للموظفين خلال الفترة';
    }

    $svgPoints = '';
    $svgArea = '';
    if (count($chartValues) > 0) {
        $maxValue = max(max($chartValues), 1);
        $count = count($chartValues);

        $pts = [];
        foreach ($chartValues as $i => $value) {
            $x = $count <= 1 ? 50 : ($i / ($count - 1)) * 100;
            $y = 84 - (($value / $maxValue) * 60);
            $pts[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
        }

        $svgPoints = implode(' ', $pts);
        $svgArea = '0,88 ' . $svgPoints . ' 100,88';
    }

    $reportCards = [
        [
            'title' => 'تقرير المبيعات',
            'sub' => 'إجمالي المبيعات والفواتير والتحصيل',
            'kind' => 'livewire',
            'action' => 'loadSalesReport',
            'active' => 'sales',
            'tone' => 'purple',
            'icon' => 'cart',
        ],
        [
            'title' => 'الأرباح والخسائر',
            'sub' => 'القائمة المالية من الأستاذ العام',
            'kind' => 'link',
            'url' => url('/admin/profit-loss'),
            'tone' => 'violet',
            'icon' => 'chart',
        ],
        [
            'title' => 'التدفقات النقدية',
            'sub' => 'التشغيل والاستثمار والتمويل',
            'kind' => 'link',
            'url' => url('/admin/cash-flow'),
            'tone' => 'green',
            'icon' => 'cash',
        ],
        [
            'title' => 'تقرير المخزون',
            'sub' => 'الأرصدة والحالة وحدود إعادة الطلب',
            'kind' => 'livewire',
            'action' => 'loadStockReport',
            'active' => 'stock',
            'tone' => 'purple',
            'icon' => 'cube',
        ],
        [
            'title' => 'الميزانية العمومية',
            'sub' => 'الأصول والخصوم وحقوق الملكية',
            'kind' => 'link',
            'url' => url('/admin/balance-sheet'),
            'tone' => 'slate',
            'icon' => 'building',
        ],
        [
            'title' => 'ميزان المراجعة',
            'sub' => 'أرصدة الحسابات وحركة الأستاذ',
            'kind' => 'link',
            'url' => url('/admin/trial-balance'),
            'tone' => 'red',
            'icon' => 'scale',
        ],
        [
            'title' => 'حساب المورد',
            'sub' => 'كشف المورد والحركات والمدفوعات',
            'kind' => 'link',
            'url' => url('/admin/supplier-statement'),
            'tone' => 'green',
            'icon' => 'users',
        ],
        [
            'title' => 'حساب العميل',
            'sub' => 'كشف العميل والمبيعات والتحصيلات',
            'kind' => 'link',
            'url' => url('/admin/customer-statement'),
            'tone' => 'amber',
            'icon' => 'user',
        ],
    ];
@endphp

<style>
    .fi-header { display:none !important; }

    .orion-reports-v3,
    .orion-reports-v3 * {
        box-sizing:border-box;
        font-family:'Cairo',sans-serif;
    }

    .orion-reports-v3 {
        --rp-bg:#08111f;
        --rp-panel:#0d1728;
        --rp-panel-2:#101b2f;
        --rp-panel-3:#121f35;
        --rp-border:#22324d;
        --rp-border-soft:#19283f;
        --rp-text:#f4f7fb;
        --rp-muted:#7f91ab;
        --rp-muted-2:#5e718d;
        --rp-purple:#8b5cf6;
        --rp-purple-2:#a78bfa;
        --rp-green:#34d399;
        --rp-red:#fb7185;
        --rp-amber:#fbbf24;
        --rp-blue:#60a5fa;

        direction:rtl;
        color:var(--rp-text);
        width:100%;
        padding-bottom:24px;
    }

    .rp-head {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:20px;
        margin-bottom:18px;
    }

    .rp-title-wrap {
        display:flex;
        align-items:center;
        gap:13px;
    }

    .rp-title-icon {
        width:48px;
        height:48px;
        border-radius:13px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:linear-gradient(145deg,rgba(124,58,237,.34),rgba(76,29,149,.18));
        border:1px solid rgba(139,92,246,.48);
        box-shadow:0 12px 30px rgba(76,29,149,.18);
        color:#b99cff;
    }

    .rp-title-icon svg { width:24px;height:24px; }

    .rp-kicker {
        font-size:10px;
        color:#7f6de1;
        font-weight:900;
        letter-spacing:1.8px;
        margin-bottom:4px;
    }

    .rp-title {
        margin:0;
        color:var(--rp-text);
        font-size:29px;
        font-weight:950;
        line-height:1.15;
    }

    .rp-subtitle {
        margin-top:5px;
        color:var(--rp-muted);
        font-size:11px;
        font-weight:600;
    }

    .rp-filterbar {
        background:linear-gradient(180deg,rgba(15,27,47,.96),rgba(11,21,37,.96));
        border:1px solid var(--rp-border);
        border-radius:15px;
        padding:12px;
        display:grid;
        grid-template-columns:minmax(290px,1.1fr) minmax(170px,.5fr) auto;
        gap:12px;
        align-items:end;
        margin-bottom:14px;
        box-shadow:0 8px 30px rgba(0,0,0,.12);
    }

    .rp-filter-group {
        display:grid;
        grid-template-columns:1fr 22px 1fr;
        gap:8px;
        align-items:end;
    }

    .rp-field label {
        display:block;
        color:var(--rp-muted);
        font-size:9px;
        font-weight:800;
        margin:0 3px 6px;
    }

    .rp-input,
    .rp-static-field {
        width:100%;
        height:40px;
        border:1px solid #2a3c59;
        border-radius:10px;
        background:#101c30;
        color:#dbe5f4;
        padding:0 11px;
        outline:none;
        font-size:11px;
        font-weight:700;
    }

    .rp-input:focus { border-color:#755bd9;box-shadow:0 0 0 2px rgba(139,92,246,.08); }
    .rp-input::-webkit-calendar-picker-indicator { filter:invert(1);opacity:.55; }

    .rp-sep {
        align-self:center;
        text-align:center;
        color:#546985;
        padding-top:18px;
    }

    .rp-static-field {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
    }

    .rp-static-field span { color:#9fb0c6; }
    .rp-static-field strong { color:#f0f4fa;font-weight:900; }

    .rp-actions {
        display:flex;
        align-items:center;
        gap:8px;
        direction:ltr;
    }

    .rp-btn {
        height:40px;
        padding:0 14px;
        border-radius:9px;
        border:1px solid transparent;
        background:#111d31;
        color:#dfe7f3;
        cursor:pointer;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        font-size:10px;
        font-weight:900;
        transition:.18s ease;
        white-space:nowrap;
    }

    .rp-btn:hover { transform:translateY(-1px); }
    .rp-btn svg { width:15px;height:15px; }

    .rp-btn.primary {
        color:white;
        background:linear-gradient(135deg,#8b5cf6,#6d3fe7);
        border-color:#986df8;
        box-shadow:0 8px 22px rgba(124,58,237,.2);
    }

    .rp-btn.excel {
        color:#63e6ae;
        background:rgba(16,185,129,.09);
        border-color:rgba(52,211,153,.3);
    }

    .rp-btn.pdf {
        color:#bda7ff;
        background:rgba(124,58,237,.09);
        border-color:rgba(139,92,246,.31);
    }

    .rp-kpis {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:10px;
        margin-bottom:14px;
    }

    .rp-kpi {
        position:relative;
        min-height:103px;
        overflow:hidden;
        background:
            radial-gradient(circle at 8% 10%,rgba(139,92,246,.11),transparent 35%),
            linear-gradient(145deg,#101a2d,#0d1728);
        border:1px solid #273754;
        border-radius:14px;
        padding:14px 15px;
    }

    .rp-kpi::before {
        content:'';
        position:absolute;
        inset:auto 0 0 0;
        height:2px;
        opacity:.68;
    }

    .rp-kpi.green::before { background:linear-gradient(90deg,transparent,var(--rp-green),transparent); }
    .rp-kpi.red::before { background:linear-gradient(90deg,transparent,var(--rp-red),transparent); }
    .rp-kpi.purple::before { background:linear-gradient(90deg,transparent,var(--rp-purple),transparent); }
    .rp-kpi.blue::before { background:linear-gradient(90deg,transparent,var(--rp-blue),transparent); }
    .rp-kpi.amber::before { background:linear-gradient(90deg,transparent,var(--rp-amber),transparent); }

    .rp-kpi-top {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
    }

    .rp-kpi-label {
        color:#a9b6c9;
        font-size:10px;
        font-weight:800;
    }

    .rp-kpi-code {
        font-size:7px;
        letter-spacing:1.2px;
        color:#725ecf;
        font-weight:950;
    }

    .rp-kpi-value {
        margin-top:10px;
        direction:rtl;
        font-size:21px;
        font-weight:950;
        color:#f7f9fc;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .rp-kpi.green .rp-kpi-value { color:#50e3b0; }
    .rp-kpi.red .rp-kpi-value { color:#ff7e90; }
    .rp-kpi.purple .rp-kpi-value { color:#b393ff; }
    .rp-kpi.blue .rp-kpi-value { color:#7eb5ff; }
    .rp-kpi.amber .rp-kpi-value { color:#f9cd67; }

    .rp-kpi-note {
        margin-top:5px;
        color:#62758f;
        font-size:8px;
        font-weight:700;
    }

    .rp-main-grid {
        display:grid;
        grid-template-columns:minmax(0,1.28fr) minmax(360px,.95fr);
        gap:12px;
        align-items:start;
    }

    .rp-card {
        background:linear-gradient(180deg,rgba(14,25,43,.98),rgba(10,20,35,.98));
        border:1px solid var(--rp-border);
        border-radius:15px;
        overflow:hidden;
        box-shadow:0 12px 35px rgba(0,0,0,.12);
    }

    .rp-card-head {
        min-height:54px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding:12px 14px;
        border-bottom:1px solid var(--rp-border-soft);
        background:rgba(11,21,36,.55);
    }

    .rp-card-title {
        display:flex;
        align-items:center;
        gap:9px;
        font-size:12px;
        font-weight:950;
        color:#eef3fa;
    }

    .rp-card-title svg {
        width:16px;
        height:16px;
        color:#8f6df3;
    }

    .rp-card-sub {
        color:#62758f;
        font-size:8px;
        font-weight:700;
        margin-top:3px;
    }

    .rp-preview-body { padding:14px; }

    .rp-chart {
        position:relative;
        height:250px;
        border:1px solid #1b2a41;
        border-radius:12px;
        background:
            linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px) 0 0/100% 25%,
            linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px) 0 0/16.666% 100%,
            linear-gradient(180deg,#0c1728,#0a1423);
        overflow:hidden;
    }

    .rp-chart-badge {
        position:absolute;
        top:10px;
        left:10px;
        z-index:3;
        padding:5px 8px;
        border-radius:7px;
        color:#9b87f2;
        border:1px solid #2e3d5a;
        background:#101c30;
        font-size:7px;
        font-weight:950;
        letter-spacing:.8px;
    }

    .rp-svg {
        position:absolute;
        inset:25px 18px 34px;
        width:calc(100% - 36px);
        height:calc(100% - 59px);
        overflow:visible;
    }

    .rp-svg .area { fill:url(#rpAreaGradient); }
    .rp-svg .line {
        fill:none;
        stroke:#8b5cf6;
        stroke-width:1.7;
        vector-effect:non-scaling-stroke;
        filter:drop-shadow(0 0 4px rgba(139,92,246,.3));
    }

    .rp-chart-labels {
        position:absolute;
        bottom:9px;
        right:20px;
        left:20px;
        display:flex;
        justify-content:space-between;
        gap:6px;
        direction:ltr;
    }

    .rp-chart-labels span {
        max-width:90px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        color:#61738d;
        font-size:7px;
        font-weight:800;
    }

    .rp-chart-empty {
        height:100%;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:8px;
        color:#536682;
        text-align:center;
    }

    .rp-chart-empty svg { width:32px;height:32px;color:#6e56c8;opacity:.7; }
    .rp-chart-empty strong { font-size:11px;color:#8c9ab0; }
    .rp-chart-empty span { font-size:8px; }

    .rp-table-wrap {
        margin-top:12px;
        border:1px solid #1d2b43;
        border-radius:11px;
        overflow:auto;
        max-height:335px;
    }

    .rp-table {
        width:100%;
        border-collapse:collapse;
        min-width:690px;
        font-size:9px;
    }

    .rp-table thead th {
        position:sticky;
        top:0;
        z-index:2;
        background:#101b2e;
        color:#71849e;
        padding:9px 10px;
        text-align:right;
        border-bottom:1px solid #243550;
        font-size:8px;
        font-weight:900;
    }

    .rp-table tbody td {
        padding:10px;
        border-bottom:1px solid #17243a;
        color:#cdd7e6;
        vertical-align:middle;
    }

    .rp-table tbody tr:hover td { background:rgba(139,92,246,.025); }
    .rp-table tbody tr:last-child td { border-bottom:0; }
    .rp-num { direction:ltr;text-align:right;font-weight:900;white-space:nowrap; }
    .rp-green { color:#46dfa7 !important; }
    .rp-red { color:#ff7688 !important; }
    .rp-purple { color:#a98aff !important; }
    .rp-amber { color:#f6c85f !important; }
    .rp-muted { color:#6e8099 !important; }

    .rp-badge {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:54px;
        padding:4px 7px;
        border-radius:999px;
        font-size:7px;
        font-weight:950;
        border:1px solid transparent;
    }

    .rp-badge.green { color:#63e6ae;background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.18); }
    .rp-badge.red { color:#ff8595;background:rgba(251,113,133,.08);border-color:rgba(251,113,133,.18); }
    .rp-badge.blue { color:#7eb5ff;background:rgba(96,165,250,.08);border-color:rgba(96,165,250,.18); }
    .rp-badge.amber { color:#f6cd72;background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.18); }
    .rp-badge.gray { color:#9aa9bd;background:rgba(148,163,184,.07);border-color:rgba(148,163,184,.15); }

    .rp-progress {
        width:72px;
        height:4px;
        border-radius:999px;
        background:#17243a;
        overflow:hidden;
        margin-top:4px;
    }

    .rp-progress > span {
        display:block;
        height:100%;
        border-radius:999px;
        background:linear-gradient(90deg,#7c5ce7,#a88cff);
    }

    .rp-report-list {
        padding:12px;
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:8px;
    }

    .rp-report-item {
        min-height:90px;
        border:1px solid #22324d;
        border-radius:12px;
        background:linear-gradient(145deg,#111c30,#0d1728);
        color:inherit;
        text-decoration:none;
        padding:12px;
        display:flex;
        align-items:center;
        gap:11px;
        cursor:pointer;
        transition:.18s ease;
        text-align:right;
    }

    button.rp-report-item { width:100%;font-family:'Cairo',sans-serif; }

    .rp-report-item:hover {
        transform:translateY(-1px);
        border-color:#3b4d70;
        background:#111e34;
    }

    .rp-report-item.active {
        border-color:#7956e4;
        background:
            radial-gradient(circle at 80% 20%,rgba(139,92,246,.16),transparent 44%),
            linear-gradient(145deg,#171c3d,#10172c);
        box-shadow:inset 0 0 0 1px rgba(139,92,246,.08),0 8px 22px rgba(76,29,149,.12);
    }

    .rp-report-icon {
        width:45px;
        height:45px;
        border-radius:11px;
        display:flex;
        align-items:center;
        justify-content:center;
        flex:0 0 45px;
        border:1px solid #2b3c5a;
        background:#15213a;
        color:#8b6bef;
    }

    .rp-report-icon svg { width:22px;height:22px; }
    .rp-report-icon.green { color:#4fe0a8;background:rgba(16,185,129,.09);border-color:rgba(52,211,153,.16); }
    .rp-report-icon.red { color:#ff778a;background:rgba(244,63,94,.09);border-color:rgba(251,113,133,.16); }
    .rp-report-icon.amber { color:#f5c65c;background:rgba(245,158,11,.09);border-color:rgba(251,191,36,.16); }
    .rp-report-icon.slate { color:#9aa9bd;background:rgba(100,116,139,.08); }
    .rp-report-icon.violet { color:#b28cff;background:rgba(124,58,237,.09); }

    .rp-report-copy { min-width:0;flex:1; }
    .rp-report-name { color:#eef2f8;font-size:10px;font-weight:950; }
    .rp-report-desc {
        margin-top:4px;
        color:#697c96;
        font-size:7.5px;
        line-height:1.7;
    }

    .rp-report-arrow {
        width:22px;
        color:#526781;
        display:flex;
        justify-content:flex-end;
    }

    .rp-report-arrow svg { width:14px;height:14px; }

    .rp-operational {
        border-top:1px solid #1b2a40;
        padding:10px 12px 12px;
    }

    .rp-operational-title {
        color:#627590;
        font-size:8px;
        font-weight:900;
        margin-bottom:8px;
    }

    .rp-operational-row {
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:7px;
    }

    .rp-mini-report {
        min-height:48px;
        border-radius:9px;
        border:1px solid #21324c;
        background:#101b2e;
        color:#9aaac0;
        cursor:pointer;
        padding:8px 9px;
        font-family:'Cairo',sans-serif;
        font-size:8px;
        font-weight:850;
        transition:.16s ease;
    }

    .rp-mini-report:hover,
    .rp-mini-report.active {
        color:#b99fff;
        border-color:#554386;
        background:#141c34;
    }

    .rp-empty-table {
        padding:40px 20px;
        text-align:center;
        color:#536680;
        font-size:9px;
    }

    @media (max-width:1200px) {
        .rp-filterbar { grid-template-columns:1fr 1fr; }
        .rp-actions { grid-column:1/-1;justify-content:flex-start; }
        .rp-main-grid { grid-template-columns:1fr; }
    }

    @media (max-width:850px) {
        .rp-kpis { grid-template-columns:repeat(2,1fr); }
        .rp-report-list { grid-template-columns:1fr; }
        .rp-filterbar { grid-template-columns:1fr; }
        .rp-actions { grid-column:auto; }
    }

    @media (max-width:560px) {
        .rp-head { flex-direction:column; }
        .rp-title { font-size:24px; }
        .rp-filter-group { grid-template-columns:1fr; }
        .rp-sep { display:none; }
        .rp-kpis { grid-template-columns:1fr; }
        .rp-actions { width:100%;display:grid;grid-template-columns:repeat(3,1fr); }
        .rp-btn { padding:0 8px; }
        .rp-operational-row { grid-template-columns:1fr; }
    }

   @page {
    size: A4 landscape;
    margin: 10mm;
}

@media print {
    html,
    body,
    .fi-body,
    .fi-main,
    .fi-page,
    .fi-page-content,
    .orion-reports-v3 {
        background: #fff !important;
        background-image: none !important;
        color: #111 !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
    }

    .fi-sidebar,
    .fi-topbar,
    .fi-header,
    .rp-head,
    .rp-filterbar,
    .rp-kpis,
    .rp-reports-card {
        display: none !important;
    }

    .rp-main-grid {
        display: block !important;
        width: 100% !important;
        min-width: 0 !important;
        overflow: visible !important;
    }

    .rp-preview-card,
    .rp-preview-card * {
        color: #111 !important;
        background: #fff !important;
        background-image: none !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }

    .rp-preview-card {
        display: block !important;
        width: 100% !important;
        min-width: 0 !important;
        border: 0 !important;
        overflow: visible !important;
        break-inside: auto !important;
    }

    .rp-preview-card .rp-card-head {
        display: flex !important;
        border-bottom: 1px solid #bbb !important;
    }

    .rp-preview-body {
        display: block !important;
        padding: 0 !important;
        overflow: visible !important;
    }

    .rp-chart {
        display: none !important;
    }

    .rp-table-wrap {
        width: 100% !important;
        max-height: none !important;
        height: auto !important;
        overflow: visible !important;
        border: 0 !important;
    }

    .rp-table {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: auto !important;
        border-collapse: collapse !important;
        color: #111 !important;
        font-size: 10px !important;
    }

    .rp-table thead {
        display: table-header-group !important;
    }

    .rp-table thead th {
        position: static !important;
        background: #eee !important;
        color: #111 !important;
        border: 1px solid #bbb !important;
    }

    .rp-table tbody tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .rp-table tbody td {
        color: #111 !important;
        border: 1px solid #ddd !important;
    }

    .rp-purple,
    .rp-green,
    .rp-red,
    .rp-amber,
    .rp-muted,
    .rp-badge {
        color: #111 !important;
    }
    /* Improve text rendering in Chrome print preview */
    .orion-reports-v3,
    .orion-reports-v3 .rp-preview-card,
    .orion-reports-v3 .rp-preview-card * {
        font-family: Arial, sans-serif !important;
        color: #111 !important;
        -webkit-text-fill-color: #111 !important;
        text-shadow: none !important;
        line-height: 1.5 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .orion-reports-v3 .rp-table th,
    .orion-reports-v3 .rp-table td {
        font-size: 9pt !important;
        line-height: 1.5 !important;
        padding: 6px !important;
        vertical-align: middle !important;
    }

    .orion-reports-v3 .rp-table tbody td {
        border-color: #ddd !important;
    }
    /* Reset Filament layout containers for browser printing */
    .fi-layout,
    .fi-main-ctn,
    .fi-main,
    .fi-page,
    .fi-page-content,
    .fi-page-content-ctn,
    .fi-body {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: none !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
        transform: none !important;
        contain: none !important;
    }

    .fi-sidebar,
    .fi-topbar {
        display: none !important;
    }

    .orion-reports-v3,
    .rp-main-grid,
    .rp-preview-card,
    .rp-preview-body,
    .rp-table-wrap {
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
        transform: none !important;
        contain: none !important;
    }
}
</style>

<div class="orion-reports-v3" wire:init="loadSalesReport">
    <div class="rp-head">
        <div class="rp-title-wrap">
            <div class="rp-title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14"/>
                    <path d="M8 15v-4m4 4V7m4 8v-2"/>
                    <path d="M3 19h18"/>
                </svg>
            </div>
            <div>
                <div class="rp-kicker">REPORTING · MANAGEMENT INSIGHT</div>
                <h1 class="rp-title">مركز التقارير</h1>
                <div class="rp-subtitle">استعرض الأداء من مكان واحد، ثم افتح التقرير التفصيلي عند الحاجة</div>
            </div>
        </div>
    </div>

    <div class="rp-filterbar">
        <div class="rp-filter-group">
            <div class="rp-field">
                <label>من تاريخ</label>
                <input type="date" wire:model="dateFrom" class="rp-input">
            </div>

            <div class="rp-sep">ـ</div>

            <div class="rp-field">
                <label>إلى تاريخ</label>
                <input type="date" wire:model="dateTo" class="rp-input">
            </div>
        </div>

        <div class="rp-field">
            <label>التقرير الحالي</label>
            <div class="rp-static-field">
                <strong>{{ $reportTitle ?: 'تقرير المبيعات' }}</strong>
                <span>تلقائي</span>
            </div>
        </div>

        <div class="rp-actions">
            <button type="button" class="rp-btn primary"
                    @if($activeReport === 'sales') wire:click="loadSalesReport"
                    @elseif($activeReport === 'profit') wire:click="loadProfitReport"
                    @elseif($activeReport === 'debts') wire:click="loadDebtsReport"
                    @elseif($activeReport === 'stock') wire:click="loadStockReport"
                    @elseif($activeReport === 'top_products') wire:click="loadTopProductsReport"
                    @elseif($activeReport === 'attendance') wire:click="loadAttendanceReport"
                    @else wire:click="loadSalesReport" @endif>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="2.5"/>
                </svg>
                عرض
            </button>

            <button type="button" wire:click="exportExcel" class="rp-btn excel" @disabled(!$activeReport)>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                    <path d="M14 2v6h6M8 13l5 5m0-5-5 5"/>
                </svg>
                Excel
            </button>

            <button type="button" onclick="printReport()" class="rp-btn pdf" @disabled(!$activeReport)>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 9V2h9l3 3v4"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <path d="M6 14h12v8H6z"/>
                </svg>
                PDF
            </button>
        </div>
    </div>

    <div class="rp-kpis">
        @foreach($kpis as $item)
            <div class="rp-kpi {{ $item['tone'] }}">
                <div class="rp-kpi-top">
                    <div class="rp-kpi-label">{{ $item['label'] }}</div>
                    <div class="rp-kpi-code">{{ $item['code'] }}</div>
                </div>
                <div class="rp-kpi-value">{{ $item['value'] }}</div>
                <div class="rp-kpi-note">خلال الفترة المحددة في أعلى الصفحة</div>
            </div>
        @endforeach
    </div>

    <div class="rp-main-grid">
        <section class="rp-card rp-preview-card">
            <div class="rp-card-head">
                <div>
                    <div class="rp-card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 19V9m5 10V5m5 14v-7m5 7V3"/>
                        </svg>
                        <span>معاينة التقرير · {{ $previewTitle }}</span>
                    </div>
                    <div class="rp-card-sub">{{ $previewSub }} · {{ $dateFrom }} إلى {{ $dateTo }}</div>
                </div>
                <div class="rp-kpi-code">LIVE PREVIEW</div>
            </div>

            <div class="rp-preview-body" id="rep-content">
                <div class="rp-chart">
                    <div class="rp-chart-badge">TREND</div>

                    @if(count($chartValues) > 0)
                        <svg class="rp-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="rpAreaGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#8b5cf6" stop-opacity=".26"/>
                                    <stop offset="100%" stop-color="#8b5cf6" stop-opacity=".01"/>
                                </linearGradient>
                            </defs>
                            <polygon class="area" points="{{ $svgArea }}"></polygon>
                            <polyline class="line" points="{{ $svgPoints }}"></polyline>
                        </svg>

                        <div class="rp-chart-labels">
                            @foreach($chartLabels as $label)
                                <span>{{ $label }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="rp-chart-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M4 19V5m0 14h16"/>
                                <path d="m7 15 3-4 3 2 4-6"/>
                            </svg>
                            <strong>لا توجد بيانات للمعاينة</strong>
                            <span>اختر تقريراً أو غيّر الفترة</span>
                        </div>
                    @endif
                </div>

                @if(!$activeReport)
                    <div class="rp-empty-table">يتم تحميل تقرير المبيعات تلقائياً...</div>

                @elseif($activeReport === 'sales')
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>العميل</th>
                                    <th>التاريخ</th>
                                    <th>طريقة الدفع</th>
                                    <th>الإجمالي</th>
                                    <th>المدفوع</th>
                                    <th>المتبقي</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['invoices'] as $inv)
                                    @php
                                        $statusMap = [
                                            'paid' => ['مدفوعة','green'],
                                            'confirmed' => ['مؤكدة','blue'],
                                            'partial' => ['جزئي','amber'],
                                            'draft' => ['مسودة','gray'],
                                            'cancelled' => ['ملغاة','red'],
                                        ];
                                        $s = $statusMap[$inv->status] ?? [$inv->status,'gray'];
                                        $payMap = ['cash'=>'نقدي','credit'=>'آجل','installment'=>'تقسيط','card'=>'بطاقة','wallet'=>'محفظة'];
                                    @endphp
                                    <tr>
                                        <td class="rp-purple rp-num">{{ $inv->invoice_number }}</td>
                                        <td>{{ $inv->customer?->name ?? 'عميل نقدي' }}</td>
                                        <td class="rp-muted">{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                                        <td>{{ $payMap[$inv->payment_type] ?? $inv->payment_type }}</td>
                                        <td class="rp-num">{{ number_format($inv->total,2) }}</td>
                                        <td class="rp-num rp-green">{{ number_format($inv->paid_amount,2) }}</td>
                                        <td class="rp-num {{ $inv->remaining_amount > 0 ? 'rp-red':'rp-green' }}">{{ number_format($inv->remaining_amount,2) }}</td>
                                        <td><span class="rp-badge {{ $s[1] }}">{{ $s[0] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rp-empty-table">لا توجد فواتير في الفترة المحددة</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($activeReport === 'profit')
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead><tr><th>الصنف</th><th>الكمية</th><th>الإيرادات</th><th>التكلفة</th><th>الربح</th><th>هامش الربح</th></tr></thead>
                            <tbody>
                                @forelse($reportData['by_product'] as $p)
                                    <tr>
                                        <td>{{ $p['name'] }}</td>
                                        <td class="rp-num">{{ number_format($p['qty'],2) }}</td>
                                        <td class="rp-num rp-green">{{ number_format($p['revenue'],2) }}</td>
                                        <td class="rp-num rp-red">{{ number_format($p['cost'],2) }}</td>
                                        <td class="rp-num {{ $p['profit'] >= 0 ? 'rp-green':'rp-red' }}">{{ number_format($p['profit'],2) }}</td>
                                        <td>
                                            <span class="rp-purple">{{ number_format($p['margin'],1) }}%</span>
                                            <div class="rp-progress"><span style="width:{{ max(0,min((float)$p['margin'],100)) }}%"></span></div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="rp-empty-table">لا توجد بيانات ربحية للفترة المحددة</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($activeReport === 'debts')
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead><tr><th>كود العميل</th><th>اسم العميل</th><th>التليفون</th><th>النوع</th><th>إجمالي الدين</th></tr></thead>
                            <tbody>
                                @forelse($reportData['customers'] as $c)
                                    <tr>
                                        <td class="rp-num rp-purple">{{ $c->code }}</td>
                                        <td>{{ $c->name }}</td>
                                        <td class="rp-muted">{{ $c->phone ?? '-' }}</td>
                                        <td><span class="rp-badge blue">{{ $c->type === 'company' ? 'شركة':'فرد' }}</span></td>
                                        <td class="rp-num rp-red">{{ number_format($c->total_debt,2) }} ر.س</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="rp-empty-table">لا توجد مديونيات حالية</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($activeReport === 'stock')
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead><tr><th>الكود</th><th>الصنف</th><th>المجموعة</th><th>الكمية</th><th>الحد الأدنى</th><th>سعر البيع</th><th>الحالة</th></tr></thead>
                            <tbody>
                                @forelse($reportData['products'] as $p)
                                    @php $isLow = ($p->quantity ?? 0) <= $p->min_quantity; @endphp
                                    <tr>
                                        <td class="rp-num rp-purple">{{ $p->code }}</td>
                                        <td>{{ $p->name }}</td>
                                        <td><span class="rp-badge gray">{{ $p->category?->name ?? '-' }}</span></td>
                                        <td class="rp-num {{ $isLow ? 'rp-red':'rp-green' }}">{{ number_format($p->quantity ?? 0,2) }}</td>
                                        <td class="rp-num rp-muted">{{ number_format($p->min_quantity,2) }}</td>
                                        <td class="rp-num rp-green">{{ number_format($p->sale_price,2) }}</td>
                                        <td><span class="rp-badge {{ $isLow ? 'red':'green' }}">{{ $isLow ? 'منخفض':'جيد' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="rp-empty-table">لا توجد أصناف</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($activeReport === 'top_products')
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead><tr><th>#</th><th>الصنف</th><th>الكود</th><th>الكمية المباعة</th><th>الإيرادات</th></tr></thead>
                            <tbody>
                                @forelse($reportData['products'] as $i => $p)
                                    <tr>
                                        <td class="{{ $i < 3 ? 'rp-amber':'rp-muted' }}">{{ $i + 1 }}</td>
                                        <td>{{ $p['name'] }}</td>
                                        <td class="rp-num rp-muted">{{ $p['code'] ?? '-' }}</td>
                                        <td class="rp-num">{{ number_format($p['qty'],2) }}</td>
                                        <td class="rp-num rp-green">{{ number_format($p['total'],2) }} ر.س</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="rp-empty-table">لا توجد مبيعات أصناف للفترة المحددة</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @elseif($activeReport === 'attendance')
                    <div class="rp-table-wrap">
                        <table class="rp-table">
                            <thead><tr><th>الموظف</th><th>القسم</th><th>حاضر</th><th>غائب</th><th>متأخر</th><th>إجازة</th><th>ساعات العمل</th><th>دقائق التأخير</th></tr></thead>
                            <tbody>
                                @forelse($reportData['employees'] as $e)
                                    <tr>
                                        <td>{{ $e['name'] }}</td>
                                        <td class="rp-muted">{{ $e['department'] }}</td>
                                        <td class="rp-num rp-green">{{ $e['present'] }}</td>
                                        <td class="rp-num rp-red">{{ $e['absent'] }}</td>
                                        <td class="rp-num rp-amber">{{ $e['late'] }}</td>
                                        <td class="rp-num rp-purple">{{ $e['vacation'] }}</td>
                                        <td class="rp-num">{{ number_format($e['total_hours'],1) }}</td>
                                        <td class="rp-num {{ $e['late_minutes'] > 0 ? 'rp-red':'rp-green' }}">{{ $e['late_minutes'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="rp-empty-table">لا توجد سجلات حضور للفترة المحددة</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <aside class="rp-card rp-reports-card">
            <div class="rp-card-head">
                <div>
                    <div class="rp-card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 4h16v16H4z"/>
                            <path d="M8 8h8M8 12h8M8 16h5"/>
                        </svg>
                        <span>التقارير المتاحة</span>
                    </div>
                    <div class="rp-card-sub">افتح تقريراً سريعاً أو انتقل للقائمة المالية المتخصصة</div>
                </div>
                <div class="rp-kpi-code">REPORT LIBRARY</div>
            </div>

            <div class="rp-report-list">
                @foreach($reportCards as $card)
                    @if($card['kind'] === 'livewire')
                        <button type="button"
                                wire:click="{{ $card['action'] }}"
                                class="rp-report-item {{ $activeReport === ($card['active'] ?? '') ? 'active':'' }}">
                    @else
                        <a href="{{ $card['url'] }}" class="rp-report-item">
                    @endif

                        <div class="rp-report-icon {{ $card['tone'] }}">
                            @if($card['icon'] === 'cart')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L20.5 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                            @elseif($card['icon'] === 'chart')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16"/><path d="m7 15 3-4 3 2 4-6"/></svg>
                            @elseif($card['icon'] === 'cash')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 9h.01M17 15h.01"/></svg>
                            @elseif($card['icon'] === 'cube')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
                            @elseif($card['icon'] === 'building')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V5l8-3 8 3v16"/><path d="M8 8h2m4 0h2M8 12h2m4 0h2M8 16h2m4 0h2M2 21h20"/></svg>
                            @elseif($card['icon'] === 'scale')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M5 6h14M3 6l-2 6h8L7 6m10 0-2 6h8l-2-6M8 21h8"/></svg>
                            @elseif($card['icon'] === 'users')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                            @endif
                        </div>

                        <div class="rp-report-copy">
                            <div class="rp-report-name">{{ $card['title'] }}</div>
                            <div class="rp-report-desc">{{ $card['sub'] }}</div>
                        </div>

                        <div class="rp-report-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>
                        </div>

                    @if($card['kind'] === 'livewire')
                        </button>
                    @else
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="rp-operational">
                <div class="rp-operational-title">تقارير تشغيلية إضافية</div>
                <div class="rp-operational-row">
                    <button wire:click="loadDebtsReport" class="rp-mini-report {{ $activeReport === 'debts' ? 'active':'' }}">المديونيات</button>
                    <button wire:click="loadTopProductsReport" class="rp-mini-report {{ $activeReport === 'top_products' ? 'active':'' }}">الأصناف الأعلى مبيعاً</button>
                    <button wire:click="loadAttendanceReport" class="rp-mini-report {{ $activeReport === 'attendance' ? 'active':'' }}">الحضور والانصراف</button>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
window.printReport = function () {
    var source = document.querySelector('.rp-preview-card');

    if (!source) {
        window.alert('لا توجد معاينة تقرير على الصفحة الحالية.');
        return;
    }

    var sourceTable = source.querySelector('.rp-table');

    if (!sourceTable) {
        window.alert('لا يوجد جدول تقرير جاهز للطباعة. اضغط عرض أولاً.');
        return;
    }

    var printWindow = window.open('', '_blank', 'width=1100,height=800');

    if (!printWindow) {
        window.alert('المتصفح منع نافذة الطباعة. اسمح بالنوافذ المنبثقة لهذا الموقع ثم حاول مرة أخرى.');
        return;
    }

    var printDocument = printWindow.document;
    var titleNode = source.querySelector('.rp-card-title');
    var subtitleNode = source.querySelector('.rp-card-sub');

    var reportTitle = titleNode && titleNode.innerText
        ? titleNode.innerText.trim()
        : 'تقرير Orion ERP';

    var reportSubtitle = subtitleNode && subtitleNode.innerText
        ? subtitleNode.innerText.trim()
        : '';

    printDocument.documentElement.setAttribute('lang', 'ar');
    printDocument.documentElement.setAttribute('dir', 'rtl');

    while (printDocument.head.firstChild) {
        printDocument.head.removeChild(printDocument.head.firstChild);
    }

    while (printDocument.body.firstChild) {
        printDocument.body.removeChild(printDocument.body.firstChild);
    }

    var charset = printDocument.createElement('meta');
    charset.setAttribute('charset', 'utf-8');
    printDocument.head.appendChild(charset);

    var viewport = printDocument.createElement('meta');
    viewport.setAttribute('name', 'viewport');
    viewport.setAttribute('content', 'width=device-width, initial-scale=1');
    printDocument.head.appendChild(viewport);

    printDocument.title = reportTitle;

    var style = printDocument.createElement('style');

    style.textContent = [
        '@page { size: A4 landscape; margin: 10mm; }',
        'html, body { margin: 0; padding: 0; background: #fff !important; color: #111 !important; direction: rtl; font-family: Tahoma, Arial, sans-serif !important; }',
        '* { box-sizing: border-box; }',
        '.orion-print-report { width: 100%; background: #fff !important; color: #111 !important; }',
        '.orion-print-title { margin: 0 0 7px; padding: 0 0 8px; border-bottom: 2px solid #444; text-align: center; font-size: 16pt; font-weight: 700; color: #111 !important; }',
        '.orion-print-subtitle { margin: 0 0 14px; text-align: right; font-size: 9pt; line-height: 1.5; color: #333 !important; }',
        '.orion-print-report table { display: table; width: 100%; min-width: 0 !important; max-width: 100%; border-collapse: collapse; table-layout: auto; direction: rtl; background: #fff !important; color: #111 !important; font-family: Tahoma, Arial, sans-serif !important; font-size: 9pt; }',
        '.orion-print-report thead { display: table-header-group; }',
        '.orion-print-report th, .orion-print-report td { padding: 6px 5px; border: 1px solid #bbb !important; background: #fff !important; color: #111 !important; -webkit-text-fill-color: #111 !important; font-family: Tahoma, Arial, sans-serif !important; font-size: 9pt !important; line-height: 1.45 !important; text-align: right; vertical-align: middle; white-space: normal; overflow-wrap: anywhere; box-shadow: none !important; text-shadow: none !important; }',
        '.orion-print-report th { background: #eee !important; font-weight: 700; }',
        '.orion-print-report tr { break-inside: avoid; page-break-inside: avoid; }',
        '.orion-print-report .rp-progress { display: none !important; }',
        '.orion-print-report .rp-badge { display: inline; min-width: 0; padding: 0; border: 0; border-radius: 0; font-weight: 400; }',
        '@media screen { body { max-width: 1400px; margin: 20px auto; padding: 18px; } }'
    ].join('\n');

    printDocument.head.appendChild(style);

    var report = printDocument.createElement('main');
    report.className = 'orion-print-report';

    var heading = printDocument.createElement('h1');
    heading.className = 'orion-print-title';
    heading.textContent = reportTitle;
    report.appendChild(heading);

    if (reportSubtitle) {
        var subtitle = printDocument.createElement('p');
        subtitle.className = 'orion-print-subtitle';
        subtitle.textContent = reportSubtitle;
        report.appendChild(subtitle);
    }

    var tableClone = sourceTable.cloneNode(true);
    report.appendChild(tableClone);
    printDocument.body.appendChild(report);

    printWindow.setTimeout(function () {
        if (!printWindow.closed) {
            printWindow.focus();
            printWindow.print();
        }
    }, 700);
};
</script>

</x-filament-panels::page>