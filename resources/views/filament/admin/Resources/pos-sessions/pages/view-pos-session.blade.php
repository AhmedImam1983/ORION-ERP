<x-filament-panels::page>
    @php
        $s = $this->getSessionSummary();
        $transactions = $this->getSessionTransactions();
        $isOpen = $this->record->status === 'open';
    @endphp

    <style>
        .opv{direction:rtl;--panel:#0d1728;--panel2:#111c30;--border:#263853;--muted:#8192aa;--purple:#8b5cf6;--green:#34d399;--amber:#fbbf24;--red:#f87171;--blue:#60a5fa}.opv *{box-sizing:border-box}.opv-hero{display:flex;justify-content:space-between;gap:16px;align-items:center;padding:16px 18px;border:1px solid #263853;border-radius:16px;background:linear-gradient(145deg,#111c30,#0a1423);margin-bottom:12px}.opv-title{font-size:20px;font-weight:950;color:#fff}.opv-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:7px}.opv-pill{padding:5px 9px;border:1px solid #2a3b56;border-radius:999px;color:#94a5bb;font-size:9px;font-weight:850}.opv-state{padding:7px 11px;border-radius:999px;font-size:10px;font-weight:950;border:1px solid}.opv-state.open{color:#6ee7b7;border-color:rgba(52,211,153,.22);background:rgba(52,211,153,.07)}.opv-state.closed{color:#aebbd0;border-color:#30415c;background:#111b2d}
        .opv-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:10px}.opv-kpi{padding:14px 15px;border:1px solid #243650;border-radius:14px;background:#0d1728}.opv-kpi-l{font-size:9px;color:#7789a1;font-weight:900}.opv-kpi-v{margin-top:9px;font-size:19px;font-weight:950;color:var(--accent);font-variant-numeric:tabular-nums}.opv-kpi-n{margin-top:4px;font-size:8px;color:#5f7189}
        .opv-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,.55fr);gap:10px}.opv-card{overflow:hidden;border:1px solid #243650;border-radius:15px;background:linear-gradient(145deg,#0d1728,#0a1423)}.opv-card-h{padding:12px 14px;border-bottom:1px solid #1d2d45;background:#0f192b;color:#f3f6fb;font-size:13px;font-weight:950}.opv-table-wrap{overflow:auto}.opv-table{width:100%;border-collapse:collapse;min-width:760px}.opv-table th{padding:10px 12px;background:#0b1525;border-bottom:1px solid #22324b;color:#71839c;font-size:9px;text-align:right;white-space:nowrap}.opv-table td{padding:11px 12px;border-bottom:1px solid #182840;color:#dce5f0;font-size:10px}.opv-table tr:hover td{background:rgba(139,92,246,.035)}.opv-type{display:inline-flex;padding:4px 7px;border-radius:7px;font-size:8px;font-weight:900}.opv-type.sale{color:#6ee7b7;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.15)}.opv-type.return{color:#fca5a5;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.15)}.opv-method{color:#b8c5d6;font-weight:850}.opv-empty{text-align:center;padding:55px 20px;color:#62748d;font-size:11px}
        .opv-side{padding:13px}.opv-row{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #1a2940;font-size:10px}.opv-row:last-child{border-bottom:0}.opv-row span:first-child{color:#6f8199}.opv-row span:last-child{color:#edf2f8;font-weight:900;text-align:left}.opv-pay{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:10px}.opv-paybox{padding:10px 8px;border:1px solid #22344d;border-radius:10px;text-align:center;background:#0b1524}.opv-paybox b{display:block;font-size:12px;color:#e7edf6}.opv-paybox small{display:block;margin-top:3px;font-size:8px;color:#65778e}.opv-warn{margin-top:10px;padding:10px;border:1px solid rgba(251,191,36,.16);border-radius:10px;background:rgba(251,191,36,.04);color:#a99975;font-size:8px;line-height:1.7}
        @media(max-width:1050px){.opv-kpis{grid-template-columns:repeat(2,1fr)}.opv-grid{grid-template-columns:1fr}}@media(max-width:650px){.opv-kpis{grid-template-columns:1fr}.opv-hero{align-items:flex-start;flex-direction:column}}
    </style>

    <div class="opv">
        <div class="opv-hero">
            <div>
                <div class="opv-title">{{ $this->record->session_number }}</div>
                <div class="opv-meta">
                    <span class="opv-pill">الكاشير: {{ $this->record->user?->name ?? '-' }}</span>
                    <span class="opv-pill">الفرع: {{ $this->record->branch?->name ?? '-' }}</span>
                    <span class="opv-pill">فتح: {{ $this->record->opened_at ? \Illuminate\Support\Carbon::parse($this->record->opened_at)->format('d/m/Y H:i') : '-' }}</span>
                    @if($this->record->closed_at)<span class="opv-pill">إغلاق: {{ \Illuminate\Support\Carbon::parse($this->record->closed_at)->format('d/m/Y H:i') }}</span>@endif
                </div>
            </div>
            <span class="opv-state {{ $isOpen ? 'open' : 'closed' }}">{{ $isOpen ? 'جلسة مفتوحة' : 'جلسة مغلقة' }}</span>
        </div>

        <div class="opv-kpis">
            <div class="opv-kpi" style="--accent:var(--purple)"><div class="opv-kpi-l">إجمالي المبيعات</div><div class="opv-kpi-v">{{ number_format($s['sales'],2) }} ر.س</div><div class="opv-kpi-n">القيمة المسجلة على الجلسة</div></div>
            <div class="opv-kpi" style="--accent:var(--green)"><div class="opv-kpi-l">صافي المبيعات</div><div class="opv-kpi-v">{{ number_format($s['net_sales'],2) }} ر.س</div><div class="opv-kpi-n">بعد خصم المرتجعات</div></div>
            <div class="opv-kpi" style="--accent:var(--red)"><div class="opv-kpi-l">المرتجعات</div><div class="opv-kpi-v">{{ number_format($s['refunds'],2) }} ر.س</div><div class="opv-kpi-n">قيمة عمليات المرتجع</div></div>
            <div class="opv-kpi" style="--accent:var(--amber)"><div class="opv-kpi-l">عدد العمليات</div><div class="opv-kpi-v">{{ number_format($s['transactions']) }}</div><div class="opv-kpi-n">بيع ومرتجع داخل الجلسة</div></div>
        </div>

        <div class="opv-grid">
            <div class="opv-card">
                <div class="opv-card-h">عمليات الجلسة</div>
                <div class="opv-table-wrap">
                    @if(empty($transactions))
                        <div class="opv-empty">لا توجد عمليات مسجلة داخل هذه الجلسة</div>
                    @else
                        <table class="opv-table">
                            <thead><tr><th>الوقت</th><th>العملية</th><th>الفاتورة</th><th>النوع</th><th>الدفع</th><th>القيمة</th><th>المستلم</th><th>الباقي</th></tr></thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr>
                                        <td>{{ $tx['created_at'] }}</td>
                                        <td style="color:#a78bfa;font-weight:900">{{ $tx['number'] }}</td>
                                        <td>{{ $tx['invoice'] }}</td>
                                        <td><span class="opv-type {{ $tx['type'] === 'return' ? 'return' : 'sale' }}">{{ $tx['type'] === 'return' ? 'مرتجع' : 'بيع' }}</span></td>
                                        <td class="opv-method">{{ match($tx['payment_method']) { 'cash' => 'نقدي', 'card' => 'بطاقة', 'wallet' => 'محفظة', default => $tx['payment_method'] } }}</td>
                                        <td>{{ number_format($tx['amount'],2) }} ر.س</td>
                                        <td>{{ number_format($tx['cash_amount'],2) }} ر.س</td>
                                        <td style="color:#6ee7b7">{{ number_format($tx['change_amount'],2) }} ر.س</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="opv-card">
                <div class="opv-card-h">ملخص الجلسة</div>
                <div class="opv-side">
                    <div class="opv-row"><span>رصيد الافتتاح</span><span>{{ number_format($s['opening_cash'],2) }} ر.س</span></div>
                    <div class="opv-row"><span>النقد المتوقع</span><span>{{ number_format($s['expected_cash'],2) }} ر.س</span></div>
                    <div class="opv-row"><span>رصيد الإغلاق المسجل</span><span>{{ $isOpen ? 'لم تغلق بعد' : number_format($s['closing_cash'],2).' ر.س' }}</span></div>
                    <div class="opv-row"><span>اسم نقطة البيع</span><span>{{ $this->record->register_name ?? '-' }}</span></div>
                    <div class="opv-pay">
                        <div class="opv-paybox"><b>{{ number_format($s['cash'],2) }}</b><small>نقدي</small></div>
                        <div class="opv-paybox"><b>{{ number_format($s['card'],2) }}</b><small>بطاقة</small></div>
                        <div class="opv-paybox"><b>{{ number_format($s['wallet'],2) }}</b><small>محفظة</small></div>
                    </div>
                    @if(($s['card'] == 0 && $s['wallet'] == 0) && collect($transactions)->contains(fn($tx) => in_array($tx['payment_method'], ['card','wallet'])))
                        <div class="opv-warn">يوجد عمليات بطاقة أو محفظة، لكن إجماليات الجلسة لا تحتويها. استخدم Patch توزيع طرق الدفع المرفق مع الحزمة لتسجيل الجلسات الجديدة بشكل صحيح.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
