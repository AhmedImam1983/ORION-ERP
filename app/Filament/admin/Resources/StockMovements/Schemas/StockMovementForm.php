<?php

namespace App\Filament\Admin\Resources\StockMovements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تسوية مخزون يدوية')
                ->description('حدد الصنف ونوع التسوية والكمية والمخزن ثم أضف مرجعاً يوضح سبب الحركة')
                ->schema([
                    Select::make('product_id')
                        ->label('الصنف')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('type')
                        ->label('نوع الحركة')
                        ->options([
                            'adjustment_in'  => 'تسوية زيادة',
                            'adjustment_out' => 'تسوية نقص',
                            'opening'        => 'رصيد افتتاحي',
                        ])
                        ->required(),

                    Select::make('warehouse_id')
                        ->label('المخزن')
                        ->relationship('warehouse', 'name')
                        ->searchable()
                        ->default(null),

                    TextInput::make('quantity')
                        ->label('الكمية')
                        ->required()
                        ->numeric()
                        ->minValue(0.001)
                        ->default(1),

                    TextInput::make('unit_price')
                        ->label('سعر الوحدة')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0),

                    TextInput::make('reference')
                        ->label('المرجع')
                        ->placeholder('مثال: ADJ-2026-001')
                        ->default(null),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('اكتب سبب التسوية أو أي تفاصيل للمراجعة...')
                        ->default(null)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
