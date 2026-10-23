<?php

namespace App\Filament\Admin\Resources\ProductVehicles\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductVehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('ربط الصنف بالمركبة')
                ->description('حدد الصنف والسيارة التي يتوافق معها')
                ->schema([
                    Select::make('product_id')
                        ->label('الصنف')
                        ->relationship('product', 'name', modifyQueryUsing: fn ($query) => $query->where('item_type', 'stock'))
                        ->searchable(['name', 'code', 'part_number'])
                        ->preload()
                        ->required()
                        ->getOptionLabelFromRecordUsing(function (Product $record): string {
                            $partNumber = $record->part_number ? ' — ' . $record->part_number : '';
                            return $record->name . $partNumber . ' [' . $record->code . ']';
                        }),

                    TextInput::make('make')
                        ->label('ماركة المركبة')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('model')
                        ->label('الموديل')
                        ->maxLength(100),

                    TextInput::make('year_from')
                        ->label('من سنة')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(2100),

                    TextInput::make('year_to')
                        ->label('إلى سنة')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(2100),

                    TextInput::make('engine')
                        ->label('المحرك')
                        ->maxLength(100),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
