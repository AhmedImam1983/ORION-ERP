<?php

namespace App\Filament\Admin\Resources\Suppliers\Schemas;

use App\Models\Supplier;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('بيانات المورد')->schema([
                TextEntry::make('code')
                    ->label('كود المورد')
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),
                TextEntry::make('name')
                    ->label('اسم المورد')
                    ->weight('bold')
                    ->size('lg'),
                IconEntry::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                TextEntry::make('phone')
                    ->label('التليفون')
                    ->placeholder('-')
                    ->icon('heroicon-o-phone')
                    ->copyable(),
                TextEntry::make('phone2')
                    ->label('تليفون 2')
                    ->placeholder('-')
                    ->icon('heroicon-o-phone'),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني')
                    ->placeholder('-')
                    ->icon('heroicon-o-envelope')
                    ->copyable(),
                TextEntry::make('address')
                    ->label('العنوان')
                    ->placeholder('-')
                    ->icon('heroicon-o-map-pin')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('البيانات المالية والبنكية')->schema([
                TextEntry::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->money('SAR'),
                TextEntry::make('balance_type')
                    ->label('نوع الرصيد')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state === 'debit' ? 'مدين' : 'دائن')
                    ->color(fn($state) => $state === 'debit' ? 'danger' : 'success'),
                TextEntry::make('tax_number')
                    ->label('الرقم الضريبي')
                    ->placeholder('-')
                    ->copyable(),
                TextEntry::make('bank_name')
                    ->label('اسم البنك')
                    ->placeholder('-')
                    ->icon('heroicon-o-building-library'),
                TextEntry::make('bank_account')
                    ->label('رقم الحساب البنكي')
                    ->placeholder('-')
                    ->copyable(),
            ])->columns(3),

            Section::make('ملاحظات')->schema([
                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('لا توجد ملاحظات')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('d/m/Y H:i'),
            ])->columns(2)->collapsible(),

        ]);
    }
}