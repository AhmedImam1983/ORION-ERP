<?php

namespace App\Filament\Admin\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الفرع')
                ->description('البيانات الأساسية التي تعرّف الفرع داخل Orion ERP.')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    TextInput::make('code')
                        ->label('كود الفرع')
                        ->placeholder('مثال: BR-RUH')
                        ->helperText('كود مختصر وفريد للفرع.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    TextInput::make('name')
                        ->label('اسم الفرع')
                        ->placeholder('مثال: الفرع الرئيسي — الرياض')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('name_en')
                        ->label('اسم الفرع بالإنجليزية')
                        ->placeholder('Riyadh Main Branch')
                        ->default(null)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('التليفون')
                        ->placeholder('01xxxxxxxxx')
                        ->tel()
                        ->default(null)
                        ->maxLength(50),

                    TextInput::make('manager')
                        ->label('مدير الفرع')
                        ->placeholder('اسم المسؤول عن الفرع')
                        ->default(null)
                        ->maxLength(255),

                    Select::make('warehouse_id')
                        ->label('المخزن الافتراضي')
                        ->relationship('warehouse', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('بدون مخزن افتراضي')
                        ->helperText('المخزن الذي سيستخدم افتراضيًا للعمليات المرتبطة بهذا الفرع.')
                        ->default(null),

                    TextInput::make('address')
                        ->label('العنوان')
                        ->placeholder('المدينة، الحي، الشارع...')
                        ->default(null)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('التشغيل والإعدادات')
                ->description('حدد حالة الفرع وما إذا كان هو الفرع الرئيسي للنظام.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Toggle::make('is_active')
                        ->label('الفرع نشط')
                        ->helperText('الفروع النشطة متاحة للاستخدام في العمليات اليومية.')
                        ->default(true),

                    Toggle::make('is_main')
                        ->label('الفرع الرئيسي')
                        ->helperText('وفق إعداد النظام الحالي يفترض وجود فرع رئيسي واحد فقط.'),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('أي ملاحظات تشغيلية أو إدارية خاصة بالفرع...')
                        ->rows(4)
                        ->default(null)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
