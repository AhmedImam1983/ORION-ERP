<?php

namespace App\Filament\Admin\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المخزن')
                ->description('البيانات الأساسية التي تعرّف المخزن داخل النظام.')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    TextInput::make('code')
                        ->label('كود المخزن')
                        ->placeholder('مثال: WH-RUH')
                        ->helperText('كود مختصر وفريد للمخزن.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    TextInput::make('name')
                        ->label('اسم المخزن')
                        ->placeholder('مثال: المستودع الرئيسي')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('name_en')
                        ->label('اسم المخزن بالإنجليزية')
                        ->placeholder('Main Warehouse')
                        ->default(null)
                        ->maxLength(255),

                    TextInput::make('location')
                        ->label('الموقع')
                        ->placeholder('المدينة / المنطقة / العنوان')
                        ->default(null)
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('التليفون')
                        ->placeholder('05xxxxxxxx')
                        ->tel()
                        ->default(null)
                        ->maxLength(50),

                    TextInput::make('manager')
                        ->label('مدير المخزن')
                        ->placeholder('اسم المسؤول عن المخزن')
                        ->default(null)
                        ->maxLength(255),
                ])
                ->columns(2),

            Section::make('التشغيل والإعدادات')
                ->description('تحكم في حالة المخزن وما إذا كان هو المخزن الافتراضي للنظام.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Toggle::make('is_active')
                        ->label('المخزن نشط')
                        ->helperText('المخزن النشط متاح للاستخدام في العمليات المخزنية.')
                        ->default(true),

                    Toggle::make('is_default')
                        ->label('المخزن الافتراضي')
                        ->helperText('سيتم استخدامه تلقائياً في الفواتير عندما لا يتم اختيار مخزن آخر.')
                        ->default(false),

                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('أي ملاحظات تشغيلية أو إدارية خاصة بالمخزن...')
                        ->rows(4)
                        ->default(null)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
