<?php

namespace App\Filament\Admin\Resources\Warehouses\Schemas;

use App\Models\Warehouse;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المخزن')
                    ->description('البيانات التعريفية والتشغيلية الأساسية للمخزن.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم المخزن')
                            ->weight('bold'),

                        TextEntry::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->placeholder('غير مسجل'),

                        TextEntry::make('code')
                            ->label('كود المخزن')
                            ->badge()
                            ->color('primary')
                            ->copyable(),

                        TextEntry::make('stocks_count')
                            ->label('عدد الأصناف بالمخزن')
                            ->state(fn (Warehouse $record): int => $record->stocks()->count())
                            ->badge()
                            ->color('info'),

                        TextEntry::make('location')
                            ->label('الموقع')
                            ->placeholder('غير محدد'),

                        TextEntry::make('manager')
                            ->label('مدير المخزن')
                            ->placeholder('غير محدد'),

                        TextEntry::make('phone')
                            ->label('التليفون')
                            ->placeholder('غير مسجل')
                            ->copyable(),
                    ])
                    ->columns(2),

                Section::make('الحالة والإعدادات')
                    ->description('حالة المخزن وإعداداته المستخدمة أثناء التشغيل.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('الحالة')
                            ->formatStateUsing(fn ($state): string => $state ? 'نشط' : 'غير نشط')
                            ->badge()
                            ->color(fn ($state): string => $state ? 'success' : 'gray')
                            ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),

                        TextEntry::make('is_default')
                            ->label('نوع المخزن')
                            ->formatStateUsing(fn ($state): string => $state ? 'المخزن الافتراضي' : 'مخزن عادي')
                            ->badge()
                            ->color(fn ($state): string => $state ? 'warning' : 'gray')
                            ->icon(fn ($state): string => $state ? 'heroicon-m-star' : 'heroicon-m-building-storefront'),

                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('بيانات النظام')
                    ->description('تاريخ إنشاء السجل وآخر تعديل تم عليه.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإضافة')
                            ->dateTime('d/m/Y - h:i A')
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('d/m/Y - h:i A')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}
