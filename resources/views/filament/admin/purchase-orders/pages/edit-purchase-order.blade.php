<x-filament-panels::page>
    @php
        $record = $this->record;
        $items = $record->items;
        $orderedQty = (float) $items->sum('quantity');
        $receivedQty = (float) $items->sum('received_quantity');
        $receivePct = $orderedQty > 0 ? min(100, round(($receivedQty / $orderedQty) * 100)) : 0;
        $statusLabel = match($record->status) {
            'draft' => 'مسودة', 'sent' => 'مُرسل للمورد', 'confirmed' => 'مؤكد',
            'partial' => 'استلام جزئي', 'received' => 'مستلم بالكامل', 'cancelled' => 'ملغي',
            default => $record->status,
        };
        $statusColor = match($record->status) {
            'received' => '#34d399', 'confirmed' => '#60a5fa', 'sent' => '#fbbf24',
            'partial' => '#fbbf24', 'cancelled' => '#f87171', default => '#94a3b8',
        };
        $overdue = false;
        if ($record->expected_date && !in_array($record->status, ['received','cancelled'], true)) {
            try { $overdue = \Illuminate\Support\Carbon::parse($record->expected_date)->startOfDay()->lt(today()); } catch (\Throwable) {}
        }
    @endphp

    <style>
        .orion-po-edit{direction:rtl;--pe-panel:#0d1728;--pe-panel2:#111c30;--pe-border:#263853;--pe-soft:#1b2a41;--pe-text:#f8fafc;--pe-muted:#8091aa;--pe-purple:#8b5cf6;--pe-green:#34d399;--pe-amber:#fbbf24;--pe-blue:#60a5fa;--pe-red:#f87171}.orion-po-edit *{box-sizing:border-box}
        .pe-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}.pe-kpi{padding:13px 15px;border:1px solid var(--pe-border);border-radius:13px;background:linear-gradient(145deg,var(--pe-panel2),#0b1525)}.pe-kpi-label{font-size:8px;color:#6f8199;font-weight:900}.pe-kpi-value{margin-top:7px;color:var(--accent);font-size:16px;font-weight:950;font-variant-numeric:tabular-nums}.pe-kpi-note{margin-top:4px;color:#5f7088;font-size:7px}
        .pe-progress{margin-bottom:14px;padding:12px 14px;border:1px solid var(--pe-border);border-radius:13px;background:#0d1728}.pe-progress-head{display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:9px}.pe-progress-head strong{color:#e5ecf5}.pe-progress-head span{color:#8da0b8}.pe-track{height:7px;margin-top:9px;border-radius:999px;background:#18263d;overflow:hidden}.pe-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#8b5cf6,#34d399)}
        .pe-alert{margin-bottom:12px;padding:10px 12px;border:1px solid rgba(248,113,113,.18);border-radius:11px;background:rgba(248,113,113,.045);color:#ff9aa5;font-size:9px;font-weight:850}
        .pe-layout{display:grid;grid-template-columns:minmax(0,1fr) 295px;gap:14px;align-items:start}.pe-shell,.pe-card{border:1px solid var(--pe-border);border-radius:16px;background:linear-gradient(145deg,#0d1728,#091321);box-shadow:0 16px 38px rgba(0,0,0,.14)}.pe-shell{overflow:hidden}.pe-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 17px;border-bottom:1px solid var(--pe-soft);background:#0f192b}.pe-title{font-size:15px;font-weight:950;color:var(--pe-text)}.pe-sub{margin-top:3px;font-size:9px;color:#6b7d96}.pe-status{padding:5px 10px;border:1px solid color-mix(in srgb,var(--status) 25%,#2b3c58);border-radius:999px;background:color-mix(in srgb,var(--status) 7%,#0f192b);color:var(--status);font-size:9px;font-weight:950}.pe-form{padding:16px}.pe-actions{margin-top:16px;padding-top:14px;border-top:1px solid var(--pe-soft)}
        .pe-side{display:grid;gap:12px;position:sticky;top:16px}.pe-card{padding:15px}.pe-kicker{font-size:8px;letter-spacing:.08em;color:#7d6ab0;font-weight:950;direction:ltr;text-align:right}.pe-side-title{margin-top:5px;color:#f5f8fc;font-size:14px;font-weight:950}.pe-row{display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:36px;border-bottom:1px solid #17243a;font-size:8px}.pe-row:last-child{border-bottom:0}.pe-row span{color:#71829a}.pe-row strong{color:#dce5f1;text-align:left}.pe-note{margin-top:10px;padding:9px 10px;border:1px solid rgba(139,92,246,.18);border-radius:10px;background:rgba(139,92,246,.045);color:#8f83ad;font-size:8px;line-height:1.7}
        .orion-po-edit .fi-sc-section{border:1px solid #22334d!important;border-radius:13px!important;background:#0f192b!important;box-shadow:none!important;overflow:hidden;margin-bottom:12px!important}.orion-po-edit .fi-sc-section-header{background:#111c30!important;border-bottom:1px solid #1e2d45!important;padding:12px 14px!important}.orion-po-edit .fi-sc-section-header-heading{color:#f3f6fb!important;font-size:11px!important;font-weight:950!important}.orion-po-edit .fi-sc-section-header-description{color:#71829a!important;font-size:8px!important}.orion-po-edit .fi-sc-section-content{padding:14px!important}.orion-po-edit .fi-fo-field-wrp-label span{font-size:9px!important;font-weight:850!important;color:#9fafc3!important}.orion-po-edit .fi-input-wrp{border-color:#2a3b56!important;background:#0b1525!important;border-radius:9px!important;box-shadow:none!important}.orion-po-edit .fi-input-wrp:focus-within{border-color:var(--pe-purple)!important;box-shadow:0 0 0 1px rgba(139,92,246,.18)!important}.orion-po-edit .fi-input,.orion-po-edit .fi-select-input{font-size:10px!important;color:#eef3f8!important}.orion-po-edit .fi-fo-repeater-item{border-color:#263853!important;background:#0c1627!important;border-radius:12px!important}
        @media(max-width:1100px){.pe-layout{grid-template-columns:1fr}.pe-side{position:static;grid-template-columns:repeat(2,1fr)}.pe-kpis{grid-template-columns:repeat(2,1fr)}}@media(max-width:700px){.pe-kpis,.pe-side{grid-template-columns:1fr}.pe-head{align-items:flex-start;flex-direction:column}}
    </style>

    <div class="orion-po-edit">
        <div class="pe-kpis">
            <div class="pe-kpi" style="--accent:var(--pe-blue)"><div class="pe-kpi-label">قيمة الطلب</div><div class="pe-kpi-value">{{ number_format((float)$record->total, 2) }} ر.س</div><div class="pe-kpi-note">إجمالي قيمة أمر الشراء</div></div>
            <div class="pe-kpi" style="--accent:var(--pe-purple)"><div class="pe-kpi-label">عدد الأصناف</div><div class="pe-kpi-value">{{ $items->count() }}</div><div class="pe-kpi-note">عدد بنود الطلب</div></div>
            <div class="pe-kpi" style="--accent:var(--pe-green)"><div class="pe-kpi-label">الكمية المستلمة</div><div class="pe-kpi-value">{{ number_format($receivedQty, 2) }}</div><div class="pe-kpi-note">من أصل {{ number_format($orderedQty, 2) }}</div></div>
            <div class="pe-kpi" style="--accent:{{ $overdue ? 'var(--pe-red)' : 'var(--pe-amber)' }}"><div class="pe-kpi-label">الاستلام المتوقع</div><div class="pe-kpi-value" style="font-size:14px">{{ $record->expected_date ? \Illuminate\Support\Carbon::parse($record->expected_date)->format('d/m/Y') : '-' }}</div><div class="pe-kpi-note">{{ $overdue ? 'متأخر عن الموعد' : 'موعد التوريد المسجل' }}</div></div>
        </div>

        <div class="pe-progress"><div class="pe-progress-head"><strong>تقدم الاستلام</strong><span>{{ $receivePct }}% · {{ number_format($receivedQty,2) }} / {{ number_format($orderedQty,2) }}</span></div><div class="pe-track"><div class="pe-fill" style="width:{{ $receivePct }}%"></div></div></div>
        @if($overdue)<div class="pe-alert">الطلب تجاوز تاريخ الاستلام المتوقع وما زال غير مكتمل. راجع المورد أو حدّث موعد التسليم.</div>@endif

        <div class="pe-layout">
            <div class="pe-shell">
                <div class="pe-head"><div><div class="pe-title">{{ $record->order_number }}</div><div class="pe-sub">تعديل بيانات طلب الشراء ومتابعة التوريد والاستلام</div></div><span class="pe-status" style="--status:{{ $statusColor }}">{{ $statusLabel }}</span></div>
                <form wire:submit="save" class="pe-form">
                    {{ $this->form }}
                    <div class="pe-actions"><x-filament::actions :actions="$this->getFormActions()" /></div>
                </form>
            </div>
            <aside class="pe-side">
                <div class="pe-card"><div class="pe-kicker">ORDER SNAPSHOT</div><div class="pe-side-title">ملخص الطلب</div>
                    <div style="margin-top:10px">
                        <div class="pe-row"><span>المورد</span><strong>{{ $record->supplier?->name ?? '-' }}</strong></div>
                        <div class="pe-row"><span>المخزن</span><strong>{{ $record->warehouse?->name ?? '-' }}</strong></div>
                        <div class="pe-row"><span>تاريخ الطلب</span><strong>{{ $record->order_date ? \Illuminate\Support\Carbon::parse($record->order_date)->format('d/m/Y') : '-' }}</strong></div>
                        <div class="pe-row"><span>الحالة</span><strong style="color:{{ $statusColor }}">{{ $statusLabel }}</strong></div>
                        <div class="pe-row"><span>الأصناف</span><strong>{{ $items->count() }} بند</strong></div>
                    </div>
                    <div class="pe-note">الـUI هنا يتابع البيانات الموجودة بالفعل. تنفيذ الاستلام وترحيل أثره على المخزون لازم يظل من خلال منطق الـPurchaseOrder في الـModel/Service.</div>
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
