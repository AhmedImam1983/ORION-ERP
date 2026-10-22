<?php

namespace App\Filament\Admin\Resources\PaymentVouchers\Pages;

use App\Filament\Admin\Resources\PaymentVouchers\PaymentVoucherResource;
use App\Models\PaymentVoucher;
use Filament\Resources\Pages\ListRecords;

class ListPaymentVouchers extends ListRecords
{
    protected static string $resource = PaymentVoucherResource::class;

    protected string $view = 'filament.admin.resources.payment-vouchers.pages.list-payment-vouchers';

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
        $base = PaymentVoucher::query();
        $today = PaymentVoucher::query()->whereDate('payment_date', today());
        $posted = PaymentVoucher::query()->where('status', 'posted');
        $reversed = PaymentVoucher::query()->where('status', 'reversed');

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
