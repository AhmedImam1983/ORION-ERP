<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $context = $this->buildContext();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.groq.key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'       => 'llama-3.3-70b-versatile',
            'max_tokens'  => 800,
            'temperature' => 0.7,
            'messages'    => [
                ['role' => 'system', 'content' => $context],
                ['role' => 'user',   'content' => $request->message],
            ],
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'فشل الاتصال بالمساعد'], 500);
        }

        $data  = $response->json();
        $reply = $data['choices'][0]['message']['content'] ?? 'لا توجد استجابة';

        return response()->json(['reply' => $reply]);
    }

    private function buildContext(): string
    {
        try {
            $totalInvoices   = \App\Models\Invoice::where('type', 'sale')->count();
            $totalCustomers  = \App\Models\Customer::count();
            $totalProducts   = \App\Models\Product::count();
            $totalDebts      = \App\Models\Invoice::where('type', 'sale')->where('remaining_amount', '>', 0)->sum('remaining_amount');
            $collectedToday  = \App\Models\Invoice::where('type', 'sale')->whereDate('created_at', today())->sum('paid_amount');
            $unpaidInv       = \App\Models\Invoice::where('type', 'sale')->where('remaining_amount', '>', 0)->where('status', '!=', 'cancelled')->count();
            $lowStock        = \App\Models\Product::where('item_type', 'stock')->whereRaw('quantity <= min_quantity AND min_quantity > 0')->where('is_active', true)->count();
            $tm              = now()->startOfMonth();
            $salesThisMonth  = \App\Models\Invoice::where('type', 'sale')->whereIn('status', ['confirmed', 'paid'])->whereDate('invoice_date', '>=', $tm)->sum('total');
            $costsThisMonth  = \App\Models\Invoice::where('type', 'purchase')->whereIn('status', ['confirmed', 'paid'])->whereDate('invoice_date', '>=', $tm)->sum('total');
            $profitThisMonth = $salesThisMonth - $costsThisMonth;

            $topCust = \App\Models\Invoice::with('customer')
                ->selectRaw('customer_id, SUM(total) as tp, COUNT(*) as cnt')
                ->where('type', 'sale')->whereIn('status', ['confirmed', 'paid'])
                ->whereNotNull('customer_id')->groupBy('customer_id')
                ->orderByDesc('tp')->first();

            $bestSell = \App\Models\InvoiceItem::selectRaw('product_name, SUM(quantity) as qty')
                ->whereHas('invoice', fn($q) => $q->where('type', 'sale')->whereIn('status', ['confirmed', 'paid']))
                ->groupBy('product_name')->orderByDesc('qty')->first();

            return "أنت مساعد ذكي متخصص لنظام Orion ERP. تحدث دائماً بالعربية بشكل مختصر ومفيد ولا تتجاوز 200 كلمة.
بيانات النظام الحالية:
- إجمالي الفواتير: {$totalInvoices}
- إجمالي العملاء: {$totalCustomers}
- إجمالي الأصناف: {$totalProducts}
- إجمالي المديونيات: " . number_format($totalDebts, 2) . " ر.س
- المحصّل اليوم: " . number_format($collectedToday, 2) . " ر.س
- فواتير غير مدفوعة: {$unpaidInv}
- أصناف بمخزون منخفض: {$lowStock}
- مبيعات الشهر: " . number_format($salesThisMonth, 2) . " ر.س
- مشتريات الشهر: " . number_format($costsThisMonth, 2) . " ر.س
- صافي ربح الشهر: " . number_format($profitThisMonth, 2) . " ر.س
- أفضل عميل: " . ($topCust?->customer?->name ?? 'غير محدد') . " (" . number_format($topCust?->tp ?? 0, 2) . " ر.س)
- أكثر صنف مبيعاً: " . ($bestSell?->product_name ?? 'غير محدد') . " (" . ($bestSell?->qty ?? 0) . " قطعة)";
        } catch (\Exception $e) {
            return "أنت مساعد ذكي لنظام Orion ERP. أجب بالعربية بشكل مختصر.";
        }
    }
}