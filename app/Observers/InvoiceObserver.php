<?php

namespace App\Observers;

use App\Models\Invoice;

/**
 * Invoice posting is intentionally NOT performed from model events.
 *
 * Filament saves the invoice header before relationship repeater rows are
 * guaranteed to be available. Posting from created/updated can therefore
 * use missing or stale invoice items.
 *
 * The safe posting points are:
 * - CreateInvoice::afterCreate()
 * - EditInvoice::afterSave()
 *
 * Both run after the InvoiceItems relationship has been persisted and then
 * delegate to InvoicePostingService, which posts GL + stock atomically.
 */
class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        // Intentionally empty.
    }

    public function updated(Invoice $invoice): void
    {
        // Intentionally empty.
    }
}
