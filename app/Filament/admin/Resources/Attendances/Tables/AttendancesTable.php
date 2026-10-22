<?php

namespace App\Filament\Admin\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('check_in')
                    ->label('الحضور')
                    ->time('H:i'),
                TextColumn::make('check_out')
                    ->label('الانصراف')
                    ->time('H:i'),
                TextColumn::make('working_hours')
                    ->label('ساعات العمل')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('overtime_hours')
                    ->label('ساعات إضافية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('late_minutes')
                    ->label('دقائق التأخير')
                    ->numeric()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'present'     => 'حاضر',
                        'absent'      => 'غائب',
                        'late'        => 'متأخر',
                        'early_leave' => 'خروج مبكر',
                        'vacation'    => 'إجازة',
                        'mission'     => 'مأمورية',
                        default       => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'present'  => 'success',
                        'absent'   => 'danger',
                        'late'     => 'warning',
                        'vacation' => 'info',
                        default    => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')
                    ->options([
                        'present'     => 'حاضر',
                        'absent'      => 'غائب',
                        'late'        => 'متأخر',
                        'early_leave' => 'خروج مبكر',
                        'vacation'    => 'إجازة',
                        'mission'     => 'مأمورية',
                    ]),
                SelectFilter::make('employee_id')->label('الموظف')
                    ->relationship('employee', 'name'),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('لا يوجد سجلات حضور')
            ->emptyStateDescription('ابدأ بتسجيل الحضور')
            ->emptyStateIcon('heroicon-o-clock');
    }
}