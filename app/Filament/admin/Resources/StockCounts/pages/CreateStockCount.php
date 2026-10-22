<?php

namespace App\Filament\Admin\Resources\StockCounts\Pages;

use App\Filament\Admin\Resources\StockCounts\StockCountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockCount extends CreateRecord
{
    protected static string $resource = StockCountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['count_number'] = \App\Models\StockCount::generateNumber();
        $data['created_by']   = auth()->id();
        $data['status']       = 'draft';
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return StockCountResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}