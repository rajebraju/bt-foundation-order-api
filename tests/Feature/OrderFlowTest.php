<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order_and_confirm()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\ProductSeeder::class);

        $customer = User::where('email','customer@example.com')->first();
        $variant = ProductVariant::first();

        $token = auth('api')->login($customer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['variant_id' => $variant->id, 'quantity' => 2]
                ],
                'shipping' => 0,
                'tax' => 0
            ]);

        $response->assertStatus(201);
        $orderId = $response->json('id');

        // Confirm as admin
        $admin = User::where('email','admin@example.com')->first();
        $adminToken = auth('api')->login($admin);
        $res2 = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson("/api/v1/orders/{$orderId}/confirm");
        $res2->assertStatus(200);

        $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => 'processing']);
    }
}
