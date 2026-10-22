<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            padding: 8px;
            color: #000;
            background: #fff;
            direction: rtl;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .sep { border-top: 1px dashed #000; margin: 6px 0; }
        .company-name { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .company-info { font-size: 11px; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 4px; font-size: 11px; vertical-align: top; }
        .items-header td { font-weight: bold; font-size: 10px; border-bottom: 1px solid #000; }
        .total-row td { font-size: 14px; font-weight: bold; border-top: 1px solid #000; }
        .footer { text-align: center; font-size: 11px; margin-top: 8px; }
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { padding: 4px; }
            .no-print { display: none; }
        }
        .print-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #4ade80;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ طباعة</button>

    @php
        $companyName   = \App\Models\Setting::get('company_name', 'Orion ERP');
        $companyNameEn = \App\Models\Setting::get('company_name_en', '');
        $companyPhone  = \App\Models\Setting::get('company_phone', '');
        $companyTax    = \App\Models\Setting::get('company_tax_number', '');
        $companyCR     = \App\Models\Setting::get('company_cr', '');
        $companyEmail  = \App\Models\Setting::get('company_email', '');
    @endphp

    {{-- Header --}}
    <div class="center">
        <div class="company-name">{{ $companyName }}</div>
        @if($companyNameEn)
        <div class="company-info" style="font-size:10px">{{ $companyNameEn }}</div>
        @endif
        @if($companyPhone)
        <div class="company-info">📞 {{ $companyPhone }}</div>
        @endif
        @if($companyEmail)
        <div class="company-info">✉️ {{ $companyEmail }}</div>
        @endif
        @if($companyTax)
        <div class="company-info">رقم ضريبي: {{ $companyTax }}</div>
        @endif
        @if($companyCR)
        <div class="company-info">س.ت: {{ $companyCR }}</div>
        @endif
    </div>

    <div class="sep"></div>

    {{-- Invoice Info --}}
    <table>
        <tr><td>رقم الفاتورة:</td><td class="bold">{{ $invoice->invoice_number }}</td></tr>
        <tr><td>التاريخ:</td><td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td></tr>
        @if($invoice->branch)
        <tr><td>الفرع:</td><td>{{ $invoice->branch->name }}</td></tr>
        @endif
        <tr><td>العميل:</td><td>{{ $invoice->customer?->name ?? 'عميل نقدي' }}</td></tr>
    </table>

    <div class="sep"></div>

    {{-- Items --}}
    <table>
        <tr class="items-header">
            <td>الصنف</td>
            <td align="center">كمية</td>
            <td align="center">سعر</td>
            <td align="left">اجمالي</td>
        </tr>
        @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td align="center">{{ $item->quantity * 1 }}</td>
            <td align="center">{{ number_format($item->unit_price, 2) }}</td>
            <td align="left">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="sep"></div>

    {{-- Totals --}}
    <table>
        <tr class="total-row">
            <td>الإجمالي</td>
            <td align="left" class="bold">{{ number_format($invoice->total, 2) }} ر.س</td>
        </tr>
        <tr>
            <td>المدفوع</td>
            <td align="left">{{ number_format($invoice->paid_amount, 2) }} ر.س</td>
        </tr>
        @if($invoice->remaining_amount > 0)
        <tr>
            <td>المتبقي</td>
            <td align="left" style="color:#cc0000">{{ number_format($invoice->remaining_amount, 2) }} ر.س</td>
        </tr>
        @endif
    </table>

    <div class="sep"></div>

    {{-- Footer --}}
    <div class="footer">
        <div>شكراً لتعاملكم معنا 🙏</div>
        <div style="font-size:10px;margin-top:4px">{{ $companyName }}</div>
        @if($companyPhone)
        <div style="font-size:10px">{{ $companyPhone }}</div>
        @endif
        <div style="font-size:9px;margin-top:4px;color:#666">Powered by Orion ERP</div>
    </div>

    <script>
        // طباعة تلقائي لو فتح من الكاشير
        if (window.location.search.includes('auto=1')) {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>