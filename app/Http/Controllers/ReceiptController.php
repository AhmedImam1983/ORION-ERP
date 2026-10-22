<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'branch']);

        return view('receipt.pos', compact('invoice'));
    }
}