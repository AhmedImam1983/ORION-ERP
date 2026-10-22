<?php

namespace App\Filament\Admin\Resources\JournalEntries\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Filament\Admin\Resources\JournalEntries\JournalEntryResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;
    protected string $view = 'filament.admin.resources.journal-entries.pages.view-journal-entry';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('source_invoice')
                ->label('فتح الفاتورة الأصلية')
                ->icon('heroicon-o-document-text')
                ->url(fn () => $this->getSourceInvoiceUrl())
                ->visible(fn (): bool => $this->getSourceInvoiceUrl() !== null),

            EditAction::make()
                ->label('تعديل')
                ->visible(fn (): bool => $this->getRecord()->status === 'draft'),
        ];
    }

    public function getSourceInvoiceUrl(): ?string
    {
        $reference = strtoupper((string) $this->getRecord()->reference);

        if (!str_starts_with($reference, 'INV-')) {
            return null;
        }

        $invoiceNumber = str_replace('-COGS', '', $reference);
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        if (!$invoice) {
            return null;
        }

        return InvoiceResource::getUrl('view', ['record' => $invoice]);
    }
}
