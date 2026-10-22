<x-filament-panels::page>
{{-- ORION EXPENSES SAUDI V4 | REAL PHOTOS | NO EMOJI ICONS --}}
@php
    $todayExpenses = $this->getTodayExpenses();
    $todayTotal = $this->getTodayTotal();
    $todayCount = $todayExpenses->count();
    $todayAverage = $todayCount > 0 ? $todayExpenses->avg('amount') : 0;
    $todayMax = $todayCount > 0 ? $todayExpenses->max('amount') : 0;

    $categoryPhoto = function (?string $name): string {
        $name = trim((string) $name);
        if (str_contains($name, 'إيجار')) return asset('images/expenses/rent.jpg');
        if (str_contains($name, 'كهرب') || str_contains($name, 'مياه')) return asset('images/expenses/utilities.jpg');
        if (str_contains($name, 'رواتب') || str_contains($name, 'أجور')) return asset('images/expenses/payroll.jpg');
        if (str_contains($name, 'صيانة') || str_contains($name, 'إصلاح')) return asset('images/expenses/maintenance.jpg');
        if (str_contains($name, 'مواصلات') || str_contains($name, 'وقود')) return asset('images/expenses/transport.jpg');
        if (str_contains($name, 'اتصالات') || str_contains($name, 'إنترنت')) return asset('images/expenses/internet.jpg');
        if (str_contains($name, 'ضيافة') || str_contains($name, 'اجتما')) return asset('images/expenses/hospitality.jpg');
        if (str_contains($name, 'تسويق') || str_contains($name, 'إعلان')) return asset('images/expenses/marketing.jpg');
        if (str_contains($name, 'قرطاسية') || str_contains($name, 'مستلزمات')) return asset('images/expenses/stationery.jpg');
        return asset('images/expenses/other.jpg');
    };
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}

:root{
    --orion-bg:#08111f;
    --orion-panel:#0d1728;
    --orion-panel-2:#111b2e;
    --orion-panel-3:#151f33;
    --orion-border:#26344c;
    --orion-border-soft:#1c2940;
    --orion-text:#f8fafc;
    --orion-muted:#8ea0b8;
    --orion-muted-2:#5f7189;
    --orion-purple:#8b5cf6;
    --orion-purple-2:#a78bfa;
    --orion-green:#34d399;
    --orion-red:#f87171;
    --orion-blue:#60a5fa;
}

.orion-expenses-page{
    direction:rtl;
    --saudi-green:#16a36f;
    --saudi-green-soft:rgba(22,163,111,.12);
    color:var(--orion-text);
    width:100%;
}

/* Header */
.orion-page-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:18px;
    margin:0 0 22px;
}
.orion-page-title{font-size:32px;font-weight:950;line-height:1.15;color:var(--orion-text);margin:0}
.orion-page-subtitle{font-size:13px;color:var(--orion-muted);margin-top:7px}
.market-row{display:flex;align-items:center;gap:8px;margin-top:10px;flex-wrap:wrap}
.market-chip{display:inline-flex;align-items:center;gap:7px;height:28px;padding:0 10px;border-radius:999px;border:1px solid rgba(22,163,111,.25);background:var(--saudi-green-soft);color:#8ee2bf;font-size:11px;font-weight:800}
.market-dot{width:7px;height:7px;border-radius:50%;background:var(--saudi-green);box-shadow:0 0 0 4px rgba(22,163,111,.10)}
.hijri-date{font-size:11px;color:var(--orion-muted-2);font-weight:700}
.orion-head-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;direction:ltr}
.orion-date-box{
    direction:rtl;
    display:flex;
    align-items:center;
    gap:9px;
    min-height:46px;
    background:var(--orion-panel);
    border:1px solid var(--orion-border);
    border-radius:12px;
    padding:0 13px;
}
.orion-date-label{font-size:11px;color:var(--orion-muted);font-weight:800;white-space:nowrap}
.orion-date-input{
    background:transparent;
    border:0;
    color:var(--orion-text);
    outline:none;
    font-size:13px;
    font-weight:700;
    min-width:135px;
}
.orion-date-input::-webkit-calendar-picker-indicator{filter:invert(1);opacity:.7;cursor:pointer}
.orion-primary-btn{
    min-height:46px;
    padding:0 19px;
    border:0;
    border-radius:12px;
    cursor:pointer;
    color:#fff;
    font-size:14px;
    font-weight:900;
    background:linear-gradient(135deg,#9b5cf6,#7c3aed);
    box-shadow:0 8px 24px rgba(124,58,237,.26);
    transition:.2s ease;
}
.orion-primary-btn:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(124,58,237,.32)}

/* KPI */
.kpi-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-bottom:20px;
}
.kpi-card{
    min-height:132px;
    background:linear-gradient(145deg,var(--orion-panel-2),#0b1525);
    border:1px solid var(--orion-border);
    border-radius:16px;
    padding:18px 19px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    box-shadow:0 14px 30px rgba(0,0,0,.12);
}
.kpi-meta{min-width:0}
.kpi-label{font-size:12px;color:#b6c1d2;font-weight:800;margin-bottom:8px}
.kpi-value{font-size:25px;font-weight:950;color:var(--orion-text);white-space:nowrap}
.kpi-value.purple{color:var(--orion-purple-2)}
.kpi-value.green{color:var(--orion-green)}
.kpi-note{font-size:11px;color:var(--orion-muted-2);margin-top:5px}
.kpi-card{position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;inset-block:18px;right:0;width:3px;border-radius:3px 0 0 3px;background:linear-gradient(180deg,#a78bfa,#7c3aed);opacity:.92}
.kpi-card.green-card::before{background:linear-gradient(180deg,#5ee0ad,#16a36f)}
.kpi-index{font-size:10px;font-weight:900;letter-spacing:.7px;color:#61738c;background:rgba(255,255,255,.025);border:1px solid #22324a;border-radius:999px;padding:5px 9px;white-space:nowrap}

/* Sections */
.orion-card{
    background:linear-gradient(145deg,var(--orion-panel),#0a1422);
    border:1px solid var(--orion-border);
    border-radius:16px;
    box-shadow:0 14px 34px rgba(0,0,0,.14);
}
.quick-card{padding:18px;margin-bottom:20px}
.section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
.section-title{font-size:18px;font-weight:950;color:var(--orion-text);display:flex;align-items:center;gap:8px;margin:0}
.section-title .accent{color:var(--orion-purple-2)}
.section-hint{font-size:11px;color:var(--orion-muted-2)}
.saudi-note{display:flex;align-items:center;gap:8px;margin-top:10px;font-size:10px;color:#71839c}
.saudi-note strong{color:#a7b6c9;font-weight:800}

/* Categories */
.cat-grid{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:10px;
}
.cat-btn{
    min-height:66px;
    padding:11px 12px;
    border-radius:10px;
    border:1px solid #2a3851;
    background:#121c2f;
    cursor:pointer;
    text-align:right;
    transition:all .18s ease;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    gap:10px;
    color:var(--orion-text);
}
.cat-btn:hover{border-color:var(--orion-purple);background:rgba(124,58,237,.10);transform:translateY(-1px)}
.cat-btn.active{border-color:var(--orion-purple-2);background:rgba(124,58,237,.16);box-shadow:0 0 0 1px rgba(167,139,250,.08) inset}
.cat-photo{width:78px;height:56px;border-radius:11px;object-fit:cover;flex:0 0 auto;border:1px solid rgba(255,255,255,.08);box-shadow:0 8px 20px rgba(0,0,0,.18);background:#0f172a;filter:saturate(.72) contrast(1.04) brightness(.78)}
.cat-name{font-size:13px;font-weight:800;color:#e6edf7;line-height:1.5}
.cat-empty{grid-column:1/-1;text-align:center;color:var(--orion-muted-2);font-size:12px;padding:24px}

/* Expense composer */
.expense-composer{
    display:grid;
    grid-template-columns:1.1fr 1.5fr auto;
    gap:10px;
    margin-top:14px;
    padding-top:14px;
    border-top:1px solid var(--orion-border-soft);
    align-items:end;
}
.field-label{display:block;font-size:11px;color:var(--orion-muted);font-weight:800;margin-bottom:6px}
.composer-input{
    width:100%;height:44px;background:#0b1525;border:1px solid #2b3954;border-radius:10px;
    padding:0 12px;color:var(--orion-text);outline:none;font-size:13px;transition:.18s ease;
}
.composer-input:focus{border-color:var(--orion-purple)}
.amount-wrap{position:relative}
.amount-wrap .currency{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:11px;color:var(--orion-muted);font-weight:800}
.amount-wrap .composer-input{padding-left:48px;font-size:17px;font-weight:900;text-align:right}
.quick-amounts{display:flex;gap:6px;flex-wrap:wrap;margin-top:7px}
.quick-btn{height:28px;padding:0 9px;border-radius:7px;border:1px solid #283750;background:rgba(255,255,255,.03);color:#9aaac0;cursor:pointer;font-size:10px;font-weight:800;transition:.15s ease}
.quick-btn:hover{border-color:var(--orion-purple);color:var(--orion-purple-2)}
.save-btn{
    min-width:150px;height:44px;border:0;border-radius:10px;color:#fff;font-size:13px;font-weight:950;cursor:pointer;
    background:linear-gradient(135deg,#8b5cf6,#6d28d9);box-shadow:0 8px 20px rgba(109,40,217,.2);transition:.18s ease;
}
.save-btn:hover{transform:translateY(-1px)}
.save-btn:disabled{background:#202a3c;color:#627188;cursor:not-allowed;box-shadow:none;transform:none}

/* Table */
.table-card{padding:18px}
.table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap}
.toolbar-left{display:flex;gap:8px;align-items:center;flex-wrap:wrap;direction:ltr}
.search-box{position:relative;direction:rtl}
.search-box input{
    width:260px;height:38px;background:#0b1525;border:1px solid #2b3954;border-radius:9px;color:var(--orion-text);outline:none;padding:0 12px;font-size:12px
}
.search-box span{position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:14px;color:var(--orion-muted)}
.filter-chip{height:38px;padding:0 12px;border:1px solid #2b3954;border-radius:9px;background:#0d1728;color:#c5cfdd;font-size:12px;font-weight:700;display:flex;align-items:center;gap:7px}

.exp-table-wrap{overflow:auto;border:1px solid var(--orion-border-soft);border-radius:12px}
.exp-table{width:100%;border-collapse:collapse;min-width:760px}
.exp-table th{
    background:#111b2e;color:#d5deea;font-size:12px;font-weight:900;text-align:right;padding:13px 16px;border-bottom:1px solid var(--orion-border-soft)
}
.exp-table td{padding:13px 16px;border-bottom:1px solid #18243a;color:#cbd5e1;font-size:12px;vertical-align:middle}
.exp-table tbody tr{transition:.15s ease}
.exp-table tbody tr:hover{background:rgba(139,92,246,.035)}
.exp-table tbody tr:last-child td{border-bottom:0}
.exp-title{font-weight:800;color:#edf2f7}
.exp-cat-badge{display:inline-flex;align-items:center;gap:7px;color:#b9c4d4}
.exp-cat-dot{width:7px;height:7px;border-radius:50%;background:var(--orion-purple-2);box-shadow:0 0 10px rgba(167,139,250,.35)}
.user-cell{display:flex;align-items:center;gap:8px;direction:ltr;justify-content:flex-end}
.user-avatar{width:27px;height:27px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;font-size:11px;font-weight:900}
.exp-amount{font-size:13px;font-weight:950;color:var(--orion-purple-2);white-space:nowrap}
.action-cell{text-align:left}
.exp-delete{
    min-width:52px;height:30px;padding:0 10px;border-radius:8px;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.15);color:var(--orion-red);cursor:pointer;font-size:13px
}
.exp-delete:hover{background:rgba(248,113,113,.14)}
.empty-exp{text-align:center!important;padding:34px!important;color:var(--orion-muted-2)!important;font-size:12px!important}
.table-summary{
    display:flex;align-items:center;justify-content:flex-start;gap:10px;padding:14px 16px;background:#101a2c;border-top:1px solid var(--orion-border-soft);direction:rtl
}
.table-summary-label{font-size:12px;color:#b8c3d2;font-weight:800}
.table-summary-value{font-size:19px;color:var(--orion-purple-2);font-weight:950}

@media (max-width:1200px){
    .kpi-grid{grid-template-columns:repeat(2,1fr)}
    .cat-grid{grid-template-columns:repeat(3,1fr)}
    .expense-composer{grid-template-columns:1fr 1fr}.save-btn{grid-column:1/-1;width:100%}
}
@media (max-width:760px){
    .orion-page-head{flex-direction:column}.orion-head-actions{width:100%;direction:rtl}.orion-primary-btn,.orion-date-box{flex:1}
    .kpi-grid{grid-template-columns:1fr}.cat-grid{grid-template-columns:repeat(2,1fr)}
    .expense-composer{grid-template-columns:1fr}.search-box input{width:100%}.search-box{width:100%}.toolbar-left{width:100%;direction:rtl}
    .orion-page-title{font-size:26px}
}
</style>

<div class="orion-expenses-page" lang="ar-SA">
    <div class="orion-page-head">
        <div>
            <h1 class="orion-page-title">المصروفات اليومية</h1>
            <div class="orion-page-subtitle">إدارة المصروفات التشغيلية اليومية ومتابعتها</div>
            <div class="market-row">
                <span class="market-chip"><span class="market-dot"></span> المملكة العربية السعودية · SAR</span>
                <span class="hijri-date" id="hijri-date"></span>
            </div>
        </div>

        <div class="orion-head-actions">
            <button type="button" class="orion-primary-btn" onclick="document.getElementById('quick-add-expense')?.scrollIntoView({behavior:'smooth',block:'start'})">
                إضافة مصروف جديد
            </button>
            <div class="orion-date-box">
                <span class="orion-date-label">تاريخ المصروفات</span>
                <input type="date" wire:model.live="filterDate" class="orion-date-input">
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-meta">
                <div class="kpi-label">إجمالي اليوم</div>
                <div class="kpi-value">{{ number_format($todayTotal, 2) }} ر.س</div>
                <div class="kpi-note">إجمالي المصروفات المسجلة</div>
            </div>
            <span class="kpi-index">SAR</span>
        </div>

        <div class="kpi-card">
            <div class="kpi-meta">
                <div class="kpi-label">عدد العمليات</div>
                <div class="kpi-value">{{ $todayCount }}</div>
                <div class="kpi-note">عملية مسجلة في اليوم المحدد</div>
            </div>
            <span class="kpi-index">TXN</span>
        </div>

        <div class="kpi-card">
            <div class="kpi-meta">
                <div class="kpi-label">متوسط العملية</div>
                <div class="kpi-value">{{ number_format($todayAverage, 2) }} ر.س</div>
                <div class="kpi-note">متوسط قيمة المصروف</div>
            </div>
            <span class="kpi-index">AVG</span>
        </div>

        <div class="kpi-card green-card">
            <div class="kpi-meta">
                <div class="kpi-label">أعلى مصروف</div>
                <div class="kpi-value green">{{ number_format($todayMax, 2) }} ر.س</div>
                <div class="kpi-note">أعلى عملية في اليوم المحدد</div>
            </div>
            <span class="kpi-index">MAX</span>
        </div>
    </div>

    <div class="orion-card quick-card" id="quick-add-expense"
        x-data="{ amount: @entangle('amount').live, categoryId: @entangle('selectedCategoryId').live }">
        <div class="section-head">
            <h2 class="section-title">إضافة مصروف جديد</h2>
            <div class="section-hint">اختر التصنيف ثم أدخل قيمة المصروف</div>
        </div>

        <div class="cat-grid">
            @forelse($this->getCategories() as $cat)
                <button class="cat-btn {{ $selectedCategoryId === $cat->id ? 'active' : '' }}" wire:click="selectCategory({{ $cat->id }})">
                    <img src="{{ $categoryPhoto($cat->name) }}" alt="{{ $cat->name }}" class="cat-photo" loading="lazy">
                    <span class="cat-name">{{ $cat->name }}</span>
                </button>
            @empty
                <div class="cat-empty">لا توجد أنواع مصروفات — أضفها من إعدادات المصروفات</div>
            @endforelse
        </div>

        <div class="saudi-note"><strong>تنبيه:</strong> أدخل الوصف ورقم الفاتورة أو المرجع في خانة الملاحظة عند الحاجة.</div>

        <div class="expense-composer">
            <div>
                <label class="field-label">المبلغ</label>
                <div class="amount-wrap">
                    <input type="number" x-model.number="amount" class="composer-input" placeholder="0.00" min="0" step="0.5">
                    <span class="currency">ر.س</span>
                </div>
                <div class="quick-amounts">
                    @foreach([50,100,200,500,1000] as $qa)
                        <button type="button" class="quick-btn" @click="amount = {{ $qa }}">{{ number_format($qa) }}</button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="field-label">ملاحظة</label>
                <input type="text" wire:model="description" class="composer-input" placeholder="ملاحظة اختيارية عن المصروف...">
            </div>

            <button wire:click="saveExpense" class="save-btn" :disabled="!categoryId || Number(amount) <= 0">
                تسجيل المصروف
            </button>
        </div>
    </div>

    <div class="orion-card table-card">
        <div class="table-toolbar">
            <h2 class="section-title">سجل مصروفات اليوم</h2>

            <div class="toolbar-left">
                <div class="search-box">
                    <input type="search" placeholder="ابحث بالبيان أو التصنيف أو المستخدم..." oninput="filterExpenseRows(this.value)">
                </div>
                <div class="filter-chip">تصفية</div>
                <div class="filter-chip">كل التصنيفات</div>
            </div>
        </div>

        <div class="exp-table-wrap">
            <table class="exp-table" id="expenses-table">
                <thead>
                    <tr>
                        <th>البيان</th>
                        <th>التصنيف</th>
                        <th>المستخدم</th>
                        <th>الوقت</th>
                        <th>المبلغ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayExpenses as $exp)
                        <tr class="expense-row">
                            <td>
                                <div class="exp-title">{{ $exp->description ?: ($exp->category->name ?? 'مصروف') }}</div>
                            </td>
                            <td>
                                <span class="exp-cat-badge">
                                    <span class="exp-cat-dot"></span>
                                    {{ $exp->category->name ?? 'أخرى' }}
                                </span>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <span>{{ $exp->createdBy?->name ?? 'System' }}</span>
                                    <span class="user-avatar">{{ mb_substr($exp->createdBy?->name ?? 'S',0,1) }}</span>
                                </div>
                            </td>
                            <td>{{ $exp->created_at->format('H:i') }}</td>
                            <td><span class="exp-amount">{{ number_format($exp->amount,2) }} ر.س</span></td>
                            <td class="action-cell">
                                <button wire:click="deleteExpense({{ $exp->id }})"
                                    onclick="confirm('حذف المصروف؟') || event.stopImmediatePropagation()"
                                    class="exp-delete" title="حذف">حذف</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-exp">لا توجد مصروفات في هذا اليوم</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="table-summary">
                <span class="table-summary-label">إجمالي اليوم:</span>
                <span class="table-summary-value">{{ number_format($todayTotal,2) }} ر.س</span>
            </div>
        </div>
    </div>
</div>

<script>
function updateSaudiHijriDate(){
    const input = document.querySelector('.orion-date-input');
    const target = document.getElementById('hijri-date');
    if(!input || !target || !input.value) return;
    const d = new Date(input.value + 'T12:00:00');
    try {
        const hijri = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura', {day:'numeric',month:'long',year:'numeric'}).format(d);
        target.textContent = hijri + ' هـ';
    } catch(e) { target.textContent = ''; }
}
document.addEventListener('DOMContentLoaded', updateSaudiHijriDate);
document.addEventListener('change', function(e){ if(e.target && e.target.classList.contains('orion-date-input')) updateSaudiHijriDate(); });
document.addEventListener('livewire:navigated', updateSaudiHijriDate);

function filterExpenseRows(term){
    const q = (term || '').trim().toLowerCase();
    document.querySelectorAll('#expenses-table tbody .expense-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
</x-filament-panels::page>