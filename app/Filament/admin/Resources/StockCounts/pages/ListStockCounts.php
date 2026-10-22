<?php

namespace App\Filament\Admin\Resources\StockCounts\Pages;

use App\Filament\Admin\Resources\StockCounts\StockCountResource;
use App\Models\StockCount;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockCounts extends ListRecords
{
    protected static string $resource = StockCountResource::class;
    protected string $view = 'filament.admin.resources.stock-counts.pages.list-stock-counts';

    public function getTotalCountsProperty(): int
    {
        return StockCount::query()->count();
    }

    public function getDraftCountsProperty(): int
    {
        return StockCount::query()->where('status', 'draft')->count();
    }

    public function getInProgressCountsProperty(): int
    {
        return StockCount::query()->where('status', 'in_progress')->count();
    }

    public function getCompletedCountsProperty(): int
    {
        return StockCount::query()->where('status', 'completed')->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('جرد جديد')
                ->icon('heroicon-o-plus'),
        ];
    }
}
