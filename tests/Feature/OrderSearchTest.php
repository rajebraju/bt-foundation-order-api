<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_search_their_orders()
    {
        $this->seed();

        $customer = User::whereEmail('customer@email.com')->first();
        Order::factory()->for($customer)->create(['order_number' => 'ORD-SEARCH-001']);
        Order::factory()->for($customer)->create(['order_number' => 'ORD-SEARCH-002']);

        $response = $this->actingAs($customer, 'api')
            ->getJson('/api/v1/orders?search=ORD-SEARCH');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['order_number' => 'ORD-SEARCH-001']);
    }

    public function test_pagination_works()
    {
        $this->seed();

        $customer = User::whereEmail('customer@email.com')->first();
        Order::factory()->count(20)->for($customer)->create();

        $response = $this->actingAs($customer, 'api')
            ->getJson('/api/v1/orders?page=1&per_page=10');

        $response->assertStatus(200)
                 ->assertJsonPath('meta.per_page', 10)
                 ->assertJsonPath('meta.total', 20);
    }
}