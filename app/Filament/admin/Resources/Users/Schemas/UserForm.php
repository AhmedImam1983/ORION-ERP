<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المستخدم')
                ->description('البيانات الأساسية المستخدمة للدخول والتعريف بالمستخدم داخل Orion ERP.')
                ->schema([
                    TextInput::make('name')
                        ->label('اسم المستخدم')
                        ->required()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-user'),
                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-envelope'),
                    TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->revealable()
                        ->required(fn ($context) => $context === 'create')
                        ->minLength(8)
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->hint('8 أحرف على الأقل')
                        ->helperText('في شاشة التعديل اترك كلمة المرور فارغة إذا لم ترغب في تغييرها.')
                        ->prefixIcon('heroicon-o-key'),
                ])
                ->columns(2),

            Section::make('الدور والصلاحيات')
                ->description('حدد الدور الوظيفي الذي يتحكم في مستوى الوصول والصلاحيات داخل النظام.')
                ->schema([
                    Select::make('roles')
                        ->label('الدور الوظيفي')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->options(Role::all()->pluck('name', 'id')->map(fn ($name) => match($name) {
                            'super_admin' => 'مدير النظام',
                            'admin'       => 'مدير',
                            'cashier'     => 'كاشير',
                            'accountant'  => 'محاسب',
                            'storekeeper' => 'أمين مخزن',
                            default       => $name,
                        }))
                        ->searchable()
                        ->preload()
                        ->helperText('يمكن إسناد أكثر من دور للمستخدم عند الحاجة.'),
                ]),

            Section::make('الفرع والصلاحيات الجغرافية')
                ->description('ربط المستخدم بفرع محدد، أو تركه بدون فرع للمستخدمين الذين يشاهدون كل الفروع.')
                ->schema([
                    Select::make('branch_id')
                        ->label('الفرع المسؤول عنه')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('كل الفروع')
                        ->helperText('اتركه فارغاً للمدير الذي يرى كل الفروع، وحدده للكاشير وأمين المخزن عند الحاجة.')
                        ->nullable(),
                ]),
        ]);
    }
}
