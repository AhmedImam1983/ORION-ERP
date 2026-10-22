<x-filament-panels::page>
@php
    $user = auth()->user();
    $isCashier = $user->hasRole('cashier');
    $isAdmin = $user->hasRole('super_admin') || $user->hasRole('admin');
    $isAccountant = $user->hasRole('accountant');
    $isStorekeeper = $user->hasRole('storekeeper');
@endphp

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat({{ $isCashier ? 2 : 4 }},1fr);gap:12px;margin-bottom:16px">

    {{-- مبيعات اليوم — الكل يشوفها --}}
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px">
        <div style="font-size:12px;opacity:.7;margin-bottom:6px">مبيعات اليوم</div>
        <div style="font-size:22px;font-weight:800">{{ number_format($totalSalesToday, 2) }} ر.س</div>
        <div style="font-size:11px;color:#4ade80;margin-top:6px">↑ إجمالي مبيعات اليوم</div>
    </div>

    {{-- المحصّل اليوم — الكل يشوفها --}}
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px">
        <div style="font-size:12px;opacity:.7;margin-bottom:6px">المحصّل اليوم</div>
        <div style="font-size:22px;font-weight:800">{{ number_format($collectedToday, 2) }} ر.س</div>
        <div style="font-size:11px;color:#60a5fa;margin-top:6px">المبالغ المحصلة</div>
    </div>

    {{-- الأصناف — مخفية عن الكاشير --}}
    @if(!$isCashier)
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px">
        <div style="font-size:12px;opacity:.7;margin-bottom:6px">إجمالي الأصناف</div>
        <div style="font-size:22px;font-weight:800">{{ $totalProducts }}</div>
        <div style="font-size:11px;color:#fbbf24;margin-top:6px">صنف في المخزون</div>
    </div>

    {{-- الموظفين — للأدمن فقط --}}
    @if($isAdmin)
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px">
        <div style="font-size:12px;opacity:.7;margin-bottom:6px">الموظفين النشطين</div>
        <div style="font-size:22px;font-weight:800">{{ $totalEmployees }}</div>
        <div style="font-size:11px;color:#c084fc;margin-top:6px">موظف نشط</div>
    </div>
    @endif
    @endif

</div>

{{-- إحصائيات سريعة — مخفية عن الكاشير --}}
@if(!$isCashier)
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px;text-align:center">
        <div style="font-size:28px;font-weight:900;color:#a78bfa">{{ $totalCustomers }}</div>
        <div style="font-size:12px;opacity:.7;margin-top:4px">إجمالي العملاء</div>
    </div>
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px;text-align:center">
        <div style="font-size:28px;font-weight:900;color:#4ade80">{{ $totalInvoices }}</div>
        <div style="font-size:12px;opacity:.7;margin-top:4px">إجمالي الفواتير</div>
    </div>
    @if($isAdmin || $isAccountant)
    <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px;text-align:center">
        <div style="font-size:28px;font-weight:900;color:#fb923c">{{ number_format($totalDebts, 0) }} ر.س</div>
        <div style="font-size:12px;opacity:.7;margin-top:4px">إجمالي المديونيات</div>
    </div>
    @endif
</div>
@endif

{{-- كارد خاص بالكاشير --}}
@if($isCashier)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
    <a href="/admin/pos" style="text-decoration:none">
        <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:24px;text-align:center;cursor:pointer;border:1px solid rgba(124,77,255,.3);transition:.2s">
            <div style="font-size:36px;margin-bottom:8px">🖥️</div>
            <div style="font-size:16px;font-weight:800;color:#a78bfa">فتح الكاشير</div>
            <div style="font-size:11px;opacity:.6;margin-top:4px">نقطة البيع</div>
        </div>
    </a>
    <a href="/admin/invoices/create" style="text-decoration:none">
        <div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:24px;text-align:center;cursor:pointer;border:1px solid rgba(74,222,128,.3);transition:.2s">
            <div style="font-size:36px;margin-bottom:8px">🧾</div>
            <div style="font-size:16px;font-weight:800;color:#4ade80">فاتورة جديدة</div>
            <div style="font-size:11px;opacity:.6;margin-top:4px">إنشاء فاتورة بيع</div>
        </div>
    </a>
</div>
@endif

{{-- آخر الفواتير --}}
<div class="fi-wi-stats-overview-stat" style="border-radius:12px;padding:16px">
    <div style="font-size:14px;font-weight:700;margin-bottom:14px">
        {{ $isCashier ? 'فواتيري اليوم' : 'آخر الفواتير · Recent Invoices' }}
    </div>
    @if($recentInvoices->isEmpty())
        <div style="text-align:center;opacity:.5;padding:32px 0">
            لا توجد فواتير بعد — ابدأ بإنشاء فاتورة جديدة
        </div>
    @else
        <table style="width:100%;font-size:13px;border-collapse:collapse">
            <thead>
                <tr style="opacity:.6;border-bottom:1px solid rgba(255,255,255,.1)">
                    <th style="text-align:right;padding:8px 4px;font-weight:600">رقم الفاتورة</th>
                    <th style="text-align:right;padding:8px 4px;font-weight:600">العميل</th>
                    <th style="text-align:right;padding:8px 4px;font-weight:600">الإجمالي</th>
                    @if(!$isCashier)
                    <th style="text-align:right;padding:8px 4px;font-weight:600">المتبقي</th>
                    @endif
                    <th style="text-align:right;padding:8px 4px;font-weight:600">الحالة</th>
                    <th style="text-align:right;padding:8px 4px;font-weight:600">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentInvoices as $invoice)
                @php
                    $statusMap = [
                        'paid'      => ['مدفوعة',  'color:#4ade80;background:rgba(74,222,128,.15)'],
                        'confirmed' => ['مؤكدة',   'color:#60a5fa;background:rgba(96,165,250,.15)'],
                        'cancelled' => ['ملغية',   'color:#f87171;background:rgba(248,113,113,.15)'],
                        'partial'   => ['جزئي',    'color:#fb923c;background:rgba(251,146,60,.15)'],
                        'draft'     => ['مسودة',   'color:#fbbf24;background:rgba(251,191,36,.15)'],
                    ];
                    $s = $statusMap[$invoice->status] ?? [$invoice->status, ''];
                @endphp
                <tr style="border-bottom:1px solid rgba(255,255,255,.05)">
                    <td style="padding:8px 4px;color:#a78bfa;font-family:monospace">{{ $invoice->invoice_number }}</td>
                    <td style="padding:8px 4px">{{ $invoice->customer?->name ?? 'عميل نقدي' }}</td>
                    <td style="padding:8px 4px;font-weight:700">{{ number_format($invoice->total, 2) }} ر.س</td>
                    @if(!$isCashier)
                    <td style="padding:8px 4px;color:#fb923c">{{ number_format($invoice->remaining_amount, 2) }} ر.س</td>
                    @endif
                    <td style="padding:8px 4px">
                        <span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;{{ $s[1] }}">{{ $s[0] }}</span>
                    </td>
                    <td style="padding:8px 4px;opacity:.7">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</x-filament-panels::page>