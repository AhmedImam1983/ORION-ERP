<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1a1a2e; direction: rtl; }
.page { padding: 30px; }
.header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 3px solid #5b4fff; padding-bottom: 16px; }
.logo-section { }
.logo-name { font-size: 24px; font-weight: bold; color: #5b4fff; }
.logo-sub { font-size: 11px; color: #666; margin-top: 3px; }
.invoice-info { text-align: left; }
.invoice-title { font-size: 20px; font-weight: bold; color: #1a1a2e; }
.invoice-number { font-size: 14px; color: #5b4fff; font-weight: bold; margin-top: 4px; }
.invoice-date { font-size: 12px; color: #666; margin-top: 3px; }
.status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 6px; }
.status-paid { background: #d1fae5; color: #065f46; }
.status-draft { background: #fef3c7; color: #92400e; }
.status-confirmed { background: #dbeafe; color: #1e40af; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.parties { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px; }
.party-box { flex: 1; background: #f8f9ff; border: 1px solid #e5e7ff; border-radius: 8px; padding: 12px; }
.party-title { font-size: 11px; color: #5b4fff; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
.party-name { font-size: 14px; font-weight: bold; color: #1a1a2e; margin-bottom: 4px; }
.party-detail { font-size: 11px; color: #666; margin-top: 2px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
thead tr { background: #5b4fff; color: white; }
thead th { padding: 10px 8px; text-align: right; font-size: 12px; font-weight: bold; }
tbody tr { border-bottom: 1px solid #f0f0f0; }
tbody tr:nth-child(even) { background: #fafafa; }
tbody td { padding: 9px 8px; font-size: 12px; color: #333; }
.totals { margin-right: auto; width: 280px; }
.totals table { border: 1px solid #e5e7ff; border-radius: 8px; overflow: hidden; }
.totals td { padding: 8px 12px; font-size: 13px; }
.totals tr:last-child { background: #5b4fff; color: white; font-weight: bold; font-size: 14px; }
.totals tr:last-child td { padding: 10px 12px; }
.divider { border: none; border-top: 1px solid #e5e7ff; margin: 16px 0; }
.notes { background: #f8f9ff; border-right: 4px solid #5b4fff; padding: 10px 14px; border-radius: 4px; font-size: 12px; color: #555; margin-bottom: 20px; }
.footer { text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 14px; margin-top: 20px; }
.payment-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; }
.payment-cash { background: #d1fae5; color: #065f46; }
.payment-credit { background: #fef3c7; color: #92400e; }
.payment-installment { background: #dbeafe; color: #1e40af; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="logo-section">
            <div class="logo-name">⚡ {{ \App\Models\Setting::get('company_name', 'Orion ERP') }}</div>
            <div class="logo-sub">نظام إدارة متكامل</div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">
                @switch($invoice->type)
                    @case('sale') فاتورة بيع @break
                    @case('purchase') فاتورة شراء @break
                    @case('sale_return') مرتجع بيع @break
                    @case('purchase_return') مرتجع شراء @break
                @endswitch
            </div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div class="invoice-date">{{ $invoice->invoice_date->format('d / m / Y') }}</div>
            @php
                $statusMap = ['paid'=>['مدفوعة','status-paid'],'confirmed'=>['مؤكدة','status-confirmed'],'cancelled'=>['ملغية','status-cancelled'],'draft'=>['مسودة','status-draft'],'partial'=>['جزئي','status-draft']];
                $s = $statusMap[$invoice->status] ?? [$invoice->status,'status-draft'];
            @endphp
            <span class="status-badge {{ $s[1] }}">{{ $s[0] }}</span>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties">
        @if($invoice->customer)
        <div class="party-box">
            <div class="party-title">بيانات العميل</div>
            <div class="party-name">{{ $invoice->customer->name }}</div>
            @if($invoice->customer->phone)<div class="party-detail">📞 {{ $invoice->customer->phone }}</div>@endif
            @if($invoice->customer->address)<div class="party-detail">📍 {{ $invoice->customer->address }}</div>@endif
            @if($invoice->customer->tax_number)<div class="party-detail">🔢 الرقم الضريبي: {{ $invoice->customer->tax_number }}</div>@endif
        </div>
        @endif
        @if($invoice->supplier)
        <div class="party-box">
            <div class="party-title">بيانات المورد</div>
            <div class="party-name">{{ $invoice->supplier->name }}</div>
            @if($invoice->supplier->phone)<div class="party-detail">📞 {{ $invoice->supplier->phone }}</div>@endif
        </div>
        @endif
        <div class="party-box">
            <div class="party-title">تفاصيل الدفع</div>
            @php
                $payMap = ['cash'=>['نقدي','payment-cash'],'credit'=>['آجل','payment-credit'],'installment'=>['تقسيط','payment-installment']];
                $p = $payMap[$invoice->payment_type] ?? [$invoice->payment_type,''];
            @endphp
            <div class="party-name"><span class="payment-badge {{ $p[1] }}">{{ $p[0] }}</span></div>
            @if($invoice->due_date)<div class="party-detail">📅 تاريخ الاستحقاق: {{ $invoice->due_date->format('d/m/Y') }}</div>@endif
            @if($invoice->warehouse)<div class="party-detail">🏪 المخزن: {{ $invoice->warehouse }}</div>@endif
        </div>
    </div>

    {{-- Items --}}
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="35%">الصنف</th>
                <th width="10%">الكود</th>
                <th width="10%">الوحدة</th>
                <th width="10%">الكمية</th>
                <th width="15%">السعر</th>
                <th width="10%">خصم٪</th>
                <th width="15%">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->product_code ?? '-' }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->unit_price, 2) }} ر.س</td>
                <td>{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '-' }}</td>
                <td>{{ number_format($item->total, 2) }} ر.س</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr><td>المجموع الفرعي</td><td>{{ number_format($invoice->subtotal, 2) }} ر.س</td></tr>
            @if($invoice->discount_amount > 0)
            <tr><td>الخصم ({{ $invoice->discount_percent }}%)</td><td>{{ number_format($invoice->discount_amount, 2) }} ر.س</td></tr>
            @endif
            @if($invoice->tax_amount > 0)
            <tr><td>الضريبة ({{ $invoice->tax_percent }}%)</td><td>{{ number_format($invoice->tax_amount, 2) }} ر.س</td></tr>
            @endif
            <tr><td>المدفوع</td><td>{{ number_format($invoice->paid_amount, 2) }} ر.س</td></tr>
            @if($invoice->remaining_amount > 0)
            <tr><td>المتبقي</td><td>{{ number_format($invoice->remaining_amount, 2) }} ر.س</td></tr>
            @endif
            <tr><td>الإجمالي النهائي</td><td>{{ number_format($invoice->total, 2) }} ر.س</td></tr>
        </table>
    </div>

    @if($invoice->notes)
    <hr class="divider">
    <div class="notes"><strong>ملاحظات:</strong> {{ $invoice->notes }}</div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>{{ \App\Models\Setting::get('company_name', 'Orion ERP') }} — نظام إدارة متكامل · تم الإنشاء في {{ now()->format('d/m/Y H:i') }}</p>
        <p style="margin-top:4px">هذه الفاتورة صادرة إلكترونياً وصالحة بدون توقيع</p>
    </div>

</div>
</body>
</html>