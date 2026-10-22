<?php

namespace App\Filament\Admin\Resources\Salaries\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

class SalaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الراتب')->schema([
                Select::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($state) {
                            $employee = Employee::find($state);
                            if ($employee) {
                                $set('basic_salary', $employee->basic_salary);
                                $set('insurance_deduction', round($employee->basic_salary * 0.11, 2));
                                self::calculateNet($set, $get);
                            }
                        }
                    }),
                Select::make('month')
                    ->label('الشهر')
                    ->options([
                        1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل',
                        5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس',
                        9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر',
                    ])
                    ->required()
                    ->default(now()->month),
                TextInput::make('year')
                    ->label('السنة')
                    ->required()
                    ->numeric()
                    ->default(now()->year),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft'    => 'مسودة',
                        'approved' => 'معتمد',
                        'paid'     => 'مصروف',
                    ])
                    ->default('draft')
                    ->required(),
            ])->columns(2),

            Section::make('المستحقات')->schema([
                TextInput::make('basic_salary')
                    ->label('المرتب الأساسي')
                    ->required()
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('allowances')
                    ->label('البدلات')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('overtime_hours')
                    ->label('ساعات إضافية')
                    ->numeric()
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $hourlyRate = floatval($get('basic_salary')) / 30 / 8;
                        $set('overtime_amount', round($hourlyRate * floatval($state) * 1.5, 2));
                        self::calculateNet($set, $get);
                    }),
                TextInput::make('overtime_amount')
                    ->label('قيمة الإضافي')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('bonus')
                    ->label('مكافأة')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
            ])->columns(2),

            Section::make('الخصومات')->schema([
                TextInput::make('absence_deduction')
                    ->label('خصم الغياب')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('late_deduction')
                    ->label('خصم التأخير')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('insurance_deduction')
                    ->label('خصم التأمينات (11%)')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('tax_deduction')
                    ->label('خصم الضريبة')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
                TextInput::make('deductions')
                    ->label('خصومات أخرى')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateNet($set, $get)),
            ])->columns(2),

            Section::make('الصرف')->schema([
                TextInput::make('net_salary')
                    ->label('صافي المرتب')
                    ->numeric()
                    ->prefix('ر.س')
                    ->default(0)
                    ->readOnly(),
                Select::make('payment_method')
                    ->label('طريقة الصرف')
                    ->options([
                        'cash'     => 'نقدي',
                        'bank'     => 'تحويل بنكي',
                        'cheque'   => 'شيك',
                    ])
                    ->default('cash'),
                DatePicker::make('payment_date')
                    ->label('تاريخ الصرف'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->default(null)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    private static function calculateNet(Set $set, Get $get): void
    {
        $gross = floatval($get('basic_salary')) + floatval($get('allowances'))
            + floatval($get('overtime_amount')) + floatval($get('bonus'));

        $deductions = floatval($get('deductions')) + floatval($get('absence_deduction'))
            + floatval($get('late_deduction')) + floatval($get('insurance_deduction'))
            + floatval($get('tax_deduction'));

        $set('net_salary', round($gross - $deductions, 2));
    }
}
