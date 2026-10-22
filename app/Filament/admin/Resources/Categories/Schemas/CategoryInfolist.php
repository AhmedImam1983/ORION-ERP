<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المجموعة')
                    ->description('البيانات التعريفية الأساسية للمجموعة.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم المجموعة')
                            ->weight('bold'),

                        TextEntry::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->placeholder('غير مسجل'),

                        TextEntry::make('code')
                            ->label('كود المجموعة')
                            ->placeholder('غير محدد')
                            ->badge()
                            ->color('primary')
                            ->copyable(),

                        TextEntry::make('products_count')
                            ->label('عدد الأصناف')
                            ->state(fn (Category $record): int => $record->products()->count())
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('description')
                            ->label('الوصف')
                            ->placeholder('لا يوجد وصف للمجموعة')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('الحالة والصورة')
                    ->description('حالة المجموعة والصورة المرتبطة بها.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('صورة المجموعة')
                            ->placeholder('لا توجد صورة')
                            ->imageHeight(110)
                            ->circular(),

                        TextEntry::make('is_active')
                            ->label('الحالة')
                            ->formatStateUsing(fn ($state): string => $state ? 'نشطة' : 'غير نشطة')
                            ->badge()
                            ->color(fn ($state): string => $state ? 'success' : 'gray')
                            ->icon(fn ($state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle'),
                    ])
                    ->columns(2),

                Section::make('بيانات النظام')
                    ->description('معلومات الإنشاء وآخر تعديل على السجل.')
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

                        TextEntry::make('deleted_at')
                            ->label('تاريخ الحذف')
                            ->dateTime('d/m/Y - h:i A')
                            ->color('danger')
                            ->visible(fn (Category $record): bool => $record->trashed()),
                    ])
                    ->columns(2),
            ]);
    }
}
