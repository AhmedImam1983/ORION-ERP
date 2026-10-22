<?php

namespace App\Filament\Admin\Resources\FinancialTransfers\Pages;

use App\Filament\Admin\Resources\FinancialTransfers\FinancialTransferResource;
use App\Models\FinancialTransfer;
use Filament\Resources\Pages\ListRecords;

class ListFinancialTransfers extends ListRecords
{
    protected static string $resource = FinancialTransferResource::class;

    protected string $view = 'filament.admin.resources.financial-transfers.pages.list-financial-transfers';

    /**
     * Orion UI Pass:
     * The create button is rendered inside the custom Orion header.
     * No accounting / posting logic is changed here.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getUiSummary(): array
    {
        $totalCount = FinancialTransfer::query()->count();

        $todayQuery = FinancialTransfer::query()
            ->whereDate('transfer_date', today());

        $postedQuery = FinancialTransfer::query()
            ->where('status', 'posted');

        $reversedQuery = FinancialTransfer::query()
            ->where('status', 'reversed');

        return [
            'total_count' => $totalCount,
            'today_count' => (clone $todayQuery)->count(),
            'today_amount' => (float) (clone $todayQuery)->sum('amount'),
            'posted_count' => (clone $postedQuery)->count(),
            'posted_amount' => (float) (clone $postedQuery)->sum('amount'),
            'reversed_count' => (clone $reversedQuery)->count(),
        ];
    }
}
