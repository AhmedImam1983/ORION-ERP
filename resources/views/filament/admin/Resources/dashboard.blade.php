<x-filament-panels::page>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@php
  $isCashier     = auth()->user()?->hasRole('cashier');
  $todaySales    = $totalSalesToday;
  $todayCollected= $collectedToday;
  $invoiceCount  = $totalInvoices;
  $customerCount = $totalCustomers;
  $productCount  = $totalProducts;
  $employeeCount = $totalEmployees;
  $debts         = $totalDebts;
@endphp

<style>
*{box-sizing:border-box}
.orion-dashboard,.orion-dashboard *{font-family:'Cairo',sans-serif!important}
.orion-dashboard{direction:rtl;width:100%;padding:0 30px 40px;color:#eef2ff;position:relative}
.orion-dashboard:before{content:"";position:fixed;inset:0;pointer-events:none;background:radial-gradient(circle at 78% 0%,rgba(124,77,255,.08),transparent 34%),radial-gradient(circle at 15% 100%,rgba(16,185,129,.035),transparent 28%);z-index:0}
.orion-dashboard>*{position:relative;z-index:1}
.od-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:13px 18px;margin-bottom:16px;border:1px solid rgba(255,255,255,.07);border-radius:15px;background:linear-gradient(135deg,rgba(16,31,55,.82),rgba(10,17,32,.92));box-shadow:0 8px 24px rgba(0,0,0,.08)}
.od-title h1{display:none}
.od-title p{margin:0;color:#e9eef9;font-size:18px;font-weight:800}.od-title p .od-role{display:inline-flex;margin-inline-start:8px;padding:3px 8px;border-radius:7px;background:rgba(124,77,255,.16);border:1px solid rgba(124,77,255,.22);color:#a889ff;font-size:11px;font-weight:800}
.od-head-actions{display:flex;align-items:center;gap:10px}
.od-date{display:flex;align-items:center;gap:8px;padding:11px 15px;border:1px solid rgba(255,255,255,.07);border-radius:11px;background:rgba(13,18,34,.72);color:#9aa9c4;font-size:13px}
.od-status{display:flex;align-items:center;gap:7px;padding:9px 13px;border-radius:11px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.14);color:#10b981;font-size:13px;font-weight:700}
.od-dot{width:6px;height:6px;border-radius:50%;background:#10b981;box-shadow:0 0 9px rgba(16,185,129,.7)}
.od-btn{display:inline-flex;align-items:center;gap:7px;text-decoration:none;border:1px solid rgba(124,77,255,.35);background:#6941e8;color:#fff;padding:9px 15px;border-radius:11px;font-size:12px;font-weight:700;box-shadow:0 8px 24px rgba(124,77,255,.18);transition:.2s}
.od-btn:hover{background:#7650f0;color:#fff;transform:translateY(-1px)}
.od-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:14px}
.od-card{min-height:168px;border:1px solid rgba(255,255,255,.065);border-radius:16px;background:linear-gradient(135deg,rgba(15,21,39,.96),rgba(11,16,31,.94));overflow:hidden;display:flex;align-items:stretch;position:relative;transition:.2s;box-shadow:0 8px 25px rgba(0,0,0,.08)}
.od-card:hover{transform:translateY(-2px);border-color:rgba(255,255,255,.13);box-shadow:0 15px 34px rgba(0,0,0,.18)}
.od-card-art{width:39%;min-width:145px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center}
.od-card-image{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.95;filter:saturate(.9) contrast(1.03);z-index:0}
.od-card-image-shade{position:absolute;inset:0;background:linear-gradient(90deg,rgba(7,10,18,.10),rgba(7,10,18,.38));z-index:2}
.od-card-art .od-card-icon{z-index:3;box-shadow:0 10px 24px rgba(0,0,0,.22)}
.od-card-art:before{content:"";position:absolute;inset:0;background:var(--accent-bg);z-index:1;mix-blend-mode:screen;opacity:.65}
.od-card-art:after{content:"";position:absolute;width:105px;height:105px;border-radius:50%;background:var(--accent);opacity:.12;filter:blur(12px);z-index:2}
.od-card-body{flex:1;padding:20px 24px 18px 18px;display:flex;flex-direction:column;justify-content:center;min-width:0}
.od-card-label{font-size:18px;color:#8998b4;margin-bottom:5px;font-weight:600}
.od-card-value{font-size:42px;line-height:1.15;color:#f3f5ff;font-weight:900;letter-spacing:-.5px;white-space:nowrap}
.od-card-sub{font-size:14px;color:#596984;margin-top:6px}
.od-card-icon{width:70px;height:70px;border-radius:15px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;position:relative;z-index:2;color:var(--accent)}
.od-card-icon svg{width:33px;height:33px}
.od-c-green{--accent:#10b981;--accent-bg:linear-gradient(145deg,rgba(16,185,129,.24),rgba(16,185,129,.03))}
.od-c-red{--accent:#f43f5e;--accent-bg:linear-gradient(145deg,rgba(244,63,94,.24),rgba(244,63,94,.03))}
.od-c-purple{--accent:#7c4dff;--accent-bg:linear-gradient(145deg,rgba(124,77,255,.25),rgba(124,77,255,.03))}
.od-c-blue{--accent:#3b82f6;--accent-bg:linear-gradient(145deg,rgba(59,130,246,.24),rgba(59,130,246,.03))}
.od-c-orange{--accent:#f59e0b;--accent-bg:linear-gradient(145deg,rgba(245,158,11,.24),rgba(245,158,11,.03))}
.od-c-pink{--accent:#ec4899;--accent-bg:linear-gradient(145deg,rgba(236,72,153,.24),rgba(236,72,153,.03))}
.od-c-teal{--accent:#14b8a6;--accent-bg:linear-gradient(145deg,rgba(20,184,166,.24),rgba(20,184,166,.03))}
.od-summary{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:11px 16px;margin:14px 0;border:1px solid rgba(255,255,255,.065);border-radius:14px;background:rgba(12,18,33,.9)}
.od-summary-title{display:flex;align-items:center;gap:10px;font-size:16px;color:#d9e0ef;font-weight:700;white-space:nowrap}
.od-summary-title span{color:#667691;font-weight:500}
.od-summary-items{display:flex;align-items:center;justify-content:flex-end;gap:28px;flex:1}
.od-summary-item{display:flex;align-items:center;gap:9px;font-size:15px;color:#7d8da8}
.od-summary-item strong{font-size:16px;color:#eef2ff}
.od-summary-item b{width:7px;height:7px;border-radius:50%;display:inline-block}
.od-lower{display:grid;grid-template-columns:1.55fr 1fr;gap:14px;margin-top:14px}
.od-panel{background:rgba(12,18,33,.92);border:1px solid rgba(255,255,255,.065);border-radius:16px;overflow:hidden}
.od-panel-head{height:58px;padding:0 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,.055)}
.od-panel-title{display:flex;align-items:center;gap:10px;font-size:19px;font-weight:800;color:#e9edfa}
.od-panel-link{font-size:14px;color:#8057f4;text-decoration:none;padding:5px 9px;border-radius:8px;background:rgba(124,77,255,.09);border:1px solid rgba(124,77,255,.15)}
.od-chart{height:300px;padding:8px 12px 12px;direction:ltr}
.od-activity{padding:4px 17px 9px}
.od-activity-row{display:flex;align-items:center;gap:12px;padding:13px 2px;border-bottom:1px solid rgba(255,255,255,.045)}
.od-activity-row:last-child{border-bottom:0}
.od-activity-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.od-activity-main{min-width:0;flex:1}
.od-activity-title{font-size:15px;color:#e5e9f4;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.od-activity-sub{font-size:13px;color:#5f708d;margin-top:3px}
.od-activity-amount{font-size:15px;font-weight:800;color:#cbd4e7;white-space:nowrap}
.od-empty{padding:34px;text-align:center;color:#71819d;font-size:15px}
.od-mini-row{display:none!important}
.od-mini{padding:16px;border:1px solid rgba(255,255,255,.065);border-radius:14px;background:rgba(12,18,33,.88)}
.od-mini-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.od-mini-label{font-size:14px;color:#71819d;font-weight:600}
.od-mini-value{font-size:30px;font-weight:900;color:#edf1fb}
.od-mini-sub{font-size:13px;color:#53637d;margin-top:4px}
.od-admin{margin-top:14px;padding:14px 17px;border:1px solid rgba(255,255,255,.065);border-radius:14px;background:rgba(12,18,33,.88);display:flex;align-items:center;justify-content:space-between;gap:15px}
.od-admin-info{font-size:10px;color:#71819d}.od-admin-info strong{display:block;font-size:12px;color:#e8edf8;margin-bottom:3px}
.od-admin-actions{display:flex;gap:8px}.od-admin button{border:0;border-radius:9px;padding:8px 13px;font-family:inherit;font-size:10px;font-weight:700;cursor:pointer}.od-backup{background:#6941e8;color:#fff}.od-restore{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.18)!important;color:#f59e0b}
.od-modal{position:fixed;inset:0;background:rgba(0,0,0,.78);z-index:9999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(7px);direction:rtl}
.od-modal-box{width:480px;max-width:94vw;background:#0d1222;border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.45)}
.od-modal-title{font-size:16px;font-weight:800;color:#eef2ff;margin-bottom:10px}.od-modal-warning{padding:10px 12px;border-radius:10px;background:rgba(244,63,94,.08);border:1px solid rgba(244,63,94,.16);color:#f43f5e;font-size:11px;margin-bottom:12px}.od-backup-file{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.055);border-radius:10px;margin-bottom:6px}.od-close{width:100%;margin-top:10px;padding:9px;border-radius:10px;background:transparent;border:1px solid rgba(255,255,255,.08);color:#8292ad;font-family:inherit;cursor:pointer}
.orion-dashboard{font-size:15px}
.orion-dashboard .od-card{min-height:178px}
.orion-dashboard .od-card-body{padding:23px 26px 20px 20px}
.orion-dashboard .od-summary{min-height:52px}
.orion-dashboard .od-panel-head{padding:17px 20px}
.orion-dashboard .od-activity{padding:15px 20px}

{{-- Cashier specific --}}
.od-cashier-actions{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.od-cashier-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:28px;border:1px solid rgba(124,77,255,.3);border-radius:16px;background:rgba(12,18,33,.9);text-decoration:none;transition:.2s}
.od-cashier-btn:hover{transform:translateY(-2px);border-color:rgba(124,77,255,.6)}
.od-cashier-btn .icon{font-size:40px}
.od-cashier-btn .label{font-size:16px;font-weight:800;color:#a889ff}
.od-cashier-btn .sub{font-size:11px;color:#596984}
.od-cashier-btn.green{border-color:rgba(16,185,129,.3)}.od-cashier-btn.green .label{color:#10b981}
.od-cashier-btn.green:hover{border-color:rgba(16,185,129,.6)}

@media(max-width:1250px){.od-grid{grid-template-columns:repeat(2,1fr)}.od-lower{grid-template-columns:1fr}.od-summary-items{gap:14px}.od-card-art{width:31%}}
@media(max-width:800px){.orion-dashboard{padding:0 12px 25px}.od-header{align-items:flex-start;flex-direction:column}.od-head-actions{width:100%;flex-wrap:wrap}.od-grid{grid-template-columns:1fr}.od-summary{align-items:flex-start;flex-direction:column}.od-summary-items{width:100%;justify-content:flex-start;flex-wrap:wrap}.od-admin{align-items:flex-start;flex-direction:column}.od-card-art{min-width:105px}}
</style>

@if($showRestore)
<div class="od-modal">
  <div class="od-modal-box">
    <div class="od-modal-title">استعادة نسخة احتياطية</div>
    <div class="od-modal-warning">تحذير: الاستعادة ستحذف كل البيانات الحالية!</div>
    @forelse($backupFiles as $f)
      <div class="od-backup-file">
        <div>
          <div style="font-size:11px;color:#eef2ff">{{ $f['name'] }}</div>
          <div style="font-size:9px;color:#5d6e89;margin-top:2px">{{ $f['date'] }} — {{ $f['size'] }} KB</div>
        </div>
        <button wire:click="restoreBackup('{{ $f['name'] }}')" onclick="confirm('هل أنت متأكد؟')||event.stopImmediatePropagation()" style="border:0;border-radius:8px;padding:6px 10px;background:rgba(244,63,94,.1);color:#f43f5e;font-family:inherit;font-size:10px;font-weight:700;cursor:pointer">استعادة</button>
      </div>
    @empty
      <div class="od-empty">لا توجد نسخ احتياطية</div>
    @endforelse
    <button wire:click="$set('showRestore',false)" class="od-close">إغلاق</button>
  </div>
</div>
@endif

<div class="orion-dashboard">
  @php
    $pendingOrders = $pendingOrders ?? 0;
    $unpaidCount   = $unpaidCount ?? 0;
    $lowStock      = $lowStockCount ?? 0;
  @endphp

  {{-- Header --}}
  <div class="od-header">
    <div class="od-title">
      <h1>لوحة التحكم</h1>
      <p>مرحباً بك، {{ auth()->user()->name }} 👋 <span class="od-role">{{ auth()->user()->roles->first()?->name ?? 'مستخدم' }}</span></p>
    </div>
    <div class="od-head-actions">
      <div class="od-status"><i class="od-dot"></i> النظام يعمل</div>
      <div class="od-date">{{ now()->format('H:i') }} &nbsp;·&nbsp; {{ now()->locale('ar')->isoFormat('D MMMM') }}</div>
      <a href="{{ route('filament.admin.resources.invoices.create') }}" class="od-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        فاتورة جديدة
      </a>
    </div>
  </div>

  @if($isCashier)
  {{-- ══ Cashier View ══════════════════════════════════════════ --}}
  <div class="od-cashier-actions">
    <a href="/admin/pos" class="od-cashier-btn">
      <div class="icon">🖥️</div><div class="label">فتح الكاشير</div><div class="sub">نقطة البيع</div>
    </a>
    <a href="{{ route('filament.admin.resources.invoices.create') }}" class="od-cashier-btn green">
      <div class="icon">🧾</div><div class="label">فاتورة جديدة</div><div class="sub">إنشاء فاتورة بيع</div>
    </a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
    <div class="od-card od-c-green">
      <div class="od-card-body" style="text-align:center">
        <div class="od-card-label">مبيعاتي اليوم</div>
        <div class="od-card-value">{{ number_format($todaySales,0) }}</div>
        <div class="od-card-sub">ر.س · إجمالي اليوم</div>
      </div>
    </div>
    <div class="od-card od-c-blue">
      <div class="od-card-body" style="text-align:center">
        <div class="od-card-label">المحصّل اليوم</div>
        <div class="od-card-value">{{ number_format($todayCollected,0) }}</div>
        <div class="od-card-sub">ر.س · مدفوعات اليوم</div>
      </div>
    </div>
  </div>

  <div class="od-panel">
    <div class="od-panel-head">
      <div class="od-panel-title">فواتيري اليوم</div>
    </div>
    <div class="od-activity">
      @forelse($recentInvoices as $inv)
        @php
          $activity = match($inv->type) {
            'sale' => ['label'=>'فاتورة بيع','bg'=>'rgba(16,185,129,.1)','color'=>'#10b981'],
            default => ['label'=>'عملية','bg'=>'rgba(124,77,255,.1)','color'=>'#7c4dff'],
          };
        @endphp
        <div class="od-activity-row">
          <div class="od-activity-icon" style="background:{{ $activity['bg'] }};color:{{ $activity['color'] }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg>
          </div>
          <div class="od-activity-main">
            <div class="od-activity-title">{{ $inv->invoice_number }}</div>
            <div class="od-activity-sub">{{ $inv->customer?->name ?? 'عميل نقدي' }} · {{ $inv->created_at?->format('H:i') }}</div>
          </div>
          <div class="od-activity-amount">{{ number_format($inv->total,0) }} ر.س</div>
        </div>
      @empty
        <div class="od-empty">لا توجد فواتير اليوم</div>
      @endforelse
    </div>
  </div>

  @else
  {{-- ══ Admin / Full View ══════════════════════════════════════ --}}
  <div class="od-grid">
    <div class="od-card od-c-green">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 10h18M5 10v9M9 10v9M15 10v9M19 10v9M2 20h20M4 7l8-4 8 4v3H4z"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">صافي مبيعات اليوم</div><div class="od-card-value">{{ number_format($todaySales,0) }}</div><div class="od-card-sub">ر.س · إجمالي اليوم</div></div>
    </div>
    <div class="od-card od-c-purple">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 2h12v20H6zM9 6h6M9 10h6M9 14h4"/><path d="M8 2v-1h8v1"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">إجمالي الفواتير</div><div class="od-card-value">{{ number_format($invoiceCount) }}</div><div class="od-card-sub">فاتورة · منذ البداية</div></div>
    </div>
    <div class="od-card od-c-red">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v18M17 7c0-2-2-3-5-3S7 5 7 7s2 3 5 3 5 1 5 3-2 3-5 3-5-1-5-3"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">إجمالي المديونيات</div><div class="od-card-value">{{ number_format($debts,0) }}</div><div class="od-card-sub">ر.س · {{ $unpaidCount }} فاتورة غير مسددة</div></div>
    </div>
    <div class="od-card od-c-orange">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1554224154-22dec7ec8818?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h4"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">المتحصل اليوم</div><div class="od-card-value">{{ number_format($todayCollected,0) }}</div><div class="od-card-sub">ر.س · مدفوعات اليوم</div></div>
    </div>
    <div class="od-card od-c-blue">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 8l9-5 9 5-9 5-9-5zM3 12l9 5 9-5M3 16l9 5 9-5"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">إجمالي الأصناف</div><div class="od-card-value">{{ number_format($productCount) }}</div><div class="od-card-sub">صنف · في المخزون</div></div>
    </div>
    <div class="od-card od-c-pink">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-6 8-6s8 2 8 6"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">إجمالي العملاء</div><div class="od-card-value">{{ number_format($customerCount) }}</div><div class="od-card-sub">عميل · مسجل</div></div>
    </div>
    <div class="od-card od-c-teal">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="7" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5M15 14c3.5 0 6 1.7 6 5"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">الموظفون النشطون</div><div class="od-card-value">{{ number_format($employeeCount) }}</div><div class="od-card-sub">موظف · نشط حالياً</div></div>
    </div>
    <div class="od-card od-c-orange">
      <div class="od-card-art"><img class="od-card-image" src="https://images.unsplash.com/photo-1554224154-22dec7ec8818?auto=format&fit=crop&w=700&q=82" alt="" loading="lazy"><div class="od-card-image-shade"></div><div class="od-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h3M14 12h3M7 16h10"/></svg></div></div>
      <div class="od-card-body"><div class="od-card-label">الحسابات</div><div class="od-card-value">{{ number_format($accountsCount) }}</div><div class="od-card-sub">حساب · في الدليل</div></div>
    </div>
  </div>

  <div class="od-summary">
    <div class="od-summary-title">ملخص اليوم <span>— {{ now()->locale('ar')->isoFormat('dddd، D MMMM') }}</span></div>
    <div class="od-summary-items">
      <div class="od-summary-item"><b style="background:#10b981"></b> حضور <strong>{{ $dailyAttendance['present'] }}</strong></div>
      <div class="od-summary-item"><b style="background:#f43f5e"></b> غياب <strong>{{ $dailyAttendance['absent'] }}</strong></div>
      <div class="od-summary-item"><b style="background:#f59e0b"></b> تأخير <strong>{{ $dailyAttendance['late'] }}</strong></div>
      <div class="od-summary-item"><b style="background:#7c4dff"></b> إجازة <strong>{{ $dailyAttendance['leave'] }}</strong></div>
      <div class="od-summary-item"><span style="color:#10b981;font-weight:800">●</span> إجمالي الموظفين <strong>{{ $employeeCount }}</strong></div>
    </div>
  </div>

  <div class="od-lower">
    <div class="od-panel">
      <div class="od-panel-head">
        <div class="od-panel-title"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c4dff" stroke-width="1.8"><path d="M3 3v18h18"/><path d="M7 16l4-5 3 3 5-7"/></svg> المبيعات — آخر 7 أيام</div>
        <a class="od-panel-link" href="{{ route('filament.admin.pages.reports-page') }}">عرض التقرير ←</a>
      </div>
      <div class="od-chart" id="orion-sales-chart"></div>
    </div>
    <div class="od-panel">
      <div class="od-panel-head">
        <div class="od-panel-title"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c4dff" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> آخر العمليات</div>
        <a class="od-panel-link" href="{{ route('filament.admin.resources.invoices.index') }}">الكل ←</a>
      </div>
      <div class="od-activity">
        @forelse($recentInvoices as $inv)
          @php
            $activity = match($inv->type) {
              'sale' => ['label'=>'فاتورة بيع','bg'=>'rgba(16,185,129,.1)','color'=>'#10b981'],
              'purchase' => ['label'=>'فاتورة شراء','bg'=>'rgba(59,130,246,.1)','color'=>'#3b82f6'],
              'sale_return','purchase_return' => ['label'=>'مرتجع','bg'=>'rgba(244,63,94,.1)','color'=>'#f43f5e'],
              default => ['label'=>'عملية','bg'=>'rgba(124,77,255,.1)','color'=>'#7c4dff'],
            };
          @endphp
          <div class="od-activity-row">
            <div class="od-activity-icon" style="background:{{ $activity['bg'] }};color:{{ $activity['color'] }}">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg>
            </div>
            <div class="od-activity-main">
              <div class="od-activity-title">{{ $activity['label'] }} — {{ $inv->invoice_number }}</div>
              <div class="od-activity-sub">{{ $inv->customer?->name ?? 'عميل نقدي' }} · {{ $inv->created_at?->format('H:i') }}</div>
            </div>
            <div class="od-activity-amount">{{ number_format($inv->total,0) }} ر.س</div>
          </div>
        @empty
          <div class="od-empty">لا توجد عمليات حديثة</div>
        @endforelse
      </div>
    </div>
  </div>

  @if(auth()->user()->hasAnyRole(['super_admin','admin']))
  <div class="od-admin">
    <div class="od-admin-info"><strong>النسخ الاحتياطي</strong>آخر نسخة: <span style="color:#10b981">{{ $lastBackup }}</span></div>
    <div class="od-admin-actions">
      <button wire:click="makeBackup" class="od-backup">نسخة الآن</button>
      <button wire:click="openRestore" class="od-restore">استعادة</button>
    </div>
  </div>
  @endif

  @endif {{-- end cashier/admin if --}}
</div>

<script>
(function(){
  const el=document.querySelector('#orion-sales-chart');
  if(!el || typeof ApexCharts==='undefined') return;
  const labels=@json(collect($chartRows)->pluck('label')->values());
  const values=@json(collect($chartRows)->pluck('value')->values());
  const chart=new ApexCharts(el,{
    chart:{type:'bar',height:245,toolbar:{show:false},background:'transparent',fontFamily:'Cairo'},
    series:[{name:'المبيعات',data:values}],
    colors:['#7c4dff'],
    plotOptions:{bar:{borderRadius:6,columnWidth:'48%'}},
    dataLabels:{enabled:false},
    grid:{borderColor:'rgba(255,255,255,.055)',strokeDashArray:4,padding:{left:0,right:0,top:0,bottom:0}},
    xaxis:{categories:labels,labels:{style:{colors:'#687894',fontFamily:'Cairo',fontSize:'10px'}},axisBorder:{show:false},axisTicks:{show:false}},
    yaxis:{labels:{style:{colors:'#53637d',fontFamily:'Cairo',fontSize:'9px'},formatter:v=>Number(v).toLocaleString('en-US')},min:0},
    tooltip:{theme:'dark',y:{formatter:v=>Number(v).toLocaleString('en-US')+' ر.س'}},
    legend:{show:false}
  });
  chart.render();
})();
</script>
</x-filament-panels::page>