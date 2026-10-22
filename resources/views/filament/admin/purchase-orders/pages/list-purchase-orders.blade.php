<x-filament-panels::page>
    @php
        $stats = $this->getPurchaseOrderStats();
        $quick = request()->query('quick');
        $baseUrl = url()->current();
    @endphp

    <style>
        .orion-po-list{
            direction:rtl;
            --po-panel:#0d1728;
            --po-panel2:#111c30;
            --po-border:#263853;
            --po-soft:#1a2940;
            --po-text:#f8fafc;
            --po-muted:#7b8ca5;
            --po-purple:#8b5cf6;
            --po-green:#34d399;
            --po-amber:#fbbf24;
            --po-red:#f87171;
            --po-blue:#60a5fa;
            --po-cyan:#22d3ee;
        }
        .orion-po-list *{box-sizing:border-box}
        .po-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
        .po-kpi{position:relative;display:block;overflow:hidden;min-height:104px;padding:15px 17px;border:1px solid var(--po-border);border-radius:15px;background:linear-gradient(145deg,var(--po-panel2),#0b1525);text-decoration:none;color:inherit;box-shadow:0 12px 28px rgba(0,0,0,.11);transition:.15s ease}
        .po-kpi:hover{transform:translateY(-2px);border-color:color-mix(in srgb,var(--accent) 48%,var(--po-border))}
        .po-kpi.active{border-color:color-mix(in srgb,var(--accent) 72%,var(--po-border));background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 9%,#111c30),#0b1525)}
        .po-kpi::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.8}
        .po-kpi-head{display:flex;justify-content:space-between;align-items:center;gap:10px}.po-kpi-label{color:#b7c4d5;font-size:10px;font-weight:900}.po-kpi-code{direction:ltr;padding:3px 8px;border:1px solid #2b3c58;border-radius:999px;color:#72839b;font-size:8px;font-weight:950}.po-kpi-value{margin-top:13px;color:var(--accent);font-size:23px;font-weight:950;line-height:1;font-variant-numeric:tabular-nums}.po-kpi-note{margin-top:7px;color:#63758e;font-size:8px;line-height:1.6}

        .po-workflow{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;margin-bottom:12px;padding:8px;border:1px solid #22334d;border-radius:13px;background:rgba(13,23,40,.72)}
        .po-step{position:relative;padding:10px 12px;border:1px solid #1f3049;border-radius:10px;background:#0e1829}.po-step-no{position:absolute;left:9px;top:8px;direction:ltr;color:#7259bb;font-size:7px;font-weight:950}.po-step-title{color:#dce5f1;font-size:9px;font-weight:900}.po-step-note{margin-top:4px;color:#60728b;font-size:7px;line-height:1.55}

        .po-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;padding:8px 10px;border:1px solid #22334c;border-radius:12px;background:rgba(13,23,40,.72)}
        .po-filters{display:flex;gap:6px;flex-wrap:wrap}.po-chip{height:31px;display:inline-flex;align-items:center;justify-content:center;padding:0 11px;border:1px solid #2b3b55;border-radius:9px;background:#0d1728;color:#9fb0c5;text-decoration:none;font-size:9px;font-weight:900}.po-chip:hover{border-color:var(--po-purple);color:#fff}.po-chip.active{border-color:var(--po-purple);background:rgba(139,92,246,.12);color:#d8ccff}.po-toolbar-note{color:#62748c;font-size:8px}

        .po-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;padding:10px 12px;border:1px solid rgba(248,113,113,.18);border-radius:11px;background:rgba(248,113,113,.045);color:#b68c92;font-size:9px}.po-alert strong{color:#ff9ba5}.po-alert a{color:#ffb3bb;text-decoration:none;font-weight:900}

        .po-shell{overflow:hidden;border:1px solid #22334c;border-radius:16px;background:linear-gradient(145deg,#0c1627,#091321);box-shadow:0 16px 38px rgba(0,0,0,.15)}
        .po-shell-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 15px;border-bottom:1px solid #1e2d45;background:#0f192b}.po-shell-title{color:#f8fafc;font-size:15px;font-weight:950}.po-shell-sub{margin-top:3px;color:#677991;font-size:8px}.po-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(52,211,153,.18);border-radius:999px;background:rgba(52,211,153,.055);color:#6ee7b7;font-size:8px;font-weight:900}.po-status::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--po-green)}
        .po-shell .fi-ta{border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}.po-shell .fi-ta-header,.po-shell .fi-ta-header-ctn{background:#0d1728!important;border-color:#1e2d45!important}.po-shell .fi-ta-row{transition:background-color .15s ease}.po-shell .fi-ta-row:hover{background:rgba(139,92,246,.04)!important}.po-shell .fi-ta-cell,.po-shell .fi-ta-header-cell{border-color:#1a2940!important}.po-shell .fi-ta-header-cell-label{color:#8292aa!important;font-size:9px!important;font-weight:900!important}.po-shell .fi-ta-text-item-label{font-size:10px}.po-shell .fi-icon-btn{border:1px solid #2b3b55!important;border-radius:8px!important;background:#0d1728!important}.po-shell .fi-icon-btn:hover{border-color:#8b5cf6!important;background:rgba(139,92,246,.08)!important}

        @media(max-width:1100px){.po-kpis{grid-template-columns:repeat(2,1fr)}.po-workflow{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:720px){.po-kpis,.po-workflow{grid-template-columns:1fr}.po-toolbar,.po-shell-head,.po-alert{align-items:flex-start;flex-direction:column}.po-toolbar-note{display:none}}
    </style>

    <div class="orion-po-list">
        <div class="po-kpis">
            <a href="{{ $baseUrl }}" class="po-kpi {{ !$quick ? 'active' : '' }}" style="--accent:var(--po-blue)">
                <div class="po-kpi-head"><span class="po-kpi-label">إجمالي طلبات الشراء</span><span class="po-kpi-code">TOTAL</span></div>
                <div class="po-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="po-kpi-note">جميع الطلبات المسجلة في النظام</div>
            </a>
            <a href="{{ $baseUrl }}?quick=open" class="po-kpi {{ $quick === 'open' ? 'active' : '' }}" style="--accent:var(--po-amber)">
                <div class="po-kpi-head"><span class="po-kpi-label">طلبات قيد التنفيذ</span><span class="po-kpi-code">OPEN</span></div>
                <div class="po-kpi-value">{{ number_format($stats['open']) }}</div>
                <div class="po-kpi-note">مسودة أو مرسلة أو مؤكدة أو مستلمة جزئيًا</div>
            </a>
            <a href="{{ $baseUrl }}?quick=received" class="po-kpi {{ $quick === 'received' ? 'active' : '' }}" style="--accent:var(--po-green)">
                <div class="po-kpi-head"><span class="po-kpi-label">مستلم بالكامل</span><span class="po-kpi-code">DONE</span></div>
                <div class="po-kpi-value">{{ number_format($stats['received']) }}</div>
                <div class="po-kpi-note">طلبات تم استلامها وإغلاق دورة التوريد</div>
            </a>
            <div class="po-kpi" style="--accent:var(--po-cyan);cursor:default">
                <div class="po-kpi-head"><span class="po-kpi-label">إجمالي قيمة الطلبات</span><span class="po-kpi-code">SAR</span></div>
                <div class="po-kpi-value">{{ number_format($stats['value'], 2) }} ر.س</div>
                <div class="po-kpi-note">باستثناء الطلبات الملغاة</div>
            </div>
        </div>

        <div class="po-workflow">
            <div class="po-step"><span class="po-step-no">01</span><div class="po-step-title">إنشاء الطلب</div><div class="po-step-note">المورد، المخزن، الأصناف والأسعار</div></div>
            <div class="po-step"><span class="po-step-no">02</span><div class="po-step-title">إرسال للمورد</div><div class="po-step-note">تحويل المسودة إلى طلب توريد فعلي</div></div>
            <div class="po-step"><span class="po-step-no">03</span><div class="po-step-title">تأكيد الطلب</div><div class="po-step-note">تأكيد السعر والكمية وموعد الاستلام</div></div>
            <div class="po-step"><span class="po-step-no">04</span><div class="po-step-title">الاستلام الجزئي</div><div class="po-step-note">متابعة الكميات التي وصلت فعليًا</div></div>
            <div class="po-step"><span class="po-step-no">05</span><div class="po-step-title">الاستلام الكامل</div><div class="po-step-note">إغلاق الطلب بعد اكتمال التوريد</div></div>
        </div>

        @if($stats['overdue'] > 0)
            <div class="po-alert">
                <div><strong>{{ $stats['overdue'] }} طلب متأخر عن تاريخ الاستلام المتوقع.</strong> يحتاج متابعة المورد أو تحديث موعد التسليم.</div>
                <a href="{{ $baseUrl }}?quick=overdue">عرض المتأخر</a>
            </div>
        @endif

        <div class="po-toolbar">
            <div class="po-filters">
                <a href="{{ $baseUrl }}" class="po-chip {{ !$quick ? 'active' : '' }}">الكل</a>
                <a href="{{ $baseUrl }}?quick=draft" class="po-chip {{ $quick === 'draft' ? 'active' : '' }}">مسودة</a>
                <a href="{{ $baseUrl }}?quick=sent" class="po-chip {{ $quick === 'sent' ? 'active' : '' }}">مُرسل</a>
                <a href="{{ $baseUrl }}?quick=confirmed" class="po-chip {{ $quick === 'confirmed' ? 'active' : '' }}">مؤكد</a>
                <a href="{{ $baseUrl }}?quick=partial" class="po-chip {{ $quick === 'partial' ? 'active' : '' }}">استلام جزئي</a>
                <a href="{{ $baseUrl }}?quick=received" class="po-chip {{ $quick === 'received' ? 'active' : '' }}">مستلم</a>
                <a href="{{ $baseUrl }}?quick=overdue" class="po-chip {{ $quick === 'overdue' ? 'active' : '' }}">متأخر ({{ $stats['overdue'] }})</a>
            </div>
            <div class="po-toolbar-note">اضغط على الحالة لعرض الطلبات المرتبطة بها مباشرة</div>
        </div>

        <div class="po-shell">
            <div class="po-shell-head">
                <div><div class="po-shell-title">سجل طلبات الشراء</div><div class="po-shell-sub">متابعة الموردين والتواريخ والقيم وحالة التوريد من نفس الجدول</div></div>
                <span class="po-status">دورة المشتريات نشطة</span>
            </div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
