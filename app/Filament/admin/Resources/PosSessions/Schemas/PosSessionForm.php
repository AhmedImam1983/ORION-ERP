<?php

namespace App\Filament\Admin\Resources\PosSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PosSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الجلسة')->schema([
                TextInput::make('session_number')
                    ->label('رقم الجلسة')
                    ->required()
                    ->disabled(),
                Select::make('user_id')
                    ->label('الكاشير')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(),
                Select::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->disabled(),
                TextInput::make('register_name')
                    ->label('اسم الكاشير')
                    ->disabled(),
                Select::make('status')
                    ->label('الحالة')
                    ->options(['open' => 'مفتوحة', 'closed' => 'مغلقة'])
                    ->disabled(),
                DateTimePicker::make('opened_at')
                    ->label('وقت الفتح')
                    ->disabled(),
                DateTimePicker::make('closed_at')
                    ->label('وقت الإغلاق')
                    ->disabled(),
            ])->columns(2),

            Section::make('الإحصائيات المالية')->schema([
                TextInput::make('opening_cash')
                    ->label('رصيد الافتتاح')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('closing_cash')
                    ->label('رصيد الإغلاق')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('total_sales')
                    ->label('إجمالي المبيعات')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('total_cash')
                    ->label('إجمالي النقدي')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('total_card')
                    ->label('إجمالي الفيزا')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('total_wallet')
                    ->label('إجمالي المحفظة')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('total_refunds')
                    ->label('إجمالي المرتجعات')
                    ->prefix('ر.س')
                    ->disabled(),
                TextInput::make('transactions_count')
                    ->label('عدد العمليات')
                    ->disabled(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}