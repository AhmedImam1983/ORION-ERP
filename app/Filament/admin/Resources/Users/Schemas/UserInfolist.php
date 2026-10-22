<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المستخدم')
                ->description('معلومات الحساب الأساسية وحالة البريد الإلكتروني.')
                ->schema([
                    TextEntry::make('name')
                        ->label('اسم المستخدم')
                        ->weight('bold')
                        ->size('lg')
                        ->icon('heroicon-o-user-circle'),
                    TextEntry::make('email')
                        ->label('البريد الإلكتروني')
                        ->copyable()
                        ->icon('heroicon-o-envelope'),
                    TextEntry::make('email_verification_status')
                        ->label('حالة التحقق')
                        ->state(fn ($record): bool => filled($record->email_verified_at))
                        ->formatStateUsing(fn ($state) => $state ? 'تم التحقق من البريد' : 'البريد غير موثّق')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'gray')
                        ->icon(fn ($state) => $state ? 'heroicon-o-check-badge' : 'heroicon-o-envelope'),
                ])
                ->columns(2),

            Section::make('الدور ونطاق الوصول')
                ->description('الأدوار الممنوحة للمستخدم والفرع المسؤول عنه.')
                ->schema([
                    TextEntry::make('roles.name')
                        ->label('الأدوار')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match($state) {
                            'super_admin' => 'مدير النظام',
                            'admin'       => 'مدير',
                            'cashier'     => 'كاشير',
                            'accountant'  => 'محاسب',
                            'storekeeper' => 'أمين مخزن',
                            default       => $state,
                        })
                        ->color(fn ($state) => match($state) {
                            'super_admin' => 'danger',
                            'admin'       => 'warning',
                            'cashier'     => 'success',
                            'accountant'  => 'info',
                            'storekeeper' => 'primary',
                            default       => 'gray',
                        }),
                    TextEntry::make('branch.name')
                        ->label('الفرع')
                        ->badge()
                        ->color('info')
                        ->placeholder('كل الفروع'),
                ])
                ->columns(2),

            Section::make('بيانات النظام')
                ->description('معلومات التوقيت الخاصة بإنشاء الحساب وآخر تحديث.')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('تاريخ الإنشاء')
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
