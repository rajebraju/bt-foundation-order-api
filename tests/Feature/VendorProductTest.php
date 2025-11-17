<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_sees_only_own_products()
    {
        $this->seed();

        $vendor1 = User::whereEmail('vendor@email.com')->first();
        $vendor2 = User::factory()->create(['email' => 'vendor2@email.com']);

        Product::factory()->create(['vendor_id' => $vendor1->id, 'name' => 'Vendor1 Product']);
        Product::factory()->create(['vendor_id' => $vendor2->id, 'name' => 'Vendor2 Product']);

        $response = $this->actingAs($vendor1, 'api')
            ->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonCount(1) 
                 ->assertJsonFragment(['name' => 'Vendor1 Product']);
    }

    public function test_vendor_cannot_create_product_for_other_vendor()
    {
        $this->seed();

        $vendor1 = User::whereEmail('vendor@email.com')->first();
        $vendor2 = User::factory()->create(['email' => 'vendor2@email.com']);

        $response = $this->actingAs($vendor1, 'api')
            ->postJson('/api/v1/products', [
                'name' => 'Unauthorized Product',
                'sku' => 'UNAUTH-001',
                'price' => 99.99,
                'vendor_id' => $vendor2->id, 
            ]);

        $response->assertStatus(422); 
    }
}