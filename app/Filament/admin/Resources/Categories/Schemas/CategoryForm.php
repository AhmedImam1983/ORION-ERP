<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->description('بيانات تعريف المجموعة التي ستظهر في المخزون والأصناف.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المجموعة')
                            ->placeholder('مثال: مستلزمات مطاعم')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->placeholder('Restaurant Supplies')
                            ->default(null)
                            ->maxLength(255),

                        TextInput::make('code')
                            ->label('كود المجموعة')
                            ->placeholder('مثال: REST-SUP')
                            ->helperText('اختياري، ويجب أن يكون غير مكرر إذا تم إدخاله.')
                            ->unique(ignoreRecord: true)
                            ->default(null)
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('الوصف')
                            ->placeholder('اكتب وصفًا مختصرًا للمجموعة واستخدامها...')
                            ->rows(4)
                            ->default(null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('الصورة والحالة')
                    ->description('الصورة اختيارية، ويمكن إيقاف المجموعة بدون حذفها.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة المجموعة')
                            ->image()
                            ->directory('categories')
                            ->imagePreviewHeight('140')
                            ->openable()
                            ->downloadable(),

                        Toggle::make('is_active')
                            ->label('المجموعة نشطة')
                            ->helperText('عند الإيقاف تظل المجموعة محفوظة ولكن تظهر كغير نشطة.')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
