<?php

namespace App\Filament\Admin\Resources\StockCounts;

use App\Filament\Admin\Resources\StockCounts\Pages\CreateStockCount;
use App\Filament\Admin\Resources\StockCounts\Pages\EditStockCount;
use App\Filament\Admin\Resources\StockCounts\Pages\ListStockCounts;
use App\Filament\Admin\Resources\StockCounts\Pages\ViewStockCount;
use App\Filament\Admin\Resources\StockCounts\Schemas\StockCountInfolist;
use App\Models\StockCount;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockCountResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'stock_counts';
    }
    protected static ?string $model = StockCount::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'الجرد المخزني';
    protected static ?string $modelLabel = 'جرد';
    protected static ?string $pluralModelLabel = 'الجرد المخزني';
    protected static ?string $recordTitleAttribute = 'count_number';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'المخازن · Warehouse';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('بيانات الجرد')->schema([
                \Filament\Forms\Components\TextInput::make('count_number')
                    ->label('رقم الجرد')
                    ->default(fn() => StockCount::generateNumber())
                    ->disabled()
                    ->dehydrated(),
                Select::make('type')
                    ->label('نوع الجرد')
                    ->options(['full' => 'جرد كامل', 'partial' => 'جرد جزئي'])
                    ->default('full')
                    ->required()
                    ->live(),
                Select::make('warehouse_id')
                    ->label('المخزن')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('category_id')
                    ->label('المجموعة (للجرد الجزئي)')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('type') === 'partial'),
                DatePicker::make('count_date')
                    ->label('تاريخ الجرد')
                    ->default(today())
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockCountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('count_number')
                    ->label('رقم الجرد')
                    ->weight('bold')
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state === 'full' ? 'كامل' : 'جزئي')
                    ->color(fn($state) => $state === 'full' ? 'info' : 'warning'),
                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->placeholder('-'),
                TextColumn::make('count_date')
                    ->label('تاريخ الجرد')
                    ->date('d/m/Y'),
                TextColumn::make('items_count')
                    ->label('عدد الأصناف')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft'       => 'مسودة',
                        'in_progress' => 'جاري',
                        'completed'   => 'مكتمل',
                        'cancelled'   => 'ملغي',
                        default       => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'completed'   => 'success',
                        'in_progress' => 'warning',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'in_progress' => 'جاري',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغي',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('نوع الجرد')
                    ->options([
                        'full' => 'جرد كامل',
                        'partial' => 'جرد جزئي',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('المخزن')
                    ->relationship('warehouse', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                \Filament\Actions\ViewAction::make()->label('عرض'),
               \Filament\Actions\EditAction::make()->label('تعديل')
    ->visible(fn($record) => in_array($record->status, ['draft', 'in_progress'])),
            ])
            ->emptyStateHeading('لا توجد جرديات بعد')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListStockCounts::route('/'),
            'create' => CreateStockCount::route('/create'),
            'view'   => ViewStockCount::route('/{record}'),
            'edit'   => EditStockCount::route('/{record}/edit'),
        ];
    }
}