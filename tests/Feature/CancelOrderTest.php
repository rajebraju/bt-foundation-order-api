<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_cancel_order()
    {
        $this->seed();
        
        $customer = User::whereEmail('customer@email.com')->first();
        $order = Order::factory()->for($customer)->create(['status' => 'processing']);

        $response = $this->actingAs($customer, 'api')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_admin_can_cancel_order_and_restore_inventory()
    {
        $this->seed();

        $variant = ProductVariant::first();
        $variant->update(['stock' => 10]);

        $order = Order::factory()
            ->for(User::whereEmail('customer@email.com')->first())
            ->hasItems(1, ['variant_id' => $variant->id, 'quantity' => 3])
            ->create(['status' => 'processing']);

        $admin = User::whereEmail('admin@email.com')->first();
        $response = $this->actingAs($admin, 'api')
            ->patchJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200);

        $variant->refresh();
        $this->assertEquals(10, $variant->stock); // 7 used → 3 restored = 10
    }
}