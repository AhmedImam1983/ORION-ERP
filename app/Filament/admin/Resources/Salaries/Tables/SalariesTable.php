<?php

namespace App\Filament\Admin\Resources\Salaries\Tables;

use App\Models\Employee;
use App\Models\Salary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->description(fn ($record) => collect([
                        $record->employee?->code,
                        $record->employee?->department,
                    ])->filter()->implode(' · '))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('month')
                    ->label('الفترة')
                    ->formatStateUsing(fn ($state) => [
                        1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                        7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
                    ][$state] ?? $state)
                    ->description(fn ($record) => (string) $record->year),

                TextColumn::make('basic_salary')
                    ->label('الأساسي')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('additions_total')
                    ->label('الإضافات')
                    ->state(fn ($record) => (float) $record->allowances + (float) $record->overtime_amount + (float) $record->bonus)
                    ->money('SAR')
                    ->color('success'),

                TextColumn::make('deductions_total')
                    ->label('الخصومات')
                    ->state(fn ($record) => (float) $record->deductions + (float) $record->absence_deduction + (float) $record->late_deduction + (float) $record->insurance_deduction + (float) $record->tax_deduction)
                    ->money('SAR')
                    ->color('danger'),

                TextColumn::make('net_salary')
                    ->label('صافي الراتب')
                    ->money('SAR')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft'    => 'مسودة',
                        'approved' => 'معتمد',
                        'paid'     => 'مصروف',
                        default    => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'paid'     => 'success',
                        'approved' => 'info',
                        'draft'    => 'warning',
                        default    => 'gray',
                    })
                    ->icon(fn ($state) => match($state) {
                        'paid' => 'heroicon-m-check-circle',
                        'approved' => 'heroicon-m-shield-check',
                        default => 'heroicon-m-clock',
                    }),

                TextColumn::make('payment_method')
                    ->label('طريقة الصرف')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => 'نقدي',
                        'bank' => 'تحويل بنكي',
                        'cheque' => 'شيك',
                        default => $state ?: '—',
                    })
                    ->color(fn ($state) => match($state) {
                        'bank' => 'info',
                        'cash' => 'success',
                        'cheque' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('payment_date')
                    ->label('تاريخ الصرف')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('allowances')
                    ->label('البدلات')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('overtime_amount')
                    ->label('قيمة الإضافي')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bonus')
                    ->label('المكافأة')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft'    => 'مسودة',
                        'approved' => 'معتمد',
                        'paid'     => 'مصروف',
                    ]),

                SelectFilter::make('month')
                    ->label('الشهر')
                    ->options([
                        1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                        7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
                    ]),

                SelectFilter::make('year')
                    ->label('السنة')
                    ->options(fn () => Salary::query()
                        ->whereNotNull('year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->all()),

                SelectFilter::make('department')
                    ->label('القسم')
                    ->options(fn () => Employee::query()
                        ->whereNotNull('department')
                        ->where('department', '!=', '')
                        ->distinct()
                        ->orderBy('department')
                        ->pluck('department', 'department')
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $department = $data['value'] ?? null;

                        return $query->when(
                            filled($department),
                            fn (Builder $query) => $query->whereHas(
                                'employee',
                                fn (Builder $employeeQuery) => $employeeQuery->where('department', $department)
                            )
                        );
                    }),

                SelectFilter::make('payment_method')
                    ->label('طريقة الصرف')
                    ->options([
                        'cash' => 'نقدي',
                        'bank' => 'تحويل بنكي',
                        'cheque' => 'شيك',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->searchPlaceholder('ابحث باسم الموظف...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد رواتب بعد')
            ->emptyStateDescription('أضف أول راتب ثم استخدم تقرير الرواتب للمراجعة والاعتماد والصرف.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
