<?php

namespace App\Filament\Admin\Resources\PosSessions;

use App\Filament\Admin\Resources\PosSessions\Pages\ListPosSessions;
use App\Filament\Admin\Resources\PosSessions\Pages\ViewPosSession;
use App\Filament\Admin\Resources\PosSessions\Schemas\PosSessionForm;
use App\Filament\Admin\Resources\PosSessions\Tables\PosSessionsTable;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\PosSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PosSessionResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'pos_sessions';
    }

    protected static ?string $model = PosSession::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected static ?string $navigationLabel = 'جلسات الكاشير';
    protected static ?string $modelLabel = 'جلسة كاشير';
    protected static ?string $pluralModelLabel = 'جلسات الكاشير';
    protected static ?string $recordTitleAttribute = 'session_number';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات · Sales';
    }

    public static function form(Schema $schema): Schema
    {
        return PosSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosSessions::route('/'),
            'view'  => ViewPosSession::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $open = static::getModel()::where('status', 'open')->count();

        return $open > 0 ? $open . ' مفتوحة' : null;
    }
}
