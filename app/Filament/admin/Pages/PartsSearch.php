<?php

namespace App\Filament\Admin\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class PartsSearch extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'بحث سريع';

    protected static ?string $title = 'بحث سريع';

    protected static ?string $slug = 'quick-search';

    protected string $view = 'filament.admin.pages.parts-search';

    protected static ?int $navigationSort = 1;

    public string $search = '';

    public ?int $brandId = null;

    public ?int $categoryId = null;

    public ?int $warehouseId = null;

    public int $quantity = 1;

    public array $cart = [];

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات · Sales';
    }

    public function getProducts(): Collection
    {
        $term = trim($this->search);

        if ($term === '' && !$this->brandId && !$this->categoryId && !$this->warehouseId) {
            return new Collection();
        }

        return Product::query()
            ->with([
                'brand',
                'category',
                'warehouse',
                'warehouseStocks.warehouse',
                'warehouseStocks.locations',
                'vehicles',
            ])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('name_en', 'like', "%{$term}%")
                        ->orWhere('part_number', 'like', "%{$term}%")
                        ->orWhere('part_number_normalized', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%")
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($this->brandId, fn ($q) => $q->where('brand_id', $this->brandId))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->where('is_active', true)
            ->latest('id')
            ->limit(36)
            ->get();
    }

    public function getBrands(): Collection
    {
        return Brand::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getCategories(): Collection
    {
        return Category::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getWarehouses(): Collection
    {
        return Warehouse::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
    }

    public function getLocationSummary(Product $product): string
    {
        $locations = $product->warehouseStocks
            ->flatMap(fn ($stock) => $stock->locations)
            ->pluck('location_code')
            ->filter()
            ->unique()
            ->values();

        return $locations->implode('، ');
    }

    public function getVehicleSummary(Product $product): string
    {
        if ($product->vehicles->isEmpty()) {
            return '';
        }

        return $product->vehicles
            ->take(2)
            ->map(function ($vehicle) {
                $years = ($vehicle->year_from || $vehicle->year_to)
                    ? ' ' . ($vehicle->year_from ?? '؟') . '-' . ($vehicle->year_to ?? '؟')
                    : '';

                return trim($vehicle->make . ' ' . ($vehicle->model ?? '') . $years);
            })
            ->implode(' • ');
    }

    public function addToCart(int $productId): void
    {
        $product = Product::query()->find($productId);

        if (!$product) {
            return;
        }

        $key = (string) $product->id;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $this->cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'part_number' => $product->part_number,
                'brand' => $product->brand?->name,
                'unit' => $product->unit,
                'quantity' => 1,
                'unit_price' => (float) $product->sale_price,
                'total' => (float) $product->sale_price,
            ];
        }

        $this->recalculateCart();
    }

    public function incrementCartItem(int $productId): void
    {
        $key = (string) $productId;

        if (!isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['quantity']++;
        $this->recalculateCart();
    }

    public function decrementCartItem(int $productId): void
    {
        $key = (string) $productId;

        if (!isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['quantity']--;

        if ($this->cart[$key]['quantity'] <= 0) {
            unset($this->cart[$key]);
        }

        $this->recalculateCart();
    }

    public function removeCartItem(int $productId): void
    {
        unset($this->cart[(string) $productId]);
        $this->recalculateCart();
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function recalculateCart(): void
    {
        foreach ($this->cart as $key => $item) {
            $this->cart[$key]['total'] = round(
                $item['quantity'] * $item['unit_price'],
                2
            );
        }
    }

    public function getCartCountProperty(): int
    {
        return (int) collect($this->cart)->sum('quantity');
    }

    public function getCartTotalProperty(): float
    {
        return round((float) collect($this->cart)->sum('total'), 2);
    }

    public function notifySale(): void
    {
        if (!$this->cart) {
            Notification::make()
                ->title('السلة فارغة')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('السلة جاهزة للفوترة')
            ->body('راجع الأصناف ثم أكمل الفاتورة من شاشة المبيعات.')
            ->success()
            ->send();
    }
}
