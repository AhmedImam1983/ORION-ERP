<x-filament-panels::page>
<style>
* { font-family: 'Cairo', sans-serif !important; }
.sw-wrap { max-width: 860px; margin: 0 auto; padding: 24px 0 48px; }

/* Progress bar */
.sw-progress { display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:40px; }
.sw-step { display:flex; align-items:center; gap:0; }
.sw-circle {
    width:40px; height:40px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:800; transition:.3s;
    border:2px solid #1e2d45;
    background:#0d1117; color:#475569;
}
.sw-circle.active { background:#8B5CF6; border-color:#8B5CF6; color:#fff; box-shadow:0 0 16px rgba(139,92,246,.4); }
.sw-circle.done   { background:#10b981; border-color:#10b981; color:#fff; }
.sw-line { width:60px; height:2px; background:#1e2d45; transition:.3s; }
.sw-line.done { background:#10b981; }
.sw-label { font-size:11px; color:#475569; margin-top:6px; text-align:center; font-weight:600; }
.sw-step-wrap { display:flex; flex-direction:column; align-items:center; }

/* Card */
.sw-card {
    background:#0e1420; border:1px solid #1e2d45;
    border-radius:18px; padding:36px 40px;
    box-shadow:0 8px 32px rgba(0,0,0,.2);
}
.sw-title { font-size:22px; font-weight:800; color:#e2e8f0; margin-bottom:6px; }
.sw-subtitle { font-size:13px; color:#64748b; margin-bottom:32px; font-weight:400; }

/* Fields */
.sw-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:10px; }
.sw-grid-1 { grid-template-columns:1fr; }
.sw-field { display:flex; flex-direction:column; gap:7px; }
.sw-field.full { grid-column:1/-1; }
.sw-label-f { font-size:13px; font-weight:700; color:#94a3b8; }
.sw-input {
    background:#161c2d; border:1px solid #1e2d45;
    border-radius:10px; padding:11px 14px;
    font-size:14px; color:#e2e8f0; outline:none;
    transition:.2s; font-family:'Cairo',sans-serif!important;
    width:100%;
}
.sw-input:focus { border-color:#8B5CF6; box-shadow:0 0 0 3px rgba(139,92,246,.15); }
.sw-select {
    background:#161c2d; border:1px solid #1e2d45;
    border-radius:10px; padding:11px 14px;
    font-size:14px; color:#e2e8f0; outline:none;
    width:100%; cursor:pointer;
    font-family:'Cairo',sans-serif!important;
}

/* Buttons */
.sw-btns { display:flex; align-items:center; justify-content:space-between; margin-top:32px; }
.sw-btn-next {
    background:#8B5CF6; color:#fff; border:none;
    padding:12px 28px; border-radius:12px;
    font-size:14px; font-weight:700; cursor:pointer;
    transition:.2s; font-family:'Cairo',sans-serif!important;
}
.sw-btn-next:hover { background:#7c3aed; box-shadow:0 0 20px rgba(139,92,246,.4); }
.sw-btn-prev {
    background:transparent; color:#64748b;
    border:1px solid #1e2d45;
    padding:12px 24px; border-radius:12px;
    font-size:14px; font-weight:600; cursor:pointer;
    transition:.2s; font-family:'Cairo',sans-serif!important;
}
.sw-btn-prev:hover { border-color:#334155; color:#94a3b8; }

/* Info boxes */
.sw-info { background:rgba(139,92,246,.08); border:1px solid rgba(139,92,246,.2); border-radius:12px; padding:16px 20px; margin-bottom:24px; }
.sw-info-title { font-size:13px; font-weight:700; color:#a78bfa; margin-bottom:4px; }
.sw-info-text  { font-size:12px; color:#64748b; font-weight:400; line-height:1.8; }

/* Success */
.sw-success { text-align:center; padding:20px 0; }
.sw-success-icon { font-size:64px; margin-bottom:16px; }
.sw-success-title { font-size:26px; font-weight:800; color:#e2e8f0; margin-bottom:8px; }
.sw-success-sub { font-size:14px; color:#64748b; margin-bottom:28px; }
.sw-btn-dashboard {
    background:linear-gradient(135deg,#8B5CF6,#6d28d9);
    color:#fff; border:none; padding:14px 36px;
    border-radius:12px; font-size:15px; font-weight:700;
    cursor:pointer; text-decoration:none; display:inline-block;
    font-family:'Cairo',sans-serif!important;
    box-shadow:0 8px 24px rgba(139,92,246,.35);
}

/* Checkbox */
.sw-check-wrap { display:flex; align-items:center; gap:12px; padding:16px; background:#161c2d; border:1px solid #1e2d45; border-radius:12px; cursor:pointer; }
.sw-check-wrap input[type=checkbox] { width:18px; height:18px; accent-color:#8B5CF6; cursor:pointer; }
.sw-check-text { font-size:14px; color:#94a3b8; font-weight:500; }

/* Step 4 — accounts */
.sw-accounts-list { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.sw-account-item { background:#161c2d; border:1px solid #1e2d45; border-radius:10px; padding:12px 16px; font-size:13px; color:#94a3b8; display:flex; align-items:center; gap:8px; }
.sw-account-item span { color:#8B5CF6; font-weight:700; }
</style>

<div class="sw-wrap">

    {{-- ══ PROGRESS BAR ══ --}}
    <div class="sw-progress">
        @php
            $steps = [
                1 => 'بيانات الشركة',
                2 => 'العنوان',
                3 => 'الفرع والمخزن',
                4 => 'شجرة الحسابات',
                5 => 'إنهاء الإعداد',
            ];
        @endphp
        @foreach($steps as $num => $label)
            <div class="sw-step-wrap">
                <div class="sw-circle {{ $currentStep > $num ? 'done' : ($currentStep === $num ? 'active' : '') }}">
                    @if($currentStep > $num)
                        ✓
                    @else
                        {{ $num }}
                    @endif
                </div>
                <div class="sw-label">{{ $label }}</div>
            </div>
            @if(!$loop->last)
                <div class="sw-line {{ $currentStep > $num ? 'done' : '' }}" style="margin-bottom:20px"></div>
            @endif
        @endforeach
    </div>

    {{-- ══ SUCCESS ══ --}}
    @if($completed)
    <div class="sw-card">
        <div class="sw-success">
            <div class="sw-success-icon">🎉</div>
            <div class="sw-success-title">تم إعداد النظام بنجاح!</div>
            <div class="sw-success-sub">يمكنك الآن البدء في استخدام Orion ERP بكامل مميزاته</div>
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="sw-btn-dashboard">
                الذهاب إلى لوحة التحكم ←
            </a>
        </div>
    </div>

    {{-- ══ STEP 1: بيانات الشركة ══ --}}
    @elseif($currentStep === 1)
    <div class="sw-card">
        <div class="sw-title">🏢 بيانات الشركة</div>
        <div class="sw-subtitle">أدخل المعلومات الأساسية للشركة — ستظهر في الفواتير والتقارير</div>

        <div class="sw-grid">
            <div class="sw-field">
                <label class="sw-label-f">اسم الشركة بالعربية *</label>
                <input wire:model="companyName" type="text" class="sw-input" placeholder="الشركة المتحدة للتجارة">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">اسم الشركة بالإنجليزية</label>
                <input wire:model="companyNameEn" type="text" class="sw-input" placeholder="United Trading Company">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">الرقم الضريبي (VAT)</label>
                <input wire:model="companyTax" type="text" class="sw-input" placeholder="310XXXXXXXXX4 — 15 رقم">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">السجل التجاري</label>
                <input wire:model="companyCr" type="text" class="sw-input" placeholder="10XXXXXXXX">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">هاتف الشركة</label>
                <input wire:model="companyPhone" type="tel" class="sw-input" placeholder="+966XXXXXXXXX">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">البريد الإلكتروني</label>
                <input wire:model="companyEmail" type="email" class="sw-input" placeholder="info@company.com">
            </div>
            <div class="sw-field full">
                <label class="sw-label-f">الموقع الإلكتروني</label>
                <input wire:model="companyWebsite" type="url" class="sw-input" placeholder="https://www.company.com">
            </div>
        </div>

        <div class="sw-btns">
            <div></div>
            <button wire:click="nextStep" class="sw-btn-next">التالي ← بيانات العنوان</button>
        </div>
    </div>

    {{-- ══ STEP 2: العنوان ══ --}}
    @elseif($currentStep === 2)
    <div class="sw-card">
        <div class="sw-title">📍 عنوان الشركة</div>
        <div class="sw-subtitle">بيانات العنوان مطلوبة لفواتير زاتكا السعودية</div>

        <div class="sw-grid">
            <div class="sw-field">
                <label class="sw-label-f">الدولة</label>
                <select wire:model="companyCountry" class="sw-select">
                    <option value="SA">🇸🇦 المملكة العربية السعودية</option>
                    <option value="EG">🇪🇬 مصر</option>
                    <option value="AE">🇦🇪 الإمارات</option>
                    <option value="KW">🇰🇼 الكويت</option>
                    <option value="QA">🇶🇦 قطر</option>
                    <option value="BH">🇧🇭 البحرين</option>
                    <option value="OM">🇴🇲 سلطنة عُمان</option>
                </select>
            </div>
            <div class="sw-field">
                <label class="sw-label-f">المدينة *</label>
                <input wire:model="companyCity" type="text" class="sw-input" placeholder="الرياض">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">الحي</label>
                <input wire:model="companyDistrict" type="text" class="sw-input" placeholder="العليا">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">اسم الشارع</label>
                <input wire:model="companyStreet" type="text" class="sw-input" placeholder="طريق الملك فهد">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">رقم المبنى</label>
                <input wire:model="companyBuilding" type="text" class="sw-input" placeholder="1234">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">الرمز البريدي</label>
                <input wire:model="companyPostal" type="text" class="sw-input" placeholder="12345">
            </div>
        </div>

        <div class="sw-btns">
            <button wire:click="prevStep" class="sw-btn-prev">→ السابق</button>
            <button wire:click="nextStep" class="sw-btn-next">التالي ← الفرع والمخزن</button>
        </div>
    </div>

    {{-- ══ STEP 3: الفرع والمخزن ══ --}}
    @elseif($currentStep === 3)
    <div class="sw-card">
        <div class="sw-title">🏪 الفرع الرئيسي والمخزن</div>
        <div class="sw-subtitle">سيتم إنشاء الفرع والمخزن الافتراضي للنظام</div>

        <div class="sw-grid">
            <div class="sw-field">
                <label class="sw-label-f">اسم الفرع الرئيسي *</label>
                <input wire:model="branchName" type="text" class="sw-input" placeholder="الفرع الرئيسي">
            </div>
            <div class="sw-field">
                <label class="sw-label-f">اسم المخزن الرئيسي *</label>
                <input wire:model="warehouseName" type="text" class="sw-input" placeholder="المخزن الرئيسي">
            </div>
        </div>

        <div class="sw-info" style="margin-top:16px">
            <div class="sw-info-title">ℹ️ ملاحظة</div>
            <div class="sw-info-text">يمكنك إضافة فروع ومخازن إضافية لاحقاً من قسم إدارة المخازن</div>
        </div>

        <div class="sw-btns">
            <button wire:click="prevStep" class="sw-btn-prev">→ السابق</button>
            <button wire:click="nextStep" class="sw-btn-next">التالي ← شجرة الحسابات</button>
        </div>
    </div>

    {{-- ══ STEP 4: شجرة الحسابات ══ --}}
    @elseif($currentStep === 4)
    <div class="sw-card">
        <div class="sw-title">📊 شجرة الحسابات</div>
        <div class="sw-subtitle">سيتم إنشاء شجرة الحسابات المحاسبية القياسية تلقائياً</div>

        <div class="sw-info">
            <div class="sw-info-title">✅ ما سيتم إنشاؤه</div>
            <div class="sw-info-text">
                شجرة حسابات محاسبية متكاملة تشمل: الأصول، الخصوم، حقوق الملكية، الإيرادات، والمصروفات
                وفقاً للمعايير المحاسبية العربية والخليجية
            </div>
        </div>

        <div class="sw-accounts-list">
            @foreach([
                ['1', 'الأصول المتداولة'],
                ['2', 'الأصول الثابتة'],
                ['3', 'الخصوم المتداولة'],
                ['4', 'حقوق الملكية'],
                ['5', 'الإيرادات'],
                ['6', 'المصروفات'],
            ] as $acc)
            <div class="sw-account-item">
                <span>{{ $acc[0] }}xx</span>
                {{ $acc[1] }}
            </div>
            @endforeach
        </div>

        <div class="sw-btns" style="margin-top:24px">
            <button wire:click="prevStep" class="sw-btn-prev">→ السابق</button>
            <button wire:click="nextStep" class="sw-btn-next" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="nextStep">التالي ← إنشاء شجرة الحسابات</span>
                <span wire:loading wire:target="nextStep">⏳ جارٍ الإنشاء...</span>
            </button>
        </div>
    </div>

    {{-- ══ STEP 5: إنهاء ══ --}}
    @elseif($currentStep === 5)
    <div class="sw-card">
        <div class="sw-title">🚀 إنهاء الإعداد</div>
        <div class="sw-subtitle">اختياري: تحميل بيانات تجريبية للتعرف على النظام</div>

        <label class="sw-check-wrap" style="margin-bottom:24px">
            <input wire:model="loadDemoData" type="checkbox">
            <div>
                <div class="sw-check-text">تحميل بيانات تجريبية (عملاء، منتجات، فواتير نموذجية)</div>
                <div style="font-size:11px;color:#475569;margin-top:3px">يمكن حذفها لاحقاً — مفيدة للتجربة فقط</div>
            </div>
        </label>

        <div class="sw-info">
            <div class="sw-info-title">📋 ملخص الإعداد</div>
            <div class="sw-info-text">
                ✅ اسم الشركة: <strong style="color:#e2e8f0">{{ $companyName ?: '—' }}</strong><br>
                ✅ الرقم الضريبي: <strong style="color:#e2e8f0">{{ $companyTax ?: 'لم يُحدد' }}</strong><br>
                ✅ المدينة: <strong style="color:#e2e8f0">{{ $companyCity ?: 'لم تُحدد' }}</strong><br>
                ✅ الفرع: <strong style="color:#e2e8f0">{{ $branchName }}</strong><br>
                ✅ المخزن: <strong style="color:#e2e8f0">{{ $warehouseName }}</strong>
            </div>
        </div>

        <div class="sw-btns">
            <button wire:click="prevStep" class="sw-btn-prev">→ السابق</button>
            <button wire:click="nextStep" class="sw-btn-next" wire:loading.attr="disabled"
                style="background:linear-gradient(135deg,#8B5CF6,#6d28d9);box-shadow:0 8px 24px rgba(139,92,246,.35)">
                <span wire:loading.remove wire:target="nextStep">🎉 إنهاء الإعداد</span>
                <span wire:loading wire:target="nextStep">⏳ جارٍ الإعداد...</span>
            </button>
        </div>
    </div>
    @endif

</div>
</x-filament-panels::page>