<x-filament-panels::page>
<style>
*{font-family:'Cairo',sans-serif!important;box-sizing:border-box}
:root{
    --ot-bg:#07101d;--ot-panel:#0d1728;--ot-panel2:#101c30;--ot-line:#22324d;
    --ot-text:#eef3fb;--ot-muted:#71829b;--ot-purple:#8b5cf6;--ot-green:#34d399;
    --ot-red:#fb7185;--ot-amber:#fbbf24;--ot-blue:#38bdf8
}
[x-cloak]{display:none!important}
.treasury-v2{direction:rtl;color:var(--ot-text);padding-bottom:30px}
.ot-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:18px}
.ot-kicker{font-size:11px;color:#8f7cff;font-weight:900;letter-spacing:.7px;margin-bottom:7px}
.ot-title{font-size:28px;font-weight:950;line-height:1.15;margin:0}
.ot-sub{font-size:12px;color:var(--ot-muted);margin-top:6px}
.ot-head-actions{display:flex;gap:8px;align-items:center}
.ot-btn{height:40px;border-radius:10px;border:1px solid #2b3d5b;background:#101a2c;color:#c4cedd;padding:0 15px;font-size:11px;font-weight:900;cursor:pointer}
.ot-btn.primary{background:linear-gradient(135deg,#7c4dff,#915eff);border-color:#946cff;color:white}
.ot-btn:hover{filter:brightness(1.08)}

.ot-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:12px}
.ot-kpi{position:relative;background:linear-gradient(145deg,#0e192b,#0b1525);border:1px solid #22334f;border-radius:14px;padding:16px 17px;min-height:102px;overflow:hidden}
.ot-kpi:after{content:"";position:absolute;inset:auto -20px -38px auto;width:95px;height:95px;border-radius:50%;background:rgba(255,255,255,.025)}
.ot-kpi-code{position:absolute;left:12px;top:12px;font-size:9px;font-weight:900;color:#5e718f;border:1px solid #263957;border-radius:7px;padding:4px 6px}
.ot-kpi-label{font-size:11px;color:#8b9ab0;font-weight:800}
.ot-kpi-value{font-size:22px;font-weight:950;margin-top:9px;direction:ltr;text-align:right}
.ot-kpi-note{font-size:9px;color:#5f7089;margin-top:4px}
.c-green{color:var(--ot-green)}.c-red{color:var(--ot-red)}.c-purple{color:#b7a4ff}.c-amber{color:var(--ot-amber)}

.ot-tools{display:flex;align-items:center;gap:8px;background:#0b1524;border:1px solid #21314a;border-radius:12px;padding:8px;margin-bottom:12px}
.ot-search{flex:1;min-width:230px;height:38px;background:#0f1b2f;border:1px solid #263956;border-radius:9px;color:#eef3fb;padding:0 12px;outline:none;font-size:11px}
.ot-search:focus,.ot-filter:focus{border-color:#7c4dff}
.ot-filter{height:38px;min-width:155px;background:#0f1b2f;border:1px solid #263956;border-radius:9px;color:#b8c3d4;padding:0 10px;font-size:11px;outline:none}

.ot-layout{display:grid;grid-template-columns:minmax(0,1.72fr) minmax(300px,.68fr);gap:12px;align-items:start}
.ot-panel{background:linear-gradient(145deg,#0c1626,#0a1321);border:1px solid #21314a;border-radius:15px;overflow:hidden}
.ot-panel-head{padding:14px 16px;border-bottom:1px solid #1b2a40;display:flex;align-items:center;justify-content:space-between}
.ot-panel-title{font-size:14px;font-weight:950}.ot-panel-sub{font-size:9px;color:#61728b;margin-top:3px}
.ot-badge{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:5px 8px;font-size:9px;font-weight:900;border:1px solid #2a3b58;color:#8ea0bb}
.ot-badge.ok{background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.2);color:#58dfaa}
.ot-badge.warn{background:rgba(251,191,36,.08);border-color:rgba(251,191,36,.2);color:#f8cc51}

.ot-table-head,.ot-row{display:grid;grid-template-columns:42px minmax(190px,1.35fr) 1fr 1fr 1fr 86px;align-items:center}
.ot-table-head{min-height:40px;padding:0 12px;background:#0e1828;color:#60728d;font-size:9px;font-weight:900;border-bottom:1px solid #1c2b42}
.ot-row{min-height:72px;padding:0 12px;border-bottom:1px solid #17253a;cursor:pointer;transition:.15s}
.ot-row:hover{background:#101c2f}.ot-row.active{background:linear-gradient(90deg,rgba(139,92,246,.08),rgba(139,92,246,.02));box-shadow:inset -2px 0 #8b5cf6}
.ot-index{font-size:11px;font-weight:900;color:#8f7cff;text-align:center}
.ot-name{display:flex;gap:9px;align-items:center;min-width:0}
.ot-mark{width:36px;height:36px;border-radius:10px;background:#171b3c;border:1px solid #343369;display:grid;place-items:center;color:#a996ff;font-size:10px;font-weight:950;flex:none}
.ot-name-main{font-size:11px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ot-name-sub{font-size:9px;color:#586b86;margin-top:3px}
.ot-cell-main{font-size:10px;color:#cbd5e1;font-weight:800}.ot-cell-sub{font-size:8px;color:#53647c;margin-top:3px}
.ot-money{font-size:12px;font-weight:950;direction:ltr;text-align:right;color:var(--ot-green)}
.ot-dayflow{display:flex;gap:7px;align-items:center;direction:ltr;font-size:9px;font-weight:900}.ot-in{color:var(--ot-green)}.ot-out{color:var(--ot-red)}
.ot-row-actions{display:flex;gap:5px;justify-content:flex-start}
.ot-mini{height:29px;padding:0 8px;border-radius:8px;border:1px solid #263956;background:#0e192b;color:#8093ae;font-size:9px;font-weight:900;cursor:pointer}.ot-mini.transfer{color:#b29bff;border-color:#40336d}
.ot-empty{padding:55px 20px;text-align:center;color:#5c6f8b;font-size:11px}

.ot-detail{padding:17px}
.ot-detail-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.ot-detail-name{font-size:17px;font-weight:950}.ot-detail-meta{font-size:9px;color:#657790;margin-top:4px}
.ot-detail-balance{font-size:29px;font-weight:950;color:var(--ot-green);direction:ltr;text-align:right;margin:18px 0 14px}
.ot-detail-stats{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:11px}
.ot-stat{background:#0b1524;border:1px solid #1e2e47;border-radius:10px;padding:11px}
.ot-stat-label{font-size:9px;color:#657790}.ot-stat-value{font-size:12px;font-weight:950;margin-top:5px;direction:ltr;text-align:right}
.ot-transfer-btn{width:100%;height:38px;border-radius:9px;background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.28);color:#b8a6ff;font-size:10px;font-weight:900;cursor:pointer}
.ot-last{display:flex;justify-content:space-between;padding-top:12px;margin-top:12px;border-top:1px solid #1b2a40;font-size:9px;color:#657790}.ot-last strong{color:#c5cfdd;font-size:9px}

.ot-tx{margin-top:12px}
.ot-tx-head{display:grid;grid-template-columns:72px minmax(220px,1fr) 130px 140px;gap:10px;padding:10px 15px;background:#0e1828;color:#61728d;font-size:9px;font-weight:900;border-bottom:1px solid #1c2b42}
.ot-tx-row{display:grid;grid-template-columns:72px minmax(220px,1fr) 130px 140px;gap:10px;align-items:center;padding:11px 15px;border-bottom:1px solid #17253a}
.ot-type{display:inline-flex;justify-content:center;border-radius:7px;padding:5px 7px;font-size:9px;font-weight:950}
.ot-type.in{background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:#58dfaa}
.ot-type.out{background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.2);color:#fb8c9d}
.ot-tx-desc{font-size:10px;font-weight:850;color:#dce4f0}.ot-tx-sub{font-size:8px;color:#586b86;margin-top:3px}
.ot-tx-amount{direction:ltr;text-align:right;font-size:11px;font-weight:950}.ot-tx-balance{direction:ltr;text-align:right;font-size:10px;color:#98a8bd}

.ot-modal{position:fixed;inset:0;background:rgba(2,6,13,.84);backdrop-filter:blur(7px);display:grid;place-items:center;z-index:1000;padding:18px}
.ot-modal-box{width:440px;max-width:100%;background:#0e1829;border:1px solid #2b3d5b;border-radius:17px;padding:22px;box-shadow:0 30px 80px rgba(0,0,0,.5)}
.ot-modal-title{font-size:16px;font-weight:950;margin-bottom:17px}.ot-label{font-size:10px;color:#7788a1;font-weight:800;margin-bottom:6px}
.ot-input,.ot-select{width:100%;height:42px;background:#101c30;border:1px solid #2a3d5c;border-radius:9px;color:#ecf2fb;padding:0 11px;outline:none;margin-bottom:11px;font-size:11px}
.ot-input:focus,.ot-select:focus{border-color:#8b5cf6}
.ot-modal-actions{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:5px}
.ot-confirm,.ot-cancel{height:40px;border-radius:9px;font-size:11px;font-weight:900;cursor:pointer}.ot-confirm{border:0;background:linear-gradient(135deg,#7c4dff,#925eff);color:white}.ot-cancel{background:transparent;border:1px solid #2b3d5b;color:#8191a9}

@media(max-width:1100px){.ot-kpis{grid-template-columns:1fr 1fr}.ot-layout{grid-template-columns:1fr}.ot-table-head,.ot-row{grid-template-columns:38px minmax(180px,1.4fr) 1fr 1fr 86px}.ot-branch{display:none}}
@media(max-width:760px){.ot-head{align-items:flex-start;flex-direction:column}.ot-head-actions{width:100%}.ot-btn{flex:1}.ot-kpis{grid-template-columns:1fr}.ot-tools{flex-wrap:wrap}.ot-search{width:100%;flex-basis:100%}.ot-table-wrap{overflow:auto}.ot-table-head,.ot-row{min-width:760px}.ot-tx-wrap{overflow:auto}.ot-tx-head,.ot-tx-row{min-width:760px}}
</style>

@php
    $treasuries = $this->getTreasuries();
    $selected = $this->getSelectedTreasury();
    $transactions = $this->getTransactions();
    $today = $this->getTodaySummary();
    $todayStats = $this->getTodayStatsByTreasury();

    $selectedInToday = $todayStats[$selected?->id]['in'] ?? 0;
    $selectedOutToday = $todayStats[$selected?->id]['out'] ?? 0;

    $selectedInAll = $transactions
        ->filter(fn($tx) => in_array($tx->type, ['in','transfer_in'], true))
        ->sum('amount');

    $selectedOutAll = $transactions
        ->reject(fn($tx) => in_array($tx->type, ['in','transfer_in'], true))
        ->sum('amount');
@endphp

<div class="treasury-v2">

    @if($showCreateTreasury)
    <div class="ot-modal">
        <div class="ot-modal-box">
            <div class="ot-modal-title">إضافة خزينة جديدة</div>

            <div class="ot-label">اسم الخزينة</div>
            <input type="text" wire:model="newTreasuryName" class="ot-input" placeholder="مثال: الخزينة الرئيسية">

            <div class="ot-label">نوع الخزينة</div>
            <select wire:model="newTreasuryType" class="ot-select">
                <option value="cash">نقدية</option>
                <option value="main">رئيسية</option>
                <option value="branch">فرعية</option>
            </select>

            <div class="ot-label">الرصيد الافتتاحي</div>
            <input type="number" wire:model="newTreasuryBalance" class="ot-input" placeholder="0.00" min="0" step="0.01">

            <div class="ot-modal-actions">
                <button wire:click="createTreasury" class="ot-confirm">حفظ الخزينة</button>
                <button wire:click="$set('showCreateTreasury',false)" class="ot-cancel">إلغاء</button>
            </div>
        </div>
    </div>
    @endif

    @if($showTransfer)
    <div class="ot-modal">
        <div class="ot-modal-box">
            <div class="ot-modal-title">تحويل بين الخزائن</div>

            <div class="ot-label">من خزينة</div>
            <select wire:model="fromTreasuryId" class="ot-select">
                <option value="">اختر خزينة...</option>
                @foreach(\App\Models\Treasury::where('is_active',true)->orderBy('name')->get() as $t)
                <option value="{{ $t->id }}">{{ $t->name }} ({{ number_format($t->current_balance,2) }} ر.س)</option>
                @endforeach
            </select>

            <div class="ot-label">إلى خزينة</div>
            <select wire:model="toTreasuryId" class="ot-select">
                <option value="">اختر خزينة...</option>
                @foreach(\App\Models\Treasury::where('is_active',true)->orderBy('name')->get() as $t)
                    @if($t->id != $fromTreasuryId)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endif
                @endforeach
            </select>

            <div class="ot-label">المبلغ</div>
            <input type="number" wire:model="transferAmount" class="ot-input" placeholder="0.00" min="0.01" step="0.01">

            <div class="ot-label">البيان</div>
            <input type="text" wire:model="transferDesc" class="ot-input" placeholder="سبب التحويل أو المرجع">

            <div class="ot-modal-actions">
                <button wire:click="doTransfer" class="ot-confirm">تأكيد التحويل</button>
                <button wire:click="$set('showTransfer',false)" class="ot-cancel">إلغاء</button>
            </div>
        </div>
    </div>
    @endif

    <div class="ot-head">
        <div>
            <div class="ot-kicker">TREASURY CONTROL</div>
            <h1 class="ot-title">إدارة الخزائن</h1>
            <div class="ot-sub">متابعة الأرصدة وحركة النقد والتحويلات بين الخزائن من شاشة تشغيلية واحدة</div>
        </div>
        <div class="ot-head-actions">
            <button wire:click="openTransfer()" class="ot-btn">تحويل بين الخزائن</button>
            <button wire:click="openCreateTreasury" class="ot-btn primary">إضافة خزينة جديدة</button>
        </div>
    </div>

    <div class="ot-kpis">
        <div class="ot-kpi">
            <div class="ot-kpi-code">BAL</div>
            <div class="ot-kpi-label">إجمالي أرصدة الخزائن</div>
            <div class="ot-kpi-value c-green">{{ number_format($this->getTotalBalance(),2) }} ر.س</div>
            <div class="ot-kpi-note">إجمالي الرصيد الحالي لكل الخزائن النشطة</div>
        </div>
        <div class="ot-kpi">
            <div class="ot-kpi-code">IN</div>
            <div class="ot-kpi-label">وارد اليوم</div>
            <div class="ot-kpi-value c-green">+{{ number_format($today['incoming'],2) }} ر.س</div>
            <div class="ot-kpi-note">{{ $today['count'] }} حركة مسجلة اليوم</div>
        </div>
        <div class="ot-kpi">
            <div class="ot-kpi-code">OUT</div>
            <div class="ot-kpi-label">صادر اليوم</div>
            <div class="ot-kpi-value c-red">{{ number_format($today['outgoing'],2) }} ر.س</div>
            <div class="ot-kpi-note">إجمالي الحركات الخارجة من الخزائن</div>
        </div>
        <div class="ot-kpi">
            <div class="ot-kpi-code">NET</div>
            <div class="ot-kpi-label">صافي حركة اليوم</div>
            <div class="ot-kpi-value {{ $today['net'] >= 0 ? 'c-purple' : 'c-red' }}">
                {{ $today['net'] >= 0 ? '+' : '' }}{{ number_format($today['net'],2) }} ر.س
            </div>
            <div class="ot-kpi-note">الوارد ناقص الصادر خلال اليوم</div>
        </div>
    </div>

    <div class="ot-tools">
        <input wire:model.live.debounce.300ms="treasurySearch" class="ot-search" placeholder="ابحث باسم الخزينة أو الفرع...">
        <select wire:model.live="treasuryFilter" class="ot-filter">
            <option value="all">كل الخزائن</option>
            <option value="today">عليها حركة اليوم</option>
            <option value="no_today">بدون حركة اليوم</option>
            <option value="zero">رصيد صفر أو أقل</option>
        </select>
        <span class="ot-badge">{{ $treasuries->count() }} خزينة</span>
    </div>

    <div class="ot-layout">
        <div class="ot-panel">
            <div class="ot-panel-head">
                <div>
                    <div class="ot-panel-title">قائمة الخزائن</div>
                    <div class="ot-panel-sub">اختر خزينة لمراجعة الرصيد وآخر الحركات</div>
                </div>
                <span class="ot-badge ok">تشغيل مباشر</span>
            </div>

            <div class="ot-table-wrap">
                <div class="ot-table-head">
                    <div>#</div>
                    <div>الخزينة</div>
                    <div class="ot-branch">الفرع</div>
                    <div>الرصيد الحالي</div>
                    <div>حركة اليوم</div>
                    <div>إجراء</div>
                </div>

                @forelse($treasuries as $treasury)
                    @php
                        $stats = $todayStats[$treasury->id] ?? ['in'=>0,'out'=>0,'count'=>0];
                    @endphp
                    <div class="ot-row {{ $selectedTreasuryId === $treasury->id ? 'active' : '' }}" wire:click="selectTreasury({{ $treasury->id }})">
                        <div class="ot-index">{{ $loop->iteration }}</div>

                        <div class="ot-name">
                            <div class="ot-mark">TR</div>
                            <div style="min-width:0">
                                <div class="ot-name-main">{{ $treasury->name }}</div>
                                <div class="ot-name-sub">{{ $treasury->type_label }}</div>
                            </div>
                        </div>

                        <div class="ot-branch">
                            <div class="ot-cell-main">{{ $treasury->branch?->name ?? 'الرئيسية' }}</div>
                            <div class="ot-cell-sub">الفرع المرتبط</div>
                        </div>

                        <div class="ot-money">{{ number_format($treasury->current_balance,2) }} ر.س</div>

                        <div>
                            <div class="ot-dayflow">
                                <span class="ot-in">+{{ number_format($stats['in'],0) }}</span>
                                <span class="ot-out">-{{ number_format($stats['out'],0) }}</span>
                            </div>
                            <div class="ot-cell-sub">{{ $stats['count'] }} حركة اليوم</div>
                        </div>

                        <div class="ot-row-actions">
                            <button type="button" wire:click.stop="openTransfer({{ $treasury->id }})" class="ot-mini transfer">تحويل</button>
                        </div>
                    </div>
                @empty
                    <div class="ot-empty">لا توجد خزائن مطابقة للبحث أو الفلتر الحالي</div>
                @endforelse
            </div>
        </div>

        <div class="ot-panel">
            @if($selected)
            <div class="ot-detail">
                <div class="ot-detail-top">
                    <div>
                        <div class="ot-detail-name">{{ $selected->name }}</div>
                        <div class="ot-detail-meta">{{ $selected->type_label }} · {{ $selected->branch?->name ?? 'الفرع الرئيسي' }}</div>
                    </div>
                    <span class="ot-badge ok">نشطة</span>
                </div>

                <div class="ot-detail-balance">{{ number_format($selected->current_balance,2) }} ر.س</div>

                <div class="ot-detail-stats">
                    <div class="ot-stat">
                        <div class="ot-stat-label">وارد اليوم</div>
                        <div class="ot-stat-value c-green">+{{ number_format($selectedInToday,2) }} ر.س</div>
                    </div>
                    <div class="ot-stat">
                        <div class="ot-stat-label">صادر اليوم</div>
                        <div class="ot-stat-value c-red">{{ number_format($selectedOutToday,2) }} ر.س</div>
                    </div>
                    <div class="ot-stat">
                        <div class="ot-stat-label">إجمالي الوارد الظاهر</div>
                        <div class="ot-stat-value c-green">{{ number_format($selectedInAll,2) }} ر.س</div>
                    </div>
                    <div class="ot-stat">
                        <div class="ot-stat-label">إجمالي الصادر الظاهر</div>
                        <div class="ot-stat-value c-red">{{ number_format($selectedOutAll,2) }} ر.س</div>
                    </div>
                </div>

                <button wire:click="openTransfer({{ $selected->id }})" class="ot-transfer-btn">تحويل من هذه الخزينة</button>

                <div class="ot-last">
                    <span>آخر حركة</span>
                    <strong>{{ $transactions->first()?->created_at?->format('d/m/Y H:i') ?? 'لا توجد حركة' }}</strong>
                </div>
                <div class="ot-last">
                    <span>عدد الحركات المعروضة</span>
                    <strong>{{ $transactions->count() }}</strong>
                </div>
            </div>
            @else
            <div class="ot-empty">اختر خزينة لعرض التفاصيل</div>
            @endif
        </div>
    </div>

    @if($selected)
    <div class="ot-panel ot-tx">
        <div class="ot-panel-head">
            <div>
                <div class="ot-panel-title">آخر حركات {{ $selected->name }}</div>
                <div class="ot-panel-sub">آخر 50 حركة مسجلة على الخزينة المحددة</div>
            </div>
            <span class="ot-badge">{{ $transactions->count() }} حركة</span>
        </div>

        <div class="ot-tx-wrap">
            <div class="ot-tx-head">
                <div>النوع</div>
                <div>البيان والمرجع</div>
                <div>القيمة</div>
                <div>الرصيد بعد الحركة</div>
            </div>

            @forelse($transactions as $tx)
                @php $isIn = in_array($tx->type, ['in','transfer_in'], true); @endphp
                <div class="ot-tx-row">
                    <div>
                        <span class="ot-type {{ $isIn ? 'in' : 'out' }}">{{ $isIn ? 'وارد' : 'صادر' }}</span>
                    </div>

                    <div>
                        <div class="ot-tx-desc">{{ $tx->description ?: 'حركة خزينة' }}</div>
                        <div class="ot-tx-sub">
                            {{ $tx->createdBy?->name ?? 'System' }}
                            · {{ $tx->created_at->format('d/m/Y H:i') }}
                            @if($tx->reference) · {{ $tx->reference }} @endif
                            @if($tx->fromTreasury && $tx->toTreasury)
                                · {{ $tx->fromTreasury->name }} → {{ $tx->toTreasury->name }}
                            @endif
                        </div>
                    </div>

                    <div class="ot-tx-amount {{ $isIn ? 'c-green' : 'c-red' }}">
                        {{ $isIn ? '+' : '-' }}{{ number_format($tx->amount,2) }} ر.س
                    </div>

                    <div class="ot-tx-balance">{{ number_format($tx->balance_after,2) }} ر.س</div>
                </div>
            @empty
                <div class="ot-empty">لا توجد حركات على هذه الخزينة حتى الآن</div>
            @endforelse
        </div>
    </div>
    @endif
</div>
</x-filament-panels::page>