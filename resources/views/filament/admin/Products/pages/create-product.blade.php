<x-filament-panels::page>
    <style>
        .orion-product-form {
            direction: rtl;
            --opf-panel:#0d1728;
            --opf-panel-2:#111c30;
            --opf-border:#263853;
            --opf-border-soft:#1b2a41;
            --opf-text:#f8fafc;
            --opf-muted:#8091aa;
            --opf-purple:#8b5cf6;
            --opf-green:#34d399;
            --opf-amber:#fbbf24;
            --opf-blue:#60a5fa;
        }
        .orion-product-form *{box-sizing:border-box}

        .opf-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}
        .opf-step{position:relative;overflow:hidden;min-height:82px;padding:13px 15px;border:1px solid var(--opf-border);border-radius:14px;background:linear-gradient(145deg,var(--opf-panel-2),#0b1525)}
        .opf-step::after{content:"";position:absolute;right:0;left:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);opacity:.75}
        .opf-step-top{display:flex;justify-content:space-between;align-items:center;gap:8px}
        .opf-step-no{width:25px;height:25px;display:flex;align-items:center;justify-content:center;border-radius:8px;border:1px solid color-mix(in srgb,var(--accent) 35%,#31435f);background:color-mix(in srgb,var(--accent) 8%,#0f192b);color:var(--accent);font-size:9px;font-weight:950}
        .opf-step-title{color:#dce5f1;font-size:10px;font-weight:900}
        .opf-step-note{margin-top:8px;color:#687a93;font-size:8px;line-height:1.6}

        .opf-layout{display:grid;grid-template-columns:minmax(0,1fr) 285px;gap:14px;align-items:start}
        .opf-shell,.opf-side-card{border:1px solid var(--opf-border);border-radius:16px;background:linear-gradient(145deg,#0d1728,#091321);box-shadow:0 16px 38px rgba(0,0,0,.14)}
        .opf-shell{overflow:hidden}
        .opf-shell-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 17px;border-bottom:1px solid var(--opf-border-soft);background:#0f192b}
        .opf-shell-title{font-size:15px;font-weight:950;color:var(--opf-text)}
        .opf-shell-sub{margin-top:3px;font-size:9px;color:#6b7d96}
        .opf-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid rgba(52,211,153,.18);border-radius:999px;background:rgba(52,211,153,.055);color:#6ee7b7;font-size:9px;font-weight:900}
        .opf-status::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--opf-green)}
        .opf-form{padding:16px}
        .opf-actions{margin-top:16px;padding-top:14px;border-top:1px solid var(--opf-border-soft)}

        .opf-side{display:grid;gap:12px;position:sticky;top:16px}
        .opf-side-card{padding:15px}
        .opf-side-kicker{font-size:8px;letter-spacing:.08em;color:#7d6ab0;font-weight:950;direction:ltr;text-align:right}
        .opf-side-title{margin-top:5px;color:#f5f8fc;font-size:14px;font-weight:950}
        .opf-side-text{margin-top:8px;color:#71829b;font-size:9px;line-height:1.8}
        .opf-checks{margin-top:12px;border-top:1px solid #1b2a41}
        .opf-check{display:flex;align-items:flex-start;gap:8px;padding:10px 0;border-bottom:1px solid #17243a}
        .opf-check:last-child{border-bottom:0}
        .opf-check-dot{width:7px;height:7px;margin-top:5px;border-radius:50%;background:var(--dot);box-shadow:0 0 10px color-mix(in srgb,var(--dot) 45%,transparent);flex:0 0 auto}
        .opf-check strong{display:block;color:#dce5f1;font-size:9px}.opf-check span{display:block;margin-top:2px;color:#687990;font-size:8px;line-height:1.6}
        .opf-tip{border-color:rgba(139,92,246,.24);background:linear-gradient(145deg,rgba(139,92,246,.09),#0b1525)}
        .opf-tip-title{color:#d8ccff;font-size:10px;font-weight:900}.opf-tip p{margin:7px 0 0;color:#8379a5;font-size:8px;line-height:1.8}

        /* Filament form skin only. No custom form APIs. */
        .orion-product-form .fi-sc-section{border:1px solid #22334d!important;border-radius:13px!important;background:#0f192b!important;box-shadow:none!important;overflow:hidden;margin-bottom:12px!important}
        .orion-product-form .fi-sc-section-header{background:#111c30!important;border-bottom:1px solid #1e2d45!important;padding:12px 14px!important}
        .orion-product-form .fi-sc-section-header-heading{color:#f3f6fb!important;font-size:11px!important;font-weight:950!important}
        .orion-product-form .fi-sc-section-header-description{color:#71829a!important;font-size:8px!important}
        .orion-product-form .fi-sc-section-content{padding:14px!important}
        .orion-product-form .fi-fo-field-wrp-label span{font-size:9px!important;font-weight:850!important;color:#9fafc3!important}
        .orion-product-form .fi-input-wrp{border-color:#2a3b56!important;background:#0b1525!important;border-radius:9px!important;box-shadow:none!important}
        .orion-product-form .fi-input-wrp:focus-within{border-color:var(--opf-purple)!important;box-shadow:0 0 0 1px rgba(139,92,246,.18)!important}
        .orion-product-form .fi-input,.orion-product-form .fi-select-input{font-size:10px!important;color:#eef3f8!important}
        .orion-product-form .fi-fo-field-wrp-helper-text{font-size:8px!important;color:#60718a!important}

        @media(max-width:1100px){.opf-layout{grid-template-columns:1fr}.opf-side{position:static;grid-template-columns:repeat(2,1fr)}.opf-steps{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:700px){.opf-steps,.opf-side{grid-template-columns:1fr}.opf-shell-head{align-items:flex-start;flex-direction:column}}
    </style>

    <div class="orion-product-form">
        <div class="opf-steps">
            <div class="opf-step" style="--accent:var(--opf-blue)"><div class="opf-step-top"><span class="opf-step-title">بيانات الصنف</span><span class="opf-step-no">01</span></div><div class="opf-step-note">الكود، الاسم، المجموعة، الوحدة والمخزن الافتراضي</div></div>
            <div class="opf-step" style="--accent:var(--opf-green)"><div class="opf-step-top"><span class="opf-step-title">المخزون والأسعار</span><span class="opf-step-no">02</span></div><div class="opf-step-note">الرصيد الافتتاحي والحالي والحد الأدنى وأسعار البيع</div></div>
            <div class="opf-step" style="--accent:var(--opf-amber)"><div class="opf-step-top"><span class="opf-step-title">الضريبة</span><span class="opf-step-no">03</span></div><div class="opf-step-note">إعداد ضريبة القيمة المضافة وبيانات التكامل الضريبي</div></div>
            <div class="opf-step" style="--accent:var(--opf-purple)"><div class="opf-step-top"><span class="opf-step-title">الصورة والتفعيل</span><span class="opf-step-no">04</span></div><div class="opf-step-note">صورة مناسبة للديمو، الوصف، وحالة الصنف في النظام</div></div>
        </div>

        <div class="opf-layout">
            <div class="opf-shell">
                <div class="opf-shell-head">
                    <div><div class="opf-shell-title">إضافة صنف جديد</div><div class="opf-shell-sub">أدخل بيانات الصنف ثم راجع المخزون والتسعير قبل الحفظ</div></div>
                    <span class="opf-status">جاهز للإدخال</span>
                </div>

                <form wire:submit="create" id="product-create-form" class="opf-form">
                    {{ $this->form }}
                    <div class="opf-actions">
                        <x-filament::actions :actions="$this->getFormActions()" />
                    </div>
                </form>
            </div>

            <aside class="opf-side">
                <div class="opf-side-card">
                    <div class="opf-side-kicker">PRODUCT CHECKLIST</div>
                    <div class="opf-side-title">قبل حفظ الصنف</div>
                    <div class="opf-side-text">خلي بيانات الديمو منطقية ومترابطة عشان شاشة الأصناف والتقارير تبان حقيقية.</div>
                    <div class="opf-checks">
                        <div class="opf-check"><span class="opf-check-dot" style="--dot:var(--opf-blue)"></span><div><strong>كود فريد</strong><span>استخدم نمط ثابت مثل CL-001 أو RS-003.</span></div></div>
                        <div class="opf-check"><span class="opf-check-dot" style="--dot:var(--opf-green)"></span><div><strong>مخزن مناسب</strong><span>اربط الصنف بمخزن افتراضي لتجنب قائمة بدون مخزن.</span></div></div>
                        <div class="opf-check"><span class="opf-check-dot" style="--dot:var(--opf-amber)"></span><div><strong>حد أدنى واقعي</strong><span>يساعد تنبيه انخفاض المخزون يبان بشكل منطقي.</span></div></div>
                        <div class="opf-check"><span class="opf-check-dot" style="--dot:var(--opf-purple)"></span><div><strong>صورة نظيفة</strong><span>صورة Packshot بخلفية بسيطة هتفرق جدًا في الديمو.</span></div></div>
                    </div>
                </div>

                <div class="opf-side-card opf-tip">
                    <div class="opf-tip-title">نصيحة للديمو</div>
                    <p>اختار أسماء وكميات وأسعار متناسقة. لو الصنف إلكتروني خلي الوحدة قطعة، ولو مواد تنظيف ممكن تكون كرتون أو عبوة حسب الاستخدام.</p>
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
