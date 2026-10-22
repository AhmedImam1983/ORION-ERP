<x-filament-panels::page>
    @php
        $record = $this->record;
        $total=(float)($record->total??0);
        $paid=(float)($record->paid_amount??0);
        $remaining=(float)($record->remaining_amount??0);
        $paidPct=$total>0?min(100,round(($paid/$total)*100)):0;

        $isReturn=in_array($record->type,['sale_return','purchase_return'],true);
        $isPurchaseReturn=$record->type==='purchase_return';

        $typeLabel=match($record->type){'sale'=>'فاتورة بيع','purchase'=>'فاتورة شراء','sale_return'=>'مرتجع بيع','purchase_return'=>'مرتجع شراء',default=>$record->type};
        $statusLabel=match($record->status){'draft'=>'مسودة','confirmed'=>'مؤكدة','paid'=>'مدفوعة','partial'=>'مدفوعة جزئيًا','cancelled'=>'ملغية',default=>$record->status};
        $statusColor=match($record->status){'paid'=>'#34d399','confirmed'=>'#60a5fa','partial'=>'#fbbf24','cancelled'=>'#f87171',default=>'#94a3b8'};
        $party=$record->customer?->name ?? $record->supplier?->name ?? 'طرف نقدي';

        $paymentLabel=$isReturn
            ? 'تسوية مرتجع'
            : match($record->payment_type){'cash'=>'نقدي','credit'=>'آجل','installment'=>'تقسيط',default=>$record->payment_type};

        $overdue=false;
        if(!$isReturn && $record->due_date&&$remaining>0&&!in_array($record->status,['paid','cancelled'],true)){
            try{$overdue=\Illuminate\Support\Carbon::parse($record->due_date)->startOfDay()->lt(today());}catch(\Throwable){}
        }
    @endphp
    <style>
        .orion-inv-view{direction:rtl;--vv-panel:#0d1728;--vv-panel2:#111c30;--vv-border:#263853;--vv-soft:#1b2a41;--vv-text:#f8fafc;--vv-muted:#8091aa;--vv-purple:#8b5cf6;--vv-green:#34d399;--vv-red:#f87171;--vv-blue:#60a5fa;--vv-amber:#fbbf24}.orion-inv-view *{box-sizing:border-box}.vv-hero{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px;margin-bottom:14px}.vv-card,.vv-main,.vv-info{border:1px solid var(--vv-border);border-radius:16px;background:linear-gradient(145deg,#0d1728,#091321);box-shadow:0 16px 38px rgba(0,0,0,.14)}.vv-main{padding:18px}.vv-number{font-size:22px;font-weight:950;color:#fff}.vv-meta{margin-top:5px;color:#708199;font-size:9px}.vv-badges{display:flex;gap:7px;flex-wrap:wrap;margin-top:13px}.vv-badge{padding:5px 9px;border:1px solid #2b3b55;border-radius:999px;background:#0d1728;color:#aebdd0;font-size:8px;font-weight:900}.vv-card{padding:16px}.vv-l{font-size:8px;color:#71829a;font-weight:900}.vv-v{margin-top:7px;color:var(--accent);font-size:20px;font-weight:950}.vv-n{margin-top:5px;color:#62738b;font-size:8px}.vv-progress{margin-top:14px}.vv-track{height:7px;border-radius:999px;background:#18263d;overflow:hidden}.vv-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#8b5cf6,#34d399)}.vv-progress-note{display:flex;justify-content:space-between;gap:10px;margin-top:6px;color:#71829a;font-size:8px}.vv-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px;align-items:start}.vv-info{overflow:hidden}.vv-info-head{padding:13px 16px;border-bottom:1px solid var(--vv-soft);background:#0f192b;color:#f3f6fb;font-size:14px;font-weight:950}.vv-info-body{padding:15px}.vv-side{display:grid;gap:12px;position:sticky;top:16px}.vv-row{display:flex;justify-content:space-between;gap:10px;min-height:38px;align-items:center;border-bottom:1px solid #17243a;font-size:8px}.vv-row:last-child{border-bottom:0}.vv-row span{color:#71829a}.vv-row strong{color:#dce5f1;text-align:left}.vv-alert{margin-bottom:12px;padding:10px 12px;border:1px solid rgba(248,113,113,.18);border-radius:11px;background:rgba(248,113,113,.045);color:#ff9aa5;font-size:9px;font-weight:850}.orion-inv-view .fi-sc-section{border:1px solid #22334d!important;border-radius:13px!important;background:#0f192b!important;box-shadow:none!important;overflow:hidden;margin-bottom:12px!important}.orion-inv-view .fi-sc-section-header{background:#111c30!important;border-bottom:1px solid #1e2d45!important;padding:12px 14px!important}.orion-inv-view .fi-sc-section-header-heading{color:#f3f6fb!important;font-size:11px!important;font-weight:950!important}.orion-inv-view .fi-sc-section-content{padding:14px!important}@media(max-width:1000px){.vv-hero,.vv-layout{grid-template-columns:1fr}.vv-side{position:static;grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.vv-side{grid-template-columns:1fr}}
    </style>
    <div class="orion-inv-view">
        @if($overdue)
            <div class="vv-alert">الفاتورة متأخرة عن تاريخ الاستحقاق وباقي عليها {{ number_format($remaining,2) }} ر.س.</div>
        @endif

        <div class="vv-hero">
            <div class="vv-main">
                <div class="vv-number">{{ $record->invoice_number }}</div>
                <div class="vv-meta">{{ $typeLabel }} · {{ $party }} · {{ $record->invoice_date ? \Illuminate\Support\Carbon::parse($record->invoice_date)->format('d/m/Y') : '-' }}</div>

                <div class="vv-badges">
                    <span class="vv-badge" style="color:{{ $statusColor }};border-color:color-mix(in srgb,{{ $statusColor }} 25%,#2b3b55)">{{ $statusLabel }}</span>
                    <span class="vv-badge">{{ $paymentLabel }}</span>
                    @if($record->type==='sale')
                        <span class="vv-badge">زاتكا: {{ $record->zatca_sent ? 'تم الإرسال' : 'غير مرسلة' }}</span>
                    @endif
                </div>

                @if($isReturn)
                    <div class="vv-progress">
                        <div class="vv-progress-note">
                            <span>{{ $isPurchaseReturn ? 'تسوية مرتجع الشراء' : 'تسوية مستند المرتجع' }}</span>
                            <span>{{ number_format($total,2) }} ر.س</span>
                        </div>
                        @if($isPurchaseReturn)
                            <div class="vv-n" style="margin-top:8px;color:#f6d988">
                                يخفض ذمة المورد بقيمة المرتجع، ولا ينشئ حركة خزينة أو بنك تلقائيًا.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="vv-progress">
                        <div class="vv-track"><div class="vv-fill" style="width:{{ $paidPct }}%"></div></div>
                        <div class="vv-progress-note">
                            <span>تقدم السداد {{ $paidPct }}%</span>
                            <span>{{ number_format($paid,2) }} / {{ number_format($total,2) }} ر.س</span>
                        </div>
                    </div>
                @endif
            </div>

            @if($isReturn)
                <div class="vv-card" style="--accent:var(--vv-amber)">
                    <div class="vv-l">{{ $isPurchaseReturn ? 'أثر التسوية' : 'قيمة المرتجع' }}</div>
                    <div class="vv-v">{{ number_format($total,2) }} ر.س</div>
                    <div class="vv-n">
                        {{ $isPurchaseReturn ? 'تخفيض ذمة المورد — بدون حركة نقدية تلقائية' : 'مستند مرتجع — لا يُعامل كفاتورة سداد' }}
                    </div>
                </div>
            @else
                <div class="vv-card" style="--accent:{{ $remaining>0?'var(--vv-red)':'var(--vv-green)' }}">
                    <div class="vv-l">الرصيد المتبقي</div>
                    <div class="vv-v">{{ number_format($remaining,2) }} ر.س</div>
                    <div class="vv-n">{{ $remaining>0?'فاتورة ما زالت مفتوحة':'مسددة بالكامل' }}</div>
                </div>
            @endif
        </div>

        <div class="vv-layout">
            <div class="vv-info">
                <div class="vv-info-head">{{ $isReturn ? 'تفاصيل المرتجع' : 'تفاصيل الفاتورة' }}</div>
                <div class="vv-info-body">{{ $this->infolist }}</div>
            </div>

            <aside class="vv-side">
                <div class="vv-card">
                    <div class="vv-l">{{ $isReturn ? 'ملخص المرتجع' : 'الملخص المالي' }}</div>
                    <div style="margin-top:8px">
                        <div class="vv-row">
                            <span>{{ $isReturn ? 'قيمة المرتجع' : 'الإجمالي' }}</span>
                            <strong>{{ number_format($total,2) }} ر.س</strong>
                        </div>

                        @if(!$isReturn)
                            <div class="vv-row"><span>المدفوع</span><strong style="color:#34d399">{{ number_format($paid,2) }} ر.س</strong></div>
                            <div class="vv-row"><span>المتبقي</span><strong style="color:{{ $remaining>0?'#f87171':'#34d399' }}">{{ number_format($remaining,2) }} ر.س</strong></div>
                        @elseif($isPurchaseReturn)
                            <div class="vv-row"><span>التسوية</span><strong style="color:#fbbf24">تخفيض ذمة المورد</strong></div>
                        @endif

                        <div class="vv-row"><span>الضريبة</span><strong>{{ number_format((float)$record->tax_amount,2) }} ر.س</strong></div>
                    </div>
                </div>

                <div class="vv-card">
                    <div class="vv-l">المتابعة</div>
                    <div style="margin-top:8px">
                        <div class="vv-row"><span>الطرف</span><strong>{{ $party }}</strong></div>

                        @if(!$isReturn)
                            <div class="vv-row">
                                <span>الاستحقاق</span>
                                <strong style="color:{{ $overdue?'#f87171':'#dce5f1' }}">
                                    {{ $record->due_date ? \Illuminate\Support\Carbon::parse($record->due_date)->format('d/m/Y') : '-' }}
                                </strong>
                            </div>
                        @else
                            <div class="vv-row"><span>نوع المعالجة</span><strong>{{ $paymentLabel }}</strong></div>
                        @endif

                        <div class="vv-row"><span>الحالة</span><strong style="color:{{ $statusColor }}">{{ $statusLabel }}</strong></div>
                        <div class="vv-row"><span>زاتكا</span><strong>{{ $record->type==='sale' ? ($record->zatca_sent?'تم الإرسال':'غير مرسلة') : 'غير مطلوبة' }}</strong></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
