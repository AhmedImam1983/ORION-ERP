<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductItemTypeTest extends TestCase
{
    public function test_service_is_not_a_stock_item(): void
    {
        $product = new Product(['item_type' => 'service']);

        $this->assertTrue($product->isService());
        $this->assertFalse($product->isStockItem());
    }

    public function test_stock_and_legacy_default_products_remain_stock_items(): void
    {
        $stock = new Product(['item_type' => 'stock']);
        $legacy = new Product();

        $this->assertFalse($stock->isService());
        $this->assertTrue($stock->isStockItem());
        $this->assertFalse($legacy->isService());
        $this->assertTrue($legacy->isStockItem());
    }
}
