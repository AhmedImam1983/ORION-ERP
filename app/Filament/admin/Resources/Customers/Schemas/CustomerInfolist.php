<?php

namespace App\Filament\Admin\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('هوية العميل')
                ->description('البيانات الرئيسية ووسائل التواصل المسجلة.')
                ->icon('heroicon-o-identification')
                ->schema([
                    TextEntry::make('code')
                        ->label('كود العميل')
                        ->weight('bold')
                        ->color('primary')
                        ->copyable(),

                    TextEntry::make('name')
                        ->label('اسم العميل')
                        ->weight('bold')
                        ->size('lg'),

                    TextEntry::make('type')
                        ->label('نوع العميل')
                        ->badge()
                        ->formatStateUsing(fn ($state): string => match ($state) {
                            'individual' => 'فرد',
                            'company' => 'شركة',
                            'government' => 'جهة حكومية',
                            default => (string) $state,
                        })
                        ->color(fn ($state): string => match ($state) {
                            'company' => 'primary',
                            'government' => 'warning',
                            default => 'gray',
                        })
                        ->icon(fn ($state): string => match ($state) {
                            'company' => 'heroicon-m-building-office-2',
                            'government' => 'heroicon-m-building-library',
                            default => 'heroicon-m-user',
                        }),

                    TextEntry::make('is_active')
                        ->label('الحالة')
                        ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                        ->badge()
                        ->color(fn ($state): string => $state ? 'success' : 'gray')
                        ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),

                    TextEntry::make('phone')
                        ->label('التليفون الرئيسي')
                        ->placeholder('غير مسجل')
                        ->icon('heroicon-o-phone')
                        ->copyable(),

                    TextEntry::make('phone2')
                        ->label('تليفون إضافي')
                        ->placeholder('غير مسجل')
                        ->icon('heroicon-o-phone'),

                    TextEntry::make('email')
                        ->label('البريد الإلكتروني')
                        ->placeholder('غير مسجل')
                        ->icon('heroicon-o-envelope')
                        ->copyable(),

                    TextEntry::make('address')
                        ->label('العنوان')
                        ->placeholder('غير مسجل')
                        ->icon('heroicon-o-map-pin')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('الموقف المالي')
                ->description('الرصيد الافتتاحي وطبيعة الحساب وحد الائتمان المسجل.')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    TextEntry::make('opening_balance')
                        ->label('الرصيد الافتتاحي')
                        ->money('SAR'),

                    TextEntry::make('balance_type')
                        ->label('طبيعة الرصيد')
                        ->badge()
                        ->formatStateUsing(fn ($state): string => $state === 'debit' ? 'مدين' : 'دائن')
                        ->color(fn ($state): string => $state === 'debit' ? 'danger' : 'success'),

                    TextEntry::make('credit_limit')
                        ->label('حد الائتمان')
                        ->money('SAR'),

                    TextEntry::make('tax_number')
                        ->label('الرقم الضريبي')
                        ->placeholder('غير مسجل')
                        ->copyable(),

                    TextEntry::make('national_id')
                        ->label('الرقم القومي / الهوية')
                        ->placeholder('غير مسجل')
                        ->copyable(),
                ])
                ->columns(3),

            Section::make('معلومات النظام')
                ->icon('heroicon-o-clock')
                ->schema([
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
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }
}
