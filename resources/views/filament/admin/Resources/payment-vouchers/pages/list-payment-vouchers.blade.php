<x-filament-panels::page>
@php
    $summary = $this->getUiSummary();
    $quick = request()->query('quick');
    $quickUrl = static fn (?string $value) => $value === null
        ? \App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource::getUrl('index')
        : \App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource::getUrl('index', ['quick' => $value]);
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
.orion-voucher-page{direction:rtl;--ov-bg:#08111f;--ov-panel:#0d1728;--ov-panel2:#111b2e;--ov-border:#26344c;--ov-soft:#1c2940;--ov-text:#f8fafc;--ov-muted:#8ea0b8;--ov-muted2:#5f7189;--ov-purple:#8b5cf6;--ov-purple2:#a78bfa;--ov-green:#34d399;--ov-red:#f87171;--ov-blue:#60a5fa;color:var(--ov-text)}
.ov-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}.ov-kicker{font-size:10px;font-weight:950;letter-spacing:.9px;color:var(--ov-purple2);margin-bottom:7px}.ov-title{margin:0;font-size:30px;line-height:1.1;color:var(--ov-text);font-weight:950}.ov-sub{margin-top:8px;font-size:12px;color:var(--ov-muted)}
.ov-meta{display:flex;align-items:center;gap:8px;margin-top:11px;flex-wrap:wrap}.ov-meta-chip{display:inline-flex;align-items:center;gap:7px;height:28px;padding:0 10px;border-radius:999px;border:1px solid rgba(52,211,153,.22);background:rgba(52,211,153,.08);color:#94e7c7;font-size:10px;font-weight:850}.ov-dot{width:7px;height:7px;border-radius:50%;background:var(--ov-green);box-shadow:0 0 0 4px rgba(52,211,153,.10)}
.ov-create{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 18px;border-radius:11px;text-decoration:none!important;color:#fff!important;font-size:13px;font-weight:950;background:linear-gradient(135deg,#9b5cf6,#6d28d9);border:1px solid rgba(167,139,250,.3);box-shadow:0 9px 24px rgba(109,40,217,.24);transition:.18s ease}.ov-create:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(109,40,217,.3)}
.ov-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:15px}.ov-kpi{position:relative;overflow:hidden;min-height:116px;padding:16px 17px;border-radius:15px;border:1px solid var(--ov-border);background:linear-gradient(145deg,var(--ov-panel2),#0b1525);box-shadow:0 14px 30px rgba(0,0,0,.11)}.ov-kpi:before{content:'';position:absolute;inset-block:17px;right:0;width:3px;background:linear-gradient(180deg,#a78bfa,#7c3aed)}.ov-kpi.green:before{background:linear-gradient(180deg,#5ee0ad,#16a36f)}.ov-kpi.red:before{background:linear-gradient(180deg,#fb7185,#dc2626)}.ov-kpi.blue:before{background:linear-gradient(180deg,#7dd3fc,#3b82f6)}
.ov-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.ov-kpi-label{color:#b6c1d2;font-size:11px;font-weight:850}.ov-kpi-code{padding:4px 8px;border:1px solid #22324a;border-radius:999px;background:rgba(255,255,255,.025);color:#61738c;font-size:9px;font-weight:950}.ov-kpi-value{margin-top:12px;font-size:25px;line-height:1;color:var(--ov-purple2);font-weight:950}.ov-kpi.green .ov-kpi-value{color:var(--ov-green)}.ov-kpi.red .ov-kpi-value{color:#fb7185}.ov-kpi.blue .ov-kpi-value{color:#7dd3fc}.ov-kpi-note{margin-top:7px;color:var(--ov-muted2);font-size:10px}
.ov-filterbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;padding:12px;border:1px solid var(--ov-soft);border-radius:13px;background:linear-gradient(145deg,var(--ov-panel),#0b1422)}.ov-filter-label{font-size:11px;font-weight:900;color:#cbd5e1}.ov-filters{display:flex;gap:7px;flex-wrap:wrap}.ov-chip{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:0 11px;border-radius:9px;border:1px solid #2a3852;background:#0c1626;color:#95a5bb!important;text-decoration:none!important;font-size:10px;font-weight:850}.ov-chip:hover{color:#d8ccff!important;border-color:#6d4bc8;background:rgba(139,92,246,.08)}.ov-chip.active{color:#efe9ff!important;border-color:rgba(167,139,250,.55);background:linear-gradient(135deg,rgba(139,92,246,.24),rgba(109,40,217,.18))}
.ov-shell{padding:0;border:1px solid var(--ov-border);border-radius:15px;overflow:hidden;background:linear-gradient(145deg,var(--ov-panel),#0a1422);box-shadow:0 14px 34px rgba(0,0,0,.13)}.ov-shell-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 17px;border-bottom:1px solid var(--ov-soft)}.ov-shell-title{font-size:16px;font-weight:950;color:var(--ov-text)}.ov-shell-sub{margin-top:4px;font-size:10px;color:var(--ov-muted2)}.ov-live{display:inline-flex;align-items:center;gap:7px;height:26px;padding:0 9px;border-radius:999px;border:1px solid rgba(52,211,153,.22);background:rgba(52,211,153,.08);color:#8fe4c1;font-size:9px;font-weight:900}
.ov-table .fi-ta,.ov-table .fi-ta-ctn{background:transparent!important;box-shadow:none!important;border:0!important;border-radius:0!important}.ov-table .fi-ta-header,.ov-table .fi-ta-table thead tr{background:#0b1525!important}.ov-table .fi-ta-header{border-bottom:1px solid var(--ov-soft)!important}.ov-table .fi-ta-header-toolbar{padding:11px 14px!important}.ov-table .fi-input-wrp,.ov-table .fi-ta-search-field .fi-input-wrp{background:#0a1423!important;border:1px solid #2a3954!important;box-shadow:none!important;border-radius:9px!important}.ov-table .fi-input{color:#dbe5f3!important}.ov-table .fi-ta-header-cell{background:#0b1525!important;border-color:var(--ov-soft)!important;color:#71829c!important;font-size:10px!important;font-weight:900!important}.ov-table .fi-ta-row{background:#0f192b!important;border-color:var(--ov-soft)!important;transition:.15s ease}.ov-table .fi-ta-row:nth-child(even){background:#101b2e!important}.ov-table .fi-ta-row:hover{background:#14213a!important}.ov-table .fi-ta-cell{border-color:var(--ov-soft)!important;color:#dce5f2!important}.ov-table .fi-ta-text-item-label{color:#dce5f2!important;font-size:11px!important}.ov-table .fi-ta-footer{background:#0b1525!important;border-top:1px solid var(--ov-soft)!important}.ov-table .fi-btn{border-radius:8px!important}
@media(max-width:1100px){.ov-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.ov-head{flex-direction:column}.ov-create{width:100%}.ov-kpis{grid-template-columns:1fr}.ov-filterbar{align-items:flex-start}.ov-filters{width:100%}.ov-chip{flex:1;min-width:105px}.ov-title{font-size:25px}}
</style>

<div class="orion-voucher-page" lang="ar-SA">
    <div class="ov-head">
        <div>
            <div class="ov-kicker">PAYABLES · DISBURSEMENT CENTER</div>
            <h1 class="ov-title">سندات الصرف</h1>
            <div class="ov-sub">مركز الصرف للموردين والمصروفات ورد مبالغ العملاء مع ربط كل عملية بمصدرها وقيدها.</div>
            <div class="ov-meta"><span class="ov-meta-chip"><span class="ov-dot"></span> صرف مرحّل · SAR</span></div>
        </div>
        @if(\App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource::canCreate())
            <a class="ov-create" href="{{ \App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource::getUrl('create') }}">+ سند صرف جديد</a>
        @endif
    </div>

    <div class="ov-kpis">
        <div class="ov-kpi"><div class="ov-kpi-top"><span class="ov-kpi-label">إجمالي السندات</span><span class="ov-kpi-code">TOTAL</span></div><div class="ov-kpi-value">{{ number_format($summary['total_count']) }}</div><div class="ov-kpi-note">بقيمة {{ number_format($summary['total_amount'],2) }} ر.س</div></div>
        <div class="ov-kpi blue"><div class="ov-kpi-top"><span class="ov-kpi-label">صرف اليوم</span><span class="ov-kpi-code">TODAY</span></div><div class="ov-kpi-value">{{ number_format($summary['today_amount'],2) }}</div><div class="ov-kpi-note">{{ $summary['today_count'] }} سند اليوم · ر.س</div></div>
        <div class="ov-kpi green"><div class="ov-kpi-top"><span class="ov-kpi-label">السندات المرحلة</span><span class="ov-kpi-code">POST</span></div><div class="ov-kpi-value">{{ number_format($summary['posted_count']) }}</div><div class="ov-kpi-note">قيمة مرحّلة {{ number_format($summary['posted_amount'],2) }} ر.س</div></div>
        <div class="ov-kpi red"><div class="ov-kpi-top"><span class="ov-kpi-label">السندات المعكوسة</span><span class="ov-kpi-code">REV</span></div><div class="ov-kpi-value">{{ number_format($summary['reversed_count']) }}</div><div class="ov-kpi-note">تم عكسها عبر القيود العكسية</div></div>
    </div>

    <div class="ov-filterbar"><div class="ov-filter-label">عرض سريع</div><div class="ov-filters">
        <a class="ov-chip {{ !$quick ? 'active' : '' }}" href="{{ $quickUrl(null) }}">الكل</a>
        <a class="ov-chip {{ $quick === 'today' ? 'active' : '' }}" href="{{ $quickUrl('today') }}">اليوم</a>
        <a class="ov-chip {{ $quick === 'posted' ? 'active' : '' }}" href="{{ $quickUrl('posted') }}">مرحّل</a>
        <a class="ov-chip {{ $quick === 'reversed' ? 'active' : '' }}" href="{{ $quickUrl('reversed') }}">معكوس</a>
        <a class="ov-chip {{ $quick === 'supplier' ? 'active' : '' }}" href="{{ $quickUrl('supplier') }}">سداد مورد</a>
        <a class="ov-chip {{ $quick === 'expense' ? 'active' : '' }}" href="{{ $quickUrl('expense') }}">مصروف مباشر</a>
        <a class="ov-chip {{ $quick === 'refund' ? 'active' : '' }}" href="{{ $quickUrl('refund') }}">رد مبلغ لعميل</a>
    </div></div>

    <div class="ov-shell"><div class="ov-shell-head"><div><div class="ov-shell-title">سجل سندات الصرف</div><div class="ov-shell-sub">مدفوعات الموردين والمصروفات وRefunds مع مصدر الصرف والقيد المحاسبي.</div></div><span class="ov-live"><span class="ov-dot"></span> LIVE PAYABLES</span></div><div class="ov-table">{{ $this->table }}</div></div>
</div>
</x-filament-panels::page>
