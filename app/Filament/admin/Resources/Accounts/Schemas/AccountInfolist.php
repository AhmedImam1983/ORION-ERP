<?php

namespace App\Filament\Admin\Resources\Accounts\Schemas;

use App\Models\Account;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الحساب')->schema([
                TextEntry::make('code')
                    ->label('الكود')
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextEntry::make('name')
                    ->label('اسم الحساب')
                    ->weight('bold'),

                TextEntry::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->placeholder('-'),

                TextEntry::make('parent.name')
                    ->label('الحساب الأب')
                    ->placeholder('حساب رئيسي'),

                TextEntry::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'asset'     => 'أصول',
                        'liability' => 'خصوم',
                        'equity'    => 'حقوق الملكية',
                        'revenue'   => 'إيرادات',
                        'expense'   => 'مصروفات',
                        default     => $state,
                    }),

                TextEntry::make('level')
                    ->label('المستوى')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'main'   => 'رئيسي',
                        'sub'    => 'فرعي',
                        'detail' => 'تفصيلي',
                        default  => $state,
                    }),

                TextEntry::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->money('SAR'),

                TextEntry::make('balance_type')
                    ->label('الطبيعة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'debit' ? 'مدين' : 'دائن'),

                IconEntry::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextEntry::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('معلومات النظام')->schema([
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),

                TextEntry::make('deleted_at')
                    ->label('تاريخ الحذف')
                    ->dateTime('d/m/Y H:i')
                    ->visible(fn (Account $record): bool => $record->trashed()),
            ])->columns(3)->collapsible(),
        ]);
    }
}
