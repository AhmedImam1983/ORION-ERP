<x-filament-panels::page>
<style>
    *{font-family:'Cairo',sans-serif!important;box-sizing:border-box}

    .orion-import-page{
        direction:rtl;
        color:#e8edf7;
        max-width:1500px;
        margin:0 auto;
        padding:8px 0 28px;
    }

    .orion-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:20px;
        margin-bottom:18px;
    }
    .orion-head-title{
        font-size:27px;
        line-height:1.25;
        font-weight:900;
        color:#f8fafc;
        margin:0;
    }
    .orion-head-sub{
        margin-top:7px;
        color:#71809b;
        font-size:13px;
    }
    .orion-status{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:9px 14px;
        border:1px solid rgba(34,197,94,.18);
        background:rgba(34,197,94,.07);
        color:#4ade80;
        border-radius:999px;
        font-size:12px;
        font-weight:800;
        white-space:nowrap;
    }
    .orion-status-dot{
        width:7px;height:7px;border-radius:50%;
        background:#22c55e;
        box-shadow:0 0 12px rgba(34,197,94,.7);
    }

    .orion-steps{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:10px;
        margin-bottom:16px;
    }
    .orion-step{
        min-height:68px;
        padding:11px 15px;
        border:1px solid #1d2a40;
        border-radius:13px;
        background:linear-gradient(180deg,rgba(18,27,42,.96),rgba(10,16,27,.96));
        display:flex;
        align-items:center;
        gap:12px;
    }
    .orion-step.active{
        border-color:rgba(124,77,255,.55);
        background:linear-gradient(180deg,rgba(77,49,145,.28),rgba(20,17,48,.9));
        box-shadow:inset 0 0 0 1px rgba(124,77,255,.08),0 10px 30px rgba(0,0,0,.12);
    }
    .orion-step-num{
        width:34px;height:34px;flex:0 0 34px;
        border-radius:10px;
        display:flex;align-items:center;justify-content:center;
        background:#182235;
        color:#8fa0ba;
        font-weight:900;
        font-size:13px;
    }
    .orion-step.active .orion-step-num{
        background:linear-gradient(135deg,#8b5cf6,#6d4aff);
        color:#fff;
        box-shadow:0 7px 18px rgba(124,77,255,.3);
    }
    .orion-step-title{font-size:13px;font-weight:900;color:#e8edf7}
    .orion-step-desc{font-size:10px;color:#62708a;margin-top:3px}

    .orion-grid{
        display:grid;
        grid-template-columns:minmax(0,1.55fr) minmax(360px,.85fr);
        gap:16px;
        align-items:start;
    }

    .orion-card{
        background:linear-gradient(180deg,#0e1624,#0a111c);
        border:1px solid #1b2a40;
        border-radius:16px;
        box-shadow:0 16px 45px rgba(0,0,0,.16);
        overflow:hidden;
    }
    .orion-card-head{
        padding:18px 20px 14px;
        border-bottom:1px solid rgba(255,255,255,.045);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
    }
    .orion-card-title{
        font-size:16px;
        font-weight:900;
        color:#f1f5f9;
        margin:0;
    }
    .orion-card-caption{
        font-size:11px;
        color:#63718a;
        margin-top:4px;
    }

    .orion-upload{
        margin:18px;
        min-height:255px;
        border:2px dashed #263957;
        border-radius:15px;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
        cursor:pointer;
        background:
            radial-gradient(circle at 50% 35%,rgba(124,77,255,.09),transparent 36%),
            rgba(5,10,18,.35);
        transition:.2s ease;
    }
    .orion-upload:hover{
        border-color:#7654ff;
        background:
            radial-gradient(circle at 50% 35%,rgba(124,77,255,.15),transparent 40%),
            rgba(7,12,22,.7);
        transform:translateY(-1px);
    }
    .orion-upload.has-file{
        border-color:rgba(34,197,94,.55);
        background:
            radial-gradient(circle at 50% 35%,rgba(34,197,94,.10),transparent 40%),
            rgba(5,16,14,.55);
    }
    .orion-upload-icon{
        width:72px;height:72px;
        margin:0 auto 12px;
        border-radius:20px;
        display:flex;align-items:center;justify-content:center;
        font-size:34px;
        background:linear-gradient(145deg,rgba(124,77,255,.17),rgba(124,77,255,.05));
        border:1px solid rgba(124,77,255,.18);
    }
    .orion-upload-title{font-size:16px;font-weight:900;color:#e8edf7}
    .orion-upload-sub{font-size:12px;color:#66758e;margin-top:7px}
    .orion-upload-success{font-size:13px;font-weight:900;color:#4ade80;margin-top:8px}

    .orion-warehouse{
        margin:0 18px 18px;
    }
    .orion-label{
        display:block;
        font-size:11px;
        color:#8795aa;
        font-weight:800;
        margin-bottom:7px;
    }
    .orion-select{
        width:100%;
        background:#111a27;
        border:1px solid #27364c;
        color:#e8edf7;
        border-radius:10px;
        padding:12px 13px;
        font-size:13px;
        outline:none;
    }
    .orion-select:focus{border-color:#7654ff;box-shadow:0 0 0 3px rgba(124,77,255,.10)}

    .orion-action{
        margin:0 18px 18px;
        width:calc(100% - 36px);
        min-height:49px;
        border:0;
        border-radius:11px;
        color:#fff;
        background:linear-gradient(135deg,#8055ff,#6344ef);
        font-size:14px;
        font-weight:900;
        cursor:pointer;
        box-shadow:0 10px 24px rgba(102,70,239,.2);
        transition:.18s ease;
    }
    .orion-action:hover{filter:brightness(1.08);transform:translateY(-1px)}
    .orion-action:disabled{
        cursor:not-allowed;
        background:#24233d;
        color:#4d536a;
        box-shadow:none;
        transform:none;
    }

    .orion-template-body{padding:18px 20px 20px}
    .orion-template-grid{
        display:grid;
        grid-template-columns:repeat(5,1fr);
        gap:9px;
    }
    .orion-col{
        min-height:70px;
        border:1px solid rgba(124,77,255,.20);
        background:rgba(124,77,255,.065);
        border-radius:10px;
        padding:9px 7px;
        text-align:center;
        display:flex;
        flex-direction:column;
        justify-content:center;
    }
    .orion-col.required{
        border-color:rgba(244,63,94,.25);
        background:rgba(244,63,94,.055);
    }
    .orion-col-letter{
        font-size:17px;
        line-height:1;
        font-weight:1000;
        color:#8b72ff;
    }
    .orion-col.required .orion-col-letter{color:#fb7185}
    .orion-col-name{font-size:10px;color:#8190a8;margin-top:6px;font-weight:700}
    .orion-col.required .orion-col-name{color:#fb7185}

    .orion-template-download{
        width:100%;
        margin-top:14px;
        min-height:47px;
        border:0;
        border-radius:11px;
        color:#fff;
        background:linear-gradient(135deg,#22c55e,#16a34a);
        font-size:13px;
        font-weight:900;
        cursor:pointer;
        box-shadow:0 10px 24px rgba(34,197,94,.12);
    }

    .orion-info{
        margin-top:15px;
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
    }
    .orion-info-box{
        padding:13px;
        border:1px solid rgba(255,255,255,.06);
        background:rgba(255,255,255,.018);
        border-radius:11px;
    }
    .orion-info-title{
        font-size:11px;
        color:#9aa7bb;
        font-weight:900;
        margin-bottom:7px;
    }
    .orion-info-value{font-size:11px;color:#63718a;line-height:1.8}

    .orion-notes{
        margin-top:15px;
        border-top:1px solid rgba(255,255,255,.045);
        padding-top:13px;
    }
    .orion-note{
        display:flex;
        gap:8px;
        align-items:flex-start;
        padding:7px 0;
        color:#708098;
        font-size:11px;
        border-bottom:1px solid rgba(255,255,255,.035);
    }
    .orion-note:last-child{border-bottom:0}
    .orion-note-dot{color:#8b72ff;font-size:15px;line-height:12px}

    .orion-result{
        margin:0 18px 18px;
        border:1px solid rgba(34,197,94,.22);
        background:rgba(34,197,94,.07);
        border-radius:11px;
        padding:13px 14px;
        color:#4ade80;
        font-size:12px;
        font-weight:800;
    }
    .orion-warning{
        margin:0 18px 18px;
        border:1px solid rgba(251,191,36,.18);
        background:rgba(251,191,36,.045);
        border-radius:11px;
        padding:13px 14px;
    }
    .orion-warning-title{font-size:12px;color:#fbbf24;font-weight:900}
    .orion-warning-text{font-size:10px;color:#748199;margin-top:5px;line-height:1.7}

    @media(max-width:1050px){
        .orion-grid{grid-template-columns:1fr}
        .orion-template-grid{grid-template-columns:repeat(5,1fr)}
    }
    @media(max-width:700px){
        .orion-head{align-items:flex-start;flex-direction:column}
        .orion-steps{grid-template-columns:1fr 1fr}
        .orion-template-grid{grid-template-columns:repeat(2,1fr)}
        .orion-info{grid-template-columns:1fr}
    }
</style>

<div class="orion-import-page">

    <div class="orion-head">
        <div>
            <h1 class="orion-head-title">استيراد الأصناف من Excel</h1>
            <div class="orion-head-sub">إضافة أو تحديث الأصناف بالجملة بطريقة آمنة وسريعة</div>
        </div>
        <div class="orion-status">
            <span class="orion-status-dot"></span>
            جاهز للاستيراد
        </div>
    </div>

    <div class="orion-steps">
        <div class="orion-step active">
            <div class="orion-step-num">1</div>
            <div>
                <div class="orion-step-title">رفع الملف</div>
                <div class="orion-step-desc">اختر ملف Excel</div>
            </div>
        </div>

        <div class="orion-step">
            <div class="orion-step-num">2</div>
            <div>
                <div class="orion-step-title">تجهيز البيانات</div>
                <div class="orion-step-desc">مراجعة الأعمدة والقيم</div>
            </div>
        </div>

        <div class="orion-step">
            <div class="orion-step-num">3</div>
            <div>
                <div class="orion-step-title">المعاينة</div>
                <div class="orion-step-desc">التأكد من البيانات</div>
            </div>
        </div>

        <div class="orion-step">
            <div class="orion-step-num">4</div>
            <div>
                <div class="orion-step-title">الاستيراد</div>
                <div class="orion-step-desc">إضافة الأصناف للنظام</div>
            </div>
        </div>
    </div>

    <div class="orion-grid">

        <div class="orion-card">
            <div class="orion-card-head">
                <div>
                    <h2 class="orion-card-title">رفع ملف الأصناف</h2>
                    <div class="orion-card-caption">يدعم ملفات Excel بصيغة XLS و XLSX</div>
                </div>
                <span style="font-size:18px">📤</span>
            </div>

            <div class="orion-upload {{ $uploadedFile ? 'has-file' : '' }}"
                 onclick="document.getElementById('excel-file').click()">
                <div>
                    <div class="orion-upload-icon">{{ $uploadedFile ? '✅' : '📊' }}</div>

                    @if($uploadedFile)
                        <div class="orion-upload-title">تم رفع الملف بنجاح</div>
                        <div class="orion-upload-success">الملف جاهز للاستيراد ✓</div>
                    @else
                        <div class="orion-upload-title">اسحب ملف Excel هنا أو اضغط للاختيار</div>
                        <div class="orion-upload-sub">XLSX أو XLS • حتى 5000 صنف في العملية الواحدة</div>
                    @endif
                </div>
            </div>

            <input type="file" id="excel-file" accept=".xlsx,.xls" style="display:none"
                x-on:change="
                    let file = $event.target.files[0];
                    if(!file) return;
                    let form = new FormData();
                    form.append('file', file);
                    form.append('_token', '{{ csrf_token() }}');
                    fetch('/admin/import-products/upload', {method:'POST', body:form})
                        .then(r=>r.json())
                        .then(d=>{ if(d.path) $wire.set('uploadedFile', d.path); })
                        .catch(e=>console.error(e));
                ">

            <div class="orion-warehouse">
                <label class="orion-label">المخزن المستهدف</label>
                <select wire:model="selectedWarehouseId" class="orion-select">
                    <option value="">بدون مخزن محدد</option>
                    @foreach(\App\Models\Warehouse::where('is_active',true)->get() as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}{{ $w->is_default ? ' (افتراضي)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="importFile" class="orion-action" {{ !$uploadedFile ? 'disabled' : '' }}>
                🚀 بدء استيراد الأصناف
            </button>

            @if($importResult)
                <div class="orion-result">{{ $importResult }}</div>
            @endif

            <div class="orion-warning">
                <div class="orion-warning-title">⚠️ تنبيه مهم</div>
                <div class="orion-warning-text">
                    إذا كان الصنف موجوداً بنفس الكود، سيتم تحديث بياناته بدلاً من إنشاء صنف مكرر.
                </div>
            </div>
        </div>

        <div class="orion-card">
            <div class="orion-card-head">
                <div>
                    <h2 class="orion-card-title">Template الأصناف</h2>
                    <div class="orion-card-caption">استخدم النموذج الجاهز لتجنب أخطاء الأعمدة</div>
                </div>
                <span style="font-size:18px">📥</span>
            </div>

            <div class="orion-template-body">
                <div class="orion-template-grid">
                    @foreach([
                        ['A','اسم الصنف','required'],
                        ['B','الكود',''],
                        ['C','باركود',''],
                        ['D','الوحدة',''],
                        ['E','المجموعة',''],
                        ['F','سعر التكلفة',''],
                        ['G','سعر البيع','required'],
                        ['H','الكمية',''],
                        ['I','الحد الأدنى',''],
                        ['J','الوصف',''],
                        ['K','نوع الضريبة',''],
                        ['L','نسبة الضريبة %',''],
                    ] as $col)
                        <div class="orion-col {{ $col[2] }}">
                            <div class="orion-col-letter">{{ $col[0] }}</div>
                            <div class="orion-col-name">{{ $col[1] }}{{ $col[2] ? ' *' : '' }}</div>
                        </div>
                    @endforeach
                </div>

                <button wire:click="downloadTemplate" class="orion-template-download">
                    📥 تحميل Template Excel
                </button>

                <div class="orion-info">
                    <div class="orion-info-box">
                        <div class="orion-info-title">📌 الحقول المطلوبة</div>
                        <div class="orion-info-value">اسم الصنف وسعر البيع مطلوبان لإتمام الاستيراد.</div>
                    </div>
                    <div class="orion-info-box">
                        <div class="orion-info-title">🔄 التحديث الذكي</div>
                        <div class="orion-info-value">الكود الموجود مسبقاً يستخدم لتحديث الصنف بدلاً من تكراره.</div>
                    </div>
                </div>

                <div class="orion-notes">
                    <div class="orion-note"><span class="orion-note-dot">•</span>المجموعة يتم إنشاؤها تلقائياً إذا لم تكن موجودة.</div>
                    <div class="orion-note"><span class="orion-note-dot">•</span>يمكن استيراد حتى 5000 صنف في العملية الواحدة.</div>
                    <div class="orion-note"><span class="orion-note-dot">•</span>اكتب الأرقام بدون فواصل أو رموز عملة.</div>
                    <div class="orion-note"><span class="orion-note-dot">•</span>يفضل استخدام القالب الرسمي للنظام قبل رفع الملف.</div>
                    <div class="orion-note"><span class="orion-note-dot">•</span>نوع الضريبة: S=خاضع 15%، Z=صفري، E=معفي، O=خارج النطاق.</div>
                    <div class="orion-note"><span class="orion-note-dot">•</span>إذا تركت نوع الضريبة فارغاً سيُطبَّق S بنسبة 15% تلقائياً.</div>
                </div>
            </div>
        </div>

    </div>
</div>
</x-filament-panels::page>