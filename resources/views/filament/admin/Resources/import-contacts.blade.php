<x-filament-panels::page>
<style>
    :root{
        --orion-bg:#080f1b;
        --orion-card:#0c1626;
        --orion-card-2:#0f1b2d;
        --orion-border:rgba(122,145,180,.16);
        --orion-muted:#71829b;
        --orion-text:#e8eef7;
        --orion-purple:#7c4dff;
        --orion-purple-2:#6046d8;
        --orion-green:#22c55e;
    }

    .orion-import-page{
        direction:rtl;
        color:var(--orion-text);
        font-family:'Cairo',sans-serif;
        margin-top:-8px;
    }

    .orion-import-page *{
        font-family:'Cairo',sans-serif;
        box-sizing:border-box;
    }

    /* Page heading */
    .orion-import-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:20px;
        margin-bottom:18px;
    }

    .orion-import-title{
        margin:0;
        color:#fff;
        font-size:27px;
        line-height:1.25;
        font-weight:950;
        letter-spacing:-.4px;
    }

    .orion-import-breadcrumb{
        margin-top:7px;
        color:#687991;
        font-size:12px;
        font-weight:600;
    }

    .orion-import-breadcrumb b{
        color:#9c8cff;
    }

    .orion-status{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:9px 14px;
        border:1px solid rgba(34,197,94,.18);
        border-radius:999px;
        background:rgba(34,197,94,.06);
        color:#69e79b;
        font-size:11px;
        font-weight:800;
        white-space:nowrap;
    }

    .orion-status-dot{
        width:7px;
        height:7px;
        border-radius:50%;
        background:#22c55e;
        box-shadow:0 0 12px rgba(34,197,94,.7);
    }

    /* Stepper */
    .orion-steps{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:0;
        padding:15px 18px;
        margin-bottom:16px;
        border:1px solid var(--orion-border);
        border-radius:16px;
        background:linear-gradient(180deg,#0d1829,#0a1422);
        box-shadow:0 15px 45px rgba(0,0,0,.16);
    }

    .orion-step{
        position:relative;
        display:flex;
        align-items:center;
        gap:11px;
        min-height:50px;
        padding:4px 15px;
        color:#596a82;
    }

    .orion-step:not(:last-child)::after{
        content:"";
        position:absolute;
        left:0;
        top:50%;
        width:1px;
        height:30px;
        transform:translateY(-50%);
        background:rgba(255,255,255,.07);
    }

    .orion-step-number{
        width:34px;
        height:34px;
        flex:0 0 34px;
        display:grid;
        place-items:center;
        border-radius:50%;
        border:1px solid rgba(255,255,255,.10);
        background:#111d2e;
        color:#65758c;
        font-size:13px;
        font-weight:900;
    }

    .orion-step.active{
        color:#e8e0ff;
    }

    .orion-step.active .orion-step-number{
        border-color:transparent;
        background:linear-gradient(135deg,#8b5cf6,#6544dc);
        color:#fff;
        box-shadow:0 0 0 5px rgba(124,77,255,.08),0 8px 20px rgba(124,77,255,.2);
    }

    .orion-step.done .orion-step-number{
        border-color:rgba(34,197,94,.25);
        background:rgba(34,197,94,.10);
        color:#4ade80;
    }

    .orion-step strong{
        display:block;
        color:inherit;
        font-size:12px;
        font-weight:900;
    }

    .orion-step span{
        display:block;
        margin-top:2px;
        color:#65758c;
        font-size:10px;
    }

    /* Customer/supplier tabs */
    .orion-type-tabs{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
        padding:7px;
        margin-bottom:16px;
        border:1px solid var(--orion-border);
        border-radius:14px;
        background:#0a1321;
    }

    .orion-type-btn{
        min-height:52px;
        border:1px solid transparent;
        border-radius:10px;
        background:transparent;
        color:#71829b;
        cursor:pointer;
        font-size:14px;
        font-weight:900;
        transition:.18s ease;
    }

    .orion-type-btn:hover{
        background:rgba(255,255,255,.035);
        color:#d9e1ec;
    }

    .orion-type-btn.customer.active{
        border-color:rgba(124,77,255,.35);
        background:linear-gradient(135deg,rgba(124,77,255,.20),rgba(124,77,255,.08));
        color:#cbbcff;
        box-shadow:inset 0 0 25px rgba(124,77,255,.05);
    }

    .orion-type-btn.supplier.active{
        border-color:rgba(168,85,247,.28);
        background:linear-gradient(135deg,rgba(168,85,247,.17),rgba(168,85,247,.06));
        color:#d8b4fe;
    }

    /* Main layout */
    .orion-main-grid{
        display:grid;
        grid-template-columns:minmax(0,1.5fr) minmax(330px,.85fr);
        gap:16px;
        align-items:stretch;
    }

    .orion-card{
        min-width:0;
        padding:20px;
        border:1px solid var(--orion-border);
        border-radius:18px;
        background:
            radial-gradient(circle at 100% 0,rgba(124,77,255,.055),transparent 28%),
            linear-gradient(180deg,#0d1829,#0a1422);
        box-shadow:0 18px 55px rgba(0,0,0,.18);
    }

    .orion-card-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        margin-bottom:15px;
    }

    .orion-card-title{
        display:flex;
        align-items:center;
        gap:9px;
        color:#f3f6fb;
        font-size:15px;
        font-weight:950;
    }

    .orion-card-number{
        width:27px;
        height:27px;
        display:grid;
        place-items:center;
        border-radius:8px;
        background:rgba(124,77,255,.13);
        color:#a996ff;
        font-size:11px;
        font-weight:950;
    }

    .orion-card-meta{
        color:#5f718a;
        font-size:10px;
        font-weight:700;
    }

    /* Upload */
    .orion-upload{
        min-height:285px;
        display:flex;
        align-items:center;
        justify-content:center;
        border:2px dashed #263b58;
        border-radius:16px;
        background:
            radial-gradient(circle at center,rgba(124,77,255,.075),transparent 42%),
            rgba(4,10,18,.30);
        cursor:pointer;
        transition:.2s ease;
        text-align:center;
    }

    .orion-upload:hover{
        border-color:#7358e9;
        background:
            radial-gradient(circle at center,rgba(124,77,255,.12),transparent 45%),
            rgba(4,10,18,.42);
        transform:translateY(-1px);
    }

    .orion-upload.has-file{
        border-color:rgba(34,197,94,.55);
        background:radial-gradient(circle at center,rgba(34,197,94,.08),transparent 48%);
    }

    .orion-upload-inner{
        padding:20px;
    }

    .orion-upload-icon{
        width:72px;
        height:72px;
        margin:0 auto 14px;
        display:grid;
        place-items:center;
        border-radius:20px;
        border:1px solid rgba(124,77,255,.24);
        background:rgba(124,77,255,.09);
        color:#a996ff;
        font-size:31px;
        box-shadow:0 14px 30px rgba(0,0,0,.16);
    }

    .orion-upload.has-file .orion-upload-icon{
        border-color:rgba(34,197,94,.25);
        background:rgba(34,197,94,.08);
        color:#4ade80;
    }

    .orion-upload-title{
        margin:0;
        color:#eaf0f8;
        font-size:17px;
        font-weight:950;
    }

    .orion-upload-sub{
        margin:7px 0 0;
        color:#687a94;
        font-size:11px;
    }

    .orion-upload-info{
        display:flex;
        justify-content:center;
        flex-wrap:wrap;
        gap:7px;
        margin-top:13px;
    }

    .orion-chip{
        padding:5px 9px;
        border:1px solid rgba(255,255,255,.07);
        border-radius:999px;
        background:rgba(255,255,255,.025);
        color:#6f8099;
        font-size:9px;
        font-weight:700;
    }

    .orion-choose{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        min-width:165px;
        height:42px;
        margin-top:17px;
        border:0;
        border-radius:10px;
        background:linear-gradient(135deg,#8057ff,#6144d9);
        color:#fff;
        font-size:12px;
        font-weight:900;
        box-shadow:0 12px 25px rgba(109,74,255,.18);
    }

    /* Template side */
    .orion-template-card{
        margin-bottom:14px;
        padding:17px;
        border:1px solid rgba(255,255,255,.07);
        border-radius:14px;
        background:rgba(255,255,255,.018);
    }

    .orion-template-card:last-child{
        margin-bottom:0;
    }

    .orion-template-title{
        display:flex;
        align-items:center;
        gap:8px;
        color:#e8eef7;
        font-size:13px;
        font-weight:900;
        margin-bottom:6px;
    }

    .orion-template-sub{
        color:#687991;
        font-size:10px;
        line-height:1.7;
        margin-bottom:12px;
    }

    .orion-download-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
    }

    .orion-download{
        height:48px;
        border:1px solid rgba(255,255,255,.08);
        border-radius:10px;
        background:#101b2b;
        color:#aebbd0;
        cursor:pointer;
        font-size:11px;
        font-weight:900;
        transition:.18s ease;
    }

    .orion-download:hover{
        transform:translateY(-1px);
        border-color:rgba(34,197,94,.35);
        color:#6ee7a0;
        background:rgba(34,197,94,.06);
    }

    .orion-download.primary{
        border-color:rgba(34,197,94,.25);
        background:linear-gradient(135deg,rgba(34,197,94,.13),rgba(34,197,94,.05));
        color:#61e99a;
    }

    .orion-download.supplier:hover{
        border-color:rgba(168,85,247,.35);
        color:#d8b4fe;
        background:rgba(168,85,247,.07);
    }

    /* Instructions */
    .orion-instructions{
        display:grid;
        gap:8px;
    }

    .orion-instruction{
        display:flex;
        align-items:center;
        gap:9px;
        color:#71829a;
        font-size:10px;
    }

    .orion-check{
        width:19px;
        height:19px;
        flex:0 0 19px;
        display:grid;
        place-items:center;
        border-radius:50%;
        background:rgba(34,197,94,.09);
        border:1px solid rgba(34,197,94,.17);
        color:#4ade80;
        font-size:10px;
        font-weight:900;
    }

    /* Fields */
    .orion-fields{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:7px;
        margin-top:14px;
    }

    .orion-field{
        padding:9px 8px;
        border:1px solid rgba(124,77,255,.13);
        border-radius:9px;
        background:rgba(124,77,255,.035);
        text-align:center;
    }

    .orion-field b{
        display:block;
        color:#9887ff;
        font-size:13px;
    }

    .orion-field span{
        display:block;
        margin-top:2px;
        color:#667891;
        font-size:9px;
    }

    .orion-field.required span{
        color:#fb7185;
    }

    /* Bottom action */
    .orion-action{
        display:grid;
        grid-template-columns:1fr auto;
        gap:12px;
        align-items:center;
        margin-top:16px;
        padding:14px;
        border:1px solid var(--orion-border);
        border-radius:16px;
        background:linear-gradient(180deg,#0d1829,#0a1422);
    }

    .orion-action-info strong{
        display:block;
        color:#e7edf6;
        font-size:12px;
        font-weight:900;
    }

    .orion-action-info span{
        display:block;
        color:#687991;
        font-size:10px;
        margin-top:3px;
    }

    .orion-import-btn{
        min-width:220px;
        height:50px;
        border:0;
        border-radius:11px;
        background:linear-gradient(135deg,#7c5cff,#6046d8);
        color:#fff;
        cursor:pointer;
        font-size:14px;
        font-weight:950;
        box-shadow:0 13px 27px rgba(96,70,216,.20);
    }

    .orion-import-btn:disabled{
        background:rgba(124,77,255,.10);
        color:#53647b;
        cursor:not-allowed;
        box-shadow:none;
    }

    .orion-result{
        margin-top:12px;
        padding:12px 14px;
        border:1px solid rgba(34,197,94,.18);
        border-radius:11px;
        background:rgba(34,197,94,.06);
        color:#69e79b;
        font-size:11px;
        font-weight:800;
    }

    .orion-warning{
        margin-top:12px;
        padding:11px 13px;
        border:1px solid rgba(251,191,36,.14);
        border-radius:11px;
        background:rgba(251,191,36,.045);
    }

    .orion-warning strong{
        color:#fbbf24;
        font-size:10px;
    }

    .orion-warning span{
        color:#687991;
        font-size:9px;
        margin-right:6px;
    }

    @media(max-width:1050px){
        .orion-main-grid{grid-template-columns:1fr}
        .orion-upload{min-height:230px}
    }

    @media(max-width:800px){
        .orion-steps{grid-template-columns:1fr 1fr}
        .orion-step{padding:8px}
        .orion-step:not(:last-child)::after{display:none}
        .orion-action{grid-template-columns:1fr}
        .orion-import-btn{width:100%}
    }

    @media(max-width:560px){
        .orion-import-head{align-items:flex-start;flex-direction:column}
        .orion-import-title{font-size:22px}
        .orion-steps{grid-template-columns:1fr}
        .orion-download-grid{grid-template-columns:1fr}
        .orion-fields{grid-template-columns:repeat(2,1fr)}
    }
</style>

<div class="orion-import-page">

    <div class="orion-import-head">
        <div>
            <h1 class="orion-import-title">استيراد العملاء والموردين</h1>
            <div class="orion-import-breadcrumb">
                الإعدادات <span> / </span> استيراد وتصدير البيانات <span> / </span>
                <b>استيراد العملاء والموردين</b>
            </div>
        </div>

        <div class="orion-status">
            <span class="orion-status-dot"></span>
            جاهز للاستيراد
        </div>
    </div>

    {{-- خطوات الاستيراد --}}
    <div class="orion-steps">
        <div class="orion-step active">
            <div class="orion-step-number">1</div>
            <div>
                <strong>رفع الملف</strong>
                <span>اختر ملف Excel</span>
            </div>
        </div>

        <div class="orion-step">
            <div class="orion-step-number">2</div>
            <div>
                <strong>تعيين الأعمدة</strong>
                <span>مطابقة الأعمدة</span>
            </div>
        </div>

        <div class="orion-step">
            <div class="orion-step-number">3</div>
            <div>
                <strong>معاينة البيانات</strong>
                <span>مراجعة البيانات</span>
            </div>
        </div>

        <div class="orion-step">
            <div class="orion-step-number">4</div>
            <div>
                <strong>استيراد البيانات</strong>
                <span>حفظ البيانات</span>
            </div>
        </div>
    </div>

    {{-- نوع الاستيراد --}}
    <div class="orion-type-tabs">
        <button
            type="button"
            class="orion-type-btn customer {{ $importType==='customer' ? 'active' : '' }}"
            wire:click="$set('importType','customer')">
            👥 استيراد العملاء
        </button>

        <button
            type="button"
            class="orion-type-btn supplier {{ $importType==='supplier' ? 'active' : '' }}"
            wire:click="$set('importType','supplier')">
            🚚 استيراد الموردين
        </button>
    </div>

    <div class="orion-main-grid">

        {{-- منطقة رفع الملف --}}
        <div class="orion-card">

            <div class="orion-card-head">
                <div class="orion-card-title">
                    <span class="orion-card-number">1</span>
                    رفع ملف Excel
                </div>
                <div class="orion-card-meta">الخطوة الأولى</div>
            </div>

            <div
                class="orion-upload {{ $uploadedFile ? 'has-file' : '' }}"
                onclick="document.getElementById('contacts-file').click()">

                <div class="orion-upload-inner">

                    <div class="orion-upload-icon">
                        {{ $uploadedFile ? '✓' : '☁' }}
                    </div>

                    @if($uploadedFile)
                        <h3 class="orion-upload-title">تم رفع الملف بنجاح</h3>
                        <p class="orion-upload-sub" style="color:#4ade80">
                            الملف جاهز للمعاينة والاستيراد
                        </p>
                    @else
                        <h3 class="orion-upload-title">
                            اسحب ملف Excel هنا أو اضغط للاختيار
                        </h3>

                        <p class="orion-upload-sub">
                            اختر ملف Excel يحتوي على بيانات
                            {{ $importType==='customer' ? 'العملاء' : 'الموردين' }}
                        </p>

                        <div class="orion-upload-info">
                            <span class="orion-chip">.xlsx</span>
                            <span class="orion-chip">.xls</span>
                            <span class="orion-chip">الحد الأقصى 10 MB</span>
                        </div>

                        <div class="orion-choose">📄 اختر ملف Excel</div>
                    @endif

                </div>
            </div>

            <input
                type="file"
                id="contacts-file"
                accept=".xlsx,.xls"
                style="display:none"
                x-on:change="
                    let file = $event.target.files[0];
                    if(!file) return;

                    let form = new FormData();
                    form.append('file', file);
                    form.append('_token', '{{ csrf_token() }}');

                    fetch('/admin/import-products/upload', {
                        method:'POST',
                        body:form
                    })
                    .then(r => r.json())
                    .then(d => {
                        if(d.path) $wire.set('uploadedFile', d.path);
                    })
                    .catch(e => console.error(e));
                ">

            <div class="orion-action">
                <div class="orion-action-info">
                    <strong>جاهز لتنفيذ الاستيراد؟</strong>
                    <span>
                        سيتم استيراد {{ $importType==='customer' ? 'العملاء' : 'الموردين' }}
                        وفقاً للـTemplate.
                    </span>
                </div>

                <button
                    type="button"
                    wire:click="importFile"
                    class="orion-import-btn"
                    {{ !$uploadedFile ? 'disabled' : '' }}>
                    🚀 بدء الاستيراد
                </button>
            </div>

            @if($importResult)
                <div class="orion-result">
                    ✓ {{ $importResult }}
                </div>
            @endif

            <div class="orion-warning">
                <strong>⚠️ تنبيه:</strong>
                <span>لو السجل موجود بنفس الكود أو البريد — هيتحدث وليس إضافة جديدة.</span>
            </div>
        </div>

        {{-- Template والتعليمات --}}
        <div class="orion-card">

            <div class="orion-card-head">
                <div class="orion-card-title">
                    <span class="orion-card-number">1</span>
                    نموذج الملف
                </div>
                <div class="orion-card-meta">Excel Template</div>
            </div>

            <div class="orion-template-card">
                <div class="orion-template-title">📥 تحميل نموذج Excel الصحيح</div>
                <div class="orion-template-sub">
                    استخدم النموذج الجاهز لضمان توافق الأعمدة مع Orion ERP.
                </div>

                <div class="orion-download-grid">
                    <button
                        type="button"
                        wire:click="$set('importType','customer')"
                        class="orion-download primary">
                        📗 نموذج العملاء
                    </button>

                    <button
                        type="button"
                        wire:click="$set('importType','supplier')"
                        class="orion-download supplier">
                        📘 نموذج الموردين
                    </button>
                </div>

                <div style="height:8px"></div>

                <button
                    type="button"
                    wire:click="downloadTemplate"
                    class="orion-download primary"
                    style="width:100%">
                    📥 تحميل Template {{ $importType==='customer' ? 'العملاء' : 'الموردين' }}
                </button>
            </div>

            <div class="orion-template-card">
                <div class="orion-template-title">
                    2️⃣ تعليمات الاستيراد
                </div>

                <div class="orion-instructions">
                    <div class="orion-instruction">
                        <span class="orion-check">✓</span>
                        يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls)
                    </div>

                    <div class="orion-instruction">
                        <span class="orion-check">✓</span>
                        الصف الأول يجب أن يحتوي على أسماء الأعمدة
                    </div>

                    <div class="orion-instruction">
                        <span class="orion-check">✓</span>
                        تأكد من صحة البيانات وتنسيقها
                    </div>

                    <div class="orion-instruction">
                        <span class="orion-check">✓</span>
                        الحد الأقصى لعدد السجلات: 5,000 سجل
                    </div>

                    <div class="orion-instruction">
                        <span class="orion-check">✓</span>
                        البيانات يجب أن تكون فريدة بدون تكرارات
                    </div>
                </div>
            </div>

            <div class="orion-template-card">
                <div class="orion-template-title">🧩 الأعمدة المدعومة</div>

                <div class="orion-fields">
                    @foreach([
                        ['A','الاسم','required'],
                        ['B','الكود',''],
                        ['C','التليفون',''],
                        ['D','الموبايل',''],
                        ['E','البريد',''],
                        ['F','العنوان',''],
                        ['G','الرقم الضريبي',''],
                        ['H','الحد الائتماني',''],
                        ['I','ملاحظات',''],
                    ] as $col)
                        <div class="orion-field {{ $col[2] }}">
                            <b>{{ $col[0] }}</b>
                            <span>{{ $col[1] }}{{ $col[2] ? ' *' : '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</div>
</x-filament-panels::page>
