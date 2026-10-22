<?php

namespace App\Filament\Admin\Resources\Employees\Tables;

use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->size(34),

                TextColumn::make('name')
                    ->label('الموظف')
                    ->description(fn ($record) => trim(($record->code ?? '') . (filled($record->job_title) ? ' · ' . $record->job_title : '')))
                    ->searchable(['name', 'code', 'job_title'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('department')
                    ->label('القسم')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->placeholder('غير محدد'),

                TextColumn::make('phone')
                    ->label('التليفون')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('basic_salary')
                    ->label('الراتب الأساسي')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('employment_type')
                    ->label('نوع التوظيف')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'full_time' => 'دوام كامل',
                        'part_time' => 'دوام جزئي',
                        'contract'  => 'عقد',
                        default     => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'full_time' => 'primary',
                        'part_time' => 'info',
                        'contract'  => 'warning',
                        default     => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active'    => 'نشط',
                        'inactive'  => 'غير نشط',
                        'suspended' => 'موقوف',
                        default     => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'active'    => 'success',
                        'inactive'  => 'gray',
                        'suspended' => 'danger',
                        default     => 'gray',
                    })
                    ->icon(fn ($state) => match($state) {
                        'active' => 'heroicon-m-check-circle',
                        'suspended' => 'heroicon-m-no-symbol',
                        default => 'heroicon-m-minus-circle',
                    }),

                TextColumn::make('hire_date')
                    ->label('تاريخ التعيين')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('job_title')
                    ->label('المسمى الوظيفي')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('allowances')
                    ->label('البدلات')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                    ]),

                SelectFilter::make('employment_type')
                    ->label('نوع التوظيف')
                    ->options([
                        'full_time' => 'دوام كامل',
                        'part_time' => 'دوام جزئي',
                        'contract' => 'عقد',
                    ]),

                SelectFilter::make('department')
                    ->label('القسم')
                    ->options(fn () => Employee::query()
                        ->whereNotNull('department')
                        ->where('department', '!=', '')
                        ->distinct()
                        ->orderBy('department')
                        ->pluck('department', 'department')
                        ->all()),

                TrashedFilter::make()->label('المحذوفات'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                    ForceDeleteBulkAction::make()->label('حذف نهائي'),
                    RestoreBulkAction::make()->label('استعادة'),
                ]),
            ])
            ->searchPlaceholder('ابحث بالاسم أو الكود أو المسمى الوظيفي أو الهاتف...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا يوجد موظفون بعد')
            ->emptyStateDescription('أضف أول موظف وابدأ بناء ملف الموارد البشرية.')
            ->emptyStateIcon('heroicon-o-identification');
    }
}
