<style>
    .orion-employee-inner,
    .orion-employee-inner * { box-sizing:border-box;font-family:'Cairo',sans-serif; }

    .orion-employee-inner {
        --oei-panel:#0c1728;--oei-panel2:#0f1b2e;--oei-border:#21324d;--oei-soft:#17263c;
        --oei-text:#eef3fb;--oei-muted:#71839e;--oei-purple:#8b5cf6;--oei-green:#34d399;
        --oei-red:#fb7185;--oei-amber:#fbbf24;--oei-blue:#60a5fa;
        direction:rtl;color:var(--oei-text);width:100%;padding-bottom:24px;
    }

    .fi-header { margin-bottom:14px !important; }
    .fi-header-heading { color:#f3f6fb !important;font-weight:950 !important; }
    .fi-breadcrumbs { color:#697b96 !important; }

    .oei-hero {
        position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;
        min-height:120px;padding:18px 19px;margin-bottom:13px;border:1px solid var(--oei-border);border-radius:15px;
        background:radial-gradient(circle at 7% 5%,rgba(139,92,246,.18),transparent 30%),
                   radial-gradient(circle at 92% 110%,rgba(52,211,153,.07),transparent 32%),
                   linear-gradient(145deg,#0e192b,#0a1423);
        box-shadow:0 18px 45px rgba(0,0,0,.12);
    }
    .oei-hero-main{display:flex;align-items:center;gap:13px;min-width:0}.oei-avatar,.oei-icon{width:52px;height:52px;border-radius:15px;flex:none;display:grid;place-items:center;border:1px solid rgba(139,92,246,.42);background:linear-gradient(145deg,rgba(124,58,237,.32),rgba(45,27,91,.2));box-shadow:0 12px 30px rgba(76,29,149,.18);color:#d2c8ff}.oei-avatar{font-size:19px;font-weight:950;text-transform:uppercase}.oei-icon svg{width:24px;height:24px}.oei-eyebrow{color:#8e7be8;font-size:8px;font-weight:950;letter-spacing:1.35px;margin-bottom:4px}.oei-title{color:#f5f7fb;font-size:18px;font-weight:950;line-height:1.25}.oei-sub{color:var(--oei-muted);font-size:9px;font-weight:650;margin-top:5px}.oei-chips{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap}.oei-chip{display:inline-flex;align-items:center;gap:6px;min-height:30px;padding:0 10px;border:1px solid #263a58;border-radius:9px;background:#0c1829;color:#9aabc0;font-size:8px;font-weight:900;white-space:nowrap}.oei-chip strong{color:#e4eaf3;font-size:9px;direction:ltr}.oei-chip.green{border-color:rgba(52,211,153,.23);background:rgba(52,211,153,.055);color:#6eddb8}.oei-chip.amber{border-color:rgba(251,191,36,.22);background:rgba(251,191,36,.05);color:#f1c85e}.oei-chip.blue{border-color:rgba(96,165,250,.22);background:rgba(96,165,250,.05);color:#79b6ff}.oei-chip.red{border-color:rgba(251,113,133,.22);background:rgba(251,113,133,.05);color:#ff8798}.oei-dot{width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 9px currentColor}

    .oei-content .fi-section{overflow:hidden!important;border:1px solid var(--oei-border)!important;border-radius:14px!important;background:linear-gradient(145deg,#0c1728,#091421)!important;box-shadow:none!important}.oei-content .fi-section+.fi-section{margin-top:11px!important}.oei-content .fi-section-header{border-bottom:1px solid var(--oei-soft)!important;background:rgba(255,255,255,.012)!important}.oei-content .fi-section-header-heading{color:#e8edf5!important;font-weight:950!important}.oei-content .fi-section-header-description{color:#657792!important;font-size:10px!important}.oei-content .fi-input-wrp,.oei-content .fi-select-input,.oei-content .fi-fo-file-upload-input-ctn{border-color:#263a59!important;background:#0d192b!important;box-shadow:none!important}.oei-content .fi-input,.oei-content textarea{color:#e1e8f2!important}.oei-content .fi-fo-field-wrp-label{color:#9aa9bd!important;font-weight:800!important}.oei-content .fi-fo-field-wrp-helper-text{color:#596c87!important}.oei-content .fi-in-entry{min-height:62px}.oei-content .fi-in-entry-label{color:#667a95!important;font-size:9px!important;font-weight:850!important}.oei-content .fi-in-text{color:#e4eaf3!important;font-weight:750!important}.oei-content .fi-ac-btn-action{border-radius:9px!important}

    @media(max-width:850px){.oei-hero{align-items:flex-start;flex-direction:column}.oei-chips{justify-content:flex-start}}
</style>
