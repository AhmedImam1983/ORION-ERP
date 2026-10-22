<?php

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    /**
     * Orion ERP custom list layout.
     */
    protected string $view = 'filament.admin.resources.categories.pages.list-categories';

    /**
     * The default Filament page header is replaced by the Orion header in Blade.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Lightweight summary cards for the categories page.
     */
    public function getCategoryStats(): array
    {
        $query = Category::query();

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
            'empty' => (clone $query)->whereDoesntHave('products')->count(),
        ];
    }
}
