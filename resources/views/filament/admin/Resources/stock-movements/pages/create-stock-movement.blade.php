<x-filament-panels::page>
    <style>
        .orion-stock-adjustment{
            direction:rtl;
            --osa-panel:#0d1728;
            --osa-panel-2:#111c30;
            --osa-border:#263853;
            --osa-border-soft:#1a2940;
            --osa-text:#f8fafc;
            --osa-muted:#8091aa;
            --osa-purple:#8b5cf6;
            --osa-green:#34d399;
            --osa-amber:#fbbf24;
            --osa-red:#f87171;
            --osa-blue:#60a5fa;
        }
        .orion-stock-adjustment *{box-sizing:border-box}
        .osa-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px}
        .osa-step{position:relative;overflow:hidden;min-height:78px;padding:13px 15px;border:1px solid var(--osa-border);border-radius:14px;background:linear-gradient(145deg,var(--osa-panel-2),#0b1525)}
        .osa-step::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.75}
        .osa-step-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .osa-step-title{color:#dce5f1;font-size:10px;font-weight:900}
        .osa-step-no{width:25px;height:25px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid color-mix(in srgb,var(--accent) 35%,#31435f);background:color-mix(in srgb,var(--accent) 8%,#0f192b);color:var(--accent);font-size:9px;font-weight:950}
        .osa-step-note{margin-top:7px;color:#687a93;font-size:8px;line-height:1.7}

        .osa-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px;align-items:start}
        .osa-shell,.osa-side-card{border:1px solid var(--osa-border);border-radius:16px;background:linear-gradient(145deg,#0d1728,#091321);box-shadow:0 16px 38px rgba(0,0,0,.14)}
        .osa-shell{overflow:hidden}
        .osa-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 17px;border-bottom:1px solid var(--osa-border-soft);background:#0f192b}
        .osa-title{font-size:15px;font-weight:950;color:var(--osa-text)}
        .osa-sub{margin-top:3px;font-size:9px;color:#6b7d96}
        .osa-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(251,191,36,.18);border-radius:999px;background:rgba(251,191,36,.055);color:#fbd27a;font-size:9px;font-weight:900}
        .osa-status::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--osa-amber)}
        .osa-form{padding:16px}
        .osa-actions{margin-top:16px;padding-top:14px;border-top:1px solid var(--osa-border-soft)}

        .osa-side{display:grid;gap:12px;position:sticky;top:16px}
        .osa-side-card{padding:15px}
        .osa-kicker{font-size:8px;letter-spacing:.08em;color:#7d6ab0;font-weight:950;direction:ltr;text-align:right}
        .osa-side-title{margin-top:5px;color:#f5f8fc;font-size:14px;font-weight:950}
        .osa-side-text{margin-top:8px;color:#71829b;font-size:9px;line-height:1.8}
        .osa-rule{display:flex;gap:8px;padding:10px 0;border-bottom:1px solid #17243a}
        .osa-rule:last-child{border-bottom:0}
        .osa-dot{width:7px;height:7px;margin-top:5px;border-radius:50%;background:var(--dot);flex:0 0 auto}
        .osa-rule strong{display:block;color:#dce5f1;font-size:9px}.osa-rule span{display:block;margin-top:2px;color:#687990;font-size:8px;line-height:1.6}
        .osa-warning{border-color:rgba(248,113,113,.20);background:linear-gradient(145deg,rgba(248,113,113,.06),#0b1525)}
        .osa-warning strong{color:#fecaca;font-size:10px}.osa-warning p{margin:7px 0 0;color:#9e7b83;font-size:8px;line-height:1.8}

        .orion-stock-adjustment .fi-sc-section{border:1px solid #22334d!important;border-radius:13px!important;background:#0f192b!important;box-shadow:none!important;overflow:hidden;margin-bottom:12px!important}
        .orion-stock-adjustment .fi-sc-section-header{background:#111c30!important;border-bottom:1px solid #1e2d45!important;padding:12px 14px!important}
        .orion-stock-adjustment .fi-sc-section-header-heading{color:#f3f6fb!important;font-size:11px!important;font-weight:950!important}
        .orion-stock-adjustment .fi-sc-section-header-description{color:#71829a!important;font-size:8px!important}
        .orion-stock-adjustment .fi-sc-section-content{padding:14px!important}
        .orion-stock-adjustment .fi-fo-field-wrp-label span{font-size:9px!important;font-weight:850!important;color:#9fafc3!important}
        .orion-stock-adjustment .fi-input-wrp{border-color:#2a3b56!important;background:#0b1525!important;border-radius:9px!important;box-shadow:none!important}
        .orion-stock-adjustment .fi-input-wrp:focus-within{border-color:var(--osa-purple)!important;box-shadow:0 0 0 1px rgba(139,92,246,.18)!important}
        .orion-stock-adjustment .fi-input,.orion-stock-adjustment .fi-select-input{font-size:10px!important;color:#eef3f8!important}
        .orion-stock-adjustment .fi-fo-field-wrp-helper-text{font-size:8px!important;color:#60718a!important}

        @media(max-width:1000px){.osa-layout{grid-template-columns:1fr}.osa-side{position:static;grid-template-columns:repeat(2,1fr)}}
        @media(max-width:700px){.osa-steps,.osa-side{grid-template-columns:1fr}.osa-head{align-items:flex-start;flex-direction:column}}
    </style>

    <div class="orion-stock-adjustment">
        <div class="osa-steps">
            <div class="osa-step" style="--accent:var(--osa-blue)">
                <div class="osa-step-top"><span class="osa-step-title">حدد الصنف والمخزن</span><span class="osa-step-no">01</span></div>
                <div class="osa-step-note">اختار الصنف الذي ستطبق عليه التسوية والمخزن المرتبط بالحركة.</div>
            </div>
            <div class="osa-step" style="--accent:var(--osa-amber)">
                <div class="osa-step-top"><span class="osa-step-title">حدد نوع وكمية التسوية</span><span class="osa-step-no">02</span></div>
                <div class="osa-step-note">زيادة أو نقص أو رصيد افتتاحي مع كمية أكبر من صفر.</div>
            </div>
            <div class="osa-step" style="--accent:var(--osa-purple)">
                <div class="osa-step-top"><span class="osa-step-title">سجل المرجع والسبب</span><span class="osa-step-no">03</span></div>
                <div class="osa-step-note">مرجع واضح وملاحظة جيدة يجعلوا الحركة قابلة للمراجعة لاحقاً.</div>
            </div>
        </div>

        <div class="osa-layout">
            <div class="osa-shell">
                <div class="osa-head">
                    <div>
                        <div class="osa-title">تسجيل تسوية مخزون</div>
                        <div class="osa-sub">أدخل بيانات الحركة ثم راجعها قبل الحفظ</div>
                    </div>
                    <span class="osa-status">حركة يدوية</span>
                </div>

                <form wire:submit="create" class="osa-form">
                    {{ $this->form }}
                    <div class="osa-actions">
                        <x-filament::actions :actions="$this->getFormActions()" />
                    </div>
                </form>
            </div>

            <aside class="osa-side">
                <div class="osa-side-card">
                    <div class="osa-kicker">ADJUSTMENT CHECKLIST</div>
                    <div class="osa-side-title">قبل الحفظ</div>
                    <div class="osa-side-text">التسويات اليدوية لازم تكون واضحة لأنها بتدخل في سجل حركة المخزون.</div>

                    <div style="margin-top:10px;border-top:1px solid #1b2a41">
                        <div class="osa-rule"><span class="osa-dot" style="--dot:var(--osa-blue)"></span><div><strong>الصنف الصحيح</strong><span>راجع الاسم والكود قبل تسجيل الحركة.</span></div></div>
                        <div class="osa-rule"><span class="osa-dot" style="--dot:var(--osa-green)"></span><div><strong>الكمية أكبر من صفر</strong><span>نوع الحركة هو اللي يحدد زيادة أو نقص.</span></div></div>
                        <div class="osa-rule"><span class="osa-dot" style="--dot:var(--osa-amber)"></span><div><strong>مرجع للمراجعة</strong><span>اكتب رقم محضر، مستند أو مرجع داخلي.</span></div></div>
                    </div>
                </div>

                <div class="osa-side-card osa-warning">
                    <strong>تنبيه</strong>
                    <p>استخدم التسوية اليدوية فقط عند الحاجة. حركات البيع والشراء والتحويلات الأفضل تظل ناتجة من مستنداتها الأصلية حتى يفضل الـAudit Trail واضح.</p>
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
