@php
    $installments = \App\Models\Installment::where('invoice_id', $invoice->id)
        ->orderBy('installment_number')
        ->get();
    $totalPaid = $installments->sum('paid_amount');
    $totalAmt  = $installments->sum('amount');
    $remaining = $totalAmt - $totalPaid;
@endphp

<div style="background:#0d1117;border:1px solid #1e2d45;border-radius:10px;padding:16px;margin-top:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div style="font-size:13px;font-weight:700;color:#94a3b8">📅 جدول الأقساط</div>
        <div style="display:flex;gap:8px;align-items:center">
            <div style="font-size:12px;color:#64748b">
                مدفوع: <span style="color:#4ade80;font-weight:700">{{ number_format($totalPaid,2) }}</span> /
                إجمالي: <span style="color:#e2e8f0;font-weight:700">{{ number_format($totalAmt,2) }}</span>
            </div>
        </div>
    </div>

    @if($installments->isEmpty())
    <div style="text-align:center;padding:20px;color:#1e2d45;font-size:13px">لا توجد أقساط</div>
    @else
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:12px">
        <thead>
            <tr>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">#</th>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">القيمة</th>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">المدفوع</th>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">المتبقي</th>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">تاريخ الاستحقاق</th>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">الحالة</th>
                <th style="text-align:right;padding:8px 10px;color:#334155;border-bottom:1px solid #1e2d45;font-size:11px">دفع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($installments as $inst)
            <tr style="{{ $loop->even ? 'background:rgba(255,255,255,.01)' : '' }}">
                <td style="padding:9px 10px;color:#475569;font-weight:700">{{ $inst->installment_number }}</td>
                <td style="padding:9px 10px;color:#e2e8f0;font-weight:700">{{ number_format($inst->amount,2) }} ر.س</td>
                <td style="padding:9px 10px;color:#4ade80">{{ number_format($inst->paid_amount,2) }} ر.س</td>
                <td style="padding:9px 10px;color:{{ $inst->remaining > 0 ? '#f87171' : '#4ade80' }};font-weight:700">
                    {{ number_format($inst->remaining,2) }} ر.س
                </td>
                <td style="padding:9px 10px;color:{{ $inst->due_date < today() && $inst->status !== 'paid' ? '#f87171' : '#64748b' }}">
                    {{ $inst->due_date->format('d/m/Y') }}
                    @if($inst->due_date < today() && $inst->status !== 'paid')
                    <span style="font-size:10px;color:#f87171"> (متأخر)</span>
                    @endif
                </td>
                <td style="padding:9px 10px">
                    <span style="padding:3px 8px;border-radius:5px;font-size:11px;font-weight:700;background:{{ $inst->status_color }}22;color:{{ $inst->status_color }}">
                        {{ $inst->status_label }}
                    </span>
                </td>
                <td style="padding:9px 10px">
                    @if($inst->status !== 'paid')
                    <button onclick="payInstallment({{ $inst->id }}, {{ $inst->remaining }}, '{{ csrf_token() }}')"
                        style="background:rgba(124,109,255,.1);border:1px solid rgba(124,109,255,.2);color:#a78bfa;padding:4px 10px;border-radius:6px;font-size:11px;cursor:pointer;font-family:Cairo,sans-serif;font-weight:700">
                        💰 دفع
                    </button>
                    @else
                    <span style="color:#334155;font-size:11px">{{ $inst->paid_date?->format('d/m/Y') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top:2px solid #1e2d45">
                <td style="padding:10px;font-weight:700;color:#e2e8f0">الإجمالي</td>
                <td style="padding:10px;font-weight:700;color:#e2e8f0">{{ number_format($totalAmt,2) }} ر.س</td>
                <td style="padding:10px;font-weight:700;color:#4ade80">{{ number_format($totalPaid,2) }} ر.س</td>
                <td style="padding:10px;font-weight:700;color:{{ $remaining > 0 ? '#f87171' : '#4ade80' }}">{{ number_format($remaining,2) }} ر.س</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
    </div>
    @endif
</div>

<script>
function payInstallment(id, remaining, token) {
    var amount = remaining;
    
    // Modal بسيط
    var modal = document.createElement('div');
    modal.innerHTML = `
        <div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.8);z-index:9999;display:flex;align-items:center;justify-content:center">
            <div style="background:#161c2d;border:1px solid #2d3f5f;border-radius:16px;padding:24px;width:340px">
                <div style="font-size:16px;font-weight:800;color:#e2e8f0;margin-bottom:16px">💰 تسجيل دفع قسط</div>
                <div style="font-size:12px;color:#475569;margin-bottom:6px">المبلغ المتبقي: <span style="color:#f87171;font-weight:700">${remaining} ر.س</span></div>
                <input id="pay-amount" type="number" value="${remaining}" step="0.01" min="0.01"
                    style="width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:12px;color:#e2e8f0;font-size:18px;text-align:center;outline:none;margin-bottom:12px;font-family:Cairo,sans-serif">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <button onclick="this.closest('div').parentElement.parentElement.remove()"
                        style="padding:10px;background:transparent;border:1px solid #1e2d45;border-radius:8px;color:#475569;cursor:pointer;font-family:Cairo,sans-serif">إلغاء</button>
                    <button id="pay-confirm"
                        style="padding:10px;background:#7c6dff;border:none;border-radius:8px;color:#fff;cursor:pointer;font-weight:700;font-family:Cairo,sans-serif">تأكيد الدفع</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal);
    
    document.getElementById('pay-confirm').onclick = function() {
        var amount = parseFloat(document.getElementById('pay-amount').value);
        if (!amount || amount <= 0) return;
        
        modal.remove();
        
        fetch('/installments/' + id + '/pay', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({ amount: amount })
        }).then(function(r) {
            if (r.ok) window.location.reload();
            else alert('خطأ في تسجيل الدفع');
        });
    };
}
</script>