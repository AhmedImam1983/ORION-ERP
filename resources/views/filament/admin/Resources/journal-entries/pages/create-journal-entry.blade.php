<x-filament-panels::page>

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{--jc-bg:#07101d;--jc-panel:#0d1728;--jc-line:#22324d;--jc-text:#eef3fb;--jc-muted:#71829b;--jc-purple:#8b5cf6;--jc-green:#34d399;--jc-red:#fb7185;--jc-amber:#fbbf24}
.jc-shell{direction:rtl;color:var(--jc-text);padding-bottom:28px}
.jc-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:18px}
.jc-kicker{font-size:10px;color:#927cff;font-weight:900;letter-spacing:.8px;margin-bottom:7px}.jc-title{font-size:28px;font-weight:950;line-height:1.1}.jc-sub{font-size:11px;color:var(--jc-muted);margin-top:6px}
.jc-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:13px}
.jc-kpi{position:relative;background:linear-gradient(145deg,#0e192b,#0a1423);border:1px solid #22334f;border-radius:14px;padding:16px 17px;min-height:100px}
.jc-code{position:absolute;left:12px;top:12px;font-size:8px;color:#60728e;border:1px solid #283b59;border-radius:7px;padding:4px 6px;font-weight:900}.jc-label{font-size:10px;color:#8c9ab0;font-weight:800}.jc-value{font-size:22px;font-weight:950;margin-top:9px;direction:ltr;text-align:right}.jc-note{font-size:8px;color:#5d708a;margin-top:4px}
.jc-green{color:var(--jc-green)}.jc-red{color:var(--jc-red)}.jc-purple{color:#b4a1ff}.jc-amber{color:var(--jc-amber)}
.jc-card{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;overflow:hidden}.jc-card-head{padding:14px 16px;border-bottom:1px solid #1b2a40;display:flex;justify-content:space-between;align-items:center}.jc-card-title{font-size:14px;font-weight:950}.jc-card-sub{font-size:9px;color:#61728b;margin-top:3px}
.jc-note-card{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px}.jc-note-item{background:#0b1524;border:1px solid #1e2e47;border-radius:10px;padding:10px}.jc-note-item b{display:block;font-size:10px;color:#d9e1ee}.jc-note-item span{font-size:8px;color:#63758f}
.jc-form-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:12px;align-items:start}.jc-side{position:sticky;top:12px;background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;padding:16px}.jc-side-title{font-size:13px;font-weight:950;margin-bottom:12px}.jc-check{padding:10px 0;border-bottom:1px solid #1b2a40}.jc-check:last-child{border-bottom:0}.jc-check b{display:block;font-size:10px}.jc-check span{font-size:8px;color:#63758f}
.jc-balance{margin-top:12px;padding:12px;border-radius:10px;border:1px solid rgba(52,211,153,.2);background:rgba(52,211,153,.06);font-size:10px;color:#62e2b2}.jc-balance.bad{border-color:rgba(251,113,133,.22);background:rgba(251,113,133,.06);color:#fb8da0}
.jc-view-grid{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:12px;align-items:start}.jc-summary{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;padding:16px}.jc-summary-row{display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid #1b2a40;font-size:9px;color:#72839c}.jc-summary-row:last-child{border-bottom:0}.jc-summary-row strong{color:#dce5f2;font-size:10px;text-align:left}.jc-status{display:inline-flex;border-radius:999px;padding:5px 8px;font-size:8px;font-weight:900}.jc-status.ok{background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:#5ce0ad}.jc-status.warn{background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);color:#f7c84a}.jc-status.bad{background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.2);color:#fb8ca0}
@media(max-width:1000px){.jc-kpis{grid-template-columns:1fr 1fr}.jc-form-layout,.jc-view-grid{grid-template-columns:1fr}.jc-side{position:static}}
@media(max-width:650px){.jc-head{align-items:flex-start;flex-direction:column}.jc-kpis,.jc-note-card{grid-template-columns:1fr}.jc-title{font-size:23px}}
</style>

<div class="jc-shell">
    <div class="jc-head">
        <div>
            <div class="jc-kicker">MANUAL JOURNAL</div>
            <div class="jc-title">إنشاء قيد يدوي</div>
            <div class="jc-sub">أدخل الحسابات والمبالغ ثم راجع توازن المدين والدائن قبل الترحيل</div>
        </div>
    </div>

    <form wire:submit="create">
        <div class="jc-form-layout">
            <div class="jc-card" style="padding:14px">
                {{ $this->form }}
                <div style="margin-top:14px">
                    <x-filament::actions :actions="$this->getFormActions()" />
                </div>
            </div>

            <div class="jc-side">
                <div class="jc-side-title">قواعد القيد</div>
                <div class="jc-check"><b>سطران على الأقل</b><span>يجب أن يحتوي القيد على حسابين أو أكثر</span></div>
                <div class="jc-check"><b>طرف واحد لكل سطر</b><span>السطر مدين أو دائن، وليس الاثنين معاً</span></div>
                <div class="jc-check"><b>الترحيل يحتاج توازن</b><span>لا يمكن حفظ قيد مرحّل وفيه فرق</span></div>
                <div class="jc-check"><b>المسودة مرنة</b><span>يمكن حفظها للمراجعة قبل الترحيل</span></div>
                <div class="jc-balance">النظام يعيد حساب الإجماليات من سطور القيد عند الحفظ.</div>
            </div>
        </div>
    </form>
</div>
</x-filament-panels::page>
