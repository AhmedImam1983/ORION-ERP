<?php

namespace App\Filament\Admin\Resources\Brands\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات العلامة التجارية')
                ->schema([
                    TextInput::make('code')
                        ->label('الكود')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('name_en')
                        ->label('الاسم بالإنجليزي')
                        ->maxLength(255),
                    Select::make('type')
                        ->label('النوع')
                        ->options([
                            'manufacturer' => 'مصنّع',
                            'aftermarket' => 'Aftermarket',
                            'generic' => 'عام',
                            'other' => 'أخرى',
                        ])
                        ->default('aftermarket')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
