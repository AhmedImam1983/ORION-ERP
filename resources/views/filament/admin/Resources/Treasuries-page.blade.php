<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --bg:#070b14;--panel:#0d1320;--panel2:#111827;--line:#1c2a43;
    --text:#e8edf7;--muted:#71809a;--purple:#7c4dff;--purple2:#9b6cff;
    --green:#22e66f;--red:#ff5d63;--blue:#28a9ff;
}
body{background:var(--bg)}
.treasury-page{direction:rtl;color:var(--text);padding-bottom:24px}
.page-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:18px}
.page-title-wrap{display:flex;align-items:center;gap:13px}
.page-icon{width:48px;height:48px;border-radius:13px;background:linear-gradient(145deg,#693cff,#8f65ff);display:flex;align-items:center;justify-content:center;font-size:23px;box-shadow:0 10px 30px #6d45ff25}
.page-title{font-size:26px;font-weight:900;line-height:1.2}
.page-crumb{font-size:11px;color:#62708a;margin-top:6px}
.balance-card{min-width:370px;background:linear-gradient(120deg,#0d1425,#101a2d);border:1px solid #233250;border-radius:16px;padding:15px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 12px 35px #00000020}
.balance-icon{width:58px;height:58px;border-radius:14px;background:linear-gradient(145deg,#6438e8,#8f61ff);display:flex;align-items:center;justify-content:center;font-size:26px}
.balance-label{font-size:11px;color:#7d8aa0;margin-bottom:4px}
.balance-value{font-size:27px;font-weight:900;color:var(--green);direction:ltr;text-align:right}

.toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
.search-box{width:320px;background:#0c1320;border:1px solid #263650;border-radius:11px;padding:11px 14px;color:#8491a8;display:flex;align-items:center;gap:8px;font-size:12px}
.search-box span{flex:1;text-align:right}
.filter-box{background:#0c1320;border:1px solid #263650;border-radius:11px;padding:10px 14px;color:#8b98ad;font-size:12px}
.new-btn{background:linear-gradient(135deg,#7143ef,#925dfd);border:0;border-radius:10px;color:#fff;padding:11px 18px;font-weight:800;font-size:12px;cursor:pointer;box-shadow:0 8px 22px #7848ff25}
.transfer-top{background:#11172a;border:1px solid #303b61;color:#a98bff;border-radius:10px;padding:10px 15px;font-weight:800;font-size:12px;cursor:pointer}

.main-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(320px,.75fr);gap:14px;align-items:start}
.panel{background:linear-gradient(145deg,#0b111c,#0d1421);border:1px solid #1d2b44;border-radius:15px;overflow:hidden;box-shadow:0 10px 35px #00000018}
.panel-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid #19263b}
.panel-title{font-size:15px;font-weight:900}
.panel-sub{font-size:10px;color:#64738c;margin-top:3px}
.table{width:100%}
.table-row{display:grid;grid-template-columns:45px 1.5fr 1fr 1fr .8fr 112px;align-items:center;min-height:72px;border-bottom:1px solid #152238;padding:0 14px;transition:.15s}
.table-row:not(.head):hover{background:#111a2b}
.table-row.head{min-height:42px;background:#101827;color:#65738a;font-size:10px;font-weight:800}
.cell{min-width:0}
.num{color:#a47bff;font-weight:900;text-align:center}
.treasury-name{display:flex;align-items:center;gap:10px}
.t-icon{width:38px;height:38px;border-radius:10px;background:#201a45;color:#a98cff;display:flex;align-items:center;justify-content:center;font-size:18px}
.name-main{font-size:12px;font-weight:800;color:#e5eaf3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.name-sub{font-size:9px;color:#596880;margin-top:3px}
.branch-main{font-size:11px;color:#d4dbea}
.branch-sub{font-size:9px;color:#52627b;margin-top:3px}
.row-balance{font-size:13px;font-weight:900;color:var(--green);direction:ltr;text-align:right}
.last-main{font-size:10px;color:#c8d0de}
.last-sub{font-size:9px;color:#5e6d85;margin-top:3px}
.status{display:inline-flex;align-items:center;gap:5px;background:#092c1b;color:#35e97d;border:1px solid #164f30;border-radius:20px;padding:5px 9px;font-size:9px;font-weight:800}
.status i{width:6px;height:6px;border-radius:50%;background:#28e66e}
.actions{display:flex;gap:5px;justify-content:flex-start;direction:ltr}
.action{width:30px;height:30px;border-radius:8px;border:1px solid #263650;background:#0e1727;color:#8090aa;cursor:pointer}
.action.edit{color:#35b8ff}.action.delete{color:#ff666b}.action.more{color:#b28bff}
.pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 15px;color:#60708a;font-size:10px}
.pages{display:flex;gap:5px;direction:ltr}
.pg{width:30px;height:30px;border:1px solid #23334e;background:#0c1422;color:#73829a;border-radius:8px}
.pg.active{background:#7848f3;color:#fff;border-color:#8e66ff}

.detail-card{padding:18px}
.detail-head{display:flex;justify-content:space-between;align-items:flex-start}
.detail-title{font-size:15px;font-weight:900}
.detail-sub{font-size:10px;color:#687790;margin-top:4px}
.detail-balance{font-size:26px;font-weight:900;color:var(--green);margin:18px 0 13px;direction:ltr;text-align:right}
.detail-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px}
.detail-btn{padding:10px 5px;border-radius:9px;border:1px solid #263650;background:#101827;color:#a9b3c5;font-weight:800;font-size:10px;cursor:pointer}
.detail-btn.in{color:#28e77a;border-color:#1b6541;background:#0b291c}
.detail-btn.out{color:#ff6a70;border-color:#633035;background:#2a1115}
.detail-btn.transfer{color:#a98cff;border-color:#46337d;background:#17132d}
.detail-stats{margin-top:13px;border-top:1px solid #1a2940}
.stat{display:flex;justify-content:space-between;padding:12px 2px;border-bottom:1px solid #162238;font-size:10px}
.stat strong{font-size:11px}.green{color:var(--green)}.red{color:var(--red)}
.detail-meta{display:flex;justify-content:space-between;align-items:center;margin-top:13px;font-size:10px;color:#687790}
.count-badge{background:#18243a;border:1px solid #273957;border-radius:8px;padding:5px 8px;color:#a8b5c9}

.tx-panel{margin-top:14px}
.tx-item{display:grid;grid-template-columns:38px 1fr 150px 130px;gap:12px;align-items:center;padding:11px 15px;border-bottom:1px solid #152238}
.tx-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px}
.tx-in{background:#082d1b;color:var(--green);border:1px solid #165a35}.tx-out{background:#311316;color:var(--red);border:1px solid #663035}
.tx-desc{font-size:11px;font-weight:800}.tx-sub{font-size:9px;color:#586780;margin-top:3px}
.tx-amount{font-size:12px;font-weight:900;text-align:right;direction:ltr}
.tx-after{font-size:9px;color:#5f6e85;text-align:left}
.tx-empty{text-align:center;padding:28px;color:#53627a;font-size:11px}
.tx-footer{display:flex;justify-content:center;padding:12px;color:#9b78ff;font-size:11px;font-weight:800}

.modal-ov{position:fixed;inset:0;background:rgba(2,5,12,.82);backdrop-filter:blur(7px);z-index:1000;display:flex;align-items:center;justify-content:center}
.modal-bx{background:#0e1625;border:1px solid #30405e;border-radius:18px;padding:24px;width:440px;max-width:95vw;box-shadow:0 25px 80px #000}
.modal-t{font-size:17px;font-weight:900;color:#edf2fa;margin-bottom:18px}
.fi-label{font-size:11px;color:#73819a;font-weight:700;margin-bottom:6px}
.fi-select,.fi-input{width:100%;background:#111a29;border:1px solid #293a57;border-radius:9px;padding:11px 12px;color:#e5eaf3;font-size:12px;outline:none;margin-bottom:13px}
.btn-confirm{width:100%;padding:12px;background:linear-gradient(135deg,#7044ef,#945cff);border:0;border-radius:9px;color:#fff;font-size:13px;font-weight:800;cursor:pointer}
.btn-cancel{width:100%;margin-top:8px;padding:10px;background:transparent;border:1px solid #273650;border-radius:9px;color:#75849b;cursor:pointer;font-size:12px}

@media(max-width:1100px){
    .main-grid{grid-template-columns:1fr}.balance-card{min-width:300px}
    .table-row{grid-template-columns:35px 1.5fr 1fr 1fr 95px}
    .table-row .branch-col{display:none}
}
@media(max-width:760px){
    .page-head,.toolbar{flex-direction:column;align-items:stretch}.balance-card{min-width:0}
    .search-box{width:100%}.table{overflow:auto}.table-row{min-width:760px}
    .tx-item{grid-template-columns:38px 1fr}.tx-amount,.tx-after{display:none}
}
</style>

<div class="treasury-page">

    @if($showCreateTreasury)
    <div class="modal-ov">
        <div class="modal-bx">
            <div class="modal-t">▣ إضافة خزينة جديدة</div>
            <div class="fi-label">اسم الخزينة</div>
            <input type="text" wire:model="newTreasuryName" class="fi-input" placeholder="مثال: الخزينة الرئيسية">
            <div class="fi-label">نوع الخزينة</div>
            <select wire:model="newTreasuryType" class="fi-select">
                <option value="cash">نقدية</option><option value="main">رئيسية</option><option value="branch">فرعية</option>
            </select>
            <div class="fi-label">الرصيد الافتتاحي (ر.س)</div>
            <input type="number" wire:model="newTreasuryBalance" class="fi-input" placeholder="0.00" min="0" step="0.01">
            <div style="font-size:10px;color:#64738c;margin:-5px 0 13px">سيتم ربط الخزينة بفرع المستخدم الحالي، أو بالفرع الرئيسي.</div>
            <button wire:click="createTreasury" class="btn-confirm">✅ حفظ الخزينة</button>
            <button wire:click="$set('showCreateTreasury',false)" class="btn-cancel">إلغاء</button>
        </div>
    </div>
    @endif

    @if($showTransfer)
    <div class="modal-ov">
        <div class="modal-bx">
            <div class="modal-t">↗️ تحويل بين الخزائن</div>

            <div class="fi-label">من خزينة</div>
            <select wire:model="fromTreasuryId" class="fi-select">
                <option value="">اختر خزينة...</option>
                @foreach($this->getTreasuries() as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} ({{ number_format($t->current_balance, 2) }} ر.س)</option>
                @endforeach
            </select>

            <div class="fi-label">إلى خزينة</div>
            <select wire:model="toTreasuryId" class="fi-select">
                <option value="">اختر خزينة...</option>
                @foreach($this->getTreasuries() as $t)
                    @if($t->id != $fromTreasuryId)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endif
                @endforeach
            </select>

            <div class="fi-label">المبلغ (ر.س)</div>
            <input type="number" wire:model="transferAmount" class="fi-input" placeholder="0.00" min="0.01" step="0.01">

            <div class="fi-label">ملاحظة (اختياري)</div>
            <input type="text" wire:model="transferDesc" class="fi-input" placeholder="سبب التحويل...">

            <button wire:click="doTransfer" class="btn-confirm">✅ تأكيد التحويل</button>
            <button wire:click="$set('showTransfer',false)" class="btn-cancel">إلغاء</button>
        </div>
    </div>
    @endif

    @php
        $treasuries = $this->getTreasuries();
        $selected = $selectedTreasuryId ? $treasuries->find($selectedTreasuryId) : $treasuries->first();
        $transactions = $selectedTreasuryId ? $this->getTransactions() : collect();
        $inTotal = $transactions->filter(fn($x) => in_array($x->type, ['in','transfer_in']))->sum('amount');
        $outTotal = $transactions->filter(fn($x) => !in_array($x->type, ['in','transfer_in']))->sum('amount');
    @endphp

    <div class="page-head">
        <div class="page-title-wrap">
            <div class="page-icon">▣</div>
            <div>
                <div class="page-title">إدارة الخزائن</div>
                <div class="page-crumb">المحاسبة &nbsp;/&nbsp; الخزائن</div>
            </div>
        </div>

        <div class="balance-card">
            <div class="balance-icon">▣</div>
            <div>
                <div class="balance-label">إجمالي رصيد الخزائن</div>
                <div class="balance-value">ر.س {{ number_format($this->getTotalBalance(), 2) }}</div>
            </div>
        </div>
    </div>

    <div class="toolbar">
        <div style="display:flex;gap:8px;align-items:center">
            <button type="button" wire:click="openCreateTreasury" class="new-btn">＋ إضافة خزينة جديدة</button>
            <button wire:click="openTransfer()" class="transfer-top">↗ تحويل بين الخزائن</button>
        </div>
        <div style="display:flex;gap:8px">
            <div class="filter-box">☰ &nbsp; الكل</div>
            <div class="search-box"><span>بحث عن خزينة...</span>⌕</div>
        </div>
    </div>

    <div class="main-grid">

        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">قائمة الخزائن &nbsp; ▣</div>
                    <div class="panel-sub">{{ $treasuries->count() }} خزائن مسجلة في النظام</div>
                </div>
            </div>

            <div class="table">
                <div class="table-row head">
                    <div class="cell num">#</div>
                    <div class="cell">اسم الخزينة</div>
                    <div class="cell branch-col">الفرع</div>
                    <div class="cell">الرصيد الحالي</div>
                    <div class="cell">آخر حركة</div>
                    <div class="cell">الحالة / الإجراءات</div>
                </div>

                @forelse($treasuries as $treasury)
                    <div class="table-row" wire:click="selectTreasury({{ $treasury->id }})">
                        <div class="cell num">{{ $loop->iteration }}</div>

                        <div class="cell treasury-name">
                            <div class="t-icon">▣</div>
                            <div>
                                <div class="name-main">{{ $treasury->name }}</div>
                                <div class="name-sub">{{ $treasury->type_label }}</div>
                            </div>
                        </div>

                        <div class="cell branch-col">
                            <div class="branch-main">{{ $treasury->branch?->name ?? 'الرئيسية' }}</div>
                            <div class="branch-sub">الفرع الرئيسي</div>
                        </div>

                        <div class="cell row-balance">ر.س {{ number_format($treasury->current_balance, 2) }}</div>

                        <div class="cell">
                            <div class="last-main">اليوم</div>
                            <div class="last-sub">آخر حركة مسجلة</div>
                        </div>

                        <div class="cell">
                            <div class="status"><i></i> نشطة</div>
                            <div class="actions" style="margin-top:5px">
                                <button class="action more" type="button" wire:click.stop="selectTreasury({{ $treasury->id }})">•••</button>
                                <button class="action edit" type="button" wire:click.stop="selectTreasury({{ $treasury->id }})">✎</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tx-empty">لا توجد خزائن — أضف خزينة من الإعدادات</div>
                @endforelse
            </div>

            <div class="pagination">
                <div>إظهار <b>10</b> &nbsp; من {{ $treasuries->count() }} خزائن</div>
                <div class="pages">
                    <button class="pg">«</button><button class="pg">‹</button>
                    <button class="pg active">1</button>
                    <button class="pg">›</button><button class="pg">»</button>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="detail-card">
                <div class="detail-head">
                    <div>
                        <div class="detail-title">تفاصيل الخزينة الرئيسية &nbsp; ▣</div>
                        <div class="detail-sub">{{ $selected?->type_label ?? 'الخزينة الأساسية' }} - {{ $selected?->branch?->name ?? 'الفرع الرئيسي' }}</div>
                    </div>
                    <div class="t-icon">▣</div>
                </div>

                <div class="detail-balance">
                    ر.س {{ number_format($selected?->current_balance ?? 0, 2) }}
                </div>

                <div class="detail-actions">
                    <button class="detail-btn in" type="button">↓ إيداع</button>
                    <button class="detail-btn out" type="button">↑ سحب</button>
                    <button wire:click="openTransfer({{ $selected?->id ?? 0 }})" class="detail-btn transfer" type="button">↔ تحويل</button>
                </div>

                <div class="detail-stats">
                    <div class="stat"><span>إجمالي الإيداعات</span><strong class="green">ر.س {{ number_format($inTotal,2) }}</strong></div>
                    <div class="stat"><span>إجمالي السحوبات</span><strong class="red">ر.س {{ number_format($outTotal,2) }}</strong></div>
                    <div class="stat"><span>آخر حركة</span><strong>{{ $transactions->first()?->created_at?->format('d/m/Y - H:i') ?? 'لا توجد' }}</strong></div>
                </div>

                <div class="detail-meta">
                    <span>عدد الحركات</span>
                    <span class="count-badge">{{ $transactions->count() }} حركة</span>
                </div>
            </div>
        </div>
    </div>

    @if($selectedTreasuryId)
        <div class="panel tx-panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">آخر الحركات &nbsp; ↕</div>
                    <div class="panel-sub">آخر العمليات المالية للخزينة المحددة</div>
                </div>
                <div class="count-badge">{{ $transactions->count() }} حركة</div>
            </div>

            @forelse($transactions->take(5) as $tx)
                @php $isIn = in_array($tx->type, ['in','transfer_in']); @endphp
                <div class="tx-item">
                    <div class="tx-dot {{ $isIn ? 'tx-in' : 'tx-out' }}">{{ $isIn ? '↓' : '↑' }}</div>
                    <div>
                        <div class="tx-desc">{{ $tx->description }}</div>
                        <div class="tx-sub">{{ $tx->createdBy?->name ?? 'System' }} · {{ $tx->created_at->format('d/m/Y H:i') }} @if($tx->reference) · {{ $tx->reference }} @endif</div>
                    </div>
                    <div class="tx-amount {{ $isIn ? 'green' : 'red' }}">{{ $isIn ? '+' : '-' }}{{ number_format($tx->amount,2) }} ر.س</div>
                    <div class="tx-after">الرصيد بعد الحركة<br><b>{{ number_format($tx->balance_after,2) }} ر.س</b></div>
                </div>
            @empty
                <div class="tx-empty">لا توجد حركات بعد</div>
            @endforelse

            @if($transactions->count())
                <div class="tx-footer">عرض كل الحركات ←</div>
            @endif
        </div>
    @endif

</div>
</x-filament-panels::page>
