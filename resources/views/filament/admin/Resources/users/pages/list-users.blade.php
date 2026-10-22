<x-filament-panels::page>
    @php
        $stats = $this->getUserStats();
        $resource = \App\Filament\Admin\Resources\Users\UserResource::class;
    @endphp

    <style>
        .fi-header { display:none !important; }

        .orion-users-v2,
        .orion-users-v2 * { box-sizing:border-box; font-family:'Cairo',sans-serif; }

        .orion-users-v2 {
            --ou-panel:#0c1728;
            --ou-panel-2:#101b2e;
            --ou-border:#21324d;
            --ou-border-soft:#17263c;
            --ou-text:#f4f7fb;
            --ou-muted:#7c8da7;
            --ou-purple:#8b5cf6;
            --ou-green:#34d399;
            --ou-red:#fb7185;
            --ou-amber:#fbbf24;
            --ou-blue:#60a5fa;
            width:100%;direction:rtl;color:var(--ou-text);padding-bottom:24px;
        }

        .ou-head { display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:18px; }
        .ou-breadcrumb { display:flex;align-items:center;gap:7px;color:#60728e;font-size:10px;font-weight:800;margin-bottom:8px; }
        .ou-breadcrumb strong { color:#9c88f5; }
        .ou-title-row { display:flex;align-items:center;gap:12px; }
        .ou-title-icon {
            width:47px;height:47px;border-radius:13px;display:grid;place-items:center;color:#c4b5fd;
            border:1px solid rgba(139,92,246,.38);
            background:linear-gradient(145deg,rgba(124,58,237,.27),rgba(76,29,149,.12));
            box-shadow:0 12px 30px rgba(76,29,149,.15);
        }
        .ou-title-icon svg { width:22px;height:22px; }
        .ou-title { margin:0;color:#f5f7fb;font-size:27px;line-height:1.1;font-weight:950; }
        .ou-subtitle { margin-top:5px;color:var(--ou-muted);font-size:10px;font-weight:650; }

        .ou-create {
            min-height:40px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 15px;
            border-radius:10px;text-decoration:none;color:#fff;font-size:10px;font-weight:950;
            border:1px solid rgba(167,139,250,.45);background:linear-gradient(135deg,#7c3aed,#8b5cf6);
            box-shadow:0 12px 24px rgba(91,33,182,.2);transition:.18s ease;
        }
        .ou-create:hover { transform:translateY(-1px);filter:brightness(1.06); }
        .ou-create svg { width:15px;height:15px; }

        .ou-kpis { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:13px; }
        .ou-kpi {
            position:relative;overflow:hidden;min-height:108px;padding:14px 15px;border-radius:13px;
            border:1px solid var(--ou-border);background:linear-gradient(145deg,#0e192b,#0a1423);
        }
        .ou-kpi::after { content:'';position:absolute;width:90px;height:90px;left:-28px;bottom:-40px;border-radius:50%;opacity:.12;background:currentColor; }
        .ou-kpi.total { color:#a78bfa; }.ou-kpi.managers { color:#fbbf24; }.ou-kpi.cashiers { color:#34d399; }.ou-kpi.branches { color:#60a5fa; }
        .ou-kpi-top { display:flex;align-items:center;justify-content:space-between;gap:8px; }
        .ou-kpi-label { color:#8b9ab0;font-size:9px;font-weight:850; }
        .ou-kpi-icon { width:29px;height:29px;border-radius:8px;display:grid;place-items:center;color:currentColor;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025); }
        .ou-kpi-icon svg { width:14px;height:14px; }
        .ou-kpi-value { margin-top:7px;color:currentColor;font-size:25px;line-height:1;font-weight:950;direction:ltr;text-align:right; }
        .ou-kpi-note { margin-top:7px;color:#5f708a;font-size:8px;font-weight:700; }

        .ou-table-panel { overflow:hidden;border-radius:14px;border:1px solid var(--ou-border);background:linear-gradient(145deg,#0b1524,#091321);box-shadow:0 18px 50px rgba(0,0,0,.12); }
        .ou-table-panel-head { min-height:56px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 15px;border-bottom:1px solid var(--ou-border-soft); }
        .ou-panel-title { color:#e7edf7;font-size:11px;font-weight:950; }
        .ou-panel-sub { margin-top:3px;color:#61738e;font-size:8px;font-weight:650; }
        .ou-count-chip { display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;border:1px solid rgba(139,92,246,.22);background:rgba(139,92,246,.07);color:#aa96f8;font-size:8px;font-weight:900; }
        .ou-count-chip span { width:5px;height:5px;border-radius:50%;background:#8b5cf6;box-shadow:0 0 10px rgba(139,92,246,.7); }

        .orion-users-v2 .fi-ta-ctn,.orion-users-v2 .fi-section,.orion-users-v2 .fi-ta { border:0 !important;border-radius:0 !important;box-shadow:none !important;background:transparent !important; }
        .orion-users-v2 .fi-ta-header { padding:11px 14px !important;background:rgba(255,255,255,.012) !important;border-bottom:1px solid #18283f !important; }
        .orion-users-v2 .fi-ta-header-toolbar { gap:8px !important; }
        .orion-users-v2 .fi-input-wrp { border-color:#263a59 !important;background:#0d192b !important;box-shadow:none !important; }
        .orion-users-v2 .fi-ta-table thead { background:#0a1422 !important; }
        .orion-users-v2 .fi-ta-header-cell { color:#657895 !important;font-size:9px !important;font-weight:900 !important;border-bottom-color:#1a2a41 !important; }
        .orion-users-v2 .fi-ta-row { background:#0c1728 !important;border-bottom:1px solid #17263c !important; }
        .orion-users-v2 .fi-ta-row:hover { background:#0f1b2e !important; }
        .orion-users-v2 .fi-ta-cell { color:#c8d3e1 !important;font-size:9px !important; }
        .orion-users-v2 .fi-ta-text { color:inherit !important; }
        .orion-users-v2 .fi-ta-footer { border-top:1px solid #192a42 !important;background:#0a1422 !important; }
        .orion-users-v2 .fi-pagination { color:#72849d !important; }
        .orion-users-v2 .fi-badge { font-weight:850 !important; }

        @media(max-width:900px){ .ou-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}.ou-head{align-items:flex-start;flex-direction:column;}.ou-create{align-self:stretch;} }
        @media(max-width:560px){ .ou-kpis{grid-template-columns:1fr;}.ou-title{font-size:23px;} }
    </style>

    <div class="orion-users-v2">
        <div class="ou-head">
            <div>
                <div class="ou-breadcrumb"><span>الإعدادات</span><span>‹</span><strong>المستخدمين</strong></div>
                <div class="ou-title-row">
                    <div class="ou-title-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h1 class="ou-title">المستخدمين</h1>
                        <div class="ou-subtitle">إدارة الحسابات والأدوار ونطاق الوصول إلى الفروع داخل Orion ERP.</div>
                    </div>
                </div>
            </div>

            @if($resource::canCreate())
                <a href="{{ $resource::getUrl('create') }}" class="ou-create">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    إضافة مستخدم جديد
                </a>
            @endif
        </div>

        <div class="ou-kpis">
            <div class="ou-kpi total">
                <div class="ou-kpi-top"><div class="ou-kpi-label">إجمالي المستخدمين</div><div class="ou-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg></div></div>
                <div class="ou-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="ou-kpi-note">كل الحسابات المسجلة في النظام</div>
            </div>
            <div class="ou-kpi managers">
                <div class="ou-kpi-top"><div class="ou-kpi-label">الإدارة</div><div class="ou-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7Z"/><path d="m9 12 2 2 4-4"/></svg></div></div>
                <div class="ou-kpi-value">{{ number_format($stats['managers']) }}</div>
                <div class="ou-kpi-note">مدير النظام والمديرون</div>
            </div>
            <div class="ou-kpi cashiers">
                <div class="ou-kpi-top"><div class="ou-kpi-label">الكاشير</div><div class="ou-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h6M7 13h3M17 11h.01"/></svg></div></div>
                <div class="ou-kpi-value">{{ number_format($stats['cashiers']) }}</div>
                <div class="ou-kpi-note">حسابات التشغيل ونقاط البيع</div>
            </div>
            <div class="ou-kpi branches">
                <div class="ou-kpi-top"><div class="ou-kpi-label">مرتبطون بفرع</div><div class="ou-kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/></svg></div></div>
                <div class="ou-kpi-value">{{ number_format($stats['assigned_branch']) }}</div>
                <div class="ou-kpi-note">لديهم نطاق وصول لفرع محدد</div>
            </div>
        </div>

        <div class="ou-table-panel">
            <div class="ou-table-panel-head">
                <div><div class="ou-panel-title">دليل المستخدمين</div><div class="ou-panel-sub">ابحث وفلتر حسب الدور أو الفرع ثم افتح الحساب أو عدّل بياناته.</div></div>
                <div class="ou-count-chip"><span></span>{{ number_format($stats['total']) }} مستخدم</div>
            </div>
            <div>{{ $this->table }}</div>
        </div>
    </div>
</x-filament-panels::page>
