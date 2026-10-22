<x-filament-panels::page>
@php
    $visibleSalaries = $loaded ? $this->filteredSalaries() : [];
    $totalAdditions = $loaded ? $this->getTotalAdditions() : 0;
    $averageNet = $loaded ? $this->getAverageNet() : 0;
    $paidProgress = $loaded ? $this->getPaidProgress() : 0;
    $pendingCount = $draftCount + $approvedCount;
    $paymentLabels = ['cash' => 'نقدي', 'bank' => 'تحويل بنكي', 'cheque' => 'شيك'];
@endphp

<style>
.fi-header{display:none!important}
.salary-v2,.salary-v2 *{box-sizing:border-box;font-family:'Cairo',sans-serif}
.salary-v2{
    --sv-bg:#08111f;--sv-panel:#0d1728;--sv-panel2:#101b2f;--sv-border:#20314c;
    --sv-border-soft:#18283f;--sv-text:#eef3fb;--sv-muted:#7688a3;--sv-purple:#8b5cf6;
    --sv-purple2:#a78bfa;--sv-green:#34d399;--sv-red:#fb7185;--sv-amber:#fbbf24;--sv-blue:#60a5fa;
    direction:rtl;color:var(--sv-text);width:100%;padding-bottom:28px
}
.sv-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:15px}
.sv-title-wrap{display:flex;align-items:center;gap:12px}.sv-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;color:#c4b5fd;background:linear-gradient(145deg,rgba(139,92,246,.28),rgba(76,29,149,.12));border:1px solid rgba(139,92,246,.42);box-shadow:0 10px 30px rgba(76,29,149,.16)}
.sv-icon svg{width:23px;height:23px}.sv-kicker{font-size:8px;color:#8f7cf0;font-weight:950;letter-spacing:1.7px}.sv-title{font-size:27px;font-weight:950;margin:2px 0 0}.sv-sub{font-size:9px;color:var(--sv-muted);margin-top:4px}.sv-period-chip{display:flex;align-items:center;gap:7px;border:1px solid #273956;background:#0d1829;border-radius:9px;padding:7px 10px;color:#8ea0bb;font-size:8px;font-weight:900}.sv-dot{width:7px;height:7px;border-radius:50%;background:var(--sv-green);box-shadow:0 0 0 4px rgba(52,211,153,.09)}
.sv-panel{background:linear-gradient(145deg,#0d1728,#0a1422);border:1px solid var(--sv-border);border-radius:14px;overflow:hidden}
.sv-controls{display:flex;align-items:flex-end;gap:10px;padding:13px;flex-wrap:wrap}.sv-field{min-width:145px}.sv-field label{display:block;font-size:8px;color:#71839e;margin-bottom:5px;font-weight:900}.sv-select,.sv-search{width:100%;height:37px;background:#111d30;border:1px solid #263958;border-radius:9px;color:#dfe7f4;padding:0 11px;font-size:9px;outline:none}.sv-search{min-width:240px}.sv-select:focus,.sv-search:focus{border-color:#7758e8;box-shadow:0 0 0 3px rgba(139,92,246,.08)}
.sv-actions{display:flex;align-items:center;gap:7px;margin-inline-start:auto;flex-wrap:wrap}.sv-btn{height:37px;border-radius:9px;padding:0 12px;display:inline-flex;align-items:center;justify-content:center;gap:7px;font-size:8px;font-weight:950;cursor:pointer;transition:.18s;text-decoration:none}.sv-btn svg{width:14px;height:14px}.sv-btn.primary{border:0;background:linear-gradient(135deg,#8154ed,#673bd7);color:white;box-shadow:0 7px 18px rgba(98,54,211,.18)}.sv-btn.primary:hover{transform:translateY(-1px)}.sv-btn.approve{border:1px solid rgba(96,165,250,.25);background:rgba(96,165,250,.07);color:#7eb6ff}.sv-btn.pay{border:1px solid rgba(52,211,153,.25);background:rgba(52,211,153,.07);color:#58d9b3}.sv-btn.pdf{border:1px solid rgba(251,191,36,.22);background:rgba(251,191,36,.06);color:#efc85c}.sv-btn.ghost{border:1px solid #263958;background:#101b2d;color:#8798b1}
.sv-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px;margin:11px 0}.sv-kpi{position:relative;min-height:105px;padding:13px 14px;background:linear-gradient(145deg,#0e192b,#0a1423);border:1px solid #22344f;border-radius:13px;overflow:hidden}.sv-kpi:after{content:"";position:absolute;width:80px;height:80px;border-radius:50%;left:-25px;bottom:-35px;background:radial-gradient(circle,rgba(139,92,246,.13),transparent 70%)}.sv-kpi-code{font-size:7px;color:#5d708d;font-weight:950;letter-spacing:1px}.sv-kpi-label{font-size:8px;color:#8394ac;font-weight:800;margin-top:6px}.sv-kpi-value{font-size:20px;font-weight:950;margin-top:5px;direction:ltr;text-align:right;white-space:nowrap}.sv-kpi-foot{font-size:7px;color:#5e708a;margin-top:5px}.c-blue{color:var(--sv-blue)}.c-green{color:var(--sv-green)}.c-red{color:var(--sv-red)}.c-purple{color:#b6a4ff}.c-amber{color:var(--sv-amber)}
.sv-insights{display:grid;grid-template-columns:1.15fr 1fr 1fr;gap:9px;margin-bottom:11px}.sv-insight{background:#0c1626;border:1px solid #20314a;border-radius:12px;padding:11px 13px}.sv-insight-head{display:flex;align-items:center;justify-content:space-between;gap:8px}.sv-insight-title{font-size:9px;font-weight:950}.sv-insight-sub{font-size:7px;color:#657792;margin-top:2px}.sv-progress{height:7px;background:#111f34;border-radius:20px;overflow:hidden;margin-top:10px}.sv-progress span{display:block;height:100%;background:linear-gradient(90deg,#34d399,#6ee7b7);border-radius:inherit}.sv-progress-labels{display:flex;justify-content:space-between;margin-top:6px;font-size:7px;color:#657792}.sv-mini-value{font-size:18px;font-weight:950;direction:ltr;text-align:right;margin-top:7px}.sv-mini-note{font-size:7px;color:#62748e;margin-top:3px}
.sv-table-panel{margin-top:0}.sv-table-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 13px;border-bottom:1px solid #1b2b43}.sv-table-title{font-size:10px;font-weight:950}.sv-table-sub{font-size:7px;color:#61748e;margin-top:2px}.sv-count{font-size:7px;color:#8c7ee4;border:1px solid rgba(139,92,246,.18);background:rgba(139,92,246,.05);border-radius:7px;padding:5px 8px;font-weight:900}
.sv-filterbar{display:grid;grid-template-columns:minmax(220px,1.4fr) repeat(2,minmax(145px,.7fr)) auto;gap:8px;padding:10px 13px;border-bottom:1px solid #1b2b43;background:#0c1625}.sv-filterbar .sv-search{min-width:0}.sv-reset{height:37px;border:1px solid #253751;background:#101b2c;color:#7e90aa;border-radius:9px;padding:0 11px;font-size:8px;font-weight:900;cursor:pointer}
.sv-table-wrap{overflow:auto}.sv-table{width:100%;border-collapse:collapse;min-width:1100px}.sv-table th{height:36px;padding:0 10px;text-align:right;background:#0b1421;color:#61728c;font-size:7px;font-weight:950;border-bottom:1px solid #1d2d45;white-space:nowrap}.sv-table td{padding:9px 10px;border-bottom:1px solid #16263d;font-size:8px;color:#b8c4d4;vertical-align:middle;white-space:nowrap}.sv-table tbody tr:hover td{background:#0f1b2d}.sv-emp{display:flex;align-items:center;gap:8px;min-width:185px}.sv-avatar{width:31px;height:31px;border-radius:9px;display:grid;place-items:center;background:linear-gradient(145deg,#1e2d47,#101a2d);border:1px solid #2d4263;color:#b8a6ff;font-weight:950;font-size:10px}.sv-emp-name{font-size:9px;color:#e8eef8;font-weight:950}.sv-emp-code{font-size:7px;color:#61738e;margin-top:2px;direction:ltr;text-align:right}.sv-dept{display:inline-flex;padding:4px 7px;border-radius:6px;background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.18);color:#a995f8;font-size:7px;font-weight:900}.sv-amount{direction:ltr;text-align:right;font-weight:900}.sv-net{color:#b9a7ff;font-size:9px}.sv-positive{color:#4addb0}.sv-negative{color:#ff788b}.sv-neutral{color:#d9e2ee}.sv-zero{color:#4d607b}.sv-badge{display:inline-flex;align-items:center;gap:5px;border-radius:6px;padding:4px 7px;font-size:7px;font-weight:950}.sv-badge.paid{color:#55d9b2;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.17)}.sv-badge.approved{color:#78b4ff;background:rgba(96,165,250,.08);border:1px solid rgba(96,165,250,.17)}.sv-badge.draft{color:#f0c653;background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.16)}.sv-badge.payment{color:#91a1b8;background:#111d2f;border:1px solid #233550}.sv-badge .dot{width:5px;height:5px;border-radius:50%;background:currentColor}
.sv-table tfoot td{background:#101a2d;border-top:1px solid #2b3b59;border-bottom:0;color:#cdd7e5;font-weight:950}.sv-total-label{color:#ad9afa!important}.sv-empty{padding:56px 20px;text-align:center}.sv-empty-icon{width:48px;height:48px;border-radius:14px;margin:0 auto 12px;display:grid;place-items:center;background:#101b2d;border:1px solid #253955;color:#60738f}.sv-empty-icon svg{width:22px;height:22px}.sv-empty-title{font-size:11px;font-weight:950;color:#dbe4f0}.sv-empty-sub{font-size:8px;color:#62748d;margin-top:4px}
@media(max-width:1180px){.sv-kpis{grid-template-columns:repeat(3,1fr)}.sv-insights{grid-template-columns:1fr}.sv-filterbar{grid-template-columns:1fr 1fr}.sv-filterbar .sv-search{grid-column:1/-1}}
@media(max-width:760px){.sv-head{align-items:flex-start}.sv-period-chip{display:none}.sv-kpis{grid-template-columns:1fr 1fr}.sv-controls{display:grid;grid-template-columns:1fr 1fr}.sv-actions{grid-column:1/-1;margin:0}.sv-field{min-width:0}.sv-filterbar{grid-template-columns:1fr}.sv-filterbar .sv-search{grid-column:auto}}
</style>

<div class="salary-v2">
    <div class="sv-head">
        <div class="sv-title-wrap">
            <div class="sv-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h6M7 13h3M16 9h2M16 13h2"/></svg>
            </div>
            <div>
                <div class="sv-kicker">PAYROLL CONTROL</div>
                <h1 class="sv-title">تقرير الرواتب الشهري</h1>
                <div class="sv-sub">مراجعة واعتماد وصرف رواتب الموظفين مع ملخص مالي واضح للفترة</div>
            </div>
        </div>
        <div class="sv-period-chip"><span class="sv-dot"></span>{{ $loaded ? $this->getMonthName().' '.$year : 'اختر الفترة لعرض التقرير' }}</div>
    </div>

    <div class="sv-panel">
        <div class="sv-controls">
            <div class="sv-field">
                <label>الشهر</label>
                <select wire:model="month" class="sv-select">
                    <option value="1">يناير</option><option value="2">فبراير</option><option value="3">مارس</option><option value="4">أبريل</option>
                    <option value="5">مايو</option><option value="6">يونيو</option><option value="7">يوليو</option><option value="8">أغسطس</option>
                    <option value="9">سبتمبر</option><option value="10">أكتوبر</option><option value="11">نوفمبر</option><option value="12">ديسمبر</option>
                </select>
            </div>
            <div class="sv-field">
                <label>السنة</label>
                <select wire:model="year" class="sv-select">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="sv-actions">
                <button wire:click="loadReport" class="sv-btn primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/></svg>
                    عرض التقرير
                </button>

                @if($loaded && $totalEmployees > 0)
                    @if($draftCount > 0)
                        <button wire:click="approveAll" onclick="confirm('اعتماد جميع الرواتب المسودة؟') || event.stopImmediatePropagation()" class="sv-btn approve">اعتماد الكل</button>
                    @endif
                    @if($pendingCount > 0)
                        <button wire:click="payAll" onclick="confirm('صرف جميع الرواتب المستحقة؟') || event.stopImmediatePropagation()" class="sv-btn pay">صرف الكل</button>
                    @endif
                    <button wire:click="exportPdf" class="sv-btn pdf">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l3 3v17H6z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg>
                        PDF
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($loaded)
        @if($totalEmployees === 0)
            <div class="sv-panel sv-empty" style="margin-top:11px">
                <div class="sv-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg></div>
                <div class="sv-empty-title">لا توجد رواتب لشهر {{ $this->getMonthName() }} {{ $year }}</div>
                <div class="sv-empty-sub">أنشئ مسيرات الرواتب أولاً من قسم الموارد البشرية ثم أعد تحميل التقرير.</div>
            </div>
        @else
            <div class="sv-kpis">
                <div class="sv-kpi">
                    <div class="sv-kpi-code">HEADCOUNT</div><div class="sv-kpi-label">إجمالي الموظفين</div>
                    <div class="sv-kpi-value c-blue">{{ number_format($totalEmployees) }}</div>
                    <div class="sv-kpi-foot">{{ $paidCount }} مصروف · {{ $approvedCount }} معتمد · {{ $draftCount }} مسودة</div>
                </div>
                <div class="sv-kpi">
                    <div class="sv-kpi-code">BASIC</div><div class="sv-kpi-label">الرواتب الأساسية</div>
                    <div class="sv-kpi-value c-green">{{ number_format($totalBasic,0) }}</div><div class="sv-kpi-foot">ر.س قبل الإضافات والخصومات</div>
                </div>
                <div class="sv-kpi">
                    <div class="sv-kpi-code">ADDITIONS</div><div class="sv-kpi-label">إجمالي الإضافات</div>
                    <div class="sv-kpi-value c-blue">+{{ number_format($totalAdditions,0) }}</div><div class="sv-kpi-foot">بدلات + إضافي + مكافآت</div>
                </div>
                <div class="sv-kpi">
                    <div class="sv-kpi-code">DEDUCTIONS</div><div class="sv-kpi-label">إجمالي الخصومات</div>
                    <div class="sv-kpi-value c-red">-{{ number_format($totalDeductions,0) }}</div><div class="sv-kpi-foot">جميع أنواع الاستقطاعات</div>
                </div>
                <div class="sv-kpi">
                    <div class="sv-kpi-code">NET PAYROLL</div><div class="sv-kpi-label">صافي الرواتب</div>
                    <div class="sv-kpi-value c-purple">{{ number_format($totalNet,0) }}</div><div class="sv-kpi-foot">إجمالي المبلغ المستحق للصرف</div>
                </div>
            </div>

            <div class="sv-insights">
                <div class="sv-insight">
                    <div class="sv-insight-head"><div><div class="sv-insight-title">معدل صرف الرواتب</div><div class="sv-insight-sub">نسبة الرواتب التي تم صرفها من إجمالي المسير</div></div><strong class="c-green" style="font-size:11px">{{ number_format($paidProgress,1) }}%</strong></div>
                    <div class="sv-progress"><span style="width:{{ min(100,$paidProgress) }}%"></span></div>
                    <div class="sv-progress-labels"><span>{{ $paidCount }} مصروف</span><span>{{ $pendingCount }} مستحق</span></div>
                </div>
                <div class="sv-insight">
                    <div class="sv-insight-title">متوسط صافي راتب الموظف</div>
                    <div class="sv-mini-value c-purple">{{ number_format($averageNet,2) }} <small style="font-size:8px">ر.س</small></div>
                    <div class="sv-mini-note">صافي الرواتب ÷ عدد الموظفين</div>
                </div>
                <div class="sv-insight">
                    <div class="sv-insight-title">أثر الإضافات والخصومات</div>
                    <div class="sv-mini-value {{ ($totalAdditions-$totalDeductions)>=0 ? 'c-green':'c-red' }}">{{ ($totalAdditions-$totalDeductions)>=0 ? '+' : '' }}{{ number_format($totalAdditions-$totalDeductions,2) }}</div>
                    <div class="sv-mini-note">صافي أثر الإضافات مقابل الخصومات</div>
                </div>
            </div>

            <div class="sv-panel sv-table-panel">
                <div class="sv-table-head">
                    <div><div class="sv-table-title">كشف رواتب {{ $this->getMonthName() }} {{ $year }}</div><div class="sv-table-sub">تفاصيل الراتب والمستحقات وحالة الصرف لكل موظف</div></div>
                    <div class="sv-count">{{ count($visibleSalaries) }} من {{ $totalEmployees }} موظف</div>
                </div>

                <div class="sv-filterbar">
                    <input wire:model.live.debounce.300ms="search" class="sv-search" type="text" placeholder="ابحث بالاسم أو الكود أو القسم أو الوظيفة...">
                    <select wire:model.live="departmentFilter" class="sv-select">
                        <option value="">كل الأقسام</option>
                        @foreach($this->departments() as $department)
                            <option value="{{ $department }}">{{ $department }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="statusFilter" class="sv-select">
                        <option value="">كل الحالات</option>
                        <option value="paid">مصروف</option><option value="approved">معتمد</option><option value="draft">مسودة</option>
                    </select>
                    <button wire:click="resetTableFilters" class="sv-reset">مسح الفلاتر</button>
                </div>

                @if(count($visibleSalaries) === 0)
                    <div class="sv-empty">
                        <div class="sv-empty-title">لا توجد نتائج مطابقة</div>
                        <div class="sv-empty-sub">غيّر البحث أو الفلاتر لعرض الموظفين.</div>
                    </div>
                @else
                    <div class="sv-table-wrap">
                        <table class="sv-table">
                            <thead><tr>
                                <th>الموظف</th><th>القسم</th><th>الوظيفة</th><th>الأساسي</th><th>البدلات</th><th>الإضافي</th><th>المكافأة</th><th>الخصومات</th><th>الصافي</th><th>طريقة الصرف</th><th>الحالة</th>
                            </tr></thead>
                            <tbody>
                            @foreach($visibleSalaries as $s)
                                @php
                                    $initial = mb_substr(trim($s['name']),0,1);
                                    $statusClass = in_array($s['status'],['paid','approved','draft']) ? $s['status'] : 'draft';
                                    $statusLabel = ['paid'=>'مصروف','approved'=>'معتمد','draft'=>'مسودة'][$s['status']] ?? $s['status'];
                                @endphp
                                <tr>
                                    <td><div class="sv-emp"><div class="sv-avatar">{{ $initial }}</div><div><div class="sv-emp-name">{{ $s['name'] }}</div><div class="sv-emp-code">{{ $s['code'] }}</div></div></div></td>
                                    <td><span class="sv-dept">{{ $s['department'] }}</span></td>
                                    <td>{{ $s['job_title'] }}</td>
                                    <td class="sv-amount sv-neutral">{{ number_format($s['basic_salary'],2) }}</td>
                                    <td class="sv-amount {{ $s['allowances']>0 ? 'sv-positive':'sv-zero' }}">{{ $s['allowances']>0 ? number_format($s['allowances'],2) : '—' }}</td>
                                    <td class="sv-amount {{ $s['overtime']>0 ? 'c-blue':'sv-zero' }}">{{ $s['overtime']>0 ? number_format($s['overtime'],2) : '—' }}</td>
                                    <td class="sv-amount {{ $s['bonus']>0 ? 'c-amber':'sv-zero' }}">{{ $s['bonus']>0 ? number_format($s['bonus'],2) : '—' }}</td>
                                    <td class="sv-amount {{ $s['deductions']>0 ? 'sv-negative':'sv-zero' }}">{{ $s['deductions']>0 ? number_format($s['deductions'],2) : '—' }}</td>
                                    <td class="sv-amount sv-net">{{ number_format($s['net_salary'],2) }} ر.س</td>
                                    <td><span class="sv-badge payment">{{ $paymentLabels[$s['payment_method']] ?? ($s['payment_method'] ?: 'غير محدد') }}</span></td>
                                    <td><span class="sv-badge {{ $statusClass }}"><span class="dot"></span>{{ $statusLabel }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot><tr>
                                <td colspan="3" class="sv-total-label">الإجمالي · {{ $totalEmployees }} موظف</td>
                                <td class="sv-amount">{{ number_format($totalBasic,2) }}</td>
                                <td class="sv-amount sv-positive">{{ number_format($totalAllowances,2) }}</td>
                                <td class="sv-amount c-blue">{{ number_format($totalOvertime,2) }}</td>
                                <td class="sv-amount c-amber">{{ number_format($totalBonus,2) }}</td>
                                <td class="sv-amount sv-negative">{{ number_format($totalDeductions,2) }}</td>
                                <td class="sv-amount sv-net">{{ number_format($totalNet,2) }} ر.س</td>
                                <td colspan="2"></td>
                            </tr></tfoot>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
</x-filament-panels::page>
