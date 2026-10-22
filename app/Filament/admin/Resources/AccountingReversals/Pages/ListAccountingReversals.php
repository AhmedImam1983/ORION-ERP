<?php

namespace App\Filament\Admin\Resources\AccountingReversals\Pages;

use App\Filament\Admin\Resources\AccountingReversals\AccountingReversalResource;
use App\Models\AccountingReversal;
use Filament\Resources\Pages\ListRecords;

class ListAccountingReversals extends ListRecords
{
    protected static string $resource = AccountingReversalResource::class;

    protected string $view = 'filament.admin.resources.accounting-reversals.pages.list-accounting-reversals';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getUiSummary(): array
    {
        $base = AccountingReversal::query();

        return [
            'total' => (clone $base)->count(),

            'today' => (clone $base)
                ->whereDate('reversal_date', today())
                ->count(),

            'receipts' => (clone $base)
                ->where('source_type', 'receipt_voucher')
                ->count(),

            'payments' => (clone $base)
                ->where('source_type', 'payment_voucher')
                ->count(),

            'transfers' => (clone $base)
                ->where('source_type', 'financial_transfer')
                ->count(),
        ];
    }
}
