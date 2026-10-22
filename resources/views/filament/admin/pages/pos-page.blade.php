<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --pos-bg:#07101f;--pos-panel:#0d182b;--pos-panel-2:#111e33;--pos-line:#22324d;
    --pos-line-soft:rgba(255,255,255,.065);--pos-text:#f7f9fc;--pos-muted:#8493aa;
    --pos-purple:#8b5cf6;--pos-purple-2:#6d4aff;--pos-green:#34d399;--pos-red:#f87171;
    --pos-amber:#fbbf24;--pos-cyan:#38bdf8
}
[x-cloak]{display:none!important}
.fi-main{max-width:none!important}
.pos-shell{direction:rtl;color:var(--pos-text);display:flex;flex-direction:column;gap:12px}
.pos-topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 18px;border:1px solid var(--pos-line);border-radius:16px;background:linear-gradient(180deg,#101c30,#0b1525);box-shadow:0 18px 50px rgba(0,0,0,.18)}
.pos-brand{display:flex;align-items:center;gap:13px}
.pos-brand-mark{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--pos-purple),#4f46e5);display:grid;place-items:center;font-weight:950;font-size:13px;letter-spacing:.4px}
.pos-brand-title{font-size:20px;font-weight:950;line-height:1.15}
.pos-brand-sub{font-size:11px;color:var(--pos-muted);margin-top:4px}
.pos-online{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.pos-pill{border:1px solid var(--pos-line);background:#0a1423;border-radius:999px;padding:7px 10px;font-size:11px;color:#a8b5c8;font-weight:800}
.pos-pill.good{color:var(--pos-green);border-color:rgba(52,211,153,.24);background:rgba(52,211,153,.07)}
.pos-dot{width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block;margin-left:5px}

.session-strip{display:grid;grid-template-columns:minmax(0,1.5fr) repeat(4,minmax(110px,.55fr)) auto;gap:8px;align-items:stretch}
.session-main,.session-stat,.session-close{border:1px solid var(--pos-line);border-radius:13px;background:#0b1626;min-height:58px}
.session-main{padding:10px 13px;display:flex;align-items:center;gap:12px}
.session-code{font-weight:950;color:var(--pos-green);font-size:14px}
.session-meta{font-size:11px;color:var(--pos-muted);margin-top:3px}
.session-stat{padding:9px 11px}
.session-stat span{display:block;font-size:9px;color:#65758e;font-weight:800;margin-bottom:3px}
.session-stat strong{font-size:14px;color:#f4f7ff}
.session-close{padding:0 15px;color:#ff9b9b;border-color:rgba(248,113,113,.24);background:rgba(248,113,113,.07);cursor:pointer;font-weight:900}

.pos-workspace{display:grid;grid-template-columns:minmax(0,1fr) 370px;gap:12px;min-height:650px;height:calc(100vh - 320px)}
.pos-main,.pos-side{min-width:0;min-height:0;display:flex;flex-direction:column;gap:10px}
.pos-side{overflow-y:auto;padding-left:2px}

.scanbar{position:relative;display:flex;align-items:center;gap:12px;border:1px solid rgba(139,92,246,.42);background:linear-gradient(180deg,#101d32,#0b1728);border-radius:15px;padding:9px 12px;box-shadow:0 14px 40px rgba(0,0,0,.18)}
.scan-key{height:40px;min-width:46px;border-radius:10px;background:rgba(139,92,246,.15);color:#c4b5fd;display:grid;place-items:center;font-size:11px;font-weight:950}
.scan-input{flex:1;height:42px;border:none!important;background:transparent!important;outline:none;color:white;font-size:17px;font-weight:800;padding:0 4px!important;box-shadow:none!important}
.scan-input::placeholder{color:#65748c;font-weight:500}
.scan-shortcut{font-size:10px;color:#66778f;white-space:nowrap}

.search-results{position:absolute;top:59px;right:0;left:0;z-index:80;border:1px solid #2a3b58;border-radius:14px;overflow:auto;max-height:330px;background:#0b1628;box-shadow:0 24px 55px rgba(0,0,0,.45)}
.sr-item{display:grid;grid-template-columns:54px minmax(0,1fr) auto;gap:12px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--pos-line-soft);cursor:pointer}
.sr-item:hover,.sr-item.active{background:rgba(139,92,246,.12)}
.sr-img,.cart-img{width:46px;height:46px;border-radius:10px;background:#101d31;border:1px solid var(--pos-line);object-fit:contain;padding:3px}
.sr-placeholder,.cart-placeholder{display:grid;place-items:center;color:#7d8ba1;font-size:10px;font-weight:900}
.sr-name{font-size:14px;font-weight:900}.sr-code{font-size:10px;color:#71819a;margin-top:2px}.sr-stock{font-size:10px;margin-top:3px}
.sr-price{font-size:16px;font-weight:950;color:#c4b5fd;white-space:nowrap}

.cart-card{flex:1;min-height:0;border:1px solid var(--pos-line);border-radius:16px;background:linear-gradient(180deg,#101b2e,#0a1525);overflow:hidden;display:flex;flex-direction:column}
.cart-head{display:grid;grid-template-columns:minmax(220px,1fr) 110px 110px 90px 145px 48px;background:#0b1422;border-bottom:1px solid var(--pos-line);color:#8392a8;font-size:11px;font-weight:900}
.cart-head>div{padding:12px;text-align:center}.cart-head>div:first-child{text-align:right;padding-right:17px}
.cart-body{flex:1;overflow:auto}
.cart-row{display:grid;grid-template-columns:minmax(220px,1fr) 110px 110px 90px 145px 48px;align-items:center;border-bottom:1px solid var(--pos-line-soft);min-height:74px}
.cart-row:hover{background:rgba(139,92,246,.035)}
.cart-product{display:grid;grid-template-columns:48px minmax(0,1fr);gap:10px;align-items:center;padding:8px 12px}
.cart-name{font-size:14px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cart-sub{font-size:10px;color:#72829a;margin-top:3px}
.cart-cell{padding:8px;display:flex;align-items:center;justify-content:center}
.qty-control{display:grid;grid-template-columns:30px 42px 30px;border:1px solid #34445e;border-radius:9px;overflow:hidden}
.qty-btn{border:0;background:#121f35;color:#c4b5fd;font-size:17px;font-weight:900;height:34px;cursor:pointer}
.qty-value{display:grid;place-items:center;background:#0b1526;color:#fff;font-weight:900;font-size:13px}
.cart-input{width:100%;height:38px!important;background:#0b1526!important;border:1px solid #2b3b57!important;border-radius:8px!important;color:#e8edf7!important;text-align:center!important;padding:5px!important;font-size:12px!important}
.cart-total{font-size:14px;font-weight:950;color:#b9a6ff}
.del-btn{width:34px;height:34px;border-radius:8px;border:1px solid rgba(248,113,113,.2);background:rgba(248,113,113,.07);color:#ff9191;cursor:pointer;font-weight:900}
.empty-cart{height:100%;min-height:300px;display:flex;align-items:center;justify-content:center;text-align:center;color:#607089}
.empty-cart strong{display:block;color:#8b9ab0;font-size:15px;margin-bottom:6px}.empty-cart span{font-size:11px}

.side-card{border:1px solid var(--pos-line);border-radius:15px;background:linear-gradient(180deg,#101c30,#0a1525);padding:14px}
.side-title{display:flex;align-items:center;justify-content:space-between;font-size:11px;color:#8b9ab1;font-weight:900;margin-bottom:9px}
.side-title b{color:#c2cce0;font-size:11px}
.customer-select{width:100%;height:42px;background:#0b1526;border:1px solid #2b3b57;border-radius:9px;color:#eef2f9;padding:0 10px;font-size:12px}
.total-hero{text-align:center;padding:5px 0 8px}
.total-hero .amount{font-size:42px;line-height:1;font-weight:950;color:#c4b5fd;letter-spacing:-1px}
.total-hero .currency{font-size:11px;color:#7f8ea5;margin-top:6px}
.total-breakdown{border-top:1px solid var(--pos-line-soft);margin-top:10px;padding-top:9px}
.total-breakdown-row{display:flex;align-items:center;justify-content:space-between;font-size:11px;padding:4px 0;color:#7f8da3}
.total-breakdown-row strong{color:#e5eaf3;font-size:12px}
.quick-amounts{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:10px}
.qa-btn{height:34px;border-radius:8px;border:1px solid var(--pos-line);background:#0b1526;color:#9caac0;font-size:11px;font-weight:900;cursor:pointer}
.qa-btn:hover{border-color:#5c4ab4;color:#ddd6fe;background:rgba(139,92,246,.09)}

.pay-methods{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}
.pm-btn{height:44px;border:1px solid var(--pos-line);background:#0b1526;color:#8291a7;border-radius:9px;cursor:pointer;font-size:11px;font-weight:900}
.pm-btn.active{border-color:#7658da;background:rgba(139,92,246,.13);color:#e4ddff;box-shadow:0 0 0 1px rgba(139,92,246,.08)}
.cash-input{width:100%;height:48px!important;margin-top:9px;background:#081321!important;border:1px solid #34445e!important;border-radius:10px!important;color:#fff!important;font-size:20px!important;font-weight:950!important;text-align:center!important}
.change-box{display:flex;justify-content:space-between;align-items:center;margin-top:8px;border:1px solid rgba(52,211,153,.2);background:rgba(52,211,153,.065);border-radius:9px;padding:9px 10px;font-size:11px;color:#8ee7c2}
.change-box strong{font-size:17px;color:var(--pos-green)}
.checkout-btn{height:64px;border:0;border-radius:14px;background:linear-gradient(135deg,#8b5cf6,#5c46d8);color:#fff;font-size:16px;font-weight:950;cursor:pointer;box-shadow:0 14px 35px rgba(109,74,255,.24)}
.checkout-btn:disabled{cursor:not-allowed;background:#17233a;color:#56657d;box-shadow:none}
.checkout-hint{display:block;font-size:9px;font-weight:700;opacity:.7;margin-top:3px}
.return-btn{height:40px;border-radius:10px;border:1px solid rgba(248,113,113,.22);background:rgba(248,113,113,.07);color:#ff9c9c;font-size:11px;font-weight:900;cursor:pointer}

.session-mini .mini-row{display:flex;justify-content:space-between;font-size:11px;padding:5px 0;border-bottom:1px solid var(--pos-line-soft);color:#7f8da3}
.session-mini .mini-row:last-child{border:0}.session-mini strong{color:#e7ecf5}
.recent-list{display:flex;flex-direction:column;gap:5px}
.recent-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;padding:7px 8px;border:1px solid var(--pos-line-soft);border-radius:8px;background:#0a1423}
.recent-num{font-size:10px;font-weight:900;color:#bcaeff}.recent-meta{font-size:9px;color:#67778f;margin-top:2px}.recent-amount{font-size:11px;font-weight:950;color:#e7ecf5;white-space:nowrap}

.shortcuts{display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding:7px 9px;border:1px solid var(--pos-line);border-radius:11px;background:#0a1423}
.shortcut{font-size:9px;color:#78879e;border:1px solid var(--pos-line-soft);border-radius:6px;padding:4px 6px}
.shortcut b{color:#fff;margin-left:3px}

.modal-ov{position:fixed;inset:0;z-index:400;background:rgba(2,7,15,.84);backdrop-filter:blur(7px);display:flex;align-items:center;justify-content:center;padding:20px}
.modal-bx{width:470px;max-width:96vw;max-height:92vh;overflow:auto;border:1px solid #30415e;border-radius:18px;background:linear-gradient(180deg,#111e32,#0a1525);box-shadow:0 30px 80px rgba(0,0,0,.55);padding:22px}
.modal-title{font-size:18px;font-weight:950;margin-bottom:16px}
.form-label{font-size:10px;color:#7f8ea4;font-weight:900;margin-bottom:5px}
.form-input,.form-select{width:100%;height:43px;background:#0b1526;border:1px solid #2d3e5a;border-radius:9px;color:white;padding:0 11px;margin-bottom:10px;font-size:12px}
.btn-primary,.btn-success,.btn-ghost,.btn-danger{width:100%;height:42px;border-radius:9px;font-weight:900;font-size:12px;cursor:pointer}
.btn-primary{border:0;background:linear-gradient(135deg,#8b5cf6,#6840e7);color:#fff}
.btn-success{border:1px solid rgba(52,211,153,.25);background:rgba(52,211,153,.11);color:#78e5ba}
.btn-danger{border:1px solid rgba(248,113,113,.25);background:rgba(248,113,113,.09);color:#ff9b9b}
.btn-ghost{border:1px solid var(--pos-line);background:#0a1423;color:#93a1b7}
.success-mark{width:66px;height:66px;margin:0 auto 12px;border-radius:50%;display:grid;place-items:center;border:1px solid rgba(52,211,153,.24);background:rgba(52,211,153,.08);color:var(--pos-green);font-size:20px;font-weight:950}
.success-title{text-align:center;font-size:20px;font-weight:950;color:#7be5bb;margin-bottom:13px}
.modal-row{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--pos-line-soft);font-size:11px;color:#8190a6}
.modal-row strong{color:#edf1f8}
.return-search-item{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:9px;border-bottom:1px solid var(--pos-line-soft);cursor:pointer}
.return-search-item:hover{background:rgba(139,92,246,.08)}
.return-grid-head,.return-grid-row{display:grid;grid-template-columns:minmax(0,1fr) 70px 80px;gap:8px;align-items:center}
.return-grid-head{font-size:9px;color:#65748c;padding:7px;border-bottom:1px solid var(--pos-line)}
.return-grid-row{padding:8px 7px;border-bottom:1px solid var(--pos-line-soft)}
.return-qty{width:100%;height:34px;background:#0b1526;border:1px solid #30405b;border-radius:7px;color:#fff;text-align:center}

@media(max-width:1180px){
    .pos-workspace{grid-template-columns:1fr;height:auto}
    .pos-main{min-height:620px}.pos-side{display:grid;grid-template-columns:repeat(2,1fr);overflow:visible}
    .checkout-btn,.return-btn{grid-column:1/-1}.session-strip{grid-template-columns:1fr 1fr 1fr}
}
@media(max-width:760px){
    .pos-topbar{align-items:flex-start}.pos-online{justify-content:flex-end}
    .session-strip{grid-template-columns:1fr 1fr}.session-main{grid-column:1/-1}.session-close{grid-column:1/-1;height:44px}
    .pos-side{display:flex}.pos-workspace{min-height:0}.scan-shortcut{display:none}
    .cart-head,.cart-row{grid-template-columns:minmax(160px,1fr) 90px 80px 70px 100px 42px}
    .cart-product{grid-template-columns:38px minmax(0,1fr)}.cart-img{width:38px;height:38px}
}
</style>

{{-- فتح جلسة --}}
@if($showOpenSession)
@php
    $userBranch = auth()->user()->branch_id ? \App\Models\Branch::find(auth()->user()->branch_id) : null;
    $isAdmin = auth()->user()->hasAnyRole(['super_admin','admin']);
@endphp
<div class="modal-ov">
    <div class="modal-bx">
        <div class="modal-title">فتح جلسة كاشير</div>

        @if($userBranch && !$isAdmin)
            <div class="form-label">الفرع</div>
            <div class="form-input" style="display:flex;align-items:center">{{ $userBranch->name }}</div>
        @elseif(\App\Models\Branch::where('is_active',true)->count() > 0)
            <div class="form-label">الفرع</div>
            <select wire:model="selectedBranchId" class="form-select">
                <option value="">اختر الفرع...</option>
                @foreach(\App\Models\Branch::where('is_active',true)->get() as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}{{ $b->is_main ? ' (رئيسي)' : '' }}</option>
                @endforeach
            </select>
        @endif

        @if(\App\Models\Warehouse::where('is_active',true)->count() > 0)
            <div class="form-label">المخزن</div>
            <select wire:model="selectedWarehouseId" class="form-select">
                <option value="">اختر المخزن...</option>
                @foreach(\App\Models\Warehouse::where('is_active',true)->get() as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}{{ $w->is_default ? ' (افتراضي)' : '' }}</option>
                @endforeach
            </select>
        @endif

        <div class="form-label">رصيد الافتتاح</div>
        <input type="number" wire:model="openingCash" class="form-input" placeholder="0.00" style="font-size:18px;text-align:center">

        <button wire:click="openSession" class="btn-primary">فتح الجلسة</button>
    </div>
</div>
@endif

{{-- نجاح العملية --}}
@if($showSuccess && $lastTransaction)
<div class="modal-ov" id="success-modal">
    <div class="modal-bx">
        <div class="success-mark">OK</div>
        <div class="success-title">تمت عملية البيع</div>
        <div class="modal-row"><span>رقم الفاتورة</span><strong style="color:#b9a6ff">{{ $lastTransaction['number'] }}</strong></div>
        <div class="modal-row"><span>الإجمالي</span><strong>{{ number_format($lastTransaction['total'], 2) }} ر.س</strong></div>
        <div class="modal-row"><span>المستلم</span><strong>{{ number_format($lastTransaction['cash'], 2) }} ر.س</strong></div>
        <div class="modal-row" style="border:0"><span>الباقي</span><strong style="font-size:17px;color:var(--pos-green)">{{ number_format($lastTransaction['change'], 2) }} ر.س</strong></div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px">
            <button class="btn-ghost" onclick="printReceipt({{ $lastTransaction['invoice_id'] }})">طباعة الإيصال</button>
            <button wire:click="closeSuccess" class="btn-success">عملية جديدة</button>
        </div>
    </div>
</div>
<script id="receipt-json" type="application/json">@json($lastTransaction)</script>
@endif

{{-- المرتجع --}}
@if($showReturnModal)
<div class="modal-ov">
    <div class="modal-bx" style="width:590px">
        <div class="modal-title" style="color:#ff9b9b">مرتجع من نقطة البيع</div>

        @if(!$returnInvoice)
            <div class="form-label">ابحث برقم الفاتورة أو اسم العميل</div>
            <input type="text" wire:model.live="returnInvoiceNumber" wire:keydown.enter="searchReturnInvoice"
                   class="form-input" placeholder="رقم الفاتورة أو اسم العميل...">

            @if(!empty($returnSearchResults))
                <div style="border:1px solid var(--pos-line);border-radius:9px;overflow:hidden;margin-bottom:10px">
                    @foreach($returnSearchResults as $inv)
                        <div wire:click="selectReturnInvoice({{ $inv['id'] }})" class="return-search-item">
                            <div>
                                <div style="font-size:11px;color:#b9a6ff;font-weight:900">{{ $inv['invoice_number'] }}</div>
                                <div style="font-size:9px;color:#68778e">{{ $inv['customer']['name'] ?? 'عميل نقدي' }}</div>
                            </div>
                            <strong style="font-size:11px">{{ number_format($inv['total'], 2) }} ر.س</strong>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($returnError)
                <div style="border:1px solid rgba(248,113,113,.22);background:rgba(248,113,113,.07);color:#ff9c9c;border-radius:8px;padding:9px;font-size:11px;margin-bottom:10px">{{ $returnError }}</div>
            @endif
        @else
            <div style="border:1px solid var(--pos-line);border-radius:9px;padding:10px;margin-bottom:10px;background:#0a1423">
                <div class="modal-row"><span>رقم الفاتورة</span><strong style="color:#b9a6ff">{{ $returnInvoice->invoice_number }}</strong></div>
                <div class="modal-row"><span>العميل</span><strong>{{ $returnInvoice->customer?->name ?? 'عميل نقدي' }}</strong></div>
                <div class="modal-row" style="border:0"><span>الإجمالي</span><strong>{{ number_format($returnInvoice->total, 2) }} ر.س</strong></div>
            </div>

            <div class="return-grid-head"><span>الصنف</span><span>المباع</span><span>المرتجع</span></div>
            @foreach($returnItems as $i => $item)
                <div class="return-grid-row">
                    <div>
                        <div style="font-size:11px;font-weight:900">{{ $item['product_name'] }}</div>
                        <div style="font-size:9px;color:#68778e">{{ number_format($item['unit_price'],2) }} ر.س</div>
                    </div>
                    <div style="text-align:center;font-size:11px">{{ $item['original_qty'] }}</div>
                    <input type="number" wire:model="returnItems.{{ $i }}.return_qty" class="return-qty"
                           min="0" max="{{ $item['original_qty'] }}" step="1" placeholder="0">
                </div>
            @endforeach

            <div class="form-label" style="margin-top:12px">سبب المرتجع</div>
            <select wire:model="returnReason" class="form-select">
                <option value="defective">صنف معيب</option>
                <option value="wrong">صنف خاطئ</option>
                <option value="customer_change">تغيير رأي العميل</option>
                <option value="other">أخرى</option>
            </select>

            <div style="border:1px solid rgba(248,113,113,.22);background:rgba(248,113,113,.07);border-radius:9px;padding:11px;text-align:center;margin-bottom:10px">
                <div style="font-size:9px;color:#ff9c9c">إجمالي المرتجع</div>
                <strong style="font-size:20px;color:#ff9c9c">
                    {{ number_format(collect($returnItems)->sum(fn($i) => ($i['return_qty'] ?? 0) * $i['unit_price']), 2) }} ر.س
                </strong>
            </div>

            <button wire:click="processReturn" class="btn-danger">تأكيد المرتجع</button>
            <button wire:click="$set('returnInvoice', null)" class="btn-ghost" style="margin-top:7px">اختيار فاتورة أخرى</button>
        @endif

        <button wire:click="closeReturnModal" class="btn-ghost" style="margin-top:7px">إغلاق</button>
    </div>
</div>
@endif

<div class="pos-shell">
    <div class="pos-topbar">
        <div class="pos-brand">
            <div class="pos-brand-mark">POS</div>
            <div>
                <div class="pos-brand-title">ORION POS</div>
                <div class="pos-brand-sub">نقطة بيع سريعة · باركود · مخزون · فواتير · ريال سعودي</div>
            </div>
        </div>
        <div class="pos-online">
            <span class="pos-pill good"><span class="pos-dot"></span>متصل</span>
            <span class="pos-pill">SAR</span>
            <span class="pos-pill">{{ auth()->user()->name }}</span>
        </div>
    </div>

    @if($currentSession)
    @php
        $sessionWarehouse = $selectedWarehouseId ? \App\Models\Warehouse::find($selectedWarehouseId) : null;
    @endphp
    <div class="session-strip">
        <div class="session-main">
            <div>
                <div class="session-code">{{ $currentSession->session_number }}</div>
                <div class="session-meta">
                    {{ $currentSession->branch?->name ?? 'بدون فرع' }}
                    @if($sessionWarehouse) · {{ $sessionWarehouse->name }} @endif
                    · منذ {{ optional($currentSession->opened_at)->format('H:i') }}
                </div>
            </div>
        </div>
        <div class="session-stat"><span>العمليات</span><strong>{{ $currentSession->transactions_count }}</strong></div>
        <div class="session-stat"><span>المبيعات</span><strong>{{ number_format($currentSession->total_sales,2) }}</strong></div>
        <div class="session-stat"><span>افتتاح الخزنة</span><strong>{{ number_format($currentSession->opening_cash,2) }}</strong></div>
        <div class="session-stat"><span>صافي الخزنة</span><strong>{{ number_format($currentSession->opening_cash + $currentSession->total_cash,2) }}</strong></div>
        <button class="session-close" wire:click="closeSession" onclick="confirm('إغلاق الجلسة؟')||event.stopImmediatePropagation()">إغلاق الجلسة</button>
    </div>
    @endif

    <div class="pos-workspace">
        <div class="pos-main">
            <div class="scanbar">
                <div class="scan-key">SCAN</div>
                <input type="text" id="barcode-input" wire:model.live="productSearch" wire:keyup="searchProduct"
                       class="scan-input" placeholder="امسح الباركود أو ابحث بالكود أو الاسم أو رقم المرجع أو العلامة التجارية..." autocomplete="off" autofocus>
                <span class="scan-shortcut">F2 بحث · Enter إضافة · F12 دفع · ESC مسح</span>

                @if(!empty($productResults))
                <div class="search-results" id="search-results">
                    @foreach($productResults as $idx => $product)
                    @php
                        $searchImage = !empty($product['image']) ? \Illuminate\Support\Facades\Storage::url($product['image']) : null;
                    @endphp
                    <div class="sr-item {{ $idx===0?'active':'' }}" wire:click="addToCart({{ $product['id'] }})" data-id="{{ $product['id'] }}" data-idx="{{ $idx }}">
                        @if($searchImage)
                            <img src="{{ $searchImage }}" class="sr-img" alt="">
                        @else
                            <div class="sr-img sr-placeholder">{{ $product['code'] ?? 'ITEM' }}</div>
                        @endif
                        <div>
                            <div class="sr-name">{{ $product['name'] }}</div>
                            <div class="sr-code">
                                {{ $product['code'] }}
                                @if(!empty($product['brand_name'])) · {{ $product['brand_name'] }} @endif
                                @if(!empty($product['part_number'])) · {{ $product['part_number'] }} @endif
                                @if(!empty($product['barcode'])) · {{ $product['barcode'] }} @endif
                            </div>
                            <div class="sr-stock" style="color:{{ $product['quantity']>0?'#34d399':'#f87171' }}">الرصيد {{ $product['quantity'] }} {{ $product['unit'] }}</div>
                            @if(!empty($product['location_codes']))
                                <div style="font-size:9px;color:#fbbf24;margin-top:2px;">📍 {{ $product['location_codes'] }}</div>
                            @endif
                            @if(!empty($product['vehicle_summary']))
                                <div style="font-size:9px;color:#94a3b8;margin-top:2px;">{{ $product['vehicle_summary'] }}</div>
                            @endif
                        </div>
                        <div class="sr-price">{{ number_format($product['sale_price'],2) }} ر.س</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="cart-card">
                <div class="cart-head">
                    <div>الصنف</div><div>الكمية</div><div>السعر</div><div>الخصم %</div><div>الإجمالي</div><div></div>
                </div>
                <div class="cart-body" id="cart-grid">
                    @if(empty($cartItems))
                        <div class="empty-cart">
                            <div>
                                <strong>السلة جاهزة للبيع</strong>
                                <span>امسح الباركود أو اكتب اسم الصنف للبدء</span>
                            </div>
                        </div>
                    @else
                        @foreach($cartItems as $i => $item)
                        @php
                            $cartImage = !empty($item['image']) ? \Illuminate\Support\Facades\Storage::url($item['image']) : null;
                        @endphp
                        <div class="cart-row" id="row-{{ $i }}">
                            <div class="cart-product">
                                @if($cartImage)
                                    <img src="{{ $cartImage }}" class="cart-img" alt="">
                                @else
                                    <div class="cart-img cart-placeholder">{{ $item['product_code'] }}</div>
                                @endif
                                <div>
                                    <div class="cart-name">{{ $item['product_name'] }}</div>
                                    <div class="cart-sub">
                                        {{ $item['product_code'] }}
                                        @if(!empty($item['brand_name'])) · {{ $item['brand_name'] }} @endif
                                        @if(!empty($item['part_number'])) · {{ $item['part_number'] }} @endif
                                        · {{ $item['unit'] }} · رصيد {{ $item['stock'] ?? '?' }}
                                    </div>
                                </div>
                            </div>
                            <div class="cart-cell">
                                <div class="qty-control">
                                    <button class="qty-btn" wire:click="updateQuantity({{ $i }}, {{ $item['quantity'] + 1 }})" tabindex="-1">+</button>
                                    <div class="qty-value">{{ $item['quantity'] }}</div>
                                    <button class="qty-btn" wire:click="updateQuantity({{ $i }}, {{ max(1, $item['quantity'] - 1) }})" tabindex="-1">−</button>
                                </div>
                            </div>
                            <div class="cart-cell">
                                <input type="number" value="{{ $item['unit_price'] }}" min="0" step="0.01"
                                       id="price-{{ $i }}" onchange="updatePriceLocal({{ $i }},this.value)"
                                       onkeydown="handleGridKey(event,{{ $i }},'price')" class="cart-input">
                            </div>
                            <div class="cart-cell">
                                <input type="number" value="{{ $item['discount_percent'] ?? 0 }}" min="0" max="100" step="0.01"
                                       onchange="updateDiscountLocal({{ $i }},this.value)"
                                       onkeydown="handleGridKey(event,{{ $i }},'discount')" class="cart-input" title="خصم بالنسبة المئوية">
                            </div>
                            <div class="cart-cell"><div class="cart-total" id="total-{{ $i }}">{{ number_format($item['total'],2) }} ر.س</div></div>
                            <div class="cart-cell"><button class="del-btn" wire:click="removeFromCart({{ $i }})" tabindex="-1">×</button></div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="pos-side">
            <div class="side-card">
                <div class="side-title"><span>العميل</span><b>{{ $selectedCustomerId ? 'مسجل' : 'نقدي' }}</b></div>
                <select wire:model="selectedCustomerId" class="customer-select">
                    <option value="">عميل نقدي</option>
                    @foreach(\App\Models\Customer::where('is_active',true)->orderBy('name')->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="side-card">
                <div class="side-title"><span>الإجمالي</span><b>{{ count($cartItems) }} صنف</b></div>
                <div class="total-hero">
                    <div class="amount">{{ number_format($this->getCartTotal(),2) }}</div>
                    <div class="currency">ريال سعودي · {{ array_sum(array_column($cartItems,'quantity')) }} وحدة</div>
                </div>
                <div class="total-breakdown">
                    <div class="total-breakdown-row"><span>قبل الخصم</span><strong>{{ number_format($this->getCartSubtotal(),2) }} ر.س</strong></div>
                    <div class="total-breakdown-row"><span>الخصم</span><strong style="color:#ff9c9c">- {{ number_format($this->getCartDiscount(),2) }} ر.س</strong></div>
                    <div class="total-breakdown-row"><span>الضريبة</span><strong>{{ number_format($this->getCartTax(),2) }} ر.س</strong></div>
                    <div class="total-breakdown-row"><span>الإجمالي المستحق</span><strong>{{ number_format($this->getCartTotal(),2) }} ر.س</strong></div>
                </div>
                @if($this->getCartTotal()>0)
                <div class="quick-amounts">
                    @php $total = $this->getCartTotal(); $shown = 0; @endphp
                    @foreach([50,100,200,500,1000] as $amt)
                        @if($amt >= $total && $shown < 5)
                            <button class="qa-btn" onclick="setAmount({{ $amt }})">{{ $amt }}</button>
                            @php $shown++ @endphp
                        @endif
                    @endforeach
                    @if($shown < 6)
                        <button class="qa-btn" onclick="setAmount({{ ceil($total/10)*10 }})">{{ ceil($total/10)*10 }}</button>
                    @endif
                </div>
                @endif
            </div>

            <div class="side-card">
                <div class="side-title"><span>طريقة الدفع</span><b>{{ strtoupper($paymentMethod) }}</b></div>
                <div class="pay-methods">
                    <button wire:click="$set('paymentMethod','cash')" class="pm-btn {{ $paymentMethod==='cash'?'active':'' }}">نقدي</button>
                    <button wire:click="$set('paymentMethod','card')" class="pm-btn {{ $paymentMethod==='card'?'active':'' }}">بطاقة</button>
                    <button wire:click="$set('paymentMethod','wallet')" class="pm-btn {{ $paymentMethod==='wallet'?'active':'' }}">محفظة</button>
                </div>

                @if($paymentMethod==='cash')
                    <input type="number" id="cash-received" wire:model.live="cashReceived" class="cash-input" placeholder="المبلغ المستلم">
                    @if($cashReceived>0)
                        <div class="change-box"><span>الباقي للعميل</span><strong>{{ number_format($this->getChange(),2) }} ر.س</strong></div>
                    @endif
                @endif
            </div>

            <button wire:click="processPayment" class="checkout-btn" {{ empty($cartItems)?'disabled':'' }} id="checkout-btn">
                إتمام البيع
                <span class="checkout-hint">F12</span>
            </button>

            <button wire:click="openReturnModal" class="return-btn">تسجيل مرتجع بيع</button>

            @if($currentSession)
            <div class="side-card session-mini">
                <div class="side-title"><span>ملخص الجلسة</span><b>LIVE</b></div>
                <div class="mini-row"><span>عدد العمليات</span><strong>{{ $currentSession->transactions_count }}</strong></div>
                <div class="mini-row"><span>المبيعات</span><strong>{{ number_format($currentSession->total_sales,2) }} ر.س</strong></div>
                <div class="mini-row"><span>المرتجعات</span><strong>{{ number_format($currentSession->total_refunds ?? 0,2) }} ر.س</strong></div>
            </div>

            @php
                $recentTransactions = \App\Models\PosTransaction::where('pos_session_id',$currentSession->id)
                    ->latest()->limit(4)->get();
            @endphp
            @if($recentTransactions->isNotEmpty())
            <div class="side-card">
                <div class="side-title"><span>آخر العمليات</span><b>{{ $recentTransactions->count() }}</b></div>
                <div class="recent-list">
                    @foreach($recentTransactions as $tx)
                    <div class="recent-item">
                        <div>
                            <div class="recent-num">{{ $tx->transaction_number }}</div>
                            <div class="recent-meta">{{ strtoupper($tx->payment_method) }} · {{ optional($tx->created_at)->format('H:i') }}</div>
                        </div>
                        <div class="recent-amount">{{ number_format($tx->amount,2) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>

    <div class="shortcuts">
        <span class="shortcut"><b>F2</b>بحث</span>
        <span class="shortcut"><b>F4</b>العميل</span>
        <span class="shortcut"><b>F6</b>الكمية</span>
        <span class="shortcut"><b>F8</b>الخصم</span>
        <span class="shortcut"><b>F12</b>إتمام البيع</span>
        <span class="shortcut"><b>ESC</b>مسح</span>
    </div>
</div>

<script>
function updateDiscountLocal(index, value) {
    const n = Math.max(0, Math.min(100, parseFloat(value) || 0));
    const component = window.Livewire?.find(document.querySelector('[wire\:id]')?.getAttribute('wire:id'));
    if (component) component.call('setDiscount', index, n);
}
</script>

<script src="/js/pos.js"></script>
</x-filament-panels::page>