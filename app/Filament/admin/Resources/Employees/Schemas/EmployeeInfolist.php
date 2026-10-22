<?php

namespace App\Filament\Admin\Resources\Employees\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية')
                ->description('هوية الموظف وبيانات التواصل المسجلة في النظام.')
                ->schema([
                    ImageEntry::make('image')
                        ->label('الصورة')
                        ->circular()
                        ->placeholder('لا توجد صورة'),
                    TextEntry::make('code')
                        ->label('كود الموظف')
                        ->copyable()
                        ->icon('heroicon-o-identification'),
                    TextEntry::make('name')
                        ->label('اسم الموظف')
                        ->weight('bold')
                        ->size('lg'),
                    TextEntry::make('name_en')
                        ->label('الاسم بالإنجليزية')
                        ->placeholder('—'),
                    TextEntry::make('national_id')
                        ->label('الرقم القومي')
                        ->copyable()
                        ->placeholder('—'),
                    TextEntry::make('phone')
                        ->label('التليفون')
                        ->copyable()
                        ->icon('heroicon-o-phone')
                        ->placeholder('—'),
                    TextEntry::make('email')
                        ->label('البريد الإلكتروني')
                        ->copyable()
                        ->icon('heroicon-o-envelope')
                        ->placeholder('—'),
                    TextEntry::make('fingerprint_id')
                        ->label('رقم البصمة')
                        ->placeholder('—'),
                    TextEntry::make('address')
                        ->label('العنوان')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('بيانات الوظيفة')
                ->description('الوضع الوظيفي ونوع الدوام وتاريخ الالتحاق.')
                ->schema([
                    TextEntry::make('department')
                        ->label('القسم')
                        ->badge()
                        ->color('primary')
                        ->placeholder('غير محدد'),
                    TextEntry::make('job_title')
                        ->label('المسمى الوظيفي')
                        ->placeholder('—'),
                    TextEntry::make('employment_type')
                        ->label('نوع التوظيف')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match($state) {
                            'full_time' => 'دوام كامل',
                            'part_time' => 'دوام جزئي',
                            'contract' => 'عقد',
                            default => $state,
                        })
                        ->color(fn ($state) => match($state) {
                            'full_time' => 'primary',
                            'part_time' => 'info',
                            'contract' => 'warning',
                            default => 'gray',
                        }),
                    TextEntry::make('hire_date')
                        ->label('تاريخ التعيين')
                        ->date('d/m/Y')
                        ->icon('heroicon-o-calendar-days')
                        ->placeholder('—'),
                    TextEntry::make('working_hours')
                        ->label('ساعات العمل')
                        ->suffix(' ساعة')
                        ->placeholder('—'),
                    TextEntry::make('status')
                        ->label('الحالة')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match($state) {
                            'active' => 'نشط',
                            'inactive' => 'غير نشط',
                            'suspended' => 'موقوف',
                            default => $state,
                        })
                        ->color(fn ($state) => match($state) {
                            'active' => 'success',
                            'inactive' => 'gray',
                            'suspended' => 'danger',
                            default => 'gray',
                        }),
                ])
                ->columns(2),

            Section::make('الراتب والتعويضات')
                ->description('تفاصيل الراتب الأساسي والبدلات وإجمالي التعويض الشهري.')
                ->schema([
                    TextEntry::make('basic_salary')
                        ->label('الراتب الأساسي')
                        ->money('SAR'),
                    TextEntry::make('allowances')
                        ->label('البدلات')
                        ->money('SAR'),
                    TextEntry::make('monthly_compensation')
                        ->label('الإجمالي الشهري')
                        ->state(fn ($record) => (float) ($record->basic_salary ?? 0) + (float) ($record->allowances ?? 0))
                        ->money('SAR')
                        ->weight('bold')
                        ->color('success'),
                ])
                ->columns(3),

            Section::make('ملاحظات وبيانات النظام')
                ->schema([
                    TextEntry::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('لا توجد ملاحظات')
                        ->columnSpanFull(),
                    TextEntry::make('created_at')
                        ->label('تاريخ إنشاء السجل')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-o-calendar-days'),
                    TextEntry::make('updated_at')
                        ->label('آخر تحديث')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-o-clock'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }
}
