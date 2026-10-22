<?php

namespace App\Filament\Admin\Resources\ReceiptVouchers\Pages;

use App\Filament\Admin\Resources\ReceiptVouchers\ReceiptVoucherResource;
use App\Models\ReceiptVoucher;
use Filament\Resources\Pages\ListRecords;

class ListReceiptVouchers extends ListRecords
{
    protected static string $resource = ReceiptVoucherResource::class;

    protected string $view = 'filament.admin.resources.receipt-vouchers.pages.list-receipt-vouchers';

    /**
     * Orion UI Pass: create action is rendered in the custom Orion header.
     * Posting/reversal logic is not changed here.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getUiSummary(): array
    {
        $base = ReceiptVoucher::query();
        $today = ReceiptVoucher::query()->whereDate('receipt_date', today());
        $posted = ReceiptVoucher::query()->where('status', 'posted');
        $reversed = ReceiptVoucher::query()->where('status', 'reversed');

        return [
            'total_count' => (clone $base)->count(),
            'total_amount' => (float) (clone $base)->sum('amount'),
            'today_count' => (clone $today)->count(),
            'today_amount' => (float) (clone $today)->sum('amount'),
            'posted_count' => (clone $posted)->count(),
            'posted_amount' => (float) (clone $posted)->sum('amount'),
            'reversed_count' => (clone $reversed)->count(),
        ];
    }
}
