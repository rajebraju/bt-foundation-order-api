<?php

namespace Tests\Unit;

use App\Services\InventoryService;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deduct_stock_successfully()
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $service = new InventoryService();

        $result = $service->deductStock($variant, 3);

        $this->assertTrue($result);
        $variant->refresh();
        $this->assertEquals(7, $variant->stock);
    }

    public function test_deduct_stock_fails_if_insufficient()
    {
        $variant = ProductVariant::factory()->create(['stock' => 2]);
        $service = new InventoryService();

        $result = $service->deductStock($variant, 5);

        $this->assertFalse($result);
        $variant->refresh();
        $this->assertEquals(2, $variant->stock); // No change
    }

    public function test_restore_stock_successfully()
    {
        $variant = ProductVariant::factory()->create(['stock' => 7]);
        $service = new InventoryService();

        $result = $service->restoreStock($variant, 3);

        $this->assertTrue($result);
        $variant->refresh();
        $this->assertEquals(10, $variant->stock);
    }
}