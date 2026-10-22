<x-filament-panels::page>
    @php
        $health = $this->getSettingsHealth();
        $mode = $health['zatca_mode'] ?? 'simulation';
        $modeClass = match($mode) {
            'production' => 'production',
            'compliance' => 'compliance',
            default => 'simulation',
        };
        $zatcaLogo = 'https://www.zatca.gov.sa/ar/MediaCenter/AuthorityIdentity/PublishingImages/dark-bg.png';
    @endphp

    <style>
        .fi-header { display: none !important; }

        .orion-settings {
            direction: rtl;
            color: #eef3fa;
            --os-panel: #0b1524;
            --os-panel-2: #0e1a2c;
            --os-panel-3: #111f34;
            --os-border: #24354f;
            --os-border-soft: #1a2940;
            --os-text: #f7f9fd;
            --os-muted: #8c9db4;
            --os-muted-2: #60728b;
            --os-purple: #8b5cf6;
            --os-purple-2: #a78bfa;
            --os-green: #34d399;
            --os-amber: #fbbf24;
            --os-red: #fb7185;
            --os-cyan: #38bdf8;
        }

        .orion-settings * { box-sizing: border-box; }
        .orion-settings svg { display: block; }

        .os-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0,1fr) 330px;
            gap: 18px;
            align-items: stretch;
            margin-bottom: 14px;
            padding: 0;
            border: 1px solid #293c59;
            border-radius: 20px;
            background:
                radial-gradient(circle at 86% 10%, rgba(139,92,246,.19), transparent 31%),
                radial-gradient(circle at 11% 90%, rgba(56,189,248,.08), transparent 34%),
                linear-gradient(145deg, #0e192b 0%, #091321 54%, #0a1525 100%);
            box-shadow: 0 22px 60px rgba(0,0,0,.22);
        }
        .os-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: .28;
            background-image: linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(to left, #000, transparent 72%);
        }

        .os-hero-main {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 16px;
            min-height: 150px;
            padding: 24px 26px;
        }
        .os-brand-mark {
            width: 58px;
            height: 58px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 17px;
            color: #e9e3ff;
            background: linear-gradient(145deg, rgba(139,92,246,.28), rgba(109,40,217,.14));
            border: 1px solid rgba(167,139,250,.28);
            box-shadow: inset 0 1px rgba(255,255,255,.05), 0 14px 34px rgba(109,40,217,.18);
        }
        .os-brand-mark svg { width: 28px; height: 28px; }
        .os-kicker { direction:ltr; text-align:right; font-size:9px; font-weight:900; letter-spacing:.14em; color:#a99cff; }
        .os-title { margin:5px 0 0; font-size:30px; line-height:1.15; font-weight:950; color:#fff; }
        .os-subtitle { max-width:680px; margin-top:7px; color:#8496ae; font-size:11px; line-height:1.9; }
        .os-hero-tags { display:flex; align-items:center; gap:7px; flex-wrap:wrap; margin-top:13px; }
        .os-hero-tag { display:inline-flex; align-items:center; gap:7px; padding:5px 9px; border-radius:999px; border:1px solid #2a3c58; background:rgba(12,22,38,.72); color:#8294ac; font-size:8px; font-weight:900; }
        .os-hero-tag b { color:#dbe5f3; font-weight:900; }
        .os-hero-tag .dot { width:6px; height:6px; border-radius:50%; background:var(--os-green); box-shadow:0 0 12px rgba(52,211,153,.65); }

        .os-control-panel {
            position: relative;
            z-index: 1;
            margin: 13px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(139,92,246,.22);
            border-radius: 16px;
            background: linear-gradient(145deg, rgba(18,31,51,.92), rgba(11,21,36,.94));
            box-shadow: inset 0 1px rgba(255,255,255,.025);
        }
        .os-control-head { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .os-control-label { color:#8293aa; font-size:9px; font-weight:850; }
        .os-control-status { display:inline-flex; align-items:center; gap:7px; padding:5px 9px; border-radius:999px; color:#7be1b2; font-size:8px; font-weight:900; border:1px solid rgba(52,211,153,.20); background:rgba(52,211,153,.05); }
        .os-control-status i { width:6px; height:6px; border-radius:50%; background:var(--os-green); box-shadow:0 0 10px rgba(52,211,153,.7); }
        .os-control-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:13px; }
        .os-control-cell { padding:10px; border-radius:10px; border:1px solid #20314a; background:#0b1525; }
        .os-control-cell small { display:block; font-size:8px; color:#61738d; font-weight:800; }
        .os-control-cell strong { display:block; margin-top:4px; color:#e7eef8; font-size:11px; font-weight:950; }
        .os-control-progress { height:4px; margin-top:8px; border-radius:999px; overflow:hidden; background:#17253a; }
        .os-control-progress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#7c3aed,#a78bfa); }

        .os-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:11px; margin-bottom:14px; }
        .os-kpi {
            position:relative;
            overflow:hidden;
            min-height:118px;
            padding:15px 16px;
            border:1px solid #263851;
            border-radius:15px;
            background:linear-gradient(145deg,#101c2f,#0a1423);
            box-shadow:0 12px 30px rgba(0,0,0,.10);
        }
        .os-kpi::after { content:''; position:absolute; inset:auto 0 0 0; height:2px; background:linear-gradient(90deg,transparent,var(--accent,#8b5cf6),transparent); opacity:.58; }
        .os-kpi::before { content:''; position:absolute; width:90px; height:90px; border-radius:50%; left:-38px; bottom:-48px; background:var(--glow,rgba(139,92,246,.07)); }
        .os-kpi.company { --accent:#8b5cf6; --glow:rgba(139,92,246,.09); }
        .os-kpi.address { --accent:#38bdf8; --glow:rgba(56,189,248,.07); }
        .os-kpi.zatca { --accent:#34d399; --glow:rgba(52,211,153,.07); }
        .os-kpi.currency { --accent:#fbbf24; --glow:rgba(251,191,36,.07); }
        .os-kpi-top { position:relative; z-index:1; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .os-kpi-label { color:#9eacc0; font-size:9px; font-weight:900; }
        .os-kpi-icon { width:31px; height:31px; display:grid; place-items:center; border-radius:9px; color:#c4b5fd; background:rgba(139,92,246,.07); border:1px solid rgba(139,92,246,.18); }
        .os-kpi.address .os-kpi-icon { color:#7dd3fc; background:rgba(56,189,248,.05); border-color:rgba(56,189,248,.18); }
        .os-kpi.currency .os-kpi-icon { color:#f8d477; background:rgba(251,191,36,.05); border-color:rgba(251,191,36,.18); }
        .os-kpi-icon svg { width:16px; height:16px; }
        .os-kpi-value { position:relative; z-index:1; margin-top:12px; font-size:18px; font-weight:950; color:#f7f9fc; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .os-kpi-note { position:relative; z-index:1; margin-top:4px; color:#62738b; font-size:8px; font-weight:750; }
        .os-progress { height:4px; margin-top:8px; border-radius:999px; overflow:hidden; background:#17253a; }
        .os-progress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#7c3aed,#a78bfa); }
        .os-zatca-mini-logo { width:82px; height:28px; object-fit:contain; object-position:center; border-radius:5px; opacity:.95; }
        .os-zatca-fallback { display:none; color:#d8fff0; font-size:10px; font-weight:950; direction:ltr; }
        .os-mode-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:999px; border:1px solid; font-size:8px; font-weight:900; direction:ltr; }
        .os-mode-pill::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
        .os-mode-pill.simulation { color:#93c5fd; border-color:rgba(56,189,248,.24); background:rgba(56,189,248,.05); }
        .os-mode-pill.compliance { color:#86efac; border-color:rgba(52,211,153,.24); background:rgba(52,211,153,.05); }
        .os-mode-pill.production { color:#fda4af; border-color:rgba(251,113,133,.25); background:rgba(251,113,133,.05); }

        .os-shell { overflow:hidden; border:1px solid #293b56; border-radius:18px; background:linear-gradient(145deg,#0b1524,#091321); box-shadow:0 18px 45px rgba(0,0,0,.15); }
        .os-shell-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:15px 18px; border-bottom:1px solid #1d2d44; background:linear-gradient(90deg,rgba(139,92,246,.035),transparent 44%); }
        .os-shell-title { color:#f3f6fb; font-size:13px; font-weight:950; }
        .os-shell-sub { margin-top:3px; color:#61738c; font-size:8px; }
        .os-env-mini { display:flex; gap:5px; flex-wrap:wrap; direction:ltr; }
        .os-env-mini span { padding:4px 7px; border-radius:999px; border:1px solid #243650; color:#647690; font-size:7px; font-weight:950; letter-spacing:.04em; }
        .os-env-mini span.active { color:#c4b5fd; border-color:rgba(139,92,246,.35); background:rgba(139,92,246,.07); }
        .os-form-wrap { padding:15px 17px 94px; }

        /* Filament form refinement */
        .orion-settings .fi-tabs { background:transparent !important; box-shadow:none !important; border:0 !important; }
        .orion-settings .fi-tabs-tab-list { padding:5px !important; gap:4px !important; border:1px solid #22334c !important; border-radius:12px !important; background:#091321 !important; margin-bottom:14px !important; }
        .orion-settings .fi-tabs-item { min-height:40px !important; padding-inline:14px !important; border-radius:8px !important; color:#8192aa !important; font-size:10px !important; font-weight:900 !important; transition:.16s ease !important; }
        .orion-settings .fi-tabs-item svg { width:16px !important; height:16px !important; color:#6d7f98 !important; }
        .orion-settings .fi-tabs-item[aria-selected="true"] { color:#ded7ff !important; background:linear-gradient(135deg,rgba(139,92,246,.16),rgba(109,40,217,.07)) !important; box-shadow:inset 0 0 0 1px rgba(139,92,246,.18) !important; }
        .orion-settings .fi-tabs-item[aria-selected="true"] svg { color:#a78bfa !important; }
        .orion-settings .fi-section { background:linear-gradient(145deg,#0e192a,#0b1525) !important; border:1px solid #22344d !important; border-radius:14px !important; box-shadow:none !important; overflow:hidden !important; }
        .orion-settings .fi-section + .fi-section { margin-top:11px !important; }
        .orion-settings .fi-section-header { border-bottom:1px solid #1c2c43 !important; background:rgba(255,255,255,.008) !important; padding-block:13px !important; }
        .orion-settings .fi-section-header-heading { color:#f1f5f9 !important; font-size:12px !important; font-weight:950 !important; }
        .orion-settings .fi-section-header-description { color:#667892 !important; font-size:8px !important; line-height:1.8 !important; }
        .orion-settings .fi-fo-field-wrp-label span { color:#ced8e7 !important; font-size:9px !important; font-weight:850 !important; }
        .orion-settings .fi-fo-field-wrp-helper-text { color:#667892 !important; font-size:8px !important; line-height:1.7 !important; }
        .orion-settings .fi-input-wrp { min-height:40px !important; background:#111c2f !important; border-color:#30415b !important; border-radius:9px !important; box-shadow:none !important; transition:.16s ease !important; }
        .orion-settings .fi-input-wrp:hover { border-color:#3a4c68 !important; }
        .orion-settings .fi-input-wrp:focus-within { border-color:#8b5cf6 !important; box-shadow:0 0 0 1px rgba(139,92,246,.20),0 0 22px rgba(139,92,246,.045) !important; }
        .orion-settings input, .orion-settings select { color:#edf2f8 !important; font-size:10px !important; }
        .orion-settings .fi-fo-placeholder { color:#d8e1ed !important; }

        /* ZATCA custom brand panel rendered inside the ZATCA tab */
        .orion-settings .fi-fo-field-wrp:has(.zatca-brand-card),
        .orion-settings .fi-fo-field-wrp:has(.zatca-status-grid) { margin:0 !important; }
        .orion-settings .fi-fo-field-wrp:has(.zatca-brand-card) > div > div:first-child,
        .orion-settings .fi-fo-field-wrp:has(.zatca-status-grid) > div > div:first-child { display:none !important; }
        .zatca-brand-card {
            position:relative;
            overflow:hidden;
            min-height:148px;
            display:grid;
            grid-template-columns:minmax(0,1fr) 250px;
            gap:20px;
            align-items:center;
            margin-bottom:12px;
            padding:20px 22px;
            border:1px solid rgba(52,211,153,.22);
            border-radius:16px;
            background:
                radial-gradient(circle at 86% 14%,rgba(52,211,153,.11),transparent 34%),
                linear-gradient(145deg,#0d1e25,#0b1825 52%,#0a1422);
            box-shadow:inset 0 1px rgba(255,255,255,.025);
        }
        .zatca-brand-card::after { content:''; position:absolute; inset:auto 0 0 0; height:2px; background:linear-gradient(90deg,transparent,#34d399,transparent); opacity:.55; }
        .zatca-brand-kicker { direction:ltr; text-align:right; color:#56c99a; font-size:8px; font-weight:950; letter-spacing:.13em; }
        .zatca-brand-title { margin-top:6px; color:#f2fbf7; font-size:18px; font-weight:950; }
        .zatca-brand-sub { max-width:620px; margin-top:6px; color:#78938a; font-size:9px; line-height:1.8; }
        .zatca-brand-meta { display:flex; gap:7px; flex-wrap:wrap; margin-top:13px; }
        .zatca-env-tag,.zatca-readiness-tag { padding:5px 9px; border-radius:999px; font-size:8px; font-weight:900; }
        .zatca-env-tag { direction:ltr; color:#c4b5fd; border:1px solid rgba(139,92,246,.25); background:rgba(139,92,246,.06); }
        .zatca-readiness-tag { color:#7ce2b5; border:1px solid rgba(52,211,153,.22); background:rgba(52,211,153,.05); }
        .zatca-logo-wrap { min-height:92px; display:flex; align-items:center; justify-content:center; padding:10px 14px; border-radius:13px; border:1px solid rgba(255,255,255,.07); background:rgba(3,11,18,.34); }
        .zatca-official-logo { display:block; max-width:210px; width:100%; max-height:72px; object-fit:contain; border-radius:6px; }
        .zatca-logo-fallback { direction:ltr; text-align:center; color:#ecfff6; font-size:25px; font-weight:950; letter-spacing:.08em; }
        .zatca-logo-fallback div { direction:rtl; margin-top:5px; color:#7da091; font-size:9px; letter-spacing:0; }

        .zatca-status-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:8px; width:100%; }
        .zatca-status-item { position:relative; min-height:94px; padding:11px; border:1px solid #263851; border-radius:11px; background:#0c1626; }
        .zatca-status-item.ready { border-color:rgba(52,211,153,.18); background:linear-gradient(145deg,rgba(52,211,153,.045),#0c1626); }
        .zatca-status-item.missing { border-color:rgba(251,191,36,.18); background:linear-gradient(145deg,rgba(251,191,36,.035),#0c1626); }
        .zatca-status-top { display:flex; align-items:center; gap:6px; }
        .zatca-status-dot { width:6px; height:6px; border-radius:50%; background:#fbbf24; box-shadow:0 0 8px rgba(251,191,36,.38); }
        .zatca-status-item.ready .zatca-status-dot { background:#34d399; box-shadow:0 0 8px rgba(52,211,153,.45); }
        .zatca-status-state { color:#c99e40; font-size:7px; font-weight:950; }
        .zatca-status-item.ready .zatca-status-state { color:#63d7a7; }
        .zatca-status-name { margin-top:9px; color:#e7edf6; font-size:10px; font-weight:950; }
        .zatca-status-detail { margin-top:4px; color:#64768e; font-size:7px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; direction:ltr; text-align:right; }

        .os-production-warning { display:flex; gap:10px; align-items:flex-start; margin-top:12px; padding:11px 13px; border-radius:11px; border:1px solid rgba(251,113,133,.22); background:rgba(251,113,133,.05); color:#fca5b2; font-size:9px; line-height:1.8; }
        .os-production-warning .warning-line { width:3px; min-height:38px; border-radius:99px; background:#fb7185; box-shadow:0 0 14px rgba(251,113,133,.28); }
        .os-production-warning strong { display:block; color:#fecdd3; font-size:9px; }
        .os-production-warning span { display:block; margin-top:2px; color:#af7882; }

        .os-savebar { position:sticky; bottom:12px; z-index:30; display:flex; align-items:center; justify-content:space-between; gap:14px; margin:-72px 15px 15px; padding:10px 11px 10px 14px; border-radius:13px; background:rgba(8,17,30,.95); border:1px solid #32445f; box-shadow:0 20px 60px rgba(0,0,0,.38); backdrop-filter:blur(14px); }
        .os-save-note { display:flex; align-items:center; gap:9px; color:#7e8fa7; font-size:9px; }
        .os-save-note i { width:6px; height:6px; border-radius:50%; background:#8b5cf6; box-shadow:0 0 12px rgba(139,92,246,.55); }
        .os-save-actions { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
        .os-btn { min-height:38px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:0 13px; border-radius:9px; border:1px solid #2c3d57; background:#0e1829; color:#b9c5d6; font-size:9px; font-weight:900; cursor:pointer; transition:.16s ease; }
        .os-btn svg { width:14px; height:14px; }
        .os-btn:hover { color:#fff; border-color:#6d5ee7; transform:translateY(-1px); }
        .os-btn.check { color:#7dd3fc; border-color:rgba(56,189,248,.26); background:rgba(56,189,248,.045); }
        .os-btn.primary { min-width:155px; color:#fff; border-color:#8b5cf6; background:linear-gradient(135deg,#8b5cf6,#6d28d9); box-shadow:0 10px 24px rgba(109,40,217,.23); }
        .os-btn:disabled { opacity:.5; cursor:wait; transform:none; }

        @media(max-width:1200px){
            .os-hero { grid-template-columns:1fr; }
            .os-control-panel { margin-top:0; }
            .os-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .zatca-status-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
        }
        @media(max-width:760px){
            .os-hero-main { min-height:auto; padding:18px; align-items:flex-start; }
            .os-control-panel { margin:0 12px 12px; }
            .os-title { font-size:24px; }
            .os-kpis { grid-template-columns:1fr; }
            .os-shell-head { flex-direction:column; align-items:stretch; }
            .os-form-wrap { padding:12px 12px 108px; }
            .zatca-brand-card { grid-template-columns:1fr; }
            .zatca-logo-wrap { order:-1; min-height:76px; }
            .zatca-status-grid { grid-template-columns:1fr 1fr; }
            .os-savebar { align-items:stretch; flex-direction:column; }
            .os-save-actions { width:100%; }
            .os-save-actions .os-btn { flex:1; }
        }
    </style>

    <div class="orion-settings">
        <section class="os-hero">
            <div class="os-hero-main">
                <div class="os-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
                        <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.04 1.55V20.3h-3v-.09a1.7 1.7 0 0 0-1.04-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7 15a1.7 1.7 0 0 0-1.55-1.04H5.3v-3h.15A1.7 1.7 0 0 0 7 9.92a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.12-2.12.06.06A1.7 1.7 0 0 0 10.66 6a1.7 1.7 0 0 0 1.04-1.55V4.3h3v.15A1.7 1.7 0 0 0 15.74 6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06a1.7 1.7 0 0 0-.34 1.88c.26.64.88 1.05 1.57 1.05h.13v3h-.13c-.69 0-1.31.41-1.57 1.05Z"/>
                    </svg>
                </div>
                <div>
                    <div class="os-kicker">ORION SYSTEM CONTROL CENTER</div>
                    <h1 class="os-title">إعدادات النظام</h1>
                    <div class="os-subtitle">مركز موحد لإدارة هوية المنشأة، العنوان الوطني، تكامل الفوترة الإلكترونية مع زاتكا، وإعدادات العرض المالي.</div>
                    <div class="os-hero-tags">
                        <span class="os-hero-tag"><span class="dot"></span><b>ONLINE</b> حالة النظام</span>
                        <span class="os-hero-tag"><b>{{ $health['company_completion'] }}%</b> اكتمال ملف المنشأة</span>
                        <span class="os-hero-tag"><b>{{ $health['country_label'] }}</b> النطاق التشغيلي</span>
                    </div>
                </div>
            </div>

            <aside class="os-control-panel">
                <div class="os-control-head">
                    <div class="os-control-label">SYSTEM HEALTH</div>
                    <div class="os-control-status"><i></i> الحالة مستقرة</div>
                </div>
                <div class="os-control-grid">
                    <div class="os-control-cell">
                        <small>بيئة زاتكا</small>
                        <strong>{{ $health['zatca_mode_label'] }}</strong>
                    </div>
                    <div class="os-control-cell">
                        <small>جاهزية الربط</small>
                        <strong>{{ $health['zatca_readiness'] }}%</strong>
                        <div class="os-control-progress"><span style="width:{{ $health['zatca_readiness'] }}%"></span></div>
                    </div>
                    <div class="os-control-cell">
                        <small>العملة</small>
                        <strong>{{ $health['currency_code'] }}</strong>
                    </div>
                    <div class="os-control-cell">
                        <small>آخر قراءة</small>
                        <strong>{{ now()->format('H:i') }}</strong>
                    </div>
                </div>
            </aside>
        </section>

        <div class="os-kpis">
            <div class="os-kpi company">
                <div class="os-kpi-top">
                    <div class="os-kpi-label">اكتمال بيانات الشركة</div>
                    <div class="os-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M8 9h1m3 0h1m3 0h1M8 12h1m3 0h1m3 0h1"/></svg>
                    </div>
                </div>
                <div class="os-kpi-value">{{ $health['company_completion'] }}%</div>
                <div class="os-progress"><span style="width:{{ $health['company_completion'] }}%"></span></div>
                <div class="os-kpi-note">الهوية القانونية وبيانات التواصل</div>
            </div>

            <div class="os-kpi address">
                <div class="os-kpi-top">
                    <div class="os-kpi-label">العنوان الوطني</div>
                    <div class="os-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    </div>
                </div>
                <div class="os-kpi-value">{{ $health['address'] ?: 'غير مكتمل' }}</div>
                <div class="os-kpi-note">العنوان الرسمي المستخدم في المستندات</div>
            </div>

            <div class="os-kpi zatca">
                <div class="os-kpi-top">
                    <div class="os-kpi-label">تكامل زاتكا</div>
                    <div style="min-width:82px;display:flex;justify-content:flex-end">
                        <img src="{{ $zatcaLogo }}" class="os-zatca-mini-logo" alt="ZATCA" referrerpolicy="no-referrer" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                        <span class="os-zatca-fallback">ZATCA</span>
                    </div>
                </div>
                <div class="os-kpi-value" style="color:#7ce2b5">{{ $health['zatca_readiness'] }}% جاهز</div>
                <div class="os-kpi-note"><span class="os-mode-pill {{ $modeClass }}">{{ strtoupper($health['zatca_mode_label']) }}</span></div>
            </div>

            <div class="os-kpi currency">
                <div class="os-kpi-top">
                    <div class="os-kpi-label">العملة الأساسية</div>
                    <div class="os-kpi-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 14h.01M17 10h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                    </div>
                </div>
                <div class="os-kpi-value" style="color:#f8d477">{{ $health['currency_code'] }}</div>
                <div class="os-kpi-note">12,450.75 {{ $health['currency_symbol'] }}</div>
            </div>
        </div>

        <form wire:submit="save">
            <div class="os-shell">
                <div class="os-shell-head">
                    <div>
                        <div class="os-shell-title">إدارة إعدادات Orion ERP</div>
                        <div class="os-shell-sub">التغييرات لا تعتمد نهائيًا إلا بعد الضغط على حفظ جميع التغييرات.</div>
                    </div>
                    <div class="os-env-mini">
                        <span class="{{ $mode === 'simulation' ? 'active' : '' }}">SIMULATION</span>
                        <span class="{{ $mode === 'compliance' ? 'active' : '' }}">COMPLIANCE</span>
                        <span class="{{ $mode === 'production' ? 'active' : '' }}">PRODUCTION</span>
                    </div>
                </div>

                <div class="os-form-wrap">
                    {{ $this->form }}

                    @if(($this->data['zatca_sending_mode'] ?? 'simulation') === 'production')
                        <div class="os-production-warning">
                            <span class="warning-line"></span>
                            <div>
                                <strong>تنبيه البيئة الإنتاجية</strong>
                                <span>أنت تستخدم ZATCA Production. راجع الرقم الضريبي والشهادة والمفتاح وSecret قبل إرسال أي فاتورة فعلية.</span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="os-savebar">
                    <div class="os-save-note"><i></i><span>راجع القيم الحساسة، خصوصًا إعدادات زاتكا، قبل اعتماد التغييرات.</span></div>
                    <div class="os-save-actions">
                        <button type="button" class="os-btn" wire:click="reloadSettings" wire:loading.attr="disabled">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 6 6v1"/></svg>
                            إلغاء التغييرات
                        </button>
                        <button type="button" class="os-btn check" wire:click="checkZatcaSetup" wire:loading.attr="disabled" wire:target="checkZatcaSetup">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 2.8 8.3 7 10 4.2-1.7 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                            فحص إعدادات زاتكا
                        </button>
                        <button type="submit" class="os-btn primary" wire:loading.attr="disabled" wire:target="save">
                            <svg wire:loading.remove wire:target="save" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h12l2 2v14H5z"/><path d="M8 4v6h8V4M8 20v-6h8v6"/></svg>
                            <span wire:loading.remove wire:target="save">حفظ جميع التغييرات</span>
                            <span wire:loading wire:target="save">جارٍ الحفظ...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
