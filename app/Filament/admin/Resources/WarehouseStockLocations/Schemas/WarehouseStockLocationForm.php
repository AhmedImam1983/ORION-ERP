<?php

namespace App\Filament\Admin\Resources\WarehouseStockLocations\Schemas;

use App\Models\WarehouseStock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseStockLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الموقع')->schema([
                Select::make('warehouse_stock_id')
                    ->label('الصنف داخل المخزن')
                    ->options(function () {
                        return WarehouseStock::query()
                            ->with(['product', 'warehouse'])
                            ->orderBy('warehouse_id')
                            ->orderBy('product_id')
                            ->get()
                            ->mapWithKeys(fn ($stock) => [
                                $stock->id => ($stock->warehouse?->name ?? 'مخزن') . ' — ' .
                                    ($stock->product?->name ?? 'صنف') . ' — رصيد ' .
                                    number_format((float) $stock->quantity, 3),
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('location_code')
                    ->label('رمز الموقع')
                    ->placeholder('مثال: A-03-12')
                    ->required()
                    ->maxLength(100),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
