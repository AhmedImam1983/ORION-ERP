<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function generate(Request $request, Invoice $invoice)
    {
        $request->validate([
            'count'      => 'required|integer|min:2|max:60',
            'start_date' => 'required|date',
            'frequency'  => 'required|in:weekly,biweekly,monthly,quarterly',
        ]);

        Installment::generateSchedule(
            $invoice,
            $request->count,
            $request->start_date,
            $request->frequency
        );

        return response()->json(['success' => true, 'message' => 'تم إنشاء جدول الأقساط']);
    }

    public function pay(Request $request, Installment $installment)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $amount = min($request->amount, $installment->remaining);
        $installment->paid_amount += $amount;
        $installment->updateStatus();

        // تحديث الفاتورة
        $invoice = $installment->invoice;
        $totalPaid = $invoice->installments()->sum('paid_amount');
        $invoice->update([
            'paid_amount'      => $totalPaid,
            'remaining_amount' => max(0, $invoice->total - $totalPaid),
            'status'           => $totalPaid >= $invoice->total ? 'paid' : 'partial',
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Installment $installment)
    {
        $installment->delete();
        return response()->json(['success' => true]);
    }
}