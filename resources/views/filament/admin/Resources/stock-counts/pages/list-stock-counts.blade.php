<x-filament-panels::page>
<style>
*{box-sizing:border-box}.sc-orion{direction:rtl}.sc-orion .hero{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:18px}.sc-orion .eyebrow{font-size:11px;color:#7c8ba3;font-weight:800;margin-bottom:7px}.sc-orion h1{font-size:30px;line-height:1.1;font-weight:950;color:#fff;margin:0}.sc-orion .sub{margin-top:7px;color:#7f90aa;font-size:12px}.sc-orion .status-note{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(52,211,153,.18);background:rgba(52,211,153,.06);color:#6ee7b7;border-radius:999px;padding:6px 11px;font-size:10px;font-weight:900}.sc-orion .status-note i{width:6px;height:6px;border-radius:50%;background:#34d399}.sc-orion .kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}.sc-orion .kpi{position:relative;overflow:hidden;padding:15px 17px;border:1px solid #26364f;border-radius:14px;background:linear-gradient(180deg,#0f1a2e,#0b1525);min-height:98px}.sc-orion .kpi:after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,#8b5cf6,transparent)}.sc-orion .kpi.green:after{background:linear-gradient(90deg,transparent,#34d399,transparent)}.sc-orion .kpi.amber:after{background:linear-gradient(90deg,transparent,#fbbf24,transparent)}.sc-orion .kpi.blue:after{background:linear-gradient(90deg,transparent,#38bdf8,transparent)}.sc-orion .kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px}.sc-orion .kpi-label{font-size:11px;font-weight:900;color:#b8c5d7}.sc-orion .kpi-code{direction:ltr;border:1px solid #2c3d59;color:#71819a;border-radius:999px;font-size:8px;font-weight:950;padding:3px 7px}.sc-orion .kpi-value{margin-top:14px;font-size:22px;font-weight:950;color:#fff}.sc-orion .kpi.green .kpi-value{color:#34d399}.sc-orion .kpi.amber .kpi-value{color:#fbbf24}.sc-orion .kpi.blue .kpi-value{color:#38bdf8}.sc-orion .kpi-desc{font-size:9px;color:#61738d;margin-top:4px}.sc-orion .workflow{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;border:1px solid #22334d;background:#0b1525;border-radius:13px;padding:9px;margin-bottom:14px}.sc-orion .step{position:relative;min-height:52px;padding:9px 11px;border:1px solid #1e2d44;border-radius:10px;background:#0e192a}.sc-orion .step-no{font-size:8px;color:#8b5cf6;font-weight:950}.sc-orion .step-title{margin-top:2px;font-size:10px;color:#e6edf7;font-weight:900}.sc-orion .step-sub{font-size:8px;color:#687b95;margin-top:2px}.sc-orion .table-shell{border:1px solid #26364f;border-radius:15px;background:#0c1627;overflow:hidden}.sc-orion .table-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;border-bottom:1px solid #203149}.sc-orion .table-title{font-size:16px;font-weight:950;color:#fff}.sc-orion .table-sub{font-size:9px;color:#687a94;margin-top:3px}.sc-orion .legend{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.sc-orion .pill{font-size:9px;font-weight:900;border-radius:999px;padding:5px 9px;border:1px solid}.sc-orion .pill.draft{color:#aab6c6;border-color:#35445a;background:rgba(148,163,184,.06)}.sc-orion .pill.progress{color:#fbbf24;border-color:rgba(251,191,36,.22);background:rgba(251,191,36,.06)}.sc-orion .pill.done{color:#34d399;border-color:rgba(52,211,153,.22);background:rgba(52,211,153,.06)}.sc-orion .fi-ta{border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}.sc-orion .fi-ta-header{background:#0d1728!important;border-bottom-color:#1f3048!important}.sc-orion .fi-ta-content{background:#0c1627!important}.sc-orion table{background:#0c1627!important}.sc-orion thead{background:#101a2b!important}.sc-orion tbody tr{border-color:#1d2b40!important}.sc-orion tbody tr:hover{background:rgba(139,92,246,.035)!important}
@media(max-width:1000px){.sc-orion .kpis{grid-template-columns:repeat(2,1fr)}.sc-orion .workflow{grid-template-columns:repeat(2,1fr)}}@media(max-width:640px){.sc-orion .hero{display:block}.sc-orion .status-note{margin-top:10px}.sc-orion .kpis,.sc-orion .workflow{grid-template-columns:1fr}}
</style>

<div class="sc-orion">
    <div class="hero">
        <div>
            <div class="eyebrow">المخازن / الجرد المخزني</div>
            <h1>الجرد المخزني</h1>
            <div class="sub">دورة جرد كاملة من تجهيز الورقة وحتى مراجعة الفروقات واعتماد الرصيد.</div>
        </div>
        <div class="status-note"><i></i> دورة جرد محكومة</div>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="kpi-top"><span class="kpi-label">إجمالي الجرديات</span><span class="kpi-code">TOTAL</span></div>
            <div class="kpi-value">{{ $this->totalCounts }}</div>
            <div class="kpi-desc">كل عمليات الجرد المسجلة</div>
        </div>
        <div class="kpi amber">
            <div class="kpi-top"><span class="kpi-label">جاري التنفيذ</span><span class="kpi-code">LIVE</span></div>
            <div class="kpi-value">{{ $this->inProgressCounts }}</div>
            <div class="kpi-desc">جرديات تحتاج استكمال أو مراجعة</div>
        </div>
        <div class="kpi blue">
            <div class="kpi-top"><span class="kpi-label">مسودات</span><span class="kpi-code">DRAFT</span></div>
            <div class="kpi-value">{{ $this->draftCounts }}</div>
            <div class="kpi-desc">لم يبدأ العد الفعلي بعد</div>
        </div>
        <div class="kpi green">
            <div class="kpi-top"><span class="kpi-label">مكتمل</span><span class="kpi-code">DONE</span></div>
            <div class="kpi-value">{{ $this->completedCounts }}</div>
            <div class="kpi-desc">جرديات تم اعتمادها وإغلاقها</div>
        </div>
    </div>

    <div class="workflow">
        <div class="step"><div class="step-no">01</div><div class="step-title">إنشاء الجرد</div><div class="step-sub">اختيار المخزن والنطاق</div></div>
        <div class="step"><div class="step-no">02</div><div class="step-title">تحميل الأصناف</div><div class="step-sub">تثبيت قائمة الجرد</div></div>
        <div class="step"><div class="step-no">03</div><div class="step-title">طباعة Blind Count</div><div class="step-sub">بدون رصيد النظام</div></div>
        <div class="step"><div class="step-no">04</div><div class="step-title">إدخال الفروقات</div><div class="step-sub">تعديل الاستثناءات فقط</div></div>
        <div class="step"><div class="step-no">05</div><div class="step-title">المراجعة والاعتماد</div><div class="step-sub">إقفال الجرد بعد التأكيد</div></div>
    </div>

    <div class="table-shell">
        <div class="table-head">
            <div>
                <div class="table-title">سجل الجرديات</div>
                <div class="table-sub">استخدم فلاتر الجدول للبحث بالحالة أو المخزن أو رقم الجرد.</div>
            </div>
            <div class="legend">
                <span class="pill draft">مسودة</span>
                <span class="pill progress">جاري</span>
                <span class="pill done">مكتمل</span>
            </div>
        </div>
        {{ $this->table }}
    </div>
</div>
</x-filament-panels::page>
