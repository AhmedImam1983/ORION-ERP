<?php

namespace App\Filament\Admin\Resources\PurchaseOrders;

use App\Filament\Admin\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Admin\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Admin\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Admin\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Filament\Admin\Resources\PurchaseOrders\Tables\PurchaseOrdersTable;
use App\Models\PurchaseOrder;
use App\Filament\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    use HasResourcePermissions;

    protected static function getPermissionPrefix(): string
    {
        return 'purchase_orders';
    }
    protected static ?string $model = PurchaseOrder::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
    protected static ?string $navigationLabel = 'طلبات الشراء';
    protected static ?string $modelLabel = 'طلب شراء';
    protected static ?string $pluralModelLabel = 'طلبات الشراء';
    protected static ?string $recordTitleAttribute = 'order_number';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'المشتريات · Purchases';
    }

    public static function form(Schema $schema): Schema
    {
        return PurchaseOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'edit'   => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', ['draft', 'sent', 'confirmed'])->count();
        return $count ?: null;
    }
}