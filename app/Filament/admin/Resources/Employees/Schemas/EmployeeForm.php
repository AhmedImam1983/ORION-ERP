<?php

namespace App\Filament\Admin\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية')
                ->description('بيانات الهوية والتواصل المستخدمة في ملف الموظف داخل Orion ERP.')
                ->schema([
                    TextInput::make('code')
                        ->label('كود الموظف')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->prefixIcon('heroicon-o-identification'),
                    TextInput::make('name')
                        ->label('اسم الموظف')
                        ->required()
                        ->prefixIcon('heroicon-o-user'),
                    TextInput::make('name_en')
                        ->label('الاسم بالإنجليزية')
                        ->default(null),
                    TextInput::make('national_id')
                        ->label('الرقم القومي')
                        ->unique(ignoreRecord: true)
                        ->default(null),
                    TextInput::make('phone')
                        ->label('التليفون')
                        ->tel()
                        ->default(null)
                        ->prefixIcon('heroicon-o-phone'),
                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->default(null)
                        ->prefixIcon('heroicon-o-envelope'),
                    TextInput::make('fingerprint_id')
                        ->label('رقم البصمة')
                        ->default(null),
                    Textarea::make('address')
                        ->label('العنوان')
                        ->default(null)
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('بيانات الوظيفة')
                ->description('القسم والمسمى ونظام التوظيف والحالة وساعات العمل.')
                ->schema([
                    TextInput::make('department')
                        ->label('القسم')
                        ->default(null)
                        ->prefixIcon('heroicon-o-building-office-2'),
                    TextInput::make('job_title')
                        ->label('المسمى الوظيفي')
                        ->default(null)
                        ->prefixIcon('heroicon-o-briefcase'),
                    Select::make('employment_type')
                        ->label('نوع التوظيف')
                        ->options([
                            'full_time' => 'دوام كامل',
                            'part_time' => 'دوام جزئي',
                            'contract' => 'عقد',
                        ])
                        ->default('full_time')
                        ->required(),
                    DatePicker::make('hire_date')
                        ->label('تاريخ التعيين')
                        ->native(false),
                    Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'active' => 'نشط',
                            'inactive' => 'غير نشط',
                            'suspended' => 'موقوف',
                        ])
                        ->default('active')
                        ->required(),
                    TextInput::make('working_hours')
                        ->label('ساعات العمل اليومية')
                        ->numeric()
                        ->suffix('ساعة')
                        ->default(8),
                ])
                ->columns(2),

            Section::make('الراتب والتعويضات')
                ->description('القيم الشهرية الأساسية المستخدمة في بيانات الرواتب.')
                ->schema([
                    TextInput::make('basic_salary')
                        ->label('الراتب الأساسي')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0),
                    TextInput::make('allowances')
                        ->label('البدلات')
                        ->numeric()
                        ->prefix('ر.س')
                        ->default(0),
                ])
                ->columns(2),

            Section::make('الصورة والملاحظات')
                ->description('صورة الموظف وأي ملاحظات إدارية مرتبطة بملفه.')
                ->schema([
                    FileUpload::make('image')
                        ->label('صورة الموظف')
                        ->image()
                        ->imageEditor()
                        ->directory('employees')
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->default(null)
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
