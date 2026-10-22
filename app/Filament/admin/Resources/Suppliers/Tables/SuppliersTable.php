<?php

namespace App\Filament\Admin\Resources\Suppliers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return match (request()->query('quick')) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'credit' => $query->where('balance_type', 'credit'),
                    'debit' => $query->where('balance_type', 'debit'),
                    'without_bank' => $query->where(function (Builder $q): void {
                        $q->whereNull('bank_account')->orWhere('bank_account', '');
                    }),
                    default => $query,
                };
            })
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('اسم المورد')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('phone')
                    ->label('التليفون')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('tax_number')
                    ->label('الرقم الضريبي')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->balance_type === 'debit' ? 'danger' : 'success'),

                TextColumn::make('balance_type')
                    ->label('نوع الرصيد')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'debit' ? 'مدين' : 'دائن')
                    ->color(fn ($state) => $state === 'debit' ? 'danger' : 'success'),

                TextColumn::make('bank_name')
                    ->label('البنك')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('الحالة'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('gray')
                    ->extraAttributes(['title' => 'عرض المورد']),

                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('primary')
                    ->extraAttributes(['title' => 'تعديل المورد']),

                Action::make('statement')
                    ->label('كشف الحساب')
                    ->icon('heroicon-o-document-text')
                    ->iconButton()
                    ->color('info')
                    ->extraAttributes(['title' => 'كشف حساب المورد'])
                    ->url(fn ($record) => \App\Filament\Admin\Pages\SupplierStatement::getUrl() . '?supplierId=' . $record->id),
            ])
            ->toolbarActions([
                Action::make('import_suppliers')
                    ->label('استيراد من Excel')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->url(fn () => \App\Filament\Admin\Pages\ImportContactsPage::getUrl())
                    ->button(),

                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا يوجد موردين بعد')
            ->emptyStateDescription('ابدأ بإضافة مورد جديد أو استيراد الموردين من Excel')
            ->emptyStateIcon('heroicon-o-truck');
    }
}
