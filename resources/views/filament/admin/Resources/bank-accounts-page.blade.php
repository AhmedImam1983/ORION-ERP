<x-filament-panels::page>
@php
    $bankAccounts = $this->getBankAccounts();
    $totalBalance = $this->getTotalBalance();
    $accountCount = $bankAccounts->count();
    $averageBalance = $accountCount > 0 ? $bankAccounts->avg('current_balance') : 0;
    $highestBalance = $accountCount > 0 ? $bankAccounts->max('current_balance') : 0;

    $detailBankId = $viewTxBankId ?: $bankAccounts->first()?->id;
    $detailBank = $bankAccounts->find($detailBankId);

    $visibleTransactions = $viewTxBankId ? $this->getTransactions() : collect();
    $incomingTransactions = $visibleTransactions->filter(fn ($tx) => in_array($tx->type, ['deposit', 'transfer_in']));
    $outgoingTransactions = $visibleTransactions->reject(fn ($tx) => in_array($tx->type, ['deposit', 'transfer_in']));
    $transactionsIn = $incomingTransactions->sum('amount');
    $transactionsOut = $outgoingTransactions->sum('amount');
@endphp

<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --ob-bg:#07111f;
    --ob-panel:#0c1526;
    --ob-panel-2:#101a2d;
    --ob-panel-3:#131f35;
    --ob-border:#24344e;
    --ob-border-soft:#1a2940;
    --ob-text:#f4f7fb;
    --ob-muted:#8798b1;
    --ob-muted-2:#5c6e87;
    --ob-purple:#8b5cf6;
    --ob-purple-2:#a78bfa;
    --ob-green:#22e56b;
    --ob-red:#ff5f6d;
    --ob-cyan:#18c8ff;
    --ob-amber:#f8b84e;
}
.bank-page{direction:rtl;color:var(--ob-text);width:100%}
.bank-page button,.bank-page input,.bank-page select{font-family:'Cairo',sans-serif!important}
.panel{background:linear-gradient(180deg,var(--ob-panel),#0a1322);border:1px solid var(--ob-border);border-radius:16px;box-shadow:0 12px 34px rgba(0,0,0,.14)}

/* header */
.hero{display:grid;grid-template-columns:300px minmax(0,1fr);gap:14px;align-items:start;margin-bottom:14px}
.balance-card{padding:14px 18px;min-height:76px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.balance-badge{width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,#9b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.08)}
.balance-badge::before{content:"";width:18px;height:18px;border:2px solid #fff;border-radius:4px;box-shadow:inset 0 0 0 3px rgba(255,255,255,.15)}
.balance-label{font-size:11px;color:var(--ob-muted);font-weight:800}
.balance-value{font-size:20px;font-weight:950;color:var(--ob-green);margin-top:4px;font-variant-numeric:tabular-nums;white-space:nowrap}
.header-main{padding:6px 0;display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
.header-title{font-size:18px;color:#f8fbff;font-weight:950;margin-bottom:10px}
.page-title{font-size:34px;line-height:1.15;font-weight:950;color:#fff;margin:0}
.page-sub{font-size:12px;color:var(--ob-muted);margin-top:6px}
.market-chip{display:inline-flex;align-items:center;gap:7px;margin-top:10px;padding:5px 10px;border-radius:999px;background:rgba(34,229,107,.06);border:1px solid rgba(34,229,107,.14);color:#6ee7b7;font-size:10px;font-weight:900}
.market-dot{width:6px;height:6px;border-radius:50%;background:var(--ob-green);box-shadow:0 0 9px rgba(34,229,107,.5)}
.header-actions{display:flex;gap:9px;align-items:center;flex-wrap:wrap;padding-top:30px}
.header-btn{height:36px;padding:0 14px;border-radius:10px;border:1px solid #33445e;background:#111b2f;color:#dbe5f4;font-size:11px;font-weight:900;cursor:pointer}
.header-btn:hover{border-color:var(--ob-purple);background:rgba(139,92,246,.08)}
.header-btn.primary{background:linear-gradient(135deg,#9b5cf6,#7c3aed);border-color:#9b5cf6;color:#fff;box-shadow:0 10px 22px rgba(124,58,237,.18)}
.header-btn[disabled]{opacity:.55;cursor:not-allowed}

/* KPI */
.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
.kpi-card{padding:15px 18px;min-height:98px;position:relative;overflow:hidden}
.kpi-card::after{content:"";position:absolute;inset:auto 0 0 0;height:2px;background:linear-gradient(90deg,transparent,var(--ob-purple),transparent);opacity:.75}
.kpi-card.green::after{background:linear-gradient(90deg,transparent,var(--ob-green),transparent)}
.kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.kpi-label{font-size:11px;color:#b7c3d3;font-weight:850}
.kpi-code{direction:ltr;font-size:9px;color:#65758d;border:1px solid #2b3b55;border-radius:999px;padding:3px 8px;font-weight:900}
.kpi-value{font-size:18px;font-weight:950;color:#fff;margin-top:14px;font-variant-numeric:tabular-nums;white-space:nowrap}
.kpi-note{font-size:10px;color:var(--ob-muted-2);margin-top:4px}
.kpi-card.green .kpi-value{color:var(--ob-green)}

/* search row */
.top-filter-bar{display:grid;grid-template-columns:300px minmax(0,1fr);gap:14px;margin-bottom:10px}
.filter-balance-placeholder{height:46px;display:flex;align-items:center;gap:8px;padding:0 14px}
.filter-chip{height:34px;padding:0 12px;border:1px solid #2a3a55;border-radius:10px;background:#0d1728;color:#d5ddea;font-size:11px;font-weight:850;display:inline-flex;align-items:center;gap:8px}
.filter-chip::after{content:"≡";font-size:13px;color:#93a2bb}
.search-panel{height:46px;display:flex;align-items:center;gap:10px;padding:0 12px}
.search-input{flex:1;height:34px;border:1px solid #24354f;background:#0b1525;border-radius:9px;padding:0 12px;color:var(--ob-text);font-size:11px;outline:none}
.search-input:focus{border-color:var(--ob-purple)}
.search-btn{width:32px;height:32px;border-radius:8px;border:1px solid #263750;background:#10192b;color:#a4b4ca;font-size:12px}

/* main layout */
.main-grid{display:grid;grid-template-columns:300px minmax(0,1fr);gap:14px;align-items:start}
.detail-panel{padding:14px;overflow:hidden}
.detail-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.detail-title{font-size:16px;font-weight:950;color:#fff}
.detail-sub{font-size:10px;color:#8c9db5;margin-top:3px}
.detail-mark{width:30px;height:30px;border-radius:10px;background:linear-gradient(135deg,#9b5cf6,#7c3aed);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.08)}
.detail-mark::before{content:"";width:10px;height:10px;border:2px solid #fff;border-radius:2px}
.detail-balance{font-size:18px;font-weight:950;color:var(--ob-green);margin:18px 0 16px;font-variant-numeric:tabular-nums;white-space:nowrap}
.detail-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:14px}
.detail-btn{height:34px;border-radius:9px;border:1px solid;font-size:10px;font-weight:900;background:transparent;cursor:pointer}
.detail-btn.deposit{border-color:rgba(34,229,107,.28);background:rgba(34,229,107,.06);color:#51f08f}
.detail-btn.withdraw{border-color:rgba(255,95,109,.28);background:rgba(255,95,109,.06);color:#ff8692}
.detail-btn.transfer{border-color:rgba(24,200,255,.28);background:rgba(24,200,255,.06);color:#55d8ff}
.detail-stats{border-top:1px solid #1d2d45;padding-top:10px}
.stat-row{display:flex;align-items:center;justify-content:space-between;gap:8px;min-height:38px;border-bottom:1px solid #18263d;font-size:10px}
.stat-row:last-child{border-bottom:0}
.stat-label{color:#7f91a8;font-weight:800}
.stat-value{color:#edf2fa;font-weight:900;white-space:nowrap}
.status-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 9px;border:1px solid rgba(34,229,107,.18);background:rgba(34,229,107,.06);border-radius:999px;color:#51f08f;font-size:9px;font-weight:900}
.status-pill i{width:5px;height:5px;border-radius:50%;background:var(--ob-green)}
.detail-footer{margin-top:10px;padding-top:10px;border-top:1px solid #1d2d45;font-size:9px;color:#64748b;display:flex;justify-content:space-between;gap:10px}

/* list panel */
.list-panel{padding:12px 12px 10px}
.list-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}
.list-title{font-size:18px;font-weight:950;color:#fff}
.list-sub{font-size:10px;color:#6e8099;margin-top:3px}
.list-actions{display:flex;gap:8px;align-items:center}
.small-btn{height:34px;padding:0 12px;border-radius:10px;border:1px solid #35455f;background:#10192c;color:#d9e1ee;font-size:11px;font-weight:900;cursor:pointer}
.small-btn.primary{background:linear-gradient(135deg,#9b5cf6,#7c3aed);border-color:#9b5cf6;color:#fff}
.small-btn[disabled]{opacity:.55;cursor:not-allowed}

.table-wrap{border:1px solid var(--ob-border-soft);border-radius:12px;overflow:hidden}
.table-row{display:grid;grid-template-columns:46px minmax(220px,1.4fr) minmax(180px,1fr) 160px 150px 110px;align-items:center;min-height:58px;border-bottom:1px solid #18253a;transition:.16s ease}
.table-row:last-child{border-bottom:0}
.table-row.head{min-height:42px;background:#111b2e;color:#8191a8;font-size:10px;font-weight:900}
.table-row.data:hover{background:rgba(139,92,246,.035)}
.table-row.selected{background:linear-gradient(90deg,rgba(139,92,246,.10),rgba(139,92,246,.03));box-shadow:inset -2px 0 0 var(--ob-purple)}
.cell{padding:8px 12px;min-width:0}
.row-no{color:#a78bfa;font-weight:950}
.bank-cell{display:flex;align-items:center;gap:10px;min-width:0}
.bank-badge{width:34px;height:34px;border-radius:10px;background:#1a1234;border:1px solid #2d3d5a;display:flex;align-items:center;justify-content:center;color:#c8bbff;font-size:8px;font-weight:950;letter-spacing:.06em}
.bank-name{font-size:12px;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bank-meta{font-size:9px;color:#718198;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.acc-number{font-size:10px;color:#c2ccdb;direction:ltr;text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.balance-cell{font-size:12px;font-weight:950;color:var(--ob-green);font-variant-numeric:tabular-nums;white-space:nowrap}
.status-row{display:inline-flex;align-items:center;gap:6px;padding:3px 9px;border-radius:999px;border:1px solid rgba(34,229,107,.18);background:rgba(34,229,107,.06);color:#51f08f;font-size:9px;font-weight:900}
.status-row i{width:5px;height:5px;border-radius:50%;background:var(--ob-green)}
.actions{display:flex;gap:6px;justify-content:flex-start;direction:rtl}
.icon-btn{width:30px;height:30px;border-radius:8px;border:1px solid #33445f;background:#10192b;color:#c8d3e1;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center}
.icon-btn:hover{border-color:var(--ob-purple);color:#fff}
.icon-btn.more{color:#d6bcff}
.icon-btn.deposit{color:#55d8ff}
.icon-btn.withdraw{color:#ff6f7f}
.empty{padding:42px 16px;text-align:center;color:#6a7a92;font-size:11px}
.pagination{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 3px 0;color:#697990;font-size:9px}
.page-buttons{display:flex;gap:5px;direction:ltr}.page-btn{min-width:30px;height:28px;border-radius:8px;border:1px solid #22324b;background:#10192b;color:#9bacc1;font-size:10px;cursor:pointer}.page-btn.active{background:#7040e8;border-color:#8b5cf6;color:#fff;font-weight:900}

/* recent tx */
.recent-panel{margin-top:14px;padding:12px 12px 10px}
.recent-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
.recent-title{font-size:16px;font-weight:950;color:#fff}
.recent-sub{font-size:10px;color:#6d8098}
.tx-list{display:grid;grid-template-columns:1fr;gap:8px}
.tx-item{display:grid;grid-template-columns:120px 130px minmax(0,1fr) 140px 44px;gap:10px;align-items:center;padding:11px 12px;border:1px solid #1d2d45;background:#0d1728;border-radius:12px}
.tx-balance{font-size:9px;color:#7586a0}
.tx-amount{font-size:13px;font-weight:950;font-variant-numeric:tabular-nums;white-space:nowrap}
.tx-amount.in{color:#51f08f}.tx-amount.out{color:#ff8590}
.tx-date{font-size:10px;color:#aeb8c8;font-variant-numeric:tabular-nums;white-space:nowrap}
.tx-desc{min-width:0}.tx-main{font-size:11px;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.tx-ref{font-size:9px;color:#718198;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tx-user{font-size:10px;color:#c9d3e2;white-space:nowrap}
.tx-ind{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;border:1px solid}.tx-ind.in{background:rgba(34,229,107,.07);border-color:rgba(34,229,107,.18);color:#51f08f}.tx-ind.out{background:rgba(255,95,109,.07);border-color:rgba(255,95,109,.18);color:#ff8590}
.recent-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:10px}
.recent-card{padding:12px 14px;border:1px solid #1d2d45;border-radius:12px;background:#0d1728}
.recent-card .lbl{font-size:10px;color:#7788a0;font-weight:850}.recent-card .val{font-size:15px;font-weight:950;margin-top:6px;font-variant-numeric:tabular-nums;white-space:nowrap}.recent-card .val.in{color:#51f08f}.recent-card .val.out{color:#ff8590}
.no-tx{padding:34px 16px;text-align:center;color:#64748b;font-size:11px}

/* modals */
.modal-ov{position:fixed;inset:0;background:rgba(2,6,15,.84);backdrop-filter:blur(6px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:18px}
.modal-bx{background:#101827;border:1px solid #2b3e5e;border-radius:16px;padding:22px;width:430px;max-width:95vw;box-shadow:0 25px 80px rgba(0,0,0,.48)}
.modal-kicker{font-size:9px;color:#7c6dff;font-weight:900;letter-spacing:.08em;direction:ltr;text-align:right}
.modal-t{font-size:17px;font-weight:950;color:#eef3f9;margin:5px 0 17px}
.modal-balance{font-size:10px;color:#71819a;margin:-7px 0 14px;padding:10px 11px;border-radius:9px;background:#0d1523;border:1px solid #1d2c45}.modal-balance b{color:#6ee7b7}
.fi-label{font-size:10px;color:#8190a7;font-weight:800;margin-bottom:6px;display:block}
.fi-inp{width:100%;background:#0d1523;border:1px solid #263750;border-radius:9px;padding:10px 12px;color:#e7edf6;font-size:12px;outline:none;margin-bottom:12px}
.fi-inp:focus{border-color:var(--ob-purple)}
.amount-inp{font-size:21px!important;font-weight:950!important;text-align:center!important;font-variant-numeric:tabular-nums}
.btn-confirm{width:100%;height:42px;border:none;border-radius:9px;color:#fff;font-size:12px;font-weight:900;cursor:pointer;margin-bottom:8px}.btn-confirm.deposit{background:#16965d}.btn-confirm.withdraw{background:#c53a47}.btn-confirm.transfer{background:#7040e8}
.btn-cancel{width:100%;height:38px;background:transparent;border:1px solid #263750;border-radius:9px;color:#8b99ae;cursor:pointer;font-size:11px}

@media(max-width:1200px){
    .hero,.top-filter-bar,.main-grid{grid-template-columns:1fr}
    .header-main{flex-direction:column}
}
@media(max-width:980px){
    .kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .table-row{grid-template-columns:46px minmax(180px,1.5fr) minmax(140px,1fr) 130px 96px}
    .table-row .hide-md{display:none}
    .recent-summary{grid-template-columns:1fr}
    .tx-item{grid-template-columns:1fr}
}
@media(max-width:680px){
    .kpi-grid{grid-template-columns:1fr}
    .page-title{font-size:28px}
    .table-row{grid-template-columns:40px minmax(150px,1fr) 90px}
    .table-row .hide-sm{display:none}
    .detail-actions{grid-template-columns:1fr}
    .pagination{flex-direction:column;align-items:flex-start}
}
</style>

<div class="bank-page">
    <div class="hero">
        <div class="panel balance-card">
            <div>
                <div class="balance-label">إجمالي أرصدة البنوك</div>
                <div class="balance-value">{{ number_format($totalBalance, 2) }} ر.س</div>
            </div>
            <div class="balance-badge"></div>
        </div>

        <div class="header-main">
            <div>
                <div class="header-title">إدارة الحسابات البنكية</div>
                <h1 class="page-title">إدارة الحسابات البنكية</h1>
                <div class="page-sub">متابعة الحسابات البنكية، الأرصدة، والحركات البنكية من شاشة تشغيلية واحدة</div>
                <div class="market-chip"><span class="market-dot"></span> المملكة العربية السعودية · SAR</div>
            </div>
            <div class="header-actions">
                <button type="button" class="header-btn">تحويل بين البنوك ↗</button>
                <button type="button" class="header-btn primary" disabled title="يتطلب إضافة إجراء إنشاء حساب في Page class">+ إضافة حساب بنكي جديد</button>
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="panel kpi-card green">
            <div class="kpi-top"><span class="kpi-label">إجمالي أرصدة البنوك</span><span class="kpi-code">SAR</span></div>
            <div class="kpi-value">{{ number_format($totalBalance, 2) }} ر.س</div>
            <div class="kpi-note">إجمالي الرصيد الحالي لجميع الحسابات</div>
        </div>
        <div class="panel kpi-card">
            <div class="kpi-top"><span class="kpi-label">عدد الحسابات البنكية</span><span class="kpi-code">ACCT</span></div>
            <div class="kpi-value">{{ $accountCount }}</div>
            <div class="kpi-note">حساب بنكي مسجل في النظام</div>
        </div>
        <div class="panel kpi-card">
            <div class="kpi-top"><span class="kpi-label">متوسط رصيد الحساب</span><span class="kpi-code">AVG</span></div>
            <div class="kpi-value">{{ number_format($averageBalance, 2) }} ر.س</div>
            <div class="kpi-note">متوسط الرصيد عبر جميع الحسابات</div>
        </div>
        <div class="panel kpi-card">
            <div class="kpi-top"><span class="kpi-label">أعلى رصيد حساب</span><span class="kpi-code">MAX</span></div>
            <div class="kpi-value">{{ number_format($highestBalance, 2) }} ر.س</div>
            <div class="kpi-note">أعلى رصيد حالي ضمن الحسابات المسجلة</div>
        </div>
    </div>

    <div class="top-filter-bar">
        <div class="panel filter-balance-placeholder">
            <button type="button" class="filter-chip">الكل</button>
        </div>
        <div class="panel search-panel">
            <button type="button" class="search-btn">⌕</button>
            <input type="search" class="search-input" placeholder="ابحث عن حساب..." oninput="filterBankRows(this.value)">
        </div>
    </div>

    <div class="main-grid">
        <div class="panel detail-panel">
            @if($detailBank)
                <div class="detail-head">
                    <div>
                        <div class="detail-title">{{ $detailBank->bank_name }}</div>
                        <div class="detail-sub">{{ $detailBank->account_name }} — {{ $detailBank->account_number }}</div>
                    </div>
                    <div class="detail-mark"></div>
                </div>

                <div class="detail-balance">{{ number_format($detailBank->current_balance, 2) }} ر.س</div>

                <div class="detail-actions">
                    <button class="detail-btn deposit" wire:click="openDeposit({{ $detailBank->id }})">إيداع</button>
                    <button class="detail-btn withdraw" wire:click="openWithdraw({{ $detailBank->id }})">سحب</button>
                    <button class="detail-btn transfer" wire:click="openTransfer({{ $detailBank->id }})">تحويل</button>
                </div>

                <div class="detail-stats">
                    <div class="stat-row">
                        <span class="stat-label">اسم الحساب</span>
                        <span class="stat-value">{{ $detailBank->account_name }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">حالة الحساب</span>
                        <span class="status-pill"><i></i> نشط</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">إجمالي الواردات</span>
                        <span class="stat-value">{{ number_format($transactionsIn, 2) }} ر.س</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">إجمالي السحوبات</span>
                        <span class="stat-value">{{ number_format($transactionsOut, 2) }} ر.س</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">آخر حركة</span>
                        <span class="stat-value">{{ $visibleTransactions->count() ? 'عرض '.min(50,$visibleTransactions->count()).' حركة' : 'لا توجد حركات' }}</span>
                    </div>
                </div>

                <div class="detail-footer">
                    <span>آخر تحديث</span>
                    <span>{{ now()->format('H:i d/m/Y') }}</span>
                </div>
            @else
                <div class="empty">لا يوجد حساب بنكي لعرضه</div>
            @endif
        </div>

        <div class="panel list-panel">
            <div class="list-head">
                <div>
                    <div class="list-title">قائمة الحسابات البنكية</div>
                    <div class="list-sub">{{ $accountCount }} حساب مسجل في النظام</div>
                </div>
                <div class="list-actions">
                    <button type="button" class="small-btn">كل الحسابات</button>
                </div>
            </div>

            <div class="table-wrap" id="bank-accounts-table">
                <div class="table-row head">
                    <div class="cell">#</div>
                    <div class="cell">اسم الحساب</div>
                    <div class="cell">رقم الحساب / IBAN</div>
                    <div class="cell hide-md">الرصيد الحالي</div>
                    <div class="cell hide-sm">الحالة / الإجراءات</div>
                    <div class="cell hide-sm">العمليات</div>
                </div>

                @forelse($bankAccounts as $bank)
                    <div class="table-row data {{ $detailBankId == $bank->id ? 'selected' : '' }} bank-data-row"
                         data-search="{{ mb_strtolower($bank->bank_name.' '.$bank->account_name.' '.$bank->account_number) }}">
                        <div class="cell row-no">{{ $loop->iteration }}</div>
                        <div class="cell bank-cell">
                            <div class="bank-badge">BANK</div>
                            <div style="min-width:0">
                                <div class="bank-name">{{ $bank->bank_name }}</div>
                                <div class="bank-meta">{{ $bank->account_name }}</div>
                            </div>
                        </div>
                        <div class="cell acc-number">{{ $bank->account_number }}</div>
                        <div class="cell balance-cell hide-md">{{ number_format($bank->current_balance, 2) }} ر.س</div>
                        <div class="cell hide-sm">
                            <span class="status-row"><i></i> نشط</span>
                        </div>
                        <div class="cell hide-sm" onclick="event.stopPropagation()">
                            <div class="actions">
                                <button type="button" class="icon-btn more" title="الحركات" wire:click.stop="toggleTransactions({{ $bank->id }})">{{ $viewTxBankId == $bank->id ? '×' : '•••' }}</button>
                                <button type="button" class="icon-btn deposit" title="إيداع" wire:click="openDeposit({{ $bank->id }})">↘</button>
                                <button type="button" class="icon-btn withdraw" title="سحب" wire:click="openWithdraw({{ $bank->id }})">↗</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty">لا توجد حسابات بنكية مسجلة حتى الآن</div>
                @endforelse
            </div>

            <div class="pagination">
                <div>إظهار <b style="color:#dfe7f2">10</b> حسابات</div>
                <div>من 1 إلى {{ $accountCount }} من {{ $accountCount }}</div>
                <div class="page-buttons">
                    <button type="button" class="page-btn">«</button>
                    <button type="button" class="page-btn">‹</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn">›</button>
                    <button type="button" class="page-btn">»</button>
                </div>
            </div>

            @if($viewTxBankId && $detailBank)
                <div class="panel recent-panel" style="margin-top:14px;">
                    <div class="recent-head">
                        <div>
                            <div class="recent-title">آخر الحركات</div>
                            <div class="recent-sub">{{ $detailBank->bank_name }} — {{ $detailBank->account_name }}</div>
                        </div>
                        <button type="button" class="small-btn" wire:click="toggleTransactions({{ $detailBank->id }})">إخفاء الحركات</button>
                    </div>

                    <div class="recent-summary">
                        <div class="recent-card">
                            <div class="lbl">إجمالي الإيداعات</div>
                            <div class="val in">{{ number_format($transactionsIn, 2) }} ر.س</div>
                        </div>
                        <div class="recent-card">
                            <div class="lbl">إجمالي السحوبات</div>
                            <div class="val out">{{ number_format($transactionsOut, 2) }} ر.س</div>
                        </div>
                        <div class="recent-card">
                            <div class="lbl">عدد الحركات</div>
                            <div class="val">{{ $visibleTransactions->count() }}</div>
                        </div>
                    </div>

                    @if($visibleTransactions->count())
                        <div class="tx-list">
                            @foreach($visibleTransactions as $tx)
                                @php
                                    $isIncoming = in_array($tx->type, ['deposit','transfer_in']);
                                @endphp
                                <div class="tx-item">
                                    <div>
                                        <div class="tx-balance">الرصيد بعد الحركة</div>
                                        <div class="tx-amount {{ $isIncoming ? 'in' : 'out' }}">{{ number_format($tx->balance_after, 2) }} ر.س</div>
                                    </div>
                                    <div>
                                        <div class="tx-date">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="tx-desc">
                                        <div class="tx-main">{{ $tx->description ?: 'حركة بنكية' }}</div>
                                        <div class="tx-ref">{{ $tx->reference ? 'REF: '.$tx->reference : 'بدون مرجع' }}</div>
                                    </div>
                                    <div class="tx-user">{{ $tx->createdBy?->name ?? 'System' }}</div>
                                    <div class="tx-ind {{ $isIncoming ? 'in' : 'out' }}">{{ $isIncoming ? '↓' : '↑' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-tx">لا توجد حركات مسجلة على هذا الحساب حتى الآن</div>
                    @endif
                </div>
            @endif
        </div>
    </div>





    {{-- Deposit modal --}}
    @if($showDeposit)
        @php $bank = $bankAccounts->find($selectedBankId); @endphp
        <div class="modal-ov">
            <div class="modal-bx">
                <div class="modal-kicker">BANK DEPOSIT</div>
                <div class="modal-t">إيداع في {{ $bank?->bank_name }}</div>
                <div class="modal-balance">الرصيد الحالي: <b>{{ number_format($bank?->current_balance ?? 0, 2) }} ر.س</b></div>
                <label class="fi-label">المبلغ (ر.س)</label>
                <input type="number" wire:model="txAmount" class="fi-inp amount-inp" placeholder="0.00" min="0.01" step="0.01">
                <label class="fi-label">رقم المرجع</label>
                <input type="text" wire:model="txRef" class="fi-inp" placeholder="رقم الإيصال أو المرجع البنكي...">
                <label class="fi-label">ملاحظة</label>
                <input type="text" wire:model="txDesc" class="fi-inp" placeholder="وصف مختصر للإيداع...">
                <button wire:click="doDeposit" class="btn-confirm deposit">تأكيد الإيداع</button>
                <button wire:click="$set('showDeposit',false)" class="btn-cancel">إلغاء</button>
            </div>
        </div>
    @endif

    {{-- Withdraw modal --}}
    @if($showWithdraw)
        @php $bank = $bankAccounts->find($selectedBankId); @endphp
        <div class="modal-ov">
            <div class="modal-bx">
                <div class="modal-kicker">BANK WITHDRAWAL</div>
                <div class="modal-t">سحب من {{ $bank?->bank_name }}</div>
                <div class="modal-balance">الرصيد الحالي: <b>{{ number_format($bank?->current_balance ?? 0, 2) }} ر.س</b></div>
                <label class="fi-label">المبلغ (ر.س)</label>
                <input type="number" wire:model="txAmount" class="fi-inp amount-inp" placeholder="0.00" min="0.01" step="0.01">
                <label class="fi-label">رقم المرجع</label>
                <input type="text" wire:model="txRef" class="fi-inp" placeholder="رقم الشيك أو المرجع البنكي...">
                <label class="fi-label">ملاحظة</label>
                <input type="text" wire:model="txDesc" class="fi-inp" placeholder="وصف مختصر للسحب...">
                <button wire:click="doWithdraw" class="btn-confirm withdraw">تأكيد السحب</button>
                <button wire:click="$set('showWithdraw',false)" class="btn-cancel">إلغاء</button>
            </div>
        </div>
    @endif

    {{-- Transfer modal --}}
    @if($showTransfer)
        @php $bank = $bankAccounts->find($selectedBankId); @endphp
        <div class="modal-ov">
            <div class="modal-bx">
                <div class="modal-kicker">INTERNAL TRANSFER</div>
                <div class="modal-t">تحويل من {{ $bank?->bank_name }}</div>
                <div class="modal-balance">الرصيد المتاح: <b>{{ number_format($bank?->current_balance ?? 0, 2) }} ر.س</b></div>
                <label class="fi-label">إلى حساب</label>
                <select wire:model="transferToId" class="fi-inp">
                    <option value="">اختر الحساب المستفيد...</option>
                    @foreach($bankAccounts as $b)
                        @if($b->id != $selectedBankId)
                            <option value="{{ $b->id }}">{{ $b->bank_name }} — {{ $b->account_number }}</option>
                        @endif
                    @endforeach
                </select>
                <label class="fi-label">المبلغ (ر.س)</label>
                <input type="number" wire:model="txAmount" class="fi-inp amount-inp" placeholder="0.00" min="0.01" step="0.01">
                <label class="fi-label">ملاحظة</label>
                <input type="text" wire:model="txDesc" class="fi-inp" placeholder="سبب التحويل...">
                <button wire:click="doTransfer" class="btn-confirm transfer">تأكيد التحويل</button>
                <button wire:click="$set('showTransfer',false)" class="btn-cancel">إلغاء</button>
            </div>
        </div>
    @endif
</div>

<script>
function filterBankRows(term){
    const q = (term || '').trim().toLowerCase();
    document.querySelectorAll('#bank-accounts-table .bank-data-row').forEach(row => {
        const haystack = (row.dataset.search || row.innerText || '').toLowerCase();
        row.style.display = haystack.includes(q) ? '' : 'none';
    });
}
</script>
</x-filament-panels::page>