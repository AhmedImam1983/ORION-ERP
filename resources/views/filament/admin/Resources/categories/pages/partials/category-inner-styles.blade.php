<style>
    .orion-category-inner,
    .orion-category-inner * { box-sizing:border-box; font-family:'Cairo',sans-serif; }

    .orion-category-inner {
        --oci-panel:#0c1728;
        --oci-panel-2:#0f1b2e;
        --oci-border:#21324d;
        --oci-border-soft:#17263c;
        --oci-text:#eef3fb;
        --oci-muted:#71839e;
        --oci-purple:#8b5cf6;
        --oci-green:#34d399;
        --oci-red:#fb7185;
        --oci-amber:#fbbf24;
        direction:rtl;
        color:var(--oci-text);
        width:100%;
        padding-bottom:24px;
    }

    /* Make the Filament page header feel like Orion instead of stock Filament */
    .fi-header { margin-bottom:14px !important; }
    .fi-header-heading { color:#f3f6fb !important; font-weight:950 !important; letter-spacing:-.2px; }
    .fi-breadcrumbs { color:#697b96 !important; }

    .oci-hero {
        position:relative;
        overflow:hidden;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:18px;
        min-height:112px;
        padding:17px 19px;
        margin-bottom:13px;
        border:1px solid var(--oci-border);
        border-radius:15px;
        background:
            radial-gradient(circle at 8% 10%,rgba(139,92,246,.16),transparent 28%),
            linear-gradient(145deg,#0e192b,#0a1423);
        box-shadow:0 18px 45px rgba(0,0,0,.12);
    }

    .oci-hero-main { display:flex;align-items:center;gap:13px;min-width:0; }
    .oci-icon {
        width:48px;height:48px;border-radius:13px;flex:none;
        display:grid;place-items:center;color:#c3b2ff;
        border:1px solid rgba(139,92,246,.38);
        background:linear-gradient(145deg,rgba(124,58,237,.28),rgba(76,29,149,.12));
        box-shadow:0 10px 25px rgba(76,29,149,.16);
    }
    .oci-icon svg { width:22px;height:22px; }
    .oci-eyebrow { color:#8e7be8;font-size:8px;font-weight:950;letter-spacing:1.3px;margin-bottom:4px; }
    .oci-title { color:#f5f7fb;font-size:18px;font-weight:950;line-height:1.25; }
    .oci-sub { color:var(--oci-muted);font-size:9px;font-weight:650;margin-top:5px; }

    .oci-chips { display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap; }
    .oci-chip {
        display:inline-flex;align-items:center;gap:6px;min-height:30px;padding:0 10px;
        border:1px solid #263a58;border-radius:9px;background:#0c1829;
        color:#9aabc0;font-size:8px;font-weight:900;white-space:nowrap;
    }
    .oci-chip strong { color:#e4eaf3;font-size:9px;direction:ltr; }
    .oci-chip.green { border-color:rgba(52,211,153,.23);background:rgba(52,211,153,.055);color:#6eddb8; }
    .oci-chip.gray { color:#8290a6; }
    .oci-dot { width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 9px currentColor; }

    .oci-content .fi-section {
        overflow:hidden !important;
        border:1px solid var(--oci-border) !important;
        border-radius:14px !important;
        background:linear-gradient(145deg,#0c1728,#091421) !important;
        box-shadow:none !important;
    }
    .oci-content .fi-section + .fi-section { margin-top:11px !important; }
    .oci-content .fi-section-header { border-bottom:1px solid var(--oci-border-soft) !important; background:rgba(255,255,255,.012) !important; }
    .oci-content .fi-section-header-heading { color:#e8edf5 !important;font-weight:950 !important; }
    .oci-content .fi-section-header-description { color:#657792 !important;font-size:10px !important; }

    .oci-content .fi-input-wrp,
    .oci-content .fi-fo-file-upload-input-ctn {
        border-color:#263a59 !important;
        background:#0d192b !important;
        box-shadow:none !important;
    }
    .oci-content .fi-input,
    .oci-content textarea { color:#e1e8f2 !important; }
    .oci-content .fi-fo-field-wrp-label { color:#9aa9bd !important;font-weight:800 !important; }
    .oci-content .fi-fo-field-wrp-helper-text { color:#596c87 !important; }

    /* Infolist readability */
    .oci-content .fi-in-entry { min-height:62px; }
    .oci-content .fi-in-entry-label { color:#667a95 !important;font-size:9px !important;font-weight:850 !important; }
    .oci-content .fi-in-text { color:#e4eaf3 !important;font-weight:750 !important; }

    /* Form footer / actions */
    .oci-content .fi-ac-btn-action { border-radius:9px !important; }

    @media (max-width:850px) {
        .oci-hero { align-items:flex-start;flex-direction:column; }
        .oci-chips { justify-content:flex-start; }
    }
</style>
