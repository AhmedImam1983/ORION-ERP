<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>فاتورة {{ $invoice->invoice_number }}</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap');

    * { margin:0; padding:0; box-sizing:border-box; font-family:'Cairo',sans-serif; }
    body { background:#fff; color:#1a1a2e; font-size:13px; }

    .page { width:210mm; min-height:297mm; margin:0 auto; padding:12mm; }

    /* Header */
    .inv-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8mm; padding-bottom:6mm; border-bottom:3px solid #1a1a2e; }
    .company-logo { font-size:24px; font-weight:900; color:#1a1a2e; letter-spacing:-1px; }
    .company-logo span { color:#7c3aed; }
    .company-logo img { max-height:50px; max-width:150px; object-fit:contain; }
    .company-info { font-size:11px; color:#555; margin-top:6px; line-height:1.9; }
    .company-info .company-name-ar { font-size:15px; font-weight:800; color:#1a1a2e; }
    .company-info .company-name-en { font-size:11px; color:#7c3aed; font-weight:600; }
    .inv-badge { text-align:left; }
    .inv-type { background:#1a1a2e; color:#fff; padding:6px 16px; border-radius:6px; font-size:13px; font-weight:800; margin-bottom:8px; display:inline-block; }
    .inv-number { font-size:22px; font-weight:900; color:#7c3aed; }
    .inv-date { font-size:11px; color:#888; margin-top:4px; }

    /* Info Grid */
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:6mm; margin-bottom:6mm; }
    .info-box { background:#f8f8fc; border-radius:8px; padding:5mm; border:1px solid #e8e8f0; }
    .info-box-title { font-size:10px; color:#888; font-weight:700; letter-spacing:.5px; margin-bottom:4px; text-transform:uppercase; }
    .info-box-value { font-size:13px; font-weight:700; color:#1a1a2e; }
    .info-box-sub { font-size:11px; color:#666; margin-top:2px; line-height:1.8; }

    /* Items Table */
    .items-section { margin-bottom:6mm; }
    .items-table { width:100%; border-collapse:collapse; }
    .items-table thead tr { background:#1a1a2e; color:#fff; }
    .items-table th { padding:8px 10px; text-align:right; font-size:11px; font-weight:700; }
    .items-table td { padding:8px 10px; border-bottom:1px solid #f0f0f8; font-size:12px; }
    .items-table tbody tr:nth-child(even) { background:#f8f8fc; }
    .items-table tbody tr:last-child td { border-bottom:none; }
    .product-name { font-weight:600; color:#1a1a2e; }
    .product-code { font-size:10px; color:#888; font-family:monospace; }

    /* Totals */
    .totals-wrap { display:flex; justify-content:flex-end; margin-bottom:6mm; }
    .totals-box { width:75mm; }
    .totals-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f0f0f8; font-size:12px; }
    .totals-row:last-child { border-bottom:none; }
    .totals-row.total { font-size:15px; font-weight:900; color:#1a1a2e; padding-top:8px; border-top:2px solid #1a1a2e; margin-top:4px; }
    .totals-row.paid { color:#16a34a; font-weight:700; }
    .totals-row.remaining { color:#dc2626; font-weight:700; }

    /* Payment Status */
    .status-badge { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:800; display:inline-block; }
    .status-paid { background:#dcfce7; color:#16a34a; }
    .status-partial { background:#fef3c7; color:#d97706; }
    .status-confirmed { background:#dbeafe; color:#1d4ed8; }
    .status-draft { background:#f3f4f6; color:#6b7280; }
    .status-cancelled { background:#fee2e2; color:#dc2626; }

    /* Tax Info Box */
    .tax-info { background:#f0f4ff; border:1px solid #c7d2fe; border-radius:8px; padding:4mm; margin-bottom:6mm; display:flex; justify-content:space-between; font-size:11px; }
    .tax-info-item { text-align:center; }
    .tax-info-label { color:#6366f1; font-weight:700; margin-bottom:2px; }
    .tax-info-value { color:#1a1a2e; font-weight:800; font-size:12px; }

    /* Notes */
    .notes-box { background:#f8f8fc; border-radius:8px; padding:4mm; border:1px solid #e8e8f0; margin-bottom:6mm; }
    .notes-title { font-size:10px; color:#888; font-weight:700; margin-bottom:4px; }
    .notes-text { font-size:12px; color:#444; }

    /* Footer */
    .inv-footer { border-top:2px solid #1a1a2e; padding-top:4mm; display:flex; justify-content:space-between; align-items:flex-end; }
    .footer-sig { text-align:center; }
    .sig-line { width:40mm; border-bottom:1px solid #888; margin:0 auto 4px; }
    .sig-label { font-size:10px; color:#888; }
    .footer-note { font-size:10px; color:#888; text-align:center; max-width:80mm; line-height:1.6; }

    /* Watermark for DRAFT */
    .watermark { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-30deg); font-size:100px; font-weight:900; color:rgba(0,0,0,.05); z-index:0; pointer-events:none; }

    @media print {
        body { background:#fff; }
        .page { padding:8mm; }
        .no-print { display:none !important; }
        @page { margin:0; size:A4; }
    }
</style>
</head>
<body>

@php
    use App\Models\Setting;
    $companyName   = Setting::get('company_name', 'Orion ERP');
    $companyNameEn = Setting::get('company_name_en', '');
    $companyTax    = Setting::get('company_tax_number', '');
    $companyCR     = Setting::get('company_cr', '');
    $companyPhone  = Setting::get('company_phone', '');
    $companyEmail  = Setting::get('company_email', '');
    $companyWebsite= Setting::get('company_website', '');
    $companyLogo   = Setting::get('company_logo', '');
@endphp

@if($invoice->status === 'draft')
<div class="watermark">مسودة</div>
@endif

<!-- Print Button -->
<div class="no-print" style="text-align:center;padding:10px;background:#1a1a2e;color:#fff">
    <button onclick="window.print()" style="background:#7c3aed;color:#fff;border:none;padding:10px 30px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:Cairo,sans-serif;margin-left:10px">🖨️ طباعة</button>
    <button onclick="window.close()" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2);padding:10px 20px;border-radius:8px;font-size:14px;cursor:pointer;font-family:Cairo,sans-serif">✕ إغلاق</button>
    <span style="margin-right:20px;font-size:13px;opacity:.7">{{ $invoice->invoice_number }}</span>
</div>

<div class="page">

    <!-- Header -->
    <div class="inv-header">
        <div>
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="{{ $companyName }}" class="company-logo">
            @else
                <div class="company-logo">{{ $companyName }}<span> ●</span></div>
            @endif
            <div class="company-info">
                <div class="company-name-ar">{{ $companyName }}</div>
                @if($companyNameEn)<div class="company-name-en">{{ $companyNameEn }}</div>@endif
                @if($companyPhone)📞 {{ $companyPhone }}<br>@endif
                @if($companyEmail)✉️ {{ $companyEmail }}<br>@endif
                @if($companyWebsite)🌐 {{ $companyWebsite }}<br>@endif
                @if($companyTax)🧾 الرقم الضريبي: {{ $companyTax }}<br>@endif
                @if($companyCR)📋 السجل التجاري: {{ $companyCR }}@endif
            </div>
        </div>
        <div class="inv-badge">
            <div class="inv-type">
                @switch($invoice->type)
                    @case('sale') فاتورة بيع @break
                    @case('purchase') فاتورة شراء @break
                    @case('sale_return') مرتجع بيع @break
                    @case('purchase_return') مرتجع شراء @break
                @endswitch
            </div>
            <div class="inv-number">{{ $invoice->invoice_number }}</div>
            <div class="inv-date">📅 {{ $invoice->invoice_date->format('d/m/Y') }}</div>
            @if($invoice->due_date)
            <div class="inv-date">⏰ استحقاق: {{ $invoice->due_date->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <!-- Tax Info Bar -->
    @if($companyTax || $companyCR)
    <div class="tax-info">
        @if($companyTax)
        <div class="tax-info-item">
            <div class="tax-info-label">الرقم الضريبي</div>
            <div class="tax-info-value">{{ $companyTax }}</div>
        </div>
        @endif
        @if($companyCR)
        <div class="tax-info-item">
            <div class="tax-info-label">السجل التجاري</div>
            <div class="tax-info-value">{{ $companyCR }}</div>
        </div>
        @endif
        <div class="tax-info-item">
            <div class="tax-info-label">تاريخ الفاتورة</div>
            <div class="tax-info-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
        </div>
        <div class="tax-info-item">
            <div class="tax-info-label">رقم الفاتورة</div>
            <div class="tax-info-value">{{ $invoice->invoice_number }}</div>
        </div>
    </div>
    @endif

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-box-title">{{ in_array($invoice->type, ['sale','sale_return']) ? 'بيانات العميل' : 'بيانات المورد' }}</div>
            @if($invoice->customer)
                <div class="info-box-value">{{ $invoice->customer->name }}</div>
                <div class="info-box-sub">
                    @if($invoice->customer->phone)📞 {{ $invoice->customer->phone }}<br>@endif
                    @if($invoice->customer->address)📍 {{ $invoice->customer->address }}<br>@endif
                    @if($invoice->customer->tax_number)🧾 {{ $invoice->customer->tax_number }}@endif
                </div>
            @elseif($invoice->supplier)
                <div class="info-box-value">{{ $invoice->supplier->name }}</div>
                <div class="info-box-sub">
                    @if($invoice->supplier->phone)📞 {{ $invoice->supplier->phone }}<br>@endif
                    @if($invoice->supplier->email)✉️ {{ $invoice->supplier->email }}<br>@endif
                    @if($invoice->supplier->tax_number)🧾 {{ $invoice->supplier->tax_number }}@endif
                </div>
            @else
                <div class="info-box-value">عميل نقدي</div>
            @endif
        </div>
        <div class="info-box">
            <div class="info-box-title">تفاصيل الدفع</div>
            <div class="info-box-value">
                @switch($invoice->payment_type)
                    @case('cash') 💵 نقدي @break
                    @case('credit') 📋 آجل @break
                    @case('installment') 📆 تقسيط @break
                    @case('card') 💳 بطاقة @break
                    @default {{ $invoice->payment_type }}
                @endswitch
            </div>
            <div class="info-box-sub" style="margin-top:8px">
                <span class="status-badge status-{{ $invoice->status }}">
                    @switch($invoice->status)
                        @case('paid') ✅ مدفوعة @break
                        @case('partial') ⚠️ دفع جزئي @break
                        @case('confirmed') 🔵 مؤكدة @break
                        @case('draft') 📝 مسودة @break
                        @case('cancelled') ❌ ملغية @break
                    @endswitch
                </span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="items-section">
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الوحدة</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    @if($invoice->items->sum('discount_amount') > 0)<th>الخصم</th>@endif
                    @if($invoice->items->sum('tax_amount') > 0)<th>الضريبة</th>@endif
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                <tr>
                    <td style="color:#888">{{ $i + 1 }}</td>
                    <td>
                        <div class="product-name">{{ $item->product_name }}</div>
                        <div class="product-code">{{ $item->product_code }}</div>
                    </td>
                    <td>{{ $item->unit }}</td>
                    <td style="font-weight:700">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }} ر.س</td>
                    @if($invoice->items->sum('discount_amount') > 0)
                        <td style="color:#dc2626">{{ $item->discount_amount > 0 ? '- '.number_format($item->discount_amount, 2).' ر.س' : '—' }}</td>
                    @endif
                    @if($invoice->items->sum('tax_amount') > 0)
                        <td style="color:#d97706">{{ $item->tax_amount > 0 ? number_format($item->tax_amount, 2).' ر.س' : '—' }}</td>
                    @endif
                    <td style="font-weight:800">{{ number_format($item->total, 2) }} ر.س</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals-wrap">
        <div class="totals-box">
            <div class="totals-row">
                <span>المجموع الفرعي</span>
                <span>{{ number_format($invoice->subtotal, 2) }} ر.س</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="totals-row" style="color:#dc2626">
                <span>الخصم</span>
                <span>- {{ number_format($invoice->discount_amount, 2) }} ر.س</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="totals-row" style="color:#d97706">
                <span>ضريبة القيمة المضافة</span>
                <span>+ {{ number_format($invoice->tax_amount, 2) }} ر.س</span>
            </div>
            @endif
            <div class="totals-row total">
                <span>الإجمالي النهائي</span>
                <span>{{ number_format($invoice->total, 2) }} ر.س</span>
            </div>
            @if($invoice->paid_amount > 0)
            <div class="totals-row paid">
                <span>المدفوع</span>
                <span>{{ number_format($invoice->paid_amount, 2) }} ر.س</span>
            </div>
            @endif
            @if($invoice->remaining_amount > 0)
            <div class="totals-row remaining">
                <span>المتبقي</span>
                <span>{{ number_format($invoice->remaining_amount, 2) }} ر.س</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="notes-box">
        <div class="notes-title">ملاحظات</div>
        <div class="notes-text">{{ $invoice->notes }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="inv-footer">
        <div class="footer-sig">
            <div class="sig-line"></div>
            <div class="sig-label">توقيع العميل</div>
        </div>
        <div class="footer-note">
            شكراً لتعاملكم معنا 🙏<br>
            {{ $companyName }}
            @if($companyPhone)<br>📞 {{ $companyPhone }}@endif
            @if($companyEmail)<br>✉️ {{ $companyEmail }}@endif
        </div>
        <div class="footer-sig">
            <div class="sig-line"></div>
            <div class="sig-label">توقيع المسؤول</div>
        </div>
    </div>

</div>
</body>
</html>