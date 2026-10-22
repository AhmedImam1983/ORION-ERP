<?php

namespace App\Filament\Admin\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الحضور')->schema([
                Select::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                TimePicker::make('check_in')
                    ->label('وقت الحضور'),
                TimePicker::make('check_out')
                    ->label('وقت الانصراف'),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'present'     => 'حاضر',
                        'absent'      => 'غائب',
                        'late'        => 'متأخر',
                        'early_leave' => 'خروج مبكر',
                        'vacation'    => 'إجازة',
                        'mission'     => 'مأمورية',
                    ])
                    ->default('present')
                    ->required(),
            ])->columns(2),

            Section::make('تفاصيل الوقت')->schema([
                TextInput::make('working_hours')
                    ->label('ساعات العمل')
                    ->numeric()
                    ->default(0),
                TextInput::make('overtime_hours')
                    ->label('ساعات إضافية')
                    ->numeric()
                    ->default(0),
                TextInput::make('late_minutes')
                    ->label('دقائق التأخير')
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->default(null)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}