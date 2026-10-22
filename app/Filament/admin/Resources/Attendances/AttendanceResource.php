<?php

namespace App\Filament\Admin\Resources\Attendances;

use App\Filament\Admin\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Admin\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Admin\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Admin\Resources\Attendances\Pages\ViewAttendance;
use App\Filament\Admin\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Admin\Resources\Attendances\Schemas\AttendanceInfolist;
use App\Filament\Admin\Resources\Attendances\Tables\AttendancesTable;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Traits\HasResourcePermissions;
class AttendanceResource extends Resource
{
use HasResourcePermissions;
    protected static ?string $model = Attendance::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;
    protected static ?string $navigationLabel = 'الحضور والانصراف';
    protected static ?string $modelLabel = 'سجل حضور';
    protected static ?string $pluralModelLabel = 'الحضور والانصراف';
    protected static ?string $recordTitleAttribute = 'date';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceInfolist::configure($schema);
    }
public static function getNavigationGroup(): ?string
{
    return 'الموارد البشرية · HR';
}

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListAttendances::route('/'),
            'create' => CreateAttendance::route('/create'),
            'view'   => ViewAttendance::route('/{record}'),
            'edit'   => EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('date', today())->count() ?: null;
    }
}