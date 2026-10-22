<?php

namespace App\Filament\Admin\Resources\JournalEntries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات القيد')->schema([
                TextEntry::make('entry_number')
                    ->label('رقم القيد')
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),

                TextEntry::make('entry_date')
                    ->label('تاريخ القيد')
                    ->date('d/m/Y'),

                TextEntry::make('source_label')
                    ->label('المصدر')
                    ->badge(),

                TextEntry::make('reference')
                    ->label('المرجع')
                    ->placeholder('-')
                    ->copyable(),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'    => 'مسودة',
                        'posted'   => 'مرحّل',
                        'reversed' => 'معكوس',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'posted'   => 'success',
                        'draft'    => 'warning',
                        'reversed' => 'danger',
                        default    => 'gray',
                    }),

                TextEntry::make('description')
                    ->label('البيان')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('التوازن')->schema([
                TextEntry::make('total_debit')
                    ->label('إجمالي المدين')
                    ->money('SAR')
                    ->weight('bold')
                    ->color('danger'),

                TextEntry::make('total_credit')
                    ->label('إجمالي الدائن')
                    ->money('SAR')
                    ->weight('bold')
                    ->color('success'),

                TextEntry::make('balance_difference')
                    ->label('الفرق')
                    ->formatStateUsing(fn ($state) => number_format(abs((float) $state), 2) . ' ر.س')
                    ->color(fn ($state) => abs((float) $state) < 0.01 ? 'success' : 'danger')
                    ->weight('bold'),

                TextEntry::make('is_balanced')
                    ->label('النتيجة')
                    ->formatStateUsing(fn ($state) => $state ? 'القيد متوازن' : 'القيد غير متوازن')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])->columns(4),

            Section::make('سطور القيد')->schema([
                \Filament\Infolists\Components\RepeatableEntry::make('lines')
                    ->label('')
                    ->schema([
                        TextEntry::make('account.name')
                            ->label('الحساب')
                            ->weight('bold'),

                        TextEntry::make('account.code')
                            ->label('كود الحساب')
                            ->color('primary'),

                        TextEntry::make('description')
                            ->label('البيان')
                            ->placeholder('-'),

                        TextEntry::make('debit')
                            ->label('مدين')
                            ->money('SAR')
                            ->color('danger'),

                        TextEntry::make('credit')
                            ->label('دائن')
                            ->money('SAR')
                            ->color('success'),
                    ])
                    ->columns(5),
            ]),

            Section::make('معلومات إضافية')->schema([
                TextEntry::make('createdBy.name')
                    ->label('أنشأ بواسطة')
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i'),

                TextEntry::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('d/m/Y H:i'),
            ])->columns(3)->collapsible(),
        ]);
    }
}
