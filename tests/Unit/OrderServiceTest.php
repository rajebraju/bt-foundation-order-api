<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\OrderService;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\Order;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_deducts_inventory_and_sets_processing()
    {
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\ProductSeeder::class);

        $customer = User::where('email','customer@email.com')->first();
        $variant = ProductVariant::first();

        $service = $this->app->make(OrderService::class);

        $order = $service->createOrder([
            'items'=>[['variant_id'=>$variant->id,'quantity'=>2]],
            'shipping'=>0,'tax'=>0
        ], $customer);

        $this->assertEquals('pending', $order->status);

        $service->confirmOrder($order->fresh());

        $this->assertDatabaseHas('orders',['id'=>$order->id,'status'=>'processing']);
        $this->assertDatabaseHas('product_variants',['id'=>$variant->id,'stock' => $variant->stock - 2]);
    }
}
